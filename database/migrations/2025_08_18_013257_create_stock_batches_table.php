<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::query()->create("stock_batches", function (Blueprint $table) {
            $table->id();
            $table->foreignId("product_id")->constrained("products")->cascadeOnDelete();
            $table->foreignId("warehouse_id")->constrained("warehouses")->cascadeOnDelete();
            $table->foreignId("qty")->default(0);
            $table->decimal("price", 18, 2)->default(0);
            $table->decimal("total_price", 18, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists("stock_batches");

        Schema::enableForeignKeyConstraints();
    }
};
