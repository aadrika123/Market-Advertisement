<?php

/**
 * | Created On-14-06-2023 
 * | Author - Anshu Kumar
 * | Change By - Bikash Kumar
 * | Created for the Shop and tolls collections routes
 * | Status - Closed By Bikash Kumar ( 03 Oct 2023 )
 */

use App\Http\Controllers\Master\CircleController;
use App\Http\Controllers\Master\MarketController;
use App\Http\Controllers\Rentals\ShopController;
use App\Http\Controllers\Rentals\TollsController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['checkToken']], function () {
    /**
     * | Shops (50)
     * | Author - Anshu Kumar
     * | Change By - Bikash Kumar
     * | Status - Closed By Bikash Kumar ( 03 Oct 2023 )
     */
    Route::controller(ShopController::class)->group(function () {
        Route::post('shop-payments', 'shopPayment');                                                                        // 01  ( Make Shop Payment )
        Route::post('crud/shop/store', 'store');                                                                            // 02  ( Add Shop Records )
        Route::post('crud/shop/edit', 'edit');                                                                              // 03  ( Edit Shop Records )
        Route::post('crud/shop/show-by-id', 'show');                                                                        // 04  ( Get Shop Details By ID )
        Route::post('crud/shop/retrieve-all', 'retrieve');                                                                  // 05  ( Retrieve All Shop )
        Route::post('crud/shop/retrieve-all-active', 'retrieveAllActive');                                                  // 06  ( Retrieve All Active Shop )
        Route::post('crud/shop/delete', 'delete');                                                                          // 07  ( Delete Shop )
        Route::post('rental/list-ulb-wise-circle', 'listUlbWiseCircle');                                                    // 08  ( Get List ULB Wise Circle )
        Route::post('rental/list-circle-wise-market', 'listCircleWiseMarket');                                              // 09  ( Get List Circle wise Market )
        Route::post('rental/list-shop-by-market-id', 'listShopByMarketId');                                                 // 10  ( Get Shop List By Market Id )
        Route::post('rental/list-shop', 'listShop');                                                                        // 11  ( Get List All Shop )
        Route::post('rental/get-shop-detail-by-id', 'getShopDetailtId');                                                    // 12  ( Get Shop Details By Id)
        Route::post('rental/get-shop-collection-summary', 'getShopCollectionSummary');                                      // 13  ( Get Shop Collection Summery )
        Route::post('rental/get-tc-collection', 'getTcCollection');                                                         // 14  ( Get TC Collection )
        Route::post('rental/shop-payment-by-admin', 'shopPaymentByAdmin');                                                  // 15  ( Shop Payment By Admin )
    });

    /**
     * | Tolls(51)
     * | Author - Ashutosh Kumar
     * | Change By - Bikash Kumar
     * | Status - Closed By Bikash Kumar ( 03 Oct 2023 )
     */
    Route::controller(TollsController::class)->group(function () {
        Route::post('toll-payments', 'tollPayments');                                                                     // 01  ( Make Toll Payment )
        Route::post('crud/toll/insert', 'store');                                                                         // 02  ( Add Toll Records )
        Route::post('crud/toll/edit', 'edit');                                                                            // 03  ( Edit Toll Records )
        Route::post('crud/toll/show-by-id', 'show');                                                                      // 04  ( Get Toll Details By Id )
        Route::post('crud/toll/retrieve-all', 'retrieve');                                                                // 05  ( Get List of All Toll Records ) 
        Route::post('crud/toll/retrieve-all-active', 'retrieveActive');                                                   // 06  ( Get List of All Active Toll Records )
        Route::post('crud/toll/delete', 'delete');                                                                        // 07  ( Delete Toll Records )
        Route::post('rental/get-toll-collection-summary', 'gettollCollectionSummary');                                    // 09  ( Get Toll Collection Summery Reports )
        Route::post('rental/list-toll-by-market-id', 'listTollByMarketId');                                               // 10  ( List Toll By Market Id )
        Route::post('rental/get-toll-detail-by-id', 'getTollDetailtId');                                                  // 11  ( Get Toll Details By Id )
        Route::post('rental/toll-payment-by-admin', 'tollPaymentByAdmin');                                                // 12  ( Toll Payment By Admin )
        Route::post('rental/get-toll-price-list', 'getTollPriceList');                                                    // 13  ( Get List of Price List)
    });


    /**
     * | Circle(52)
     */

    /**
     * | Created On - 16-06-2023 
     * | Author - Ashutosh Kumar
     * | Change By - Bikash Kumar
     * | Status - Closed By Bikash Kumar ( 03 Oct 2023 )
     */
    Route::controller(CircleController::class)->group(function () {
        Route::post('v1/crud/circle/insert', 'store');                                                                   // 01  ( Add Circle Records )
        Route::post('v1/crud/circle/update', 'edit');                                                                    // 02  ( Edit Details of Circle )
        Route::post('v1/crud/circle/list-circle-by-ulbId', 'getCircleByUlb');                                            // 03  ( Get List Circle By ULB Id )
        Route::post('v1/crud/circle/list-all-circle', 'retireveAll');                                                    // 04  ( Get List of All Circle )
        Route::post('v1/crud/circle/delete', 'delete');                                                                  // 05  ( Delete Circle )
    });

    /**
     * | Market(53)
     * | Author - Ashutosh Kumar
     * | Change By - Bikash Kumar
     * | Status - Closed By Bikash Kumar ( 03 Oct 2023 )
     */

    Route::controller(MarketController::class)->group(function () {
        Route::post('v1/crud/market/insert', 'store');                                                                  // 01  ( Add Market Records )
        Route::post('v1/crud/market/update', 'edit');                                                                   // 02  ( Edit Market Records )
        Route::post('v1/crud/market/list-market-by-circleId', 'getMarketByCircleId');                                   // 03  ( Get List of Market By Circle Id )
        Route::post('v1/crud/market/list-all-market', 'retireveAll');                                                   // 04  ( Get List of All Market )
        Route::post('v1/crud/market/delete', 'delete');                                                                 // 05  ( Delete Market Records )
        Route::post('rental/list-construction', 'listConstruction');                                                    // 06  ( Get List Construction )

    });
});
