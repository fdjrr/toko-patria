<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use SoftDeletes;

    protected $table = 'product_categories';

    protected $guarded = ['id'];

    public function scopeFilter(Builder $query, array $filters = [])
    {
        $parent_id = $filters['parent_id'] ?? null;
        $search = $filters['search'] ?? null;

        $query->when($parent_id, fn (Builder $query, $parent_id) => $query
            ->where('parent_id', $parent_id));

        $query->when($search, fn (Builder $query, $search) => $query
            ->whereLike('name', "%$search%"));
    }

    /**
     * Get the parent that owns the ProductCategory
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id', 'id');
    }
}
