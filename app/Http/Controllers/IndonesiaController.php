<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Province;
use Illuminate\Http\Request;

class IndonesiaController extends Controller
{
    public function getProvince(Request $request)
    {
        $search = $request->q ?? null;
        $page = $request->page;
        $rows = $request->rows;

        $provinces = Province::query()->filter([
            'search' => $search,
        ])->orderBy('name');

        $total = $provinces->count();

        if ($page && $rows) {
            $provinces = $provinces
                ->limit($rows)
                ->offset(($page - 1) * $rows)
                ->get();
        } else {
            $provinces = $provinces->get();
        }

        $rows = collect($provinces)->map(fn ($province) => [
            'id' => $province->id,
            'code' => $province->code,
            'name' => $province->name,
        ])->toArray();

        return response()->json([
            'rows' => $rows,
            'total' => $total,
        ]);
    }

    public function getCity(Request $request)
    {
        $province_id = $request->province_id ?? null;

        if (! $province_id) {
            return response()->json([
                'rows' => [],
                'total' => 0,
            ]);
        }

        $search = $request->q ?? null;
        $page = $request->page;
        $rows = $request->rows;

        $cities = City::query()->filter([
            'province_id' => $province_id,
            'search' => $search,
        ])->orderBy('name');

        $total = $cities->count();

        if ($page && $rows) {
            $cities = $cities
                ->limit($rows)
                ->offset(($page - 1) * $rows)
                ->get();
        } else {
            $cities = $cities->get();
        }

        $rows = collect($cities)->map(fn ($city) => [
            'id' => $city->id,
            'code' => $city->code,
            'province_code' => $city->province_code,
            'name' => $city->name,
        ])->toArray();

        return response()->json([
            'rows' => $rows,
            'total' => $total,
        ]);
    }
}
