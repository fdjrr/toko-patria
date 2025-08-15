<?php

use App\Http\Controllers\ProductBrandController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('products')->group(function () {
    Route::post('getProduct', [ProductController::class, 'getProduct'])->name('products.getProduct');
    Route::post('store', [ProductController::class, 'store'])->name('products.store');
    Route::post('generateKeywords', [ProductController::class, 'generateKeywords'])->name('products.generateKeywords');
    Route::post('{product}/update', [ProductController::class, 'update'])->name('products.update');
    Route::post('{product}/destroy', [ProductController::class, 'destroy'])->name('products.destroy');
});

Route::prefix('product_categories')->group(function () {
    Route::post('getCategory', [ProductCategoryController::class, 'getCategory'])->name('product_categories.getCategory');
    Route::post('store', [ProductCategoryController::class, 'store'])->name('product_categories.store');
    Route::post('{product_category}/update', [ProductCategoryController::class, 'update'])->name('product_categories.update');
    Route::post('{product_category}/destroy', [ProductCategoryController::class, 'destroy'])->name('product_categories.destroy');
});

Route::prefix('product_brands')->group(function () {
    Route::post('getBrand', [ProductBrandController::class, 'getBrand'])->name('product_brands.getBrand');
    Route::post('store', [ProductBrandController::class, 'store'])->name('product_brands.store');
    Route::post('{product_brand}/update', [ProductBrandController::class, 'update'])->name('product_brands.update');
    Route::post('{product_brand}/destroy', [ProductBrandController::class, 'destroy'])->name('product_brands.destroy');
});
