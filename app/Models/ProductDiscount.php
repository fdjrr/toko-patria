<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductDiscount extends Model
{
    use SoftDeletes;

    protected $table = 'product_discounts';

    protected $guarded = ['id'];

    public function scopeFilter(Builder $query, array $filters = [])
    {
        $product_id = $filters['product_id'] ?? null;
        $product_codes = $filters['product_codes'] ?? null;
        $start_date = $filters['start_date'] ?? null;
        $end_date = $filters['end_date'] ?? null;

        $query->when($product_id, fn (Builder $query, $product_id) => $query->where('product_id', $product_id));

        $query->when($product_codes, fn (Builder $query, $product_codes) => $query->whereHas('product', fn ($query) => $query->whereIn('code', $product_codes)));

        $query->when($start_date && $end_date, function (Builder $query) use ($start_date, $end_date) {
            $query
                ->where('start_date', '<=', $end_date)
                ->where('end_date', '>=', $start_date);
        });
    }

    /**
     * Get the product that owns the ProductDiscount
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
