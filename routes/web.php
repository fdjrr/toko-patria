<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductBrandController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductDiscountController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('pages/dashboard'))->name('dashboard');

Route::prefix('products')->group(function () {
    Route::get('', [ProductController::class, 'index'])->name('products.index');
});

Route::prefix('transactions')->group(function () {
    Route::get('', [TransactionController::class, 'index'])->name('transactions.index');
});

Route::prefix('customers')->group(function () {
    Route::get('', [CustomerController::class, 'index'])->name('customers.index');
});

Route::prefix('product_discounts')->group(function () {
    Route::get('', [ProductDiscountController::class, 'index'])->name('product_discounts.index');
});

Route::prefix('product_categories')->group(function () {
    Route::get('', [ProductCategoryController::class, 'index'])->name('product_categories.index');
});

Route::prefix('product_brands')->group(function () {
    Route::get('', [ProductBrandController::class, 'index'])->name('product_brands.index');
});
