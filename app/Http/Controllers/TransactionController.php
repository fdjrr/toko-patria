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
            'customer_name' => $transaction->customer ? $transaction->customer?->code.' - '.$transaction->customer?->name : null,
            'channel' => $transaction->channel,
            'transaction_date' => $transaction->transaction_date,
            'status' => $transaction->status,
            'total_amount' => $transaction->total_amount,
            'payment_method' => $transaction->payment_method,
        ])->toArray();

        return response()->json([
            'rows' => $rows,
            'total' => $total,
        ]);
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
                    $discount = 0;
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
                'total_amount' => $total_amount - $discount,
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
}
