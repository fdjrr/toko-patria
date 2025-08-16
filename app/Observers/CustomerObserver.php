<?php

namespace App\Observers;

use App\Models\Customer;

class CustomerObserver
{
    public function creating(Customer $customer)
    {
        $lastCode = Customer::withTrashed()
            ->orderBy('id', 'desc')
            ->value('code');

        if ($lastCode) {
            $lastNumber = (int) str_replace('CUST-', '', $lastCode);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $customer->code = 'CUST-'.str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
