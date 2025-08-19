<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class ProductController extends Controller
{
    public function index()
    {
        return view('pages.product.index', [
            'page_meta' => [
                'title' => 'Product',
            ],
        ]);
    }

    public function getProduct(Request $request)
    {
        $search = $request->q;
        $page = $request->page;
        $rows = $request->rows;
        $sorts = $request->sort;
        $orders = $request->order;

        $products = Product::query()
            ->with(['product_category', 'product_brand'])
            ->leftJoin('product_categories as product_category', 'products.category_id', '=', 'product_category.id')
            ->leftJoin('product_brands as product_brand', 'products.category_id', '=', 'product_brand.id')
            ->select('products.*')
            ->filter([
                'search' => $search,
            ]);

        if ($sorts && $orders) {
            $sortArr = explode(',', $sorts);
            $orderArr = explode(',', $orders);

            foreach ($sortArr as $i => $sortField) {
                $orderDir = $orderArr[$i] ?? 'asc';

                if ($sortField === 'category_name') {
                    $products->orderBy('product_category.name', $orderDir);
                } elseif ($sortField === 'brand_name') {
                    $products->orderBy('product_brand.name', $orderDir);
                } else {
                    $products->orderBy("products.$sortField", $orderDir);
                }
            }
        } else {
            $products->orderBy('products.code');
        }

        $total = $products->count();

        if ($page && $rows) {
            $products = $products
                ->limit($rows)
                ->offset(($page - 1) * $rows)
                ->get();
        } else {
            $products = $products->get();
        }

        $rows = collect($products)
            ->map(
                fn ($product) => [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'part_code' => $product->part_code,
                    'category_id' => $product->category_id,
                    'category_name' => $product->product_category?->name,
                    'brand_id' => $product->brand_id,
                    'brand_name' => $product->product_brand?->name,
                    'keywords' => $product->keywords,
                    'description' => $product->description,
                ],
            )
            ->toArray();

        return response()->json([
            'rows' => $rows,
            'total' => $total,
        ]);
    }

    public function generateKeywords(Request $request)
    {
        $name = $request->name;
        $description = $request->description;

        $system = "
Peran:
Kamu adalah AI spesialis SEO dan riset keyword produk.

Tugas:
Diberikan nama produk dan deskripsi produk, hasilkan daftar keywords yang:
    1. Relevan dengan produk yang diberikan.
    2. Menggunakan bahasa alami yang umum dicari di mesin pencari.
    3. Mengandung kata kunci spesifik (long-tail keywords) jika relevan.
    4. Tidak mengandung stopwords umum yang tidak membantu pencarian (seperti 'dan', 'untuk', 'di').
    5. Dipisahkan dengan tanda koma tanpa spasi di awal/akhir.
    6. Menggunakan huruf kecil semua.
Output hanya berupa daftar keyword dengan pemisah koma, tanpa tambahan teks atau penjelasan.

Format Output:
keyword1,keyword2,keyword3,keyword4
        ";

        try {
            $result = Gemini::generativeModel('gemini-2.5-flash')
                ->generateContent([$system, $name, $description])
                ->text();

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $product = Product::query()->create([
                'code' => Str::upper($request->code),
                'name' => Str::upper($request->name),
                'part_code' => Str::upper($request->part_code),
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'keywords' => $request->keywords,
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'data' => $product,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request, Product $product)
    {
        try {
            $product->update([
                'code' => Str::upper($request->code),
                'name' => Str::upper($request->name),
                'part_code' => Str::upper($request->part_code),
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'keywords' => $request->keywords,
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'data' => $product,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(Product $product)
    {
        try {
            $product->delete();

            return response()->json([
                'success' => true,
                'data' => $product,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
