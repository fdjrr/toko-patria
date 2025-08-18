<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransactionController extends Controller
{
    public function index(): View
    {
        $channels = [
            "offline" => "Offline",
            "online" => "Online",
        ];
        $statuses = [
            "pending" => "Pending",
            "paid" => "Paid",
            "shipped" => "Shipped",
            "completed" => "Completed",
            "cancelled" => "Cancelled",
        ];
        $payment_methods = [
            "cash" => "Cash",
            "transfer" => "Transfer",
        ];

        return view("pages.transactions.index", [
            "page_meta" => [
                "title" => "Transactions",
            ],
            "channels" => $channels,
            "statuses" => $statuses,
            "payment_methods" => $payment_methods,
        ]);
    }

    public function getTransaction(Request $request): JsonResponse
    {
        $search = $request->q;
        $page = $request->page;
        $rows = $request->rows;
        $sorts = $request->sort;
        $orders = $request->order;

        $transactions = Transaction::query()
            ->with(["customer"])
            ->leftJoin("customers as customer", "transactions.customer_id", "=", "customer.id")
            ->select("transactions.*")
            ->filter([
                "search" => $search,
            ]);

        if ($sorts && $orders) {
            $sortArr = explode(",", $sorts);
            $orderArr = explode(",", $orders);

            foreach ($sortArr as $i => $sortField) {
                $orderDir = $orderArr[$i] ?? "asc";

                if ($sortField === "customer_name") {
                    $transactions->orderBy("customer.name", $orderDir);
                } else {
                    $transactions->orderBy("transactions.$sortField", $orderDir);
                }
            }
        } else {
            $transactions->orderBy("transactions.code");
        }

        $total = $transactions->count();

        if ($page && $rows) {
            $transactions = $transactions
                ->limit($rows)
                ->offset(($page - 1) * $rows)
                ->get();
        } else {
            $transactions = $transactions->get();
        }

        $rows = collect($transactions)
            ->map(
                fn($transaction) => [
                    "id" => $transaction->id,
                    "code" => $transaction->code,
                    "customer_id" => $transaction->customer_id,
                    "customer_name" => $transaction->customer?->code . " - " . $transaction->customer?->name,
                    "shipment_no" => $transaction->shipment_no,
                    "transaction_date" => $transaction->transaction_date,
                    "channel" => $transaction->channel,
                    "status" => $transaction->status,
                    "payment_method" => $transaction->payment_method,
                    "total_discount" => $transaction->total_discount,
                    "total_extra_disc" => $transaction->total_extra_disc,
                    "total_amount" => $transaction->total_amount,
                    "notes" => $transaction->notes,
                ],
            )
            ->toArray();

        return response()->json([
            "rows" => $rows,
            "total" => $total,
        ]);
    }

    public function getItems(Transaction $transaction): JsonResponse
    {
        try {
            $transaction_items = $transaction
                ->transaction_items()
                ->with(["product"])
                ->get();

            $rows = collect($transaction_items)
                ->map(
                    fn($transaction_item) => [
                        "id" => $transaction_item->id,
                        "product_code" => $transaction_item->product?->code,
                        "product_name" => $transaction_item->product?->name,
                        "price" => $transaction_item->price,
                        "qty" => $transaction_item->qty,
                        "discount" => $transaction_item->discount,
                        "extra_disc" => $transaction_item->extra_disc,
                        "subtotal" => $transaction_item->subtotal,
                    ],
                )
                ->toArray();

            return response()->json([
                "success" => true,
                "data" => $rows,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ]);
        }
    }

    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $items = json_decode($request->items, true);

            if (empty($items)) {
                return response()->json([
                    "success" => false,
                    "message" => "Transaction items cannot be empty!",
                ]);
            }

            $transaction = Transaction::query()->create([
                "customer_id" => $request->customer_id,
                "shipment_no" => $request->shipment_no,
                "transaction_date" => Carbon::parse($request->transaction_date)->format("Y-m-d"),
                "channel" => $request->channel,
                "status" => $request->status,
                "payment_method" => $request->payment_method,
                "notes" => $request->notes,
            ]);

            $data = [];
            $now = now();
            $total_discount = 0;
            $total_extra_disc = 0;
            $total_amount = 0;
            $productNotExists = [];

            $productCodes = collect($items)->pluck("product_code")->filter()->unique()->toArray();

            $products = Product::query()->whereIn("code", $productCodes)->get();
            $product_discounts = ProductDiscount::query()
                ->filter([
                    "product_codes" => $productCodes,
                    "start_date" => $now->format("Y-m-d"),
                    "end_date" => $now->format("Y-m-d"),
                ])
                ->get();

            foreach ($items as $item) {
                $product_code = $item["product_code"];

                $product = $products->where("code", $product_code)->first();
                if ($product) {
                    $qty = $item["qty"];
                    $extra_disc = $item["extra_disc"];
                    $subtotal = $product->price * $qty;

                    $product_discount = $product_discounts->where("product_id", $product->id)->first();
                    if ($product_discount) {
                        $itemQty =
                            $product_discount->min_purchase > 0 ? floor($qty / $product_discount->min_purchase) : $qty;

                        switch ($product_discount->discount_type) {
                            case "percentage":
                                $defaultDiscount = $product->price * ($product_discount->discount_value / 100);

                                $discount = $product_discount->is_multiple
                                    ? $defaultDiscount * $itemQty
                                    : $defaultDiscount;
                                break;

                            case "fixed":
                                $defaultDiscount = $product_discount->discount_value;

                                $discount = $product_discount->is_multiple
                                    ? $defaultDiscount * $itemQty
                                    : $defaultDiscount;
                                break;

                            default:
                                throw new Exception("Invalid discount type: {$product_discount->discount_type}");
                        }
                    }

                    $data[] = [
                        "transaction_id" => $transaction->id,
                        "product_id" => $product->id,
                        "price" => $product->price,
                        "qty" => $qty,
                        "discount" => $discount,
                        "extra_disc" => $extra_disc,
                        "subtotal" => $subtotal,
                    ];

                    $total_discount += $discount;
                    $total_extra_disc += $extra_disc;
                    $total_amount += $subtotal;
                } else {
                    $productNotExists[] = $product_code;
                }
            }

            if (count($productNotExists) > 0) {
                throw new Exception("Product not exists: " . implode(", ", $productNotExists));
            }

            $transaction->transaction_items()->insert($data);

            $transaction->update([
                "total_discount" => $total_discount,
                "total_extra_disc" => $total_extra_disc,
                "total_amount" => $total_amount - $total_discount - $total_extra_disc,
            ]);

            DB::commit();

            return response()->json([
                "success" => true,
                "data" => $transaction,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        DB::beginTransaction();

        try {
            $items = json_decode($request->items, true);

            if (empty($items)) {
                return response()->json([
                    "success" => false,
                    "message" => "Transaction items cannot be empty!",
                ]);
            }

            $transaction->update([
                "customer_id" => $request->customer_id,
                "shipment_no" => $request->shipment_no,
                "transaction_date" => Carbon::parse($request->transaction_date)->format("Y-m-d"),
                "channel" => $request->channel,
                "status" => $request->status,
                "payment_method" => $request->payment_method,
                "notes" => $request->notes,
            ]);

            $transaction->transaction_items()->forceDelete();

            $data = [];
            $now = now();
            $total_discount = 0;
            $total_extra_disc = 0;
            $total_amount = 0;
            $productNotExists = [];

            $productCodes = collect($items)->pluck("product_code")->filter()->unique()->toArray();

            $products = Product::query()->whereIn("code", $productCodes)->get();
            $product_discounts = ProductDiscount::query()
                ->filter([
                    "product_codes" => $productCodes,
                    "start_date" => $now->format("Y-m-d"),
                    "end_date" => $now->format("Y-m-d"),
                ])
                ->isActive()
                ->get();

            foreach ($items as $item) {
                $product_code = $item["product_code"];

                $product = $products->where("code", $product_code)->first();
                if ($product) {
                    $qty = $item["qty"];
                    $extra_disc = $item["extra_disc"];
                    $subtotal = $product->price * $qty;

                    $product_discount = $product_discounts->where("product_id", $product->id)->first();
                    if ($product_discount) {
                        $itemQty =
                            $product_discount->min_purchase > 0 ? floor($qty / $product_discount->min_purchase) : $qty;

                        switch ($product_discount->discount_type) {
                            case "percentage":
                                $defaultDiscount = $product->price * ($product_discount->discount_value / 100);

                                $discount = $product_discount->is_multiple
                                    ? $defaultDiscount * $itemQty
                                    : $defaultDiscount;
                                break;

                            case "fixed":
                                $defaultDiscount = $product_discount->discount_value;

                                $discount = $product_discount->is_multiple
                                    ? $defaultDiscount * $itemQty
                                    : $defaultDiscount;
                                break;

                            default:
                                throw new Exception("Invalid discount type: {$product_discount->discount_type}");
                        }
                    }

                    $data[] = [
                        "transaction_id" => $transaction->id,
                        "product_id" => $product->id,
                        "price" => $product->price,
                        "qty" => $qty,
                        "discount" => $discount,
                        "extra_disc" => $extra_disc,
                        "subtotal" => $subtotal,
                    ];

                    $total_discount += $discount;
                    $total_extra_disc += $extra_disc;
                    $total_amount += $subtotal;
                } else {
                    $productNotExists[] = $product_code;
                }
            }

            if (count($productNotExists) > 0) {
                throw new Exception("Product not exists: " . implode(", ", $productNotExists));
            }

            $transaction->transaction_items()->insert($data);

            $transaction->update([
                "total_discount" => $total_discount,
                "total_extra_disc" => $total_extra_disc,
                "total_amount" => $total_amount - $total_discount - $total_extra_disc,
            ]);

            DB::commit();

            return response()->json([
                "success" => true,
                "data" => $transaction,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ]);
        }
    }

    public function destroy(Transaction $transaction): JsonResponse
    {
        try {
            $transaction->delete();

            return response()->json([
                "success" => true,
                "data" => $transaction,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ]);
        }
    }
}
