<?php

use App\Http\Controllers\Pet\ReportController;
use App\Http\Controllers\Pet\PetPaymentController;
use App\Http\Controllers\Pet\PetRegistrationController;
use App\Http\Controllers\Pet\PetWorkflowController;
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
Route::group(['middleware' => ['json.response']], function () {
    /**
     * | Pet Registration Operation and more fundamental oprations
        | Serial No : 01
        | Status : Open
     */
    Route::controller(PetRegistrationController::class)->group(function () {
        Route::post('get-master-data', 'getAllMasters');                                                        // Admin/ Citizen        
        Route::post('application/apply-pet-registration', 'applyPetRegistration');                              // Citizen
        Route::post('application/get-registration-list', 'getApplicationList');                                 // Citizen
        Route::post('application/get-details', 'getApplicationDetails');                                        // Citizen
        Route::post('application/delete', 'deletePetApplication');   # ❗❗❗                                           // Citizen / Admin
        Route::post('application/get-prop-details', 'getSafHoldingDetails');
        Route::post('application/get-wf-detials', 'getApplicationsDetails');                                    // Workflow
        Route::post('application/edit-pet-details', 'editPetDetails');                                          // Admin / Citizen
        Route::post('application/edit-applicant-details', 'editApplicantDetails');                              // Not Used
        Route::post('citizen-holding-saf', 'citizenHoldingSaf');
        Route::post('registration/apply-renewal', 'applyPetRenewal');                                           // Admin / Citizen
        Route::post('application/searh-application', 'searchApplication');                                      // Admin

        Route::post('search-approved-applications', 'searchApprovedApplication');                               // Admin
        Route::post('get-approve-registration-list', 'getApprovedApplicationDetails');                          // Admin
        Route::post('get-approve-registrations', 'getApproveRegistration');                                     // Citizen

        Route::post('search-rejected-applications', 'searchRejectedApplication');                               // Admin
        Route::post('get-rejected-registration-list', 'getRejectedApplicationDetails');                         // Admin
        Route::post('get-rejected-registrations', 'getRejectedRegistration');

        Route::post('get-renewal-history', 'getRenewalHistory');
        Route::post('get-renewal-registration-details', 'getRenewalApplicationDetails');

        # Document Api
        Route::post('application/get-doc-to-upload', 'getDocToUpload');                                         // Admin/ Citizen
        Route::post('application/upload-docs', 'uploadPetDoc');                                                 // Admin/ Citizen
        Route::post('application/get-uploaded-docs', 'getUploadDocuments');                                     // Admin/ Citizen

        //written by prity pandey
        Route::post('dashboard_details', 'petDashbordDtls');

        //alok
        Route::post('application/searh-pet-details', 'getPetDetailsInfo');  
    });

    /**
     * | Pet Module payment Operations
     */
    Route::controller(PetPaymentController::class)->group(function () {
        Route::post("application/offline-payment", "offlinePayment");                                           // Admin
        Route::post("application/initiate-online-payment", "handelOnlinePayment");                              // Admin
        Route::post("application/payment-receipt", "generatePaymentReceipt");                                   // Admin / Citizen
        Route::post("application/license", "generateLicense");                                   // Admin / Citizen
    });

    /**
     * | Pet Workflow 
     */
    Route::controller(PetWorkflowController::class)->group(function () {
        Route::post('inbox', 'inbox');                                                                          // Workflow
        Route::post('outbox', 'outbox');                                                                        // Workflow
        Route::post('post-next-level', 'postNextLevel');                                                        // Workflow
        Route::post('special-inbox', 'waterSpecialInbox');                                                      // Workflow
        Route::post('escalate', 'postEscalate');                                                                // Workflow                     
        Route::post('doc-verify-reject', 'docVerifyRejects');                                                   // Workflow
        Route::post('final-verify-reject', 'finalApprovalRejection');                                           // Workflow
        Route::post('list-approved-application', 'listfinisherApproveApplications');                            // Workflow
        Route::post('list-rejected-application', 'listfinisherRejectApplications');                             // Workflow

        // written by prity pandey
        Route::post('back-to-citizen', 'backToCitizen');  
        Route::post('reupload-document', 'reuploadDocument');
        Route::post('send-to-next-level', 'forwardNextLevelBtc');  
        Route::post('btc-list', 'btcList');
    });

    //written by prity pandey
    Route::controller(ReportController::class)->group(function () {
        Route::post("application-type-reports", "applicationReports");   
        Route::post("payment-type-reports", "payCollectionReports"); 
        Route::post("vaccination-reports", "vaccinationReports");     
    });
});

/**
 * | Pet Module payment Operations
 */
Route::controller(PetPaymentController::class)->group(function () {
    Route::post("webhook/end-online-payment", "endOnlinePayment");                                              // Admin
    Route::post('cash-verification-list', 'listCashVerification');                                      #_List of Cash Verification --------------- 0703
    Route::post('cash-verification-dtl', 'cashVerificationDtl');                                        #_Cash Verification Detail ---------------- 0704
    Route::post('verify-cash', 'verifyCash');                                                           #_Verify Cash ----------------------------- 0705

    //written by prity pandey
    Route::post('search-transaction-no', 'searchTransactionNo'); 
    Route::post('deactivate-transaction', 'deactivateTransaction'); 
    Route::post('deactivate-transaction-list', 'deactivatedTransactionList'); 

});
