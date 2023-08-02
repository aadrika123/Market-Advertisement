<?php

use App\Http\Controllers\Marriage\MarriageRegistrationController;
use App\Models\Marriage\MarriageActiveRegistration;
use Illuminate\Support\Facades\Route;


/**
 * | Module Id = 10
 * | Marraige Registration
 */
// Route::group(['middleware' => ['auth.citizen', 'json.response']], function () {

#> Controller 01
Route::controller(MarriageRegistrationController::class)->group(function () {
    Route::post('apply', 'apply');                                              #API_ID=100101
    Route::post('get-doc-list', 'getDocList');                                  #API_ID=100102
    Route::post('upload-document', 'uploadDocument');                           #API_ID=100103
    Route::post('get-uploaded-document', 'getUploadedDocuments');               #API_ID=100104
    Route::post('static-details', 'staticDetails');                             #API_ID=100105
    Route::post('applied-application', 'listApplications');                     #API_ID=100106
    Route::post('inbox', 'inbox');                                              #API_ID=100107
    Route::post('details', 'details');                                          #API_ID=100108
    Route::post('set-appiontment-date', 'appointmentDate');                     #API_ID=100109
    Route::post('final-approval-rejection', 'approvalRejection');               #API_ID=100110
    Route::post("online-payment", "onlinePayment");                             #API_ID=100111
});
// });
