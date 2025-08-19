<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class ProductCategoryController extends Controller
{
    public function index()
    {
        return view('pages.product-category.index', [
            'page_meta' => [
                'title' => 'Product Category',
            ],
        ]);
    }

    public function getCategory(Request $request)
    {
        $search = $request->q;
        $page = $request->page;
        $rows = $request->rows;
        $sorts = $request->sort;
        $orders = $request->order;

        $product_categories = ProductCategory::query()
            ->with(['parent'])
            ->leftJoin('product_categories as parent', 'product_categories.parent_id', '=', 'parent.id')
            ->select('product_categories.*')
            ->filter([
                'search' => $search,
            ]);

        if ($sorts && $orders) {
            $sortArr = explode(',', $sorts);
            $orderArr = explode(',', $orders);

            foreach ($sortArr as $i => $sortField) {
                $orderDir = $orderArr[$i] ?? 'asc';

                if ($sortField === 'parent_name') {
                    $product_categories->orderBy('parent.name', $orderDir);
                } else {
                    $product_categories->orderBy("product_categories.$sortField", $orderDir);
                }
            }
        } else {
            $product_categories->orderBy('product_categories.name');
        }

        $total = $product_categories->count();

        if ($page && $rows) {
            $product_categories = $product_categories
                ->limit($rows)
                ->offset(($page - 1) * $rows)
                ->get();
        } else {
            $product_categories = $product_categories->get();
        }

        $rows = collect($product_categories)
            ->map(
                fn ($product_category) => [
                    'id' => $product_category->id,
                    'parent_id' => $product_category->parent_id,
                    'parent_name' => $product_category->parent?->name,
                    'name' => $product_category->name,
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
            $product_category = ProductCategory::query()->create([
                'parent_id' => $request->parent_id,
                'name' => Str::upper($request->name),
            ]);

            return response()->json([
                'success' => true,
                'data' => $product_category,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request, ProductCategory $product_category)
    {
        try {
            $product_category->update([
                'parent_id' => $request->parent_id,
                'name' => Str::upper($request->name),
            ]);

            return response()->json([
                'success' => true,
                'data' => $product_category,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(ProductCategory $product_category)
    {
        try {
            $product_category->delete();

            return response()->json([
                'success' => true,
                'data' => $product_category,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
