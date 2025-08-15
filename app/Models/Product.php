<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';

    protected $guarded = ['id'];

    public function scopeFilter(Builder $query, array $filters = [])
    {
        $search      = $filters['search'] ?? null;
        $category_id = $filters['category_id'] ?? null;
        $brand_id    = $filters['brand_id'] ?? null;

        $query->when($search, fn (Builder $query, $search) => $query
            ->whereLike('code', "%$search%")
            ->orWhereLike('name', "%$search%")
            ->orWhereLike('description', "%$search%"));

        $query->when($category_id, fn (Builder $query, $category_id) => $query->where('category_id', $category_id));

        $query->when($brand_id, fn (Builder $query, $brand_id) => $query->where('brand_id', $brand_id));
    }

    /**
     * Get the product_category that owns the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product_category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id', 'id');
    }

    /**
     * Get the product_brand that owns the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product_brand(): BelongsTo
    {
        return $this->belongsTo(ProductBrand::class, 'brand_id', 'id');
    }
}
