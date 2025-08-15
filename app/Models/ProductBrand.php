<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductBrand extends Model
{
    use SoftDeletes;

    protected $table = 'product_brands';

    protected $guarded = ['id'];

    public function scopeFilter(Builder $query, array $filters = [])
    {
        $search = $filters['search'] ?? null;

        $query->when($search, fn (Builder $query, $search) => $query
            ->whereLike('name', "%$search%"));
    }
}
