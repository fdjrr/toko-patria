<?php

namespace App\Observers;

use App\Models\Transaction;

class TransactionObserver
{
    public function creating(Transaction $transaction)
    {
        $now = now();
        $today = $now->format('Ymd');

        $lastTransaction = Transaction::query()
            ->whereDate('transaction_date', $now->format('Y-m-d'))
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransaction) {
            $lastCode = explode('-', $lastTransaction->code);
            $lastNumber = end($lastCode);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $transaction->code = 'TRX-'.$today.'-'.str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
