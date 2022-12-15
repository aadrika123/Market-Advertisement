<?php

use App\Http\Controllers\Advertisements\SelfAdvetController;
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
 */

// Self Advertisement Controller
Route::post('advertisement/self-advert/save', 'Advertisements\SelfAdvetController@store');

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::controller(SelfAdvetController::class)->group(function () {
        Route::post('advertisement/self-advert/save', 'store');
    });
});
