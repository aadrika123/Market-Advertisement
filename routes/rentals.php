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
use App\Http\Controllers\Rentals\PaymentController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['checkToken']], function () {
    /**
     * | Shops (50)
     * | Author - Anshu Kumar
     * | Change By - Bikash Kumar
     * | Status - Closed By Bikash Kumar ( 03 Oct 2023 )
     * | Changes By - Arshad Hussain 
     * | updated status - Open by Arshad Hussain (16 Apr 2024 )
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
        Route::post('rental/list-shop-by-market-id-v1', 'listShopByMarketIdv1');                                                 // 10  ( Get Shop List By Market Id )
        Route::post('rental/list-shop', 'listShop');                                                                        // 11  ( Get List All Shop )
        Route::post('rental/get-shop-detail-by-id', 'getShopDetailtId');                                                    // 12  ( Get Shop Details By Id)
        Route::post('rental/get-shop-collection-summary', 'getShopCollectionSummary');                                      // 13  ( Get Shop Collection Summery )
        Route::post('rental/get-tc-collection', 'getTcCollection');                                                         // 14  ( Get TC Collection )
        Route::post('rental/shop-payment-by-admin', 'shopPaymentByAdmin');                                                  // 15  ( Shop Payment By Admin )
        Route::post('rental/calculate-shop-price', 'calculateShopPrice');                                                   // 16  ( Calculate Shop Price )
        Route::post('rental/shop-reciept', 'shopReciept');                                                                  // 17  ( Get Shop Reciept )
        # Written BY  Arshad Hussain  
        Route::post('rental/generate-shop-demand', 'generateShopDemand');                                                   // 18  (Generate shop demand)
        Route::post('rental/entry-check-or-dd', 'entryCheckOrDD');                                                          // 19  (payment by cheque or dd )
        Route::post('rental/generate-shop-demand_bill', 'generateShopDemandBill');                                          // 20 (Generate shop demand bill  )
        Route::post('rental/calculate-shop-rate-month-wise', 'calculateShopRateMonhtlyWise');                               // 20 Calculate Shop Amount According to month wise 
        Route::post('rental/shop-payment-reciept-bt-print', 'shopPaymentRecieptBluetoothPrint');                            // 21  Get Shop Payment Receipt For Bluetooth Printer
        Route::post('rental/clear-bounce-cheque-or-dd', 'clearOrBounceChequeOrDD');                                         // 22 Update Data After Cheque is clear or bounce 
        Route::post('rental/list-uncleared-check-dd', 'listEntryCheckorDD');                                                // 23  List Entry Cheque/DD Details Data  
        Route::post('rental/list-cash-verification', 'listCashVerification');                                               // 24 List Cash Verification  
        Route::post('rental/list-details-cash-verification', 'listDetailCashVerification');                                 // 25  List Details Cash Verification User wise 
        Route::post('rental/verified-cash-payment', 'verifiedCashPayment');                                                 // 26  Verified Cash Payment 
        Route::post('rental/list-circle-wise-market', 'listCircleWiseMarket');                                              // 27 List Circle Wise Market
        Route::post('rental/search-shop-for-payment', 'searchShopForPayment');                                              // 28  Search Shop Data For Payment
        Route::post('rental/generate-all-shop-demands', 'generateAllShopDemand');                                           // 29  Search Shop Data For Payment
        Route::post('rental/transaction-deactivation', 'transactionDeactivation');                                          // 30  Deactive Transaction
        Route::post('rental/shop-master', 'shopMaster');                                                                    // 31  Get Shop Master Data
        Route::post('rental/edit-shop-data', 'editShopData');                                                               // 32  Edit Shop Details Data
        Route::post('rental/list-shop-demand', 'listShopDemand');                                                           // 33  Edit Shop Details Data
        Route::post('rental/generate-payment-order-id', 'generatePaymentOrderId');                                          // 34  (Initiate Online Payments)
        Route::post('rental/shop-reciept-list', 'shopRecieptList');                                                         // 35  Shop receipt 
        Route::post('rental/list-shop-collection', 'listShopCollection');                                                   // 36  List Shop Collection
        Route::post('rental/save-tran-dtl', 'storeTransactionDtl');                                                         // 37  END Online Payment
        Route::post('rental/search-shop-by-parameters', 'searchShopPipeline');                                               // 37  END Online Payment
        Route::post('rental/view-shop-documents', 'viewShopDocuments');
        Route::post('rental/view-shop-list', 'listShops');

        Route::post('municipal-rental/dashboard-details', 'dashboardDetails');                                          // Dashboard Details
    });

    /**
     * | Author - Arshad Hussain 
     * | payment Related Function 
     */

    Route::controller(PaymentController::class)->group(function () {
        Route::post('rental/search-transaction-no', 'searchTransactionNo');
        Route::post('rental/transaction-deactivated-list', 'transactionDeactList');
        #cash verification 
        Route::post('rental/cash-verification-list', 'listCashVerificationDtl');                                             #_List of Cash Verification --------------- 0703
        Route::post('cash-verification-dtl', 'cashVerificationDtl');                                                         #_Cash Verification Detail ---------------- 0704
        Route::post('verify-cash', 'verifyCash');                                                                            #_Verify Cash ----------------------------- 0705  
        # Bank Reconcillation
        Route::post('rental/search-transaction', 'searchTransaction');                                               // Search Transaction of Cheque
        Route::post('rental/cheque-dtl-by-id', 'chequeDtlById');                                                     // Get Detail of Cheque Transaction
        Route::post('rental/cheque-clearance', 'chequeClearance');                                                   // clear or bounce cheque

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
        Route::post('rental/get-toll-collection-summary', 'gettollCollectionSummary');                                    // 08  ( Get Toll Collection Summery Reports )
        Route::post('rental/list-toll-by-market-id', 'listTollByMarketId');                                               // 09  ( List Toll By Market Id )
        Route::post('rental/get-toll-detail-by-id', 'getTollDetailtId');                                                  // 10  ( Get Toll Details By Id )
        Route::post('rental/toll-payment-by-admin', 'tollPaymentByAdmin');                                                // 11  ( Toll Payment By Admin )
        Route::post('rental/get-toll-price-list', 'getTollPriceList');                                                    // 12  ( Get List of Price List)
        Route::post('rental/calculate-toll-price', 'calculateTollPrice');                                                 // 13  ( Calculate Toll Price )
        Route::post('rental/toll-reciept', 'tollReciept');                                                                // 14  ( Get Toll Reciept )
        Route::post('rental/generate-toll-demand', 'generateTollDemand');                                                 // 17  (Generate toll demand)
        // Route::post('rental/generate-payment-order-id', 'generatePaymentOrderId');                                     // 25 ( Generate Payment Order ID)
        Route::post('rental/toll/generate-payment-order-id', 'generatePaymentOrderId');                                        // 34  ( Generate Payment Order ID)
        Route::post('rental/toll-reciept-list', 'tollRecieptList');
        Route::post('rental/toll-list', 'listTools');
    });


    /**
     * | Circle(52)
     */

    /**
     * | Created On - 16-06-2023 
     * | Author - Ashutosh Kumar
     * | Change By - Bikash Kumar
    //  * | Status - Closed By Bikash Kumar ( 03 Oct 2023 )
     */
    Route::controller(CircleController::class)->group(function () {
        Route::post('add-circle', 'store');                                                                     // 01  ( Add Circle Records )
        Route::post('edit-circle', 'edit');                                                                     // 02  ( Edit Details of Circle )
        Route::post('get-list-circle', 'getListCircle');                                                        // 03  ( Get List Circle By ULB Id )
        // Route::post('delete-circle', 'delete');                                                              // 04  ( Delete Circle )
        Route::post('get-circle-detail-by-id', 'getCircleDetailById');                                          // 05  ( Get Circle Details By Id)
        Route::post('list-all-circle', 'listAllCircle');                                                        // 06  ( List All Circle )
    });

    /**
     * | Market(53)
     * | Author - Ashutosh Kumar
     * | Change By - Bikash Kumar
    //  * | Status - Closed By Bikash Kumar ( 03 Oct 2023 )
     */

    Route::controller(MarketController::class)->group(function () {
        Route::post('add-market', 'store');                                                                     // 01  ( Add Market Records )
        Route::post('edit-market', 'edit');                                                                     // 02  ( Edit Market Records )
        Route::post('list-market-by-circleId', 'getMarketByCircleId');                                          // 03  ( Get List of Market By Circle Id )
        Route::post('list-all-market', 'listAllMarket');                                                        // 04  ( Get List of All Market )
        // Route::post('delete-market', 'delete');                                                              // 05  ( Delete Market Records )
        Route::post('get-market-detail-by-id', 'getDetailByMarketId');                                          // 06  ( Get Market Records By Id )
        Route::post('rental/list-construction', 'listConstruction');                                            // 07  ( Get List Construction )

    });
});
