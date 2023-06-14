<?php

/**
 * | Created On-14-06-2023 
 * | Author-Anshu Kumar
 * | Created for the Shop and tolls collections routes
 */

use App\Http\Controllers\Rentals\ShopController;
use Illuminate\Support\Facades\Route;

/**
 * | Shops (50)
 */
Route::controller(ShopController::class)->group(function () {
    Route::post('shop-payments', 'shopPayment');                // 01
});
