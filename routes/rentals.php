<?php

/**
 * | Created On-14-06-2023 
 * | Author-Anshu Kumar
 * | Created for the Shop and tolls collections routes
 */

use App\Http\Controllers\Rentals\ShopController;
use App\Http\Controllers\Rentals\TollsController;
use Illuminate\Support\Facades\Route;

/**
 * | Shops (50)
 */
Route::controller(ShopController::class)->group(function () {
    Route::post('shop-payments', 'shopPayment');                               // 01
    Route::post('crud/shop/store', 'store');                                   // 02
    Route::post('crud/shop/edit', 'edit');                                     // 03
    Route::post('crud/shop/show-by-id', 'show');                               // 04
    Route::post('crud/shop/retrieve-all', 'retrieve');                         // 05
    Route::post('crud/shop/retrieve-all-active', 'retrieveAllActive');         // 06
    Route::post('crud/shop/delete', 'delete');                                 // 07
});

/**
 * | Tolls(51)
 */
Route::controller(TollsController::class)->group(function () {
    Route::post('toll-payments', 'tollPayments');                            //01
    Route::post('crud/toll/insert', 'store');                                //02
    Route::post('crud/toll/edit', 'edit');                                   //03
    Route::post('crud/toll/show-by-id', 'show');                             //04 
    Route::post('crud/toll/retrieve-all', 'retrieve');                       //05 
    Route::post('crud/toll/retrieve-all-active', 'retrieveActive');          //06
    Route::post('crud/toll/delete', 'delete');                               //07
});
