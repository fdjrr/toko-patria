<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Throwable;

class CustomerController extends Controller
{
    public function index()
    {
        return view('pages.customers.index', [
            'page_meta' => [
                'title' => 'Customers',
            ],
        ]);
    }

    public function getCustomer(Request $request)
    {
        $search = $request->q;
        $page = $request->page;
        $rows = $request->rows;

        $customers = Customer::query()->filter([
            'search' => $search,
        ])->orderBy('name');

        $total = $customers->count();

        if ($page && $rows) {
            $customers = $customers
                ->limit($rows)
                ->offset(($page - 1) * $rows)
                ->get();
        } else {
            $customers = $customers->get();
        }

        $rows = collect($customers)->map(fn ($customer) => [
            'id' => $customer->id,
            'code' => $customer->code,
            'name' => $customer->name,
            'phone_number' => $customer->phone_number,
            'address' => $customer->address,
            'city_id' => $customer->city_id,
            'city_name' => $customer->city?->name,
            'province_id' => $customer->province_id,
            'province_name' => $customer->province?->name,
        ])->toArray();

        return response()->json([
            'rows' => $rows,
            'total' => $total,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $customer = Customer::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'city_id' => $request->city_id,
                'province_id' => $request->province_id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $customer,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request, Customer $customer)
    {
        try {
            $customer->update([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'city_id' => $request->city_id,
                'province_id' => $request->province_id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $customer,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();

            return response()->json([
                'success' => true,
                'data' => $customer,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
