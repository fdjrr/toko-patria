<?php

namespace App\Models;

use App\Observers\WarehouseObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(WarehouseObserver::class)]
class Warehouse extends Model
{
    use SoftDeletes;

    protected $table = 'warehouses';

    protected $guarded = ['id'];

    public function scopeFilter(Builder $query, array $filters = [])
    {
        $search = $filters['search'] ?? null;

        $query->when($search, fn (Builder $query, $search) => $query
            ->whereLike('code', "%$search%")
            ->orWhereLike('name', "%$search%"));
    }

    /**
     * Get all of the stocks for the Warehouse
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class, 'warehouse_id', 'id');
    }

    /**
     * The products that belong to the Warehouse
     */
    public function products(): BelongsToMany
    {
        return $this
            ->belongsToMany(Product::class, 'product_stocks')
            ->withPivot('qty')
            ->withTimestamps();
    }
}
