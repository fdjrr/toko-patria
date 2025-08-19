<?php

namespace App\Http\Controllers;

use App\Models\ProductDiscount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Throwable;

class ProductDiscountController extends Controller
{
    public function index()
    {
        $discount_types = [
            'percentage' => 'Percentage',
            'fixed' => 'Fixed',
        ];

        return view('pages.product-discount.index', [
            'page_meta' => [
                'title' => 'Product Discount',
            ],
            'discount_types' => $discount_types,
        ]);
    }

    public function getDiscount(Request $request)
    {
        $search = $request->q;
        $page = $request->page;
        $rows = $request->rows;
        $sorts = $request->sort;
        $orders = $request->order;

        $product_discounts = ProductDiscount::query()
            ->with(['product'])
            ->leftJoin('products as product', 'product_discounts.product_id', '=', 'product.id')
            ->select('product_discounts.*')
            ->filter([
                'search' => $search,
            ]);

        if ($sorts && $orders) {
            $sortArr = explode(',', $sorts);
            $orderArr = explode(',', $orders);

            foreach ($sortArr as $i => $sortField) {
                $orderDir = $orderArr[$i] ?? 'asc';

                if ($sortField === 'product_code') {
                    $product_discounts->orderBy('product.code', $orderDir);
                } elseif ($sortField === 'product_name') {
                    $product_discounts->orderBy('product.name', $orderDir);
                } elseif ($sortField === 'product_price') {
                    $product_discounts->orderBy('product.price', $orderDir);
                } else {
                    $product_discounts->orderBy("product_discounts.$sortField", $orderDir);
                }
            }
        } else {
            $product_discounts->orderBy('product.code');
        }

        $total = $product_discounts->count();

        if ($page && $rows) {
            $product_discounts = $product_discounts
                ->limit($rows)
                ->offset(($page - 1) * $rows)
                ->get();
        } else {
            $product_discounts = $product_discounts->get();
        }

        $rows = collect($product_discounts)
            ->map(
                fn ($product_discount) => [
                    'id' => $product_discount->id,
                    'product_id' => $product_discount->product_id,
                    'product_code' => $product_discount->product?->code,
                    'product_name' => $product_discount->product?->name,
                    'product_price' => $product_discount->product?->price,
                    'discount_type' => $product_discount->discount_type,
                    'discount_value' => $product_discount->discount_value,
                    'min_purchase' => $product_discount->min_purchase,
                    'is_multiple' => $product_discount->is_multiple,
                    'multiple_status' => $product_discount->is_multiple ? 'Y' : 'N',
                    'description' => $product_discount->description,
                    'start_date' => $product_discount->start_date,
                    'end_date' => $product_discount->end_date,
                    'is_active' => $product_discount->is_active,
                    'status' => $product_discount->is_active ? 'Active' : 'Inactive',
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
            $product_discount = ProductDiscount::query()->create([
                'product_id' => $request->product_id,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'min_purchase' => $request->min_purchase,
                'is_multiple' => (bool) $request->is_multiple,
                'description' => $request->description,
                'start_date' => Carbon::parse($request->start_date)->format('Y-m-d'),
                'end_date' => Carbon::parse($request->end_date)->format('Y-m-d'),
                'is_active' => (bool) $request->is_active,
            ]);

            return response()->json([
                'success' => true,
                'data' => $product_discount,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request, ProductDiscount $product_discount)
    {
        try {
            $product_discount->update([
                'product_id' => $request->product_id,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'min_purchase' => $request->min_purchase,
                'is_multiple' => (bool) $request->is_multiple,
                'description' => $request->description,
                'start_date' => Carbon::parse($request->start_date)->format('Y-m-d'),
                'end_date' => Carbon::parse($request->end_date)->format('Y-m-d'),
                'is_active' => $request->is_active,
            ]);

            return response()->json([
                'success' => true,
                'data' => $product_discount,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(ProductDiscount $product_discount)
    {
        try {
            $product_discount->delete();

            return response()->json([
                'success' => true,
                'data' => $product_discount,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
