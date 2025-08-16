<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Laravolt\Indonesia\Models\City as BaseModel;

class City extends BaseModel
{
    public function scopeFilter(Builder $query, array $filters = [])
    {
        $province_id = $filters['province_id'] ?? null;
        $search = $filters['search'] ?? null;

        $query->when($province_id, function ($query) use ($province_id) {
            $query->whereHas('province', fn ($query) => $query->where('id', $province_id));
        });

        $query->when($search, function ($query) use ($search) {
            $query
                ->whereLike('code', "%$search%")
                ->orWhereLike('name', "%$search%");
        });
    }
}
