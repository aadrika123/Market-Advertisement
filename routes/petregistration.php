<?php

use App\Http\Controllers\Pet\PetPaymentController;
use App\Http\Controllers\Pet\PetRegistrationController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Pet Module Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an pet module.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/**
 * | ----------------------------------------------------------------------------------
 * | Pet Registration Module Routes |
 * |-----------------------------------------------------------------------------------
 * | Created On- 14-06-2023 
 * | Created For- The Routes defined for the Pet Registration System Module
 * | Created By- Sam kerketta
 */

Route::post('/pet-connection', function () {
    return ('Welcome to simple pet route file');                                                                // 00
});

/**
 * | Grouped Route for middleware
 */
Route::group(['middleware' => ['auth.citizen', 'json.response']], function () {
    /**
     * | Pet Registration Operation and more fundamental oprations
        | Serial No : 01
        | Status : Open
     */
    Route::controller(PetRegistrationController::class)->group(function () {
        Route::post('get-master-data', 'getAllMasters');                                                        // Admin/ Citizen        
        Route::post('application/apply-pet-registration', 'applyPetRegistration');                              // Citizen
        Route::post('application/get-doc-to-upload', 'getDocToUpload');                                         // Admin/ Citizen
        Route::post('application/upload-docs', 'uploadPetDoc');                                                 // Admin/ Citizen
        Route::post('application/get-uploaded-docs', 'getUploadDocuments');                                     // Admin/ Citizen
        Route::post('application/get-registration-list', 'getApplicationList');                                 // Citizen
        Route::post('application/get-details', 'getApplicationDetails');                                        // Citizen
        Route::post('application/delete', 'deletePetApplication');                                              // Citizen / Admin
        Route::post('application/get-prop-details', 'getSafHoldingDetails');
    });

    /**
     * | Pet Module payment Operations
     */
    Route::controller(PetPaymentController::class)->group(function () {
        Route::post("application/offline-payment", "offlinePayment");                                           // Admin
    });
});
