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

        Schema::query()->create("transactions", function (Blueprint $table) {
            $table->id();
            $table->foreignId("customer_id")->constrained("customers")->cascadeOnDelete();
            $table->string("code")->index();
            $table->string("shipment_no")->nullable();
            $table->enum("channel", ["offline", "online"])->default("offline");
            $table->date("transaction_date");
            $table->enum("status", ["pending", "paid", "shipped", "completed", "cancelled"])->default("pending");
            $table->decimal("total_discount", 18, 2)->default(0);
            $table->decimal("total_extra_disc", 18, 2)->default(0);
            $table->decimal("total_amount", 18, 2)->default(0);
            $table->enum("payment_method", ["cash", "transfer"])->default("cash");
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

        Schema::dropIfExists("transactions");

        Schema::enableForeignKeyConstraints();
    }
};
