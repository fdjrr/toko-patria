<?php

namespace App\Observers;

use App\Models\Customer;

class CustomerObserver
{
    public function creating(Customer $customer)
    {
        $lastCustomer = Customer::withTrashed()
            ->withTrashed()
            ->orderBy('id', 'desc')
            ->first();

        if ($lastCustomer) {
            $lastNumber = (int) str_replace('CUST-', '', $lastCustomer->code);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $customer->code = 'CUST-'.str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
