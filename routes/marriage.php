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
        Route::post('upload-documents', 'docUpload');
        Route::post('list-documents', 'documentList');
        Route::post('offline-payment', 'offlinePayment');
    });
});
