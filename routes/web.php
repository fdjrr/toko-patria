<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductBrandController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductDiscountController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('pages/dashboard'))->name('dashboard');

Route::prefix('customers')->group(function () {
    Route::get('', [CustomerController::class, 'index'])->name('customers.index');
});

Route::prefix('warehouses')->group(function () {
    Route::get('', [WarehouseController::class, 'index'])->name('warehouses.index');
});

Route::prefix('products')->group(function () {
    Route::get('', [ProductController::class, 'index'])->name('products.index');
});

Route::prefix('product-discounts')->group(function () {
    Route::get('', [ProductDiscountController::class, 'index'])->name('product_discounts.index');
});

Route::prefix('product-categories')->group(function () {
    Route::get('', [ProductCategoryController::class, 'index'])->name('product_categories.index');
});

Route::prefix('product-brands')->group(function () {
    Route::get('', [ProductBrandController::class, 'index'])->name('product_brands.index');
});
