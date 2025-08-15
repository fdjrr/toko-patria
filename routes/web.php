<?php

use App\Http\Controllers\ProductBrandController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages/dashboard');
});

Route::prefix('products')->group(function () {
    Route::get('', [ProductController::class, 'index'])->name('products.index');
});

Route::prefix('product_categories')->group(function () {
    Route::get('', [ProductCategoryController::class, 'index'])->name('product_categories.index');
});

Route::prefix('product_brands')->group(function () {
    Route::get('', [ProductBrandController::class, 'index'])->name('product_brands.index');
});
