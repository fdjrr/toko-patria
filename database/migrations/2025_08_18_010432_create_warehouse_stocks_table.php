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

        Schema::query()->create("warehouse_stocks", function (Blueprint $table) {
            $table->id();
            $table->foreignId("warehouse_id")->constrained("warehouses")->cascadeOnDelete();
            $table->foreignId("product_id")->constrained("products")->cascadeOnDelete();
            $table->foreignId("qty")->default(0);
            $table->enum("type", ["in", "out"])->default("in");
            $table->string("reference")->nullable();
            $table->text("notes")->nullable();
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

        Schema::dropIfExists("warehouse_stocks");

        Schema::enableForeignKeyConstraints();
    }
};
