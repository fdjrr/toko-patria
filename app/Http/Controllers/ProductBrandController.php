<?php

namespace App\Http\Controllers;

use App\Models\ProductBrand;
use Illuminate\Http\Request;
use Throwable;

class ProductBrandController extends Controller
{
    public function index()
    {
        return view('pages.product_brands.index', [
            'page_meta' => [
                'title' => 'Product Brand',
            ],
        ]);
    }

    public function getBrand(Request $request)
    {
        $search = $request->q;
        $page = $request->page;
        $rows = $request->rows;
        $sorts = $request->sorts;
        $orders = $request->order;

        $product_brands = ProductBrand::query()->filter([
            'search' => $search,
        ]);

        if ($sorts && $orders) {
            $sortArr = explode(',', $sorts);
            $orderArr = explode(',', $orders);

            foreach ($sortArr as $i => $sortField) {
                $orderDir = $orderArr[$i] ?? 'asc';
                $product_brands->orderBy($sortField, $orderDir);
            }
        } else {
            $product_brands->orderBy('name');
        }

        $total = $product_brands->count();

        if ($page && $rows) {
            $product_brands = $product_brands
                ->limit($rows)
                ->offset(($page - 1) * $rows)
                ->get();
        } else {
            $product_brands = $product_brands->get();
        }

        $rows = collect($product_brands)->map(fn ($product_brand) => [
            'id' => $product_brand->id,
            'name' => $product_brand->name,
        ])->toArray();

        return response()->json([
            'rows' => $rows,
            'total' => $total,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $product_brand = ProductBrand::create([
                'name' => $request->name,
            ]);

            return response()->json([
                'success' => true,
                'data' => $product_brand,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request, ProductBrand $product_brand)
    {
        try {
            $product_brand->update([
                'name' => $request->name,
            ]);

            return response()->json([
                'success' => true,
                'data' => $product_brand,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(ProductBrand $product_brand)
    {
        try {
            $product_brand->delete();

            return response()->json([
                'success' => true,
                'data' => $product_brand,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
