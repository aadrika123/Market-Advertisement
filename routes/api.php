<?php

use App\Http\Controllers\Advertisements\AgencyController;
use App\Http\Controllers\Advertisements\PrivateLandController;
use App\Http\Controllers\Advertisements\SelfAdvetController;
use App\Http\Controllers\Advertisements\VehicleAdvetController;
use App\Http\Controllers\Params\ParamController;
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
 * | Module Id for Advetisements=04
 */

Route::group(['middleware' => 'auth:sanctum'], function () {
    /**
     * | Self Advertisements
     * | Controller-01
     */
    Route::controller(SelfAdvetController::class)->group(function () {
        Route::post('advertisement/self-advert/save', 'store');     // 01
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
