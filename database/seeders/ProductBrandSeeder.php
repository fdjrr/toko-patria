<?php

namespace Database\Seeders;

use App\Models\ProductBrand;
use Illuminate\Database\Seeder;

class ProductBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $product_brands = [
            'ACCELERA',
            'AKEBONO',
            'ASPIRA',
            'EVALUBE',
            'DENSO',
            'GS ASTRA',
            'IHATA',
            'INCOE',
            'KAYABA',
            'NLK',
            'SEIKEN',
            'TDW',
            'HZF',
            'BRIO',
            'EURO',
            'SHENG WEY',
            'TKR',
            'GM',
            'HIREV',
            'OTANI',
        ];

        $data = [];
        $now = now();

        foreach ($product_brands as $product_brand) {
            $data[] = [
                'name' => $product_brand,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        ProductBrand::query()->insert($data);
    }
}
