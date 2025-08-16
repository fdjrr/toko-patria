<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Laravolt\Indonesia\Models\Province as BaseModel;

class Province extends BaseModel
{
    public function scopeFilter(Builder $query, array $filters = [])
    {
        $search = $filters['search'] ?? null;

        $query->when($search, function ($query) use ($search) {
            $query
                ->whereLike('code', "%$search%")
                ->orWhereLike('name', "%$search%");
        });
    }
}
