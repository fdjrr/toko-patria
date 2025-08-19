<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::query()->create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->index();
            $table->string('name');
            $table->string('phone_number');
            $table->text('address');
            $table->foreignId('province_id')->nullable()->constrained('indonesia_provinces')->cascadeOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('indonesia_cities')->cascadeOnDelete();
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

        Schema::dropIfExists('customers');

        Schema::enableForeignKeyConstraints();
    }
};
