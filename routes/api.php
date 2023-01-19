<?php

use App\Http\Controllers\Advertisements\AgencyController;
use App\Http\Controllers\Advertisements\PrivateLandController;
use App\Http\Controllers\Advertisements\SelfAdvetController;
use App\Http\Controllers\Advertisements\VehicleAdvetController;
use App\Http\Controllers\Params\ParamController;
use Illuminate\Routing\RouteGroup;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/**
 * | Routers for Advertisement Modules
 * | Created On-14-12-2022 
 * | Created By-Anshu Kumar
 * | Module Id for Advetisements=05
 */

Route::group(['middleware' => 'auth:sanctum'], function () {
    /**
     * | Self Advertisements
     * | Controller-01
     */
    Route::controller(SelfAdvetController::class)->group(function () {
        Route::post('advertisement/self-advert/save', 'store');       // 01 ( Save Application )
        Route::post('advertisement/self-advert/edit', 'edit');        // 02 ( Edit Application )
        Route::post('advertisement/self-advert/inbox', 'inbox');      // 03 ( Application Inbox Lists )
        Route::post('advertisement/self-advert/outbox', 'outbox');    // 04 ( Application Outbox Lists )
        Route::post('advertisement/self-advert/details', 'details');  // 05 ( Get Application Details By Application ID )
        Route::post('advertisement/self-advert/get-citizen-applications', 'getCitizenApplications');     // 06 ( Get Applied Applications List )
        Route::post('advertisement/self-advert/escalate', 'escalate');  // 07 ( Escalate or De-escalate Application )
        Route::post('advertisement/self-advert/special-inbox', 'specialInbox');  // 08 ( Special Inbox Applications )
        Route::post('advertisement/self-advert/level', 'postNextLevel');  // 09 ( Forward or Backward Application )
        Route::post('advertisement/self-advert/comment-independent', 'commentIndependent');  // 10 ( Independent Comment )
        Route::post('advertisement/self-advert/get-license-by-userid', 'getLicense');  // 11 ( Get License By User ID )
        Route::post('advertisement/self-advert/get-license-by-holding-no', 'getLicenseByHoldingNo');  // 11 ( Get License By Holding No )
        Route::post('advertisement/self-advert/get-license-details-by-license-no', 'getLicenseDetailso');  // 12 ( Get License Details By Licence No )
        Route::post('advertisement/self-advert/advertisement-upload-document', 'uploadDocuments');  // 13 ( Get Uploaded Document By Advertisement ID )
        Route::post('advertisement/self-advert/get-details-by-license-no', 'getDetailsByLicenseNo');  // 13 ( Get Uploaded Document By Advertisement ID )
    });

    /**
     * | Param Strings 
     * | Controller-02
     */
    Route::controller(ParamController::class)->group(function () {
        Route::post('crud/param-strings', 'paramStrings');          // 01
        Route::post('advertisements/crud/v1/document-mstrs', 'documentMstrs');      // 02
    });

    /**
     * | Movable Vehicles 
     * | Controller-03
     */
    Route::controller(VehicleAdvetController::class)->group(function () {
        Route::post('advertisement/movable-vehicle/save', 'store');    // 01
    });

    /**
     * | Private Lands
     * | Controller-04 
     */
    Route::controller(PrivateLandController::class)->group(function () {
        Route::post('advertisement/private-land/save', 'store'); // 01
    });

    /**
     * | Agency 
     * | Controller-05 
     */
    Route::controller(AgencyController::class)->group(function () {
        Route::post('advertisements/agency/save', 'store');             // 01
    });
});
