<?php

namespace App\Observers;

use App\Models\Warehouse;

class WarehouseObserver
{
    public function creating(Warehouse $warehouse)
    {
        $lastWarehouse = Warehouse::withTrashed()
            ->withTrashed()
            ->orderBy('id', 'desc')
            ->first();

        if ($lastWarehouse) {
            $lastNumber = (int) str_replace('WH-', '', $lastWarehouse->code);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $warehouse->code = 'WH-'.str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
