<?php

namespace App\Observers;

use App\Models\Transaction;

class TransactionObserver
{
    public function creating(Transaction $transaction)
    {
        $today = now()->format('Ymd');

        $lastTransaction = Transaction::query()
            ->whereDate('transaction_date', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransaction) {
            $lastNumber = (int) substr($lastTransaction->transaction_code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $transaction->code = 'TRX-'.$today.'-'.str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
