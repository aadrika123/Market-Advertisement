<?php

use App\Http\Controllers\Marriage\MarriageRegistrationController;
use App\Models\Marriage\MarriageActiveRegistration;
use Illuminate\Support\Facades\Route;


/**
 * | Marraige Registration
 */
Route::group(['middleware' => ['auth.citizen', 'json.response']], function () {

    Route::controller(MarriageRegistrationController::class)->group(function () {
        Route::post('apply', 'apply');
        Route::post('get-doc-list', 'getDocList');
        Route::post('upload-document', 'uploadDocument');
        Route::post('uploaded-document', 'uploadedDocument');
        Route::post('offline-payment', 'offlinePayment');
    });
});
