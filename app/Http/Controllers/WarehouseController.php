<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class WarehouseController extends Controller
{
    public function index(): View
    {
        return view("pages.warehouses.index", [
            "page_meta" => [
                "title" => "Warehouses",
            ],
        ]);
    }

    public function getWarehouse(Request $request): JsonResponse
    {
        $search = $request->q;
        $page = $request->page;
        $rows = $request->rows;
        $sorts = $request->sort;
        $orders = $request->order;

        $warehouses = Warehouse::query()->filter([
            "search" => $search,
        ]);

        if ($sorts && $orders) {
            $sortArr = explode(",", $sorts);
            $orderArr = explode(",", $orders);

            foreach ($sortArr as $i => $sortField) {
                $orderDir = $orderArr[$i] ?? "asc";
                $warehouses->orderBy($sortField, $orderDir);
            }
        } else {
            $warehouses->orderBy("code");
        }

        $total = $warehouses->count();

        if ($page && $rows) {
            $warehouses = $warehouses
                ->limit($rows)
                ->offset(($page - 1) * $rows)
                ->get();
        } else {
            $warehouses = $warehouses->get();
        }

        $rows = collect($warehouses)
            ->map(
                fn($warehouse) => [
                    "id" => $warehouse->id,
                    "code" => $warehouse->code,
                    "name" => $warehouse->name,
                    "address" => $warehouse->address,
                ],
            )
            ->toArray();

        return response()->json([
            "rows" => $rows,
            "total" => $total,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $warehouse = Warehouse::query()->create([
                "name" => Str::upper($request->name),
                "address" => Str::upper($request->address),
            ]);

            return response()->json([
                "success" => true,
                "data" => $warehouse,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request, Warehouse $warehouse): JsonResponse
    {
        try {
            $warehouse->update([
                "name" => Str::upper($request->name),
                "address" => Str::upper($request->address),
            ]);

            return response()->json([
                "success" => true,
                "data" => $warehouse,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ]);
        }
    }

    public function destroy(Warehouse $warehouse): JsonResponse
    {
        try {
            $warehouse->delete();

            return response()->json([
                "success" => true,
                "data" => $warehouse,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ]);
        }
    }
}
