<?php

use Illuminate\Support\Facades\Route;


/**
 * | Marraige Registration
 */
Route::controller(PropMaster::class)->group(function () {
    Route::post('apply', 'apply');
    Route::post('upload-documents', 'docUpload');
    Route::post('list-documents', 'documentList');
    Route::post('offline-payment', 'offlinePayment');
});
