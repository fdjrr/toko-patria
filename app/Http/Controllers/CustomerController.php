<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class CustomerController extends Controller
{
    public function index()
    {
        return view('pages.customer.index', [
            'page_meta' => [
                'title' => 'Customer',
            ],
        ]);
    }

    public function getCustomer(Request $request)
    {
        $search = $request->q;
        $page = $request->page;
        $rows = $request->rows;
        $sorts = $request->sort;
        $orders = $request->order;

        $customers = Customer::query()
            ->with(['province', 'city'])
            ->leftJoin('indonesia_provinces as province', 'customers.province_id', '=', 'province.id')
            ->leftJoin('indonesia_cities as city', 'customers.city_id', '=', 'city.id')
            ->select('customers.*')
            ->filter([
                'search' => $search,
            ]);

        if ($sorts && $orders) {
            $sortArr = explode(',', $sorts);
            $orderArr = explode(',', $orders);

            foreach ($sortArr as $i => $sortField) {
                $orderDir = $orderArr[$i] ?? 'asc';

                if ($sortField === 'province_name') {
                    $customers->orderBy('province.name', $orderDir);
                } elseif ($sortField === 'city_name') {
                    $customers->orderBy('city.name', $orderDir);
                } else {
                    $customers->orderBy("customers.$sortField", $orderDir);
                }
            }
        } else {
            $customers->orderBy('customers.code');
        }

        $total = $customers->count();

        if ($page && $rows) {
            $customers = $customers
                ->limit($rows)
                ->offset(($page - 1) * $rows)
                ->get();
        } else {
            $customers = $customers->get();
        }

        $rows = collect($customers)
            ->map(
                fn ($customer) => [
                    'id' => $customer->id,
                    'code' => $customer->code,
                    'name' => $customer->name,
                    'phone_number' => $customer->phone_number,
                    'address' => $customer->address,
                    'city_id' => $customer->city_id,
                    'city_name' => $customer->city?->name,
                    'province_id' => $customer->province_id,
                    'province_name' => $customer->province?->name,
                ],
            )
            ->toArray();

        return response()->json([
            'rows' => $rows,
            'total' => $total,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $customer = Customer::query()->create([
                'name' => Str::upper($request->name),
                'phone_number' => $request->phone_number,
                'address' => Str::upper($request->address),
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
                'name' => Str::upper($request->name),
                'phone_number' => $request->phone_number,
                'address' => Str::upper($request->address),
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
