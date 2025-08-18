<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\IndonesiaController;
use App\Http\Controllers\ProductBrandController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductDiscountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::prefix('transactions')->group(function () {
    Route::post('getTransaction', [TransactionController::class, 'getTransaction'])->name('transactions.getTransaction');
    Route::post('store', [TransactionController::class, 'store'])->name('transactions.store');
    Route::post('{transaction}/items', [TransactionController::class, 'getItems'])->name('transactions.getItems');
    Route::post('{transaction}/update', [TransactionController::class, 'update'])->name('transactions.update');
    Route::post('{transaction}/destroy', [TransactionController::class, 'destroy'])->name('transactions.destroy');
});

Route::prefix('customers')->group(function () {
    Route::post('getCustomer', [CustomerController::class, 'getCustomer'])->name('customers.getCustomer');
    Route::post('store', [CustomerController::class, 'store'])->name('customers.store');
    Route::post('{customer}/update', [CustomerController::class, 'update'])->name('customers.update');
    Route::post('{customer}/destroy', [CustomerController::class, 'destroy'])->name('customers.destroy');
});

Route::prefix('warehouses')->group(function () {
    Route::post('getWarehouse', [WarehouseController::class, 'getWarehouse'])->name('warehouses.getWarehouse');
    Route::post('store', [WarehouseController::class, 'store'])->name('warehouses.store');
    Route::post('{warehouse}/update', [WarehouseController::class, 'update'])->name('warehouses.update');
    Route::post('{warehouse}/destroy', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
});

Route::prefix('products')->group(function () {
    Route::post('getProduct', [ProductController::class, 'getProduct'])->name('products.getProduct');
    Route::post('store', [ProductController::class, 'store'])->name('products.store');
    Route::post('generateKeywords', [ProductController::class, 'generateKeywords'])->name('products.generateKeywords');
    Route::post('{product}/update', [ProductController::class, 'update'])->name('products.update');
    Route::post('{product}/destroy', [ProductController::class, 'destroy'])->name('products.destroy');
});

Route::prefix('product_discounts')->group(function () {
    Route::post('getDiscount', [ProductDiscountController::class, 'getDiscount'])->name('product_discounts.getDiscount');
    Route::post('store', [ProductDiscountController::class, 'store'])->name('product_discounts.store');
    Route::post('{product_discount}/update', [ProductDiscountController::class, 'update'])->name('product_discounts.update');
    Route::post('{product_discount}/destroy', [ProductDiscountController::class, 'destroy'])->name('product_discounts.destroy');
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

Route::prefix('provinces')->group(function () {
    Route::post('getProvince', [IndonesiaController::class, 'getProvince'])->name('provinces.getProvince');
});

Route::prefix('cities')->group(function () {
    Route::post('getCity', [IndonesiaController::class, 'getCity'])->name('cities.getCity');
});
