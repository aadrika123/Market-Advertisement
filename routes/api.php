<?php

use App\Http\Controllers\Advertisements\AgencyController;
use App\Http\Controllers\Advertisements\PrivateLandController;
use App\Http\Controllers\Advertisements\SelfAdvetController;
use App\Http\Controllers\Advertisements\VehicleAdvetController;
use App\Http\Controllers\Markets\BanquetMarriageHallController;
use App\Http\Controllers\Markets\LodgeController;
use App\Http\Controllers\Markets\HostelController;
use App\Http\Controllers\Markets\DharamshalaController;
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
Route::post('advertisements/payment-success-failure', [ParamController::class, 'paymentSuccessFailure']);


Route::group(['middleware' => 'auth.citizen', 'json.response'], function () {
    // Route::group(['middleware' => ['auth:sanctum', 'request_logger']], function () {
    /**
     * | Self Advertisements
     * | Controller-01
     */
    Route::controller(SelfAdvetController::class)->group(function () {
        Route::post('advert/self/add-new', 'addNew');       // 01 ( Save Application )
        Route::post('advert/self/get-application-details-for-renew', 'applicationDetailsForRenew');       // 01 ( Renew Application )
        Route::post('advert/self/renewal-selfAdvt', 'renewalSelfAdvt');       // 01 ( Renew Application )
        Route::post('advert/self/list-self-advt-category', 'listSelfAdvtCategory');       // 01 ( Save Application )
        Route::post('advert/self/list-inbox', 'listInbox');      // 02 ( Application Inbox Lists )
        Route::post('advert/self/list-outbox', 'listOutbox');    // 03 ( Application Outbox Lists )
        Route::post('advert/self/get-details-by-id', 'getDetailsById');  // 04 ( Get Application Details By Application ID )
        Route::post('advert/self/list-applied-applications', 'listAppliedApplications');     // 05 ( Get Applied Applications List By CityZen )
        Route::post('advert/self/escalate-application', 'escalateApplication');  // 06 ( Escalate or De-escalate Application )
        Route::post('advert/self/list-escalated', 'listEscalated');  // 07 ( Special Inbox Applications )
        Route::post('advert/self/forward-next-level', 'forwordNextLevel');  // 08 ( Forward or Backward Application )
        Route::post('advert/self/comment-application', 'commentApplication');  // 09 ( Independent Comment )
        Route::post('advert/self/get-license-by-id', 'getLicenseById');  // 10 ( Get License By User ID )
        Route::post('advert/self/get-license-by-holding-no', 'getLicenseByHoldingNo');  // 11 ( Get License By Holding No )
        // Route::post('advert/self/get-license-details-by-license-no', 'getLicenseDetailso');  // 12 ( Get License Details By Licence No )
        Route::post('advert/self/view-advert-document', 'viewAdvertDocument');  // 13 ( Get Uploaded Document By Advertisement ID )
        Route::post('advert/self/view-active-document', 'viewActiveDocument');  // 13 ( Get Uploaded Document By Advertisement ID )
        Route::post('advert/self/get-details-by-license-no', 'getDetailsByLicenseNo');  // 14 ( Get Uploaded Document By Advertisement ID )
        Route::post('advert/self/view-documents-on-workflow', 'viewDocumentsOnWorkflow');  // 15 ( View Uploaded Document By Advertisement ID )
        // Route::post('advert/self/workflow-upload-document', 'workflowUploadDocument');  // 16 ( Workflow Upload Document )
        Route::post('advert/self/approved-or-reject', 'approvalOrRejection');          // 17 ( Approve or Reject )
        Route::post('advert/self/list-approved', 'listApproved');          // 18 ( Approved list for Citizen)
        Route::post('advert/self/list-rejected', 'listRejected');          // 19 ( Rejected list for Citizen)
        Route::post('advert/self/get-jsk-applications', 'getJSKApplications');          // 20 ( Get Applied Applications List By JSK )
        Route::post('advert/self/list-jsk-approved-application', 'listJskApprovedApplication');          // 21 ( Approved list for JSK)
        Route::post('advert/self/list-jsk-rejected-application', 'listJskRejectedApplication');          // 22 ( Rejected list for JSK)    
        Route::post('advert/self/generate-payment-order-id', 'generatePaymentOrderId');          // 23 ( Generate Payment Order ID)
        Route::post('advert/self/get-application-details-for-payment', 'applicationDetailsForPayment');          // 24 ( Application Details For Payments )
        // Route::post('advert/self/get-payment-details', 'getPaymentDetails');          // 19 ( Application Details For Payments )
        Route::post('advert/self/payment-by-cash', 'paymentByCash');          // 19 ( Application Details For Payments )
        Route::post('advert/self/entry-cheque-dd', 'entryChequeDd');          // 19 ( Application Details For Payments )
        Route::post('advert/self/clear-or-bounce-cheque', 'clearOrBounceCheque');          // 19 ( Application Details For Payments )
        Route::post('advert/self/verify-or-reject-doc', 'verifyOrRejectDoc');          // 19 ( Application Details For Payments )
        Route::post('advert/self/back-to-citizen', 'backToCitizen');          // 19 ( Application Details For Payments )
        Route::post('advert/self/list-btc-inbox', 'listBtcInbox');          // 19 ( Application Details For Payments )
        // Route::post('advert/self/check-full-upload', 'checkFullUpload');          // 19 ( Application Details For Payments )
        Route::post('advert/self/reupload-document', 'reuploadDocument');          // 19 ( Application Details For Payments )
    });
    

    /**
     * | Param Strings 
     * | Controller-02
     */
    Route::controller(ParamController::class)->group(function () {
        Route::post('crud/param-strings', 'paramStrings');          // 01
        Route::post('advertisements/crud/v1/document-mstrs', 'documentMstrs');      // 02
        Route::post('crud/district-mstrs', 'districtMstrs');      // 03
        Route::post('advertisements/payment-success-failure', 'paymentSuccessFailure'); // 06 
        Route::post('advertisements/get-payment-details', 'getPaymentDetails');          // 19 ( Application Details For Payments )
     });

    /**
     * | Movable Vehicles 
     * | Controller-03
     */
    Route::controller(VehicleAdvetController::class)->group(function () {
        Route::post('advert/vehicle/add-new', 'addNew');    // 01 ( Save Application )
        Route::post('advert/vehicle/get-application-details-for-renew', 'applicationDetailsForRenew');       // 01 ( Renew Application )
        Route::post('advert/vehicle/renewal-application', 'renewalApplication');       // 01 ( Renew Application )
        Route::post('advert/vehicle/list-inbox', 'listInbox');    // 03 ( Application Inbox Lists )
        Route::post('advert/vehicle/list-outbox', 'listOutbox');    // 04 ( Application Outbox Lists )
        Route::post('advert/vehicle/get-details-by-id', 'getDetailsById');  // 05 ( Get Application Details By Application ID )
        Route::post('advert/vehicle/list-applied-applications', 'listAppliedApplications');     // 06 ( Get Applied Applications List )
        Route::post('advert/vehicle/escalate-application', 'escalateApplication');  // 07 ( Escalate or De-escalate Application )
        Route::post('advert/vehicle/list-escalated', 'listEscalated');  // 08 ( Special Inbox Applications )
        Route::post('advert/vehicle/forward-next-level', 'forwardNextLevel');  // 09 ( Forward or Backward Application )
        Route::post('advert/vehicle/comment-application', 'commentApplication');  // 10 ( Independent Comment )
        Route::post('advert/vehicle/view-vehicle-documents', 'viewVehicleDocuments');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('advert/vehicle/view-active-document', 'viewActiveDocument');  // 13 ( Get Uploaded Document By Advertisement ID )
        Route::post('advert/vehicle/view-documents-on-workflow', 'viewDocumentsOnWorkflow');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('advert/vehicle/approved-or-reject', 'approvedOrReject');          // 12 ( Approve or Reject )
        Route::post('advert/vehicle/list-approved', 'listApproved');          // 13 ( Approved list for Citizen)
        Route::post('advert/vehicle/list-rejected', 'listRejected');          // 14 ( Rejected list for Citizen)
        Route::post('advert/vehicle/get-jsk-applications', 'getJSKApplications');          // 20 ( Get Applied Applications List By JSK )
        Route::post('advert/vehicle/list-jsk-approved-application', 'listjskApprovedApplication');          // 15 ( Approved list for JSK)
        Route::post('advert/vehicle/list-jsk-rejected-application', 'listJskRejectedApplication');          // 16 ( Rejected list for JSK)  
        Route::post('advert/vehicle/generate-payment-order-id', 'generatePaymentOrderId');          // 17 ( Generate Payment Order ID)
        Route::post('advert/vehicle/get-application-details-for-payment', 'getApplicationDetailsForPayment');          // 18 ( Application Details For Payments )
        // Route::post('advert/vehicle/get-payment-details', 'getPaymentDetails');          // 19 ( Application Details For Payments )
        Route::post('advert/vehicle/payment-by-cash', 'paymentByCash');          // 19 ( Application Details For Payments )
        Route::post('advert/vehicle/entry-cheque-dd', 'entryChequeDd');          // 19 ( Application Details For Payments )
        Route::post('advert/vehicle/clear-or-bounce-cheque', 'clearOrBounceCheque');          // 19 ( Application Details For Payments )
        Route::post('advert/vehicle/entry-zone', 'entryZone');          // 19 ( Application Details For Payments )
        Route::post('advert/vehicle/verify-or-reject-doc', 'verifyOrRejectDoc');          // 19 ( Application Details For Payments )
        Route::post('advert/vehicle/back-to-citizen', 'backToCitizen');          // 19 ( Application Details For Payments )
        Route::post('advert/vehicle/list-btc-inbox', 'listBtcInbox');          // 19 ( Application Details For Payments )        
        // Route::post('advert/vehicle/check-full-upload', 'checkFullUpload');          // 19 ( Application Details For Payments )
        Route::post('advert/vehicle/reupload-document', 'reuploadDocument');          // 19 ( Application Details For Payments )
    });
    
    /**
     * | Private Lands
     * | Controller-04 
     */
    Route::controller(PrivateLandController::class)->group(function () {
        Route::post('advert/pvt-land/add-new', 'addNew'); // 01   ( Save Application )  Route::post('advert/vehicle/get-application-details-for-renew', 'applicationDetailsForRenew');       // 01 ( Renew Application )
        Route::post('advert/pvt-land/get-application-details-for-renew', 'applicationDetailsForRenew');       // 01 ( Renew Application )
        Route::post('advert/pvt-land/renewal-application', 'renewalApplication');       // 01 ( Renew Application ) 
        Route::post('advert/pvt-land/list-inbox', 'listInbox');    // 03 ( Application Inbox Lists )
        Route::post('advert/pvt-land/list-outbox', 'listOutbox');    // 04 ( Application Outbox Lists )
        Route::post('advert/pvt-land/get-details-by-id', 'getDetailsById');  // 05 ( Get Application Details By Application ID )
        Route::post('advert/pvt-land/list-applied-applications', 'listAppliedApplications');     // 06 ( Get Applied Applications List )
        Route::post('advert/pvt-land/escalate-application', 'escalateApplication');  // 07 ( Escalate or De-escalate Application )
        Route::post('advert/pvt-land/list-escalated', 'listEscalated');  // 08 ( Special Inbox Applications )
        Route::post('advert/pvt-land/forward-next-level', 'forwardNextLevel');  // 09 ( Forward or Backward Application )
        Route::post('advert/pvt-land/comment-application', 'commentApplication');  // 10 ( Independent Comment )
        Route::post('advert/pvt-land/view-pvt-land-documents', 'viewPvtLandDocuments');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('advert/pvt-land/view-active-document', 'viewActiveDocument');  // 13 ( Get Uploaded Document By Advertisement ID )
        Route::post('advert/pvt-land/view-documents-on-workflow', 'viewDocumentsOnWorkflow');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('advert/pvt-land/approved-or-reject', 'approvedOrReject');          // 12 ( Approve or Reject )
        Route::post('advert/pvt-land/list-approved', 'listApproved');          // 13 ( Approved list for Citizen)
        Route::post('advert/pvt-land/list-rejected', 'listRejected');          // 14 ( Rejected list for Citizen)
        Route::post('advert/pvt-land/get-jsk-applications', 'getJSKApplications');          // 15 ( Get Applied Applications List By JSK )
        Route::post('advert/pvt-land/list-jsk-approved-application', 'listjskApprovedApplication');          // 16 ( Approved list for JSK)
        Route::post('advert/pvt-land/list-jsk-rejected-application', 'listJskRejectedApplication');          // 17 ( Rejected list for JSK)  
        Route::post('advert/pvt-land/generate-payment-order-id', 'generatePaymentOrderId');          // 17 ( Generate Payment Order ID)
        Route::post('advert/pvt-land/get-application-details-for-payment', 'getApplicationDetailsForPayment');          // 18 ( Application Details For Payments )
        // Route::post('advert/pvt-land/get-payment-details', 'getPaymentDetails');          // 19 ( Application Details For Payments )
        Route::post('advert/pvt-land/payment-by-cash', 'paymentByCash');          // 19 ( Application Details For Payments )
        Route::post('advert/pvt-land/entry-cheque-dd', 'entryChequeDd');          // 19 ( Application Details For Payments )
        Route::post('advert/pvt-land/clear-or-bounce-cheque', 'clearOrBounceCheque');          // 19 ( Application Details For Payments )
        Route::post('advert/pvt-land/entry-zone', 'entryZone');          // 19 ( Application Details For Payments )
        Route::post('advert/pvt-land/verify-or-reject-doc', 'verifyOrRejectDoc');          // 19 ( Application Details For Payments )
        Route::post('advert/pvt-land/back-to-citizen', 'backToCitizen');          // 19 ( Application Details For Payments )
        Route::post('advert/pvt-land/list-btc-inbox', 'listBtcInbox');          // 19 ( Application Details For Payments )
        // Route::post('advert/pvt-land/check-full-upload', 'checkFullUpload');          // 19 ( Application Details For Payments )
        Route::post('advert/pvt-land/reupload-document', 'reuploadDocument');          // 19 ( Application Details For Payments )
    });

    /**
     * | Agency 
     * | Controller-05 
     */
    Route::controller(AgencyController::class)->group(function () {
        Route::post('advert/agency/get-agency-details', 'getagencyDetails');             //  ( Agency Details )

        Route::post('advert/agency/add-new', 'addNew');             // 01   ( Save Application )
        Route::post('advert/agency/list-inbox', 'listInbox');             // 03 ( Application Inbox Lists )
        Route::post('advert/agency/list-outbox', 'listOutbox');    // 04 ( Application Outbox Lists )
        Route::post('advert/agency/get-details-by-id', 'getDetailsById');  // 05 ( Get Application Details By Application ID )
        Route::post('advert/agency/list-applied-applications', 'listAppliedApplications');     // 06 ( Get Applied Applications List )
        Route::post('advert/agency/escalate-application', 'escalateApplication');  // 07 ( Escalate or De-escalate Application )
        Route::post('advert/agency/list-escalated', 'listEscalated');  // 08 ( Special Inbox Applications )
        Route::post('advert/agency/forward-next-level', 'forwardNextLevel');  // 09 ( Forward or Backward Application )
        Route::post('advert/agency/comment-application', 'commentApplication');  // 10 ( Independent Comment )
        Route::post('advert/agency/view-agency-documents', 'viewAgencyDocuments');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('advert/agency/view-active-document', 'viewActiveDocument');  // 13 ( Get Uploaded Document By Advertisement ID )
        Route::post('advert/agency/view-documents-on-workflow', 'viewDocumentsOnWorkflow');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('advert/agency/approved-or-reject', 'approvedOrReject');          // 12 ( Approve or Reject )
        Route::post('advert/agency/list-approved', 'listApproved');          // 13 ( Approved list for Citizen)
        Route::post('advert/agency/list-rejected', 'listRejected');          // 14 ( Rejected list for Citizen)
        Route::post('advert/agency/get-jsk-applications', 'getJSKApplications');          // 15 ( Get Applied Applications List By JSK )
        Route::post('advert/agency/list-jsk-approved-application', 'listjskApprovedApplication');          // 16 ( Approved list for JSK)
        Route::post('advert/agency/list-jsk-rejected-application', 'listJskRejectedApplication');          // 17 ( Rejected list for JSK)  
        Route::post('advert/agency/generate-payment-order-id', 'generatePaymentOrderId');          // 18 ( Generate Payment Order ID)
        Route::post('advert/agency/get-application-details-for-payment', 'getApplicationDetailsForPayment');          // 19 ( Application Details For Payments )
        // Route::post('advert/agency/get-payment-details', 'getPaymentDetails');          // 19 ( Application Details For Payments )
        Route::post('advert/agency/renewal-agency', 'renewalAgency');          // 19 ( Application Details For Payments )
        Route::post('advert/agency/payment-by-cash', 'agencyPaymentByCash');          // 19 ( Application Details For Payments )
        Route::post('advert/agency/entry-cheque-dd', 'entryChequeDd');          // 19 ( Application Details For Payments )
        Route::post('advert/agency/clear-or-bounce-cheque', 'clearOrBounceCheque');          // 19 ( Application Details For Payments )
        Route::post('advert/agency/list-approved-agency', 'listApprovedAgency');          // 19 ( Application Details For Payments )
        Route::post('advert/agency/verify-or-reject-doc', 'verifyOrRejectDoc');          // 19 ( Application Details For Payments )
        Route::post('advert/agency/back-to-citizen', 'backToCitizen');          // 19 ( Application Details For Payments )
        Route::post('advert/agency/list-btc-inbox', 'listBtcInbox');          // 19 ( Application Details For Payments )
        // Route::post('advert/agency/check-full-upload', 'checkFullUpload');          // 19 ( Application Details For Payments )
        Route::post('advert/agency/reupload-document', 'reuploadDocument');          // 19 ( Application Details For Payments )
        Route::post('advert/agency/search-by-name-or-mobile', 'searchByNameorMobile');          // 19 ( Application Details For Payments )

        /*------------ Apply For Hording License -------------------*/
        Route::post('advert/hording/get-hording-category', 'getHordingCategory');  // 20 ( Get Typology List )
        // Route::post('advert/hording/renewal-hording', 'renewalHording');  // 20 ( Get Typology List )
        Route::post('advert/hording/list-typology', 'listTypology');  // 20 ( Get Typology List )
        Route::post('advert/hording/add-new-license', 'addNewLicense');  // 21 ( Save Application For Licence )
        Route::post('advert/hording/list-license-inbox', 'listLicenseInbox');             // 22 ( Application Inbox Lists )
        Route::post('advert/hording/list-license-outbox', 'listLicenseOutbox');    // 23 ( Application Outbox Lists )
        Route::post('advert/hording/get-license-details-by-id', 'getLicenseDetailsById');  // 24 ( Get Application Details By Application ID )
        Route::post('advert/hording/list-license-applied-applications', 'listLicenseAppliedApplications');     // 25 ( Get Applied Applications List )
        Route::post('advert/hording/escalate-license-application', 'escalateLicenseApplication');  // 26 ( Escalate or De-escalate Application )
        Route::post('advert/hording/list-license-escalated', 'listLicenseEscalated');  // 27 ( Special Inbox Applications )
        Route::post('advert/hording/forward-license-next-level', 'forwardLicenseNextLevel');  // 28 ( Forward or Backward Application )
        Route::post('advert/hording/comment-license-application', 'commentLicenseApplication');  // 29 ( Independent Comment )
        Route::post('advert/hording/view-license-documents', 'viewLicenseDocuments');  // 30 ( Get Uploaded Document By Application ID )
        Route::post('advert/hording/view-active-document', 'viewActiveLicenseDocument');  // 13 ( Get Uploaded Document By Advertisement ID )
        Route::post('advert/hording/view-license-documents-on-workflow', 'viewLicenseDocumentsOnWorkflow');  // 30 ( Get Uploaded Document By Application ID )
        Route::post('advert/hording/approval-or-rejection-license', 'approvalOrRejectionLicense');          // 31 ( Approve or Reject )
        Route::post('advert/hording/list-approved-license', 'listApprovedLicense');          // 32 ( License Approved list for Citizen)
        Route::post('advert/hording/list-rejected-license', 'listRejectedLicense');          // 33 ( License Rejected list for Citizen)
        Route::post('advert/hording/list-unpaid-licenses', 'listUnpaidLicenses');          // 33 ( License Rejected list for Citizen)
        Route::post('advert/hording/get-jsk-license-applications', 'getJskLicenseApplications');          // 34 ( Get Applied Applications List By JSK )
        Route::post('advert/hording/list-jsk-approved-license-application', 'listJskApprovedLicenseApplication');          // 35 ( Approved list for JSK)
        Route::post('advert/hording/list-jsk-rejected-license-application', 'listJskRejectedLicenseApplication');          // 36 ( Rejected list for JSK)  
        Route::post('advert/hording/generate-license-payment-order-id', 'generateLicensePaymentOrderId');          // 37 ( Generate Payment Order ID)
        Route::post('advert/hording/get-license-application-details-for-payment', 'getLicenseApplicationDetailsForPayment');          // 38 ( Application Details For Payments )
        Route::post('advert/hording/get-hording-details-for-renew', 'getHordingDetailsForRenew');          // 38 ( Application Details For Payments )
        Route::post('advert/hording/renewal-hording', 'renewalHording');          // 19 ( Application Details For Payments )
        Route::post('advert/hording/payment-by-cash', 'paymentByCash');          // 19 ( Application Details For Payments )
        Route::post('advert/hording/entry-cheque-dd-license', 'entryChequeDdLicense');          // 19 ( Application Details For Payments )
        Route::post('advert/hording/clear-or-bounce-cheque-license', 'clearOrBounceChequeLicense');          // 19 ( Application Details For Payments )
        Route::post('advert/hording/verify-or-reject-doc', 'verifyOrRejectLicenseDoc');          // 19 ( Application Details For Payments )
        Route::post('advert/hording/back-to-citizen', 'backToCitizenLicense');          // 19 ( Application Details For Payments )
        Route::post('advert/hording/list-btc-inbox', 'listLicenseBtcInbox');          // 19 ( Application Details For Payments )
        // Route::post('advert/hording/check-full-upload', 'checkFullLicenseUpload1');          // 19 ( Application Details For Payments )
        Route::post('advert/hording/reupload-document', 'reuploadLicenseDocument');          // 19 ( Application Details For Payments )
        Route::post('advert/hording/get-renew-active-applications', 'getRenewActiveApplications'); // (Get Agency Dashboard)

        Route::post('advert/hording/list-expired-hording', 'listExpiredHording'); // (Get Expired Hording)
        Route::post('advert/hording/archived-hording', 'archivedHording'); // (Archieves Hording)
        Route::post('advert/hording/list-hording-archived', 'listHordingArchived'); // (list Expired Hording)
        Route::post('advert/hording/blacklist-hording', 'blacklistHording'); // (Blacklist Hording)
        Route::post('advert/hording/list-hording-blacklist', 'listHordingBlacklist'); // (list Blacklist Hording)


        //================================= Other Apis ===========================
        Route::post('advert/agency/is-agency', 'isAgency'); // (Get Agency Approve or not By Login Token)
        Route::post('advert/agency/get-agency-dashboard', 'getAgencyDashboard'); // (Get Agency Dashboard)
        Route::post('advert/hording/get-renew-application', 'getRenewApplication'); // (Get Agency Dashboard)
    });

    /**
     * | Lodge Controller
     * | Controller-06
     * | By - Bikash Kumar
     * | Date 21-02-2023
     */
    Route::controller(LodgeController::class)->group(function () {
        Route::post('market/lodge/add-new', 'addNew'); // 01   ( Save Application )  
        Route::post('market/lodge/list-inbox', 'listInbox');    // 03 ( Application Inbox Lists )
        Route::post('market/lodge/list-outbox', 'listOutbox');    // 04 ( Application Outbox Lists )
        Route::post('market/lodge/get-details-by-id', 'getDetailsById');  // 05 ( Get Application Details By Application ID )
        Route::post('market/lodge/list-applied-applications', 'listAppliedApplications');     // 06 ( Get Applied Applications List )
        Route::post('market/lodge/escalate-application', 'escalateApplication');  // 07 ( Escalate or De-escalate Application )
        Route::post('market/lodge/list-escalated', 'listEscalated');  // 08 ( Special Inbox Applications )
        Route::post('market/lodge/forward-next-level', 'forwardNextLevel');  // 09 ( Forward or Backward Application )
        Route::post('market/lodge/comment-application', 'commentApplication');  // 10 ( Independent Comment )
        Route::post('market/lodge/view-lodge-documents', 'viewLodgeDocuments');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('advert/lodge/view-active-document', 'viewActiveDocument');  // 13 ( Get Uploaded Document By Advertisement ID )
        Route::post('market/lodge/view-documents-on-workflow', 'viewDocumentsOnWorkflow');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('market/lodge/approved-or-reject', 'approvedOrReject');          // 12 ( Approve or Reject )
        Route::post('market/lodge/list-approved', 'listApproved');          // 13 ( Approved list for Citizen)
        Route::post('market/lodge/list-rejected', 'listRejected');          // 14 ( Rejected list for Citizen)
        Route::post('market/lodge/generate-payment-order-id', 'generatePaymentOrderId');          // 17 ( Generate Payment Order ID)
        Route::post('market/lodge/get-application-details-for-payment', 'getApplicationDetailsForPayment');          // 18 ( Application Details For Payments )
        // Route::post('market/lodge/get-payment-details', 'getApplicationDetailsForPayment');          // 18 ( Application Details For Payments )
        Route::post('market/lodge/verify-or-reject-doc', 'verifyOrRejectDoc');          // 19 ( Application Details For Payments )
        Route::post('market/lodge/back-to-citizen', 'backToCitizen');          // 19 ( Application Details For Payments )
        Route::post('market/lodge/list-btc-inbox', 'listBtcInbox');          // 19 ( Application Details For Payments )
        // Route::post('market/lodge/check-full-upload', 'checkFullUpload');          // 19 ( Application Details For Payments )
        Route::post('market/lodge/reupload-document', 'reuploadDocument');          // 19 ( Application Details For Payments )
        Route::post('market/lodge/payment-by-cash', 'paymentByCash');          // 19 ( Application Details For Payments )
        Route::post('market/lodge/entry-cheque-dd', 'entryChequeDd');          // 19 ( Application Details For Payments )
        Route::post('market/lodge/clear-or-bounce-cheque', 'clearOrBounceCheque');          // 19 ( Application Details For Payments )
    });

    /**
     * | Banquet Marriage Hall Controller
     * | Controller-07
     * | By - Bikash Kumar
     * | Date 18-02-2023
     * | Status - Open
     */
    Route::controller(BanquetMarriageHallController::class)->group(function () {
        Route::post('market/bm-hall/add-new', 'addNew'); // 01   ( Save Application )  
        Route::post('market/bm-hall/list-inbox', 'listInbox');    // 03 ( Application Inbox Lists )
        Route::post('market/bm-hall/list-outbox', 'listOutbox');    // 04 ( Application Outbox Lists )
        Route::post('market/bm-hall/get-details-by-id', 'getDetailsById');  // 05 ( Get Application Details By Application ID )
        Route::post('market/bm-hall/list-applied-applications', 'listAppliedApplications');     // 06 ( Get Applied Applications List )
        Route::post('market/bm-hall/escalate-application', 'escalateApplication');  // 07 ( Escalate or De-escalate Application )
        Route::post('market/bm-hall/list-escalated', 'listEscalated');  // 08 ( Special Inbox Applications )
        Route::post('market/bm-hall/forward-next-level', 'forwardNextLevel');  // 09 ( Forward or Backward Application )
        Route::post('market/bm-hall/comment-application', 'commentApplication');  // 10 ( Independent Comment )
        Route::post('market/bm-hall/view-bm-hall-documents', 'viewBmHallDocuments');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('advert/bm-hall/view-active-document', 'viewActiveDocument');  // 13 ( Get Uploaded Document By Advertisement ID )
        Route::post('market/bm-hall/view-documents-on-workflow', 'viewDocumentsOnWorkflow');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('market/bm-hall/approved-or-reject', 'approvedOrReject');          // 12 ( Approve or Reject )
        Route::post('market/bm-hall/list-approved', 'listApproved');          // 13 ( Approved list for Citizen)
        Route::post('market/bm-hall/list-rejected', 'listRejected');          // 14 ( Rejected list for Citizen)
        Route::post('market/bm-hall/generate-payment-order-id', 'generatePaymentOrderId');          // 17 ( Generate Payment Order ID)
        Route::post('market/bm-hall/get-application-details-for-payment', 'getApplicationDetailsForPayment');          // 18 ( Application Details For Payments )
        // Route::post('market/bm-hall/get-payment-details', 'getApplicationDetailsForPayment');          // 18 ( Application Details For Payments )
        Route::post('market/bm-hall/verify-or-reject-doc', 'verifyOrRejectDoc');          // 19 ( Application Details For Payments )
        Route::post('market/bm-hall/back-to-citizen', 'backToCitizen');          // 19 ( Application Details For Payments )
        Route::post('market/bm-hall/list-btc-inbox', 'listBtcInbox');          // 19 ( Application Details For Payments )
        // Route::post('market/bm-hall/check-full-upload', 'checkFullUpload');          // 19 ( Application Details For Payments )
        Route::post('market/bm-hall/reupload-document', 'reuploadDocument');          // 19 ( Application Details For Payments )
        Route::post('market/bm-hall/payment-by-cash', 'paymentByCash');          // 19 ( Application Details For Payments )
        Route::post('market/bm-hall/entry-cheque-dd', 'entryChequeDd');          // 19 ( Application Details For Payments )
        Route::post('market/bm-hall/clear-or-bounce-cheque', 'clearOrBounceCheque');          // 19 ( Application Details For Payments )
    });


    /**
     * | Hostel Controller
     * | Controller-08
     * | By - Bikash Kumar
     * | Date 20-02-2023
     */
    Route::controller(HostelController::class)->group(function () {
        Route::post('market/hostel/add-new', 'addNew'); // 01   ( Save Application )  
        Route::post('market/hostel/list-inbox', 'listInbox');    // 03 ( Application Inbox Lists )
        Route::post('market/hostel/list-outbox', 'listOutbox');    // 04 ( Application Outbox Lists )
        Route::post('market/hostel/get-details-by-id', 'getDetailsById');  // 05 ( Get Application Details By Application ID )
        Route::post('market/hostel/list-applied-applications', 'listAppliedApplications');     // 06 ( Get Applied Applications List )
        Route::post('market/hostel/escalate-application', 'escalateApplication');  // 07 ( Escalate or De-escalate Application )
        Route::post('market/hostel/list-escalated', 'listEscalated');  // 08 ( Special Inbox Applications )
        Route::post('market/hostel/forward-next-level', 'forwardNextLevel');  // 09 ( Forward or Backward Application )
        Route::post('market/hostel/comment-application', 'commentApplication');  // 10 ( Independent Comment )
        Route::post('market/hostel/view-hostel-documents', 'viewHostelDocuments');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('advert/hostel/view-active-document', 'viewActiveDocument');  // 13 ( Get Uploaded Document By Advertisement ID )
        Route::post('market/hostel/view-documents-on-workflow', 'viewDocumentsOnWorkflow');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('market/hostel/approved-or-reject', 'approvedOrReject');          // 12 ( Approve or Reject )
        Route::post('market/hostel/list-approved', 'listApproved');          // 13 ( Approved list for Citizen)
        Route::post('market/hostel/list-rejected', 'listRejected');          // 14 ( Rejected list for Citizen)
        Route::post('market/hostel/generate-payment-order-id', 'generatePaymentOrderId');          // 17 ( Generate Payment Order ID)
        Route::post('market/hostel/get-application-details-for-payment', 'getApplicationDetailsForPayment');          // 18 ( Application Details For Payments )
        // Route::post('market/hostel/get-payment-details', 'getPaymentDetails');          // 18 ( Application Details For Payments )
        Route::post('market/hostel/verify-or-reject-doc', 'verifyOrRejectDoc');          // 19 ( Application Details For Payments )
        Route::post('market/hostel/back-to-citizen', 'backToCitizen');          // 19 ( Application Details For Payments )
        Route::post('market/hostel/list-btc-inbox', 'listBtcInbox');          // 19 ( Application Details For Payments )
        // Route::post('market/hostel/check-full-upload', 'checkFullUpload');          // 19 ( Application Details For Payments )
        Route::post('market/hostel/reupload-document', 'reuploadDocument');          // 19 ( Application Details For Payments )
        Route::post('market/hostel/payment-by-cash', 'paymentByCash');          // 19 ( Application Details For Payments )
        Route::post('market/hostel/entry-cheque-dd', 'entryChequeDd');          // 19 ( Application Details For Payments )
        Route::post('market/hostel/clear-or-bounce-cheque', 'clearOrBounceCheque');          // 19 ( Application Details For Payments )
    });

    /**
     * | Dharamshala Controller
     * | Controller-09
     * | By - Bikash Kumar
     * | Date 21-02-2023
     */
    Route::controller(DharamshalaController::class)->group(function () {
        Route::post('market/dharamshala/add-new', 'addNew'); // 01   ( Save Application )  
        Route::post('market/dharamshala/list-inbox', 'listInbox');    // 03 ( Application Inbox Lists )
        Route::post('market/dharamshala/list-outbox', 'listOutbox');    // 04 ( Application Outbox Lists )
        Route::post('market/dharamshala/get-details-by-id', 'getDetailsById');  // 05 ( Get Application Details By Application ID )
        Route::post('market/dharamshala/list-applied-applications', 'listAppliedApplications');     // 06 ( Get Applied Applications List )
        Route::post('market/dharamshala/escalate-application', 'escalateApplication');  // 07 ( Escalate or De-escalate Application )
        Route::post('market/dharamshala/list-escalated', 'listEscalated');  // 08 ( Special Inbox Applications )
        Route::post('market/dharamshala/forward-next-level', 'forwardNextLevel');  // 09 ( Forward or Backward Application )
        Route::post('market/dharamshala/comment-application', 'commentApplication');  // 10 ( Independent Comment )
        Route::post('market/dharamshala/view-dharamshala-documents', 'viewDharamshalaDocuments');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('advert/dharamshala/view-active-document', 'viewActiveDocument');  // 13 ( Get Uploaded Document By Advertisement ID )
        Route::post('market/dharamshala/view-documents-on-workflow', 'viewDocumentsOnWorkflow');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('market/dharamshala/approved-or-reject', 'approvedOrReject');          // 12 ( Approve or Reject )
        Route::post('market/dharamshala/list-approved', 'listApproved');          // 13 ( Approved list for Citizen)
        Route::post('market/dharamshala/list-rejected', 'listRejected');          // 14 ( Rejected list for Citizen)
        Route::post('market/dharamshala/generate-payment-order-id', 'generatePaymentOrderId');          // 17 ( Generate Payment Order ID)
        Route::post('market/dharamshala/get-application-details-for-payment', 'getApplicationDetailsForPayment');          // 18 ( Application Details For Payments )
        // Route::post('market/dharamshala/get-payment-details', 'getPaymentDetails');          // 18 ( Application Details For Payments )
        Route::post('market/dharamshala/verify-or-reject-doc', 'verifyOrRejectDoc');          // 19 ( Application Details For Payments )
        Route::post('market/dharamshala/back-to-citizen', 'backToCitizen');          // 19 ( Application Details For Payments )
        Route::post('market/dharamshala/list-btc-inbox', 'listBtcInbox');          // 19 ( Application Details For Payments )
        // Route::post('market/dharamshala/check-full-upload', 'checkFullUpload');          // 19 ( Application Details For Payments )
        Route::post('market/dharamshala/reupload-document', 'reuploadDocument');          // 19 ( Application Details For Payments )
        Route::post('market/dharamshala/payment-by-cash', 'paymentByCash');          // 19 ( Application Details For Payments )
        Route::post('market/dharamshala/entry-cheque-dd', 'entryChequeDd');          // 19 ( Application Details For Payments )
        Route::post('market/dharamshala/clear-or-bounce-cheque', 'clearOrBounceCheque');          // 19 ( Application Details For Payments )
     });

});
