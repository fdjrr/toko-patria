<?php

namespace App\Models;

use App\Observers\TransactionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(TransactionObserver::class)]
class Transaction extends Model
{
    use SoftDeletes;

    protected $table = 'transactions';

    protected $guarded = ['id'];

    public function scopeFilter(Builder $query, array $filters = [])
    {
        $customer_id = $this->customer_id;
        $search = $filters['search'] ?? null;
        $start_date = $filters['start_date'] ?? null;
        $end_date = $filters['end_date'] ?? null;

        $query->when($customer_id, fn (Builder $query, $customer_id) => $query->where('customer_id', $customer_id));

        $query->when($search, fn (Builder $query, $search) => $query->whereLike('code', "%$search%"));

        $query->when($start_date && $end_date, fn (Builder $query, $start_date, $end_date) => $query->whereBetween('transaction_date', [$start_date, $end_date]));
    }

    /**
     * Get the customer that owns the Transaction
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * Get all of the transaction_items for the Transaction
     */
    public function transaction_items(): HasMany
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id', 'id');
    }
}
