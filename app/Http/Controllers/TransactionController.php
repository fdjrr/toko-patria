<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransactionController extends Controller
{
    public function index()
    {
        $channels = ['offline', 'online'];
        $statuses = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];
        $payment_methods = ['cash', 'transfer'];

        return view('pages.transactions.index', [
            'page_meta' => [
                'title' => 'Transactions',
            ],
            'channels' => $channels,
            'statuses' => $statuses,
            'payment_methods' => $payment_methods,
        ]);
    }

    public function getTransaction(Request $request)
    {
        $search = $request->q;
        $page = $request->page;
        $rows = $request->rows;

        $transactions = Transaction::query()->filter([
            'search' => $search,
        ])->orderBy('code');

        $total = $transactions->count();

        if ($page && $rows) {
            $transactions = $transactions
                ->limit($rows)
                ->offset(($page - 1) * $rows)
                ->get();
        } else {
            $transactions = $transactions->get();
        }

        $rows = collect($transactions)->map(fn ($transaction) => [
            'id' => $transaction->id,
            'code' => $transaction->code,
            'shipment_no' => $transaction->shipment_no,
            'customer_id' => $transaction->customer_id,
            'customer_name' => $transaction->customer?->code.' - '.$transaction->customer?->name,
            'channel' => $transaction->channel,
            'transaction_date' => $transaction->transaction_date,
            'status' => $transaction->status,
            'total_discount' => $transaction->total_discount,
            'total_amount' => $transaction->total_amount,
            'payment_method' => $transaction->payment_method,
            'notes' => $transaction->notes,
        ])->toArray();

        return response()->json([
            'rows' => $rows,
            'total' => $total,
        ]);
    }

    public function getItems(Transaction $transaction)
    {
        try {
            $transaction_items = $transaction->transaction_items()->with(['product'])->get();

            $rows = collect($transaction_items)->map(fn ($transaction_item) => [
                'id' => $transaction_item->id,
                'product_code' => $transaction_item->product?->code,
                'product_name' => $transaction_item->product?->name,
                'price' => $transaction_item->price,
                'qty' => $transaction_item->qty,
                'discount' => $transaction_item->discount,
            ])->toArray();

            return response()->json([
                'success' => true,
                'data' => $rows,
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
        DB::beginTransaction();

        try {
            $items = json_decode($request->items, true);

            if (empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction items cannot be empty!',
                ]);
            }

            $transaction = Transaction::query()->create([
                'customer_id' => $request->customer_id,
                'shipment_no' => $request->shipment_no,
                'transaction_date' => Carbon::parse($request->transaction_date)->format('Y-m-d'),
                'channel' => $request->channel,
                'status' => $request->status,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            $data = [];
            $now = now();
            $total_discount = 0;
            $total_amount = 0;
            $productNotExists = [];

            $productCodes = collect($items)->pluck('product_code')->filter()->unique()->toArray();

            $products = Product::query()->whereIn('code', $productCodes)->get();
            $product_discounts = ProductDiscount::query()->filter([
                'product_codes' => $productCodes,
                'start_date' => $now->format('Y-m-d'),
                'end_date' => $now->format('Y-m-d'),
            ])->get();

            foreach ($items as $item) {
                $product_code = $item['product_code'];

                $product = $products->where('code', $product_code)->first();
                if ($product) {
                    $qty = $item['qty'];
                    $discount = $item['discount'] ?? 0;
                    $subtotal = $product->price * $qty;

                    $product_discount = $product_discounts->where('product_id', $product->id)->first();
                    if ($product_discount) {
                        switch ($product_discount->discount_type) {
                            case 'percentage':
                                $discount = $subtotal * ($product_discount->discount_value / 100);
                                break;

                            case 'fixed':
                                $discount = $subtotal - $product_discount->discount_value;
                                break;

                            default:
                                throw new Exception("Invalid discount type: {$product_discount->discount_type}");
                        }
                    }

                    $data[] = [
                        'transaction_id' => $transaction->id,
                        'product_id' => $product->id,
                        'price' => $product->price,
                        'qty' => $qty,
                        'discount' => $discount,
                        'subtotal' => $subtotal,
                    ];

                    $total_discount += $discount;
                    $total_amount += $subtotal;
                } else {
                    $productNotExists[] = $product_code;
                }
            }

            if (count($productNotExists) > 0) {
                throw new Exception('Product not exists: '.implode(', ', $productNotExists));
            }

            $transaction->transaction_items()->insert($data);

            $transaction->update([
                'total_discount' => $total_discount,
                'total_amount' => $total_amount - $total_discount,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $transaction,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request, Transaction $transaction)
    {
        DB::beginTransaction();

        try {
            $items = json_decode($request->items, true);

            if (empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction items cannot be empty!',
                ]);
            }

            $transaction->update([
                'customer_id' => $request->customer_id,
                'shipment_no' => $request->shipment_no,
                'transaction_date' => Carbon::parse($request->transaction_date)->format('Y-m-d'),
                'channel' => $request->channel,
                'status' => $request->status,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            $transaction->transaction_items()->forceDelete();

            $data = [];
            $now = now();
            $total_discount = 0;
            $total_amount = 0;
            $productNotExists = [];

            $productCodes = collect($items)->pluck('product_code')->filter()->unique()->toArray();

            $products = Product::query()->whereIn('code', $productCodes)->get();
            $product_discounts = ProductDiscount::query()->filter([
                'product_codes' => $productCodes,
                'start_date' => $now->format('Y-m-d'),
                'end_date' => $now->format('Y-m-d'),
            ])->get();

            foreach ($items as $item) {
                $product_code = $item['product_code'];

                $product = $products->where('code', $product_code)->first();
                if ($product) {
                    $qty = $item['qty'];
                    $discount = $item['discount'] ?? 0;
                    $subtotal = $product->price * $qty;

                    $product_discount = $product_discounts->where('product_id', $product->id)->first();
                    if ($product_discount) {
                        switch ($product_discount->discount_type) {
                            case 'percentage':
                                $discount = $subtotal * ($product_discount->discount_value / 100);
                                break;

                            case 'fixed':
                                $discount = $subtotal - $product_discount->discount_value;
                                break;

                            default:
                                throw new Exception("Invalid discount type: {$product_discount->discount_type}");
                        }
                    }

                    $data[] = [
                        'transaction_id' => $transaction->id,
                        'product_id' => $product->id,
                        'price' => $product->price,
                        'qty' => $qty,
                        'discount' => $discount,
                        'subtotal' => $subtotal,
                    ];

                    $total_discount += $discount;
                    $total_amount += $subtotal;
                } else {
                    $productNotExists[] = $product_code;
                }
            }

            if (count($productNotExists) > 0) {
                throw new Exception('Product not exists: '.implode(', ', $productNotExists));
            }

            $transaction->transaction_items()->insert($data);

            $transaction->update([
                'total_discount' => $total_discount,
                'total_amount' => $total_amount - $total_discount,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $transaction,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(Transaction $transaction)
    {
        try {
            $transaction->delete();

            return response()->json([
                'success' => true,
                'data' => $transaction,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
