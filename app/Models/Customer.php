<?php

namespace App\Models;

use App\Observers\CustomerObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;

#[ObservedBy(CustomerObserver::class)]
class Customer extends Model
{
    use SoftDeletes;

    protected $table = 'customers';

    protected $guarded = ['id'];

    public function scopeFilter(Builder $query, array $filters = [])
    {
        $search = $filters['search'] ?? null;

        $query->when($search, fn (Builder $query, $search) => $query
            ->whereLike('name', "%$search%"));
    }

    /**
     * Get the city that owns the Customer
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    /**
     * Get the province that owns the Customer
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }

    /**
     * Get all of the product_reviews for the Customer
     */
    public function product_reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class, 'customer_id', 'id');
    }
}
