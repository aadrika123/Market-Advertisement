<?php

use App\Http\Controllers\Advertisements\AgencyController;
use App\Http\Controllers\Advertisements\PrivateLandController;
use App\Http\Controllers\Advertisements\SelfAdvetController;
use App\Http\Controllers\Advertisements\VehicleAdvetController;
use App\Http\Controllers\Markets\BanquetMarriageHallController;
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
        Route::post('advert/self/list-inbox', 'listInbox');      // 02 ( Application Inbox Lists )
        Route::post('advert/self/list-outbox', 'listOutbox');    // 03 ( Application Outbox Lists )
        Route::post('advert/self/get-details-by-id', 'getDetailsById');  // 04 ( Get Application Details By Application ID )
        Route::post('advert/self/list-applied-applications', 'listAppliedApplications');     // 05 ( Get Applied Applications List By CityZen )
        Route::post('advert/self/escalate-application', 'escalateApplication');  // 06 ( Escalate or De-escalate Application )
        Route::post('advert/self/list-special-inbox', 'listSpecialInbox');  // 07 ( Special Inbox Applications )
        Route::post('advert/self/forward-next-level', 'forwordNextLevel');  // 08 ( Forward or Backward Application )
        Route::post('advert/self/comment-application', 'commentApplication');  // 09 ( Independent Comment )
        Route::post('advert/self/get-license-by-id', 'getLicenseById');  // 10 ( Get License By User ID )
        Route::post('advert/self/get-license-by-holding-no', 'getLicenseByHoldingNo');  // 11 ( Get License By Holding No )
        // Route::post('advert/self/get-license-details-by-license-no', 'getLicenseDetailso');  // 12 ( Get License Details By Licence No )
        Route::post('advert/self/view-advert-document', 'viewAdvertDocument');  // 13 ( Get Uploaded Document By Advertisement ID )
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
    });

    /**
     * | Param Strings 
     * | Controller-02
     */
    Route::controller(ParamController::class)->group(function () {
        Route::post('crud/param-strings', 'paramStrings');          // 01
        Route::post('advertisements/crud/v1/document-mstrs', 'documentMstrs');      // 02
        Route::post('advertisements/crud/v1/district-mstrs', 'districtMstrs');      // 03
        Route::post('advertisements/payment-success-failure', 'paymentSuccessFailure'); // 06
    });

    /**
     * | Movable Vehicles 
     * | Controller-03
     */
    Route::controller(VehicleAdvetController::class)->group(function () {
        Route::post('advert/vehicle/add-new', 'addNew');    // 01 ( Save Application )\
        Route::post('advert/vehicle/list-inbox', 'listInbox');    // 03 ( Application Inbox Lists )
        Route::post('advert/vehicle/list-outbox', 'listOutbox');    // 04 ( Application Outbox Lists )
        Route::post('advert/vehicle/get-details-by-id', 'getDetailsById');  // 05 ( Get Application Details By Application ID )
        Route::post('advert/vehicle/list-applied-applications', 'listAppliedApplications');     // 06 ( Get Applied Applications List )
        Route::post('advert/vehicle/escalate-application', 'escalateApplication');  // 07 ( Escalate or De-escalate Application )
        Route::post('advert/vehicle/list-escalated', 'listEscalated');  // 08 ( Special Inbox Applications )
        Route::post('advert/vehicle/forward-next-level', 'forwardNextLevel');  // 09 ( Forward or Backward Application )
        Route::post('advert/vehicle/comment-application', 'commentApplication');  // 10 ( Independent Comment )
        Route::post('advert/vehicle/view-vehicle-documents', 'viewVehicleDocuments');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('advert/vehicle/approved-or-reject', 'approvedOrReject');          // 12 ( Approve or Reject )
        Route::post('advert/vehicle/list-approved', 'listApproved');          // 13 ( Approved list for Citizen)
        Route::post('advert/vehicle/list-rejected', 'listRejected');          // 14 ( Rejected list for Citizen)

        Route::post('advert/vehicle/get-jsk-applications', 'getJSKApplications');          // 20 ( Get Applied Applications List By JSK )
        Route::post('advert/vehicle/list-jsk-approved-application', 'listjskApprovedApplication');          // 15 ( Approved list for JSK)
        Route::post('advert/vehicle/list-jsk-rejected-application', 'listJskRejectedApplication');          // 16 ( Rejected list for JSK)  
        Route::post('advert/vehicle/generate-payment-order-id', 'generatePaymentOrderId');          // 17 ( Generate Payment Order ID)
        Route::post('advert/vehicle/get-application-details-for-payment', 'getApplicationDetailsForPayment');          // 18 ( Application Details For Payments )
    });

    /**
     * | Private Lands
     * | Controller-04 
     */
    Route::controller(PrivateLandController::class)->group(function () {
        Route::post('advert/pvt-land/add-new', 'addNew'); // 01   ( Save Application )  
        Route::post('advert/pvt-land/list-inbox', 'listInbox');    // 03 ( Application Inbox Lists )
        Route::post('advert/pvt-land/list-outbox', 'listOutbox');    // 04 ( Application Outbox Lists )
        Route::post('advert/pvt-land/get-details-by-id', 'getDetailsById');  // 05 ( Get Application Details By Application ID )
        Route::post('advert/pvt-land/list-applied-applications', 'listAppliedApplications');     // 06 ( Get Applied Applications List )
        Route::post('advert/pvt-land/escalate-application', 'escalateApplication');  // 07 ( Escalate or De-escalate Application )
        Route::post('advert/pvt-land/list-escalated', 'listEscalated');  // 08 ( Special Inbox Applications )
        Route::post('advert/pvt-land/forward-next-level', 'forwardNextLevel');  // 09 ( Forward or Backward Application )
        Route::post('advert/pvt-land/comment-application', 'commentApplication');  // 10 ( Independent Comment )
        Route::post('advert/pvt-land/view-pvt-land-documents', 'viewPvtLandDocuments');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('advert/pvt-land/approved-or-reject', 'approvedOrReject');          // 12 ( Approve or Reject )
        Route::post('advert/pvt-land/list-approved', 'listApproved');          // 13 ( Approved list for Citizen)
        Route::post('advert/pvt-land/list-rejected', 'listRejected');          // 14 ( Rejected list for Citizen)
        Route::post('advert/pvt-land/get-jsk-applications', 'getJSKApplications');          // 15 ( Get Applied Applications List By JSK )
        Route::post('advert/pvt-land/list-jsk-approved-application', 'listjskApprovedApplication');          // 16 ( Approved list for JSK)
        Route::post('advert/pvt-land/list-jsk-rejected-application', 'listJskRejectedApplication');          // 17 ( Rejected list for JSK)  
        Route::post('advert/pvt-land/generate-payment-order-id', 'generatePaymentOrderId');          // 17 ( Generate Payment Order ID)
        Route::post('advert/pvt-land/get-application-details-for-payment', 'getApplicationDetailsForPayment');          // 18 ( Application Details For Payments )
    });

    /**
     * | Agency 
     * | Controller-05 
     */
    Route::controller(AgencyController::class)->group(function () {
        Route::post('advertisement/agency/get-agency-details', 'agencyDetails');             //  ( Agency Details )

        Route::post('advertisement/agency/save', 'store');             // 01   ( Save Application )
        Route::post('advertisement/agency/inbox', 'inbox');             // 03 ( Application Inbox Lists )
        Route::post('advertisement/agency/outbox', 'outbox');    // 04 ( Application Outbox Lists )
        Route::post('advertisement/agency/details', 'details');  // 05 ( Get Application Details By Application ID )
        Route::post('advertisement/agency/get-citizen-applications', 'getCitizenApplications');     // 06 ( Get Applied Applications List )
        Route::post('advertisement/agency/escalate', 'escalate');  // 07 ( Escalate or De-escalate Application )
        Route::post('advertisement/agency/special-inbox', 'specialInbox');  // 08 ( Special Inbox Applications )
        Route::post('advertisement/agency/post-next-level', 'postNextLevel');  // 09 ( Forward or Backward Application )
        Route::post('advertisement/agency/comment-independent', 'commentIndependent');  // 10 ( Independent Comment )
        Route::post('advertisement/agency/agency-document-view', 'uploadDocumentsView');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('advertisement/agency/approval-rejection', 'finalApprovalRejection');          // 12 ( Approve or Reject )
        Route::post('advertisement/agency/approved-list', 'approvedList');          // 13 ( Approved list for Citizen)
        Route::post('advertisement/agency/rejected-list', 'rejectedList');          // 14 ( Rejected list for Citizen)
        Route::post('advertisement/agency/get-jsk-applications', 'getJSKApplications');          // 15 ( Get Applied Applications List By JSK )
        Route::post('advertisement/agency/jsk-approved-list', 'jskApprovedList');          // 16 ( Approved list for JSK)
        Route::post('advertisement/agency/jsk-rejected-list', 'jskRejectedList');          // 17 ( Rejected list for JSK)  
        Route::post('advertisement/agency/generate-payment-order-id', 'generatePaymentOrderId');          // 18 ( Generate Payment Order ID)
        Route::post('advertisement/agency/application-details-for-payment', 'applicationDetailsForPayment');          // 19 ( Application Details For Payments )

        /*------------ Apply For Hording License -------------------*/
        Route::post('advertisement/hording/get-typology-list', 'getTypologyList');  // 20 ( Get Typology List )
        Route::post('advertisement/hording/licence-save', 'saveForLicence');  // 21 ( Save Application For Licence )
        Route::post('advertisement/hording/license-inbox', 'licenseInbox');             // 22 ( Application Inbox Lists )
        Route::post('advertisement/hording/license-outbox', 'licenseOutbox');    // 23 ( Application Outbox Lists )
        Route::post('advertisement/hording/license-details', 'licenseDetails');  // 24 ( Get Application Details By Application ID )
        Route::post('advertisement/hording/license-get-citizen-applications', 'licenseGetCitizenApplications');     // 25 ( Get Applied Applications List )
        Route::post('advertisement/hording/license-escalate', 'licenseEscalate');  // 26 ( Escalate or De-escalate Application )
        Route::post('advertisement/hording/license-special-inbox', 'licenseSpecialInbox');  // 27 ( Special Inbox Applications )
        Route::post('advertisement/hording/license-post-next-level', 'licensePostNextLevel');  // 28 ( Forward or Backward Application )
        Route::post('advertisement/hording/license-comment-independent', 'LicenseCommentIndependent');  // 29 ( Independent Comment )
        Route::post('advertisement/hording/license-hording-document-view', 'licenseUploadDocumentsView');  // 30 ( Get Uploaded Document By Application ID )
        Route::post('advertisement/hording/license-approval-rejection', 'licenseFinalApprovalRejection');          // 31 ( Approve or Reject )
        Route::post('advertisement/hording/license-approved-list', 'licenseApprovedList');          // 32 ( License Approved list for Citizen)
        Route::post('advertisement/hording/license-rejected-list', 'licenseRejectedList');          // 33 ( License Rejected list for Citizen)
        Route::post('advertisement/hording/license-get-jsk-applications', 'licenseGetJSKApplications');          // 34 ( Get Applied Applications List By JSK )
        Route::post('advertisement/hording/license-jsk-approved-list', 'licenseJskApprovedList');          // 35 ( Approved list for JSK)
        Route::post('advertisement/hording/license-jsk-rejected-list', 'licenseJskRejectedList');          // 36 ( Rejected list for JSK)  
        Route::post('advertisement/hording/license-generate-payment-order-id', 'licenseGeneratePaymentOrderId');          // 37 ( Generate Payment Order ID)
        Route::post('advertisement/hording/license-application-details-for-payment', 'licenseApplicationDetailsForPayment');          // 38 ( Application Details For Payments )

        //================================= Other Apis ===========================
        Route::post('advertisement/agency/is-agency', 'isAgency'); // (Get Agency Approve or not By Login Token)
        Route::post('advertisement/agency/agency-dashboard', 'agencyDashboard'); // (Get Agency Dashboard)


    });

    /**
     * | Lodge Controller
     * | Controller-06
     * | By - Bikash Kumar
     * | Date 06-02-2023
     */
    Route::controller(LodgeController::class)->group(function () {
        Route::post('market/lodge/save', 'store'); // 01   ( Save Application )  
        Route::post('market/lodge/inbox', 'inbox');    // 03 ( Application Inbox Lists )
        Route::post('market/lodge/outbox', 'outbox');    // 04 ( Application Outbox Lists )
        Route::post('market/lodge/details', 'details');  // 05 ( Get Application Details By Application ID )
        Route::post('market/lodge/get-citizen-applications', 'getCitizenApplications');     // 06 ( Get Applied Applications List )
        Route::post('market/lodge/escalate', 'escalate');  // 07 ( Escalate or De-escalate Application )
        Route::post('market/lodge/special-inbox', 'specialInbox');  // 08 ( Special Inbox Applications )
        Route::post('market/lodge/post-next-level', 'postNextLevel');  // 09 ( Forward or Backward Application )
        Route::post('market/lodge/comment-independent', 'commentIndependent');  // 10 ( Independent Comment )
        Route::post('market/lodge/private-land-document-view', 'uploadDocumentsView');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('market/lodge/approval-rejection', 'finalApprovalRejection');          // 12 ( Approve or Reject )
        Route::post('market/lodge/approved-list', 'approvedList');          // 13 ( Approved list for Citizen)
        Route::post('market/lodge/rejected-list', 'rejectedList');          // 14 ( Rejected list for Citizen)
        Route::post('market/lodge/get-jsk-applications', 'getJSKApplications');          // 15 ( Get Applied Applications List By JSK )
        Route::post('market/lodge/jsk-approved-list', 'jskApprovedList');          // 16 ( Approved list for JSK)
        Route::post('market/lodge/jsk-rejected-list', 'jskRejectedList');          // 17 ( Rejected list for JSK)  
        Route::post('market/lodge/generate-payment-order-id', 'generatePaymentOrderId');          // 17 ( Generate Payment Order ID)
        Route::post('market/lodge/application-details-for-payment', 'applicationDetailsForPayment');          // 18 ( Application Details For Payments )

    });

    /**
     * | Banquet Marriage Hall Controller
     * | Controller-07
     * | By - Bikash Kumar
     * | Date 09-02-2023
     * | Status - Open
     */
    Route::controller(BanquetMarriageHallController::class)->group(function () {
        Route::post('market/banquet-marriage-hall/save', 'store'); // 01   ( Save Application )  
        Route::post('market/banquet-marriage-hall/inbox', 'inbox');    // 03 ( Application Inbox Lists )
        Route::post('market/banquet-marriage-hall/outbox', 'outbox');    // 04 ( Application Outbox Lists )
        Route::post('market/banquet-marriage-hall/details', 'details');  // 05 ( Get Application Details By Application ID )
        Route::post('market/banquet-marriage-hall/get-citizen-applications', 'getCitizenApplications');     // 06 ( Get Applied Applications List )
        Route::post('market/banquet-marriage-hall/escalate', 'escalate');  // 07 ( Escalate or De-escalate Application )
        Route::post('market/banquet-marriage-hall/special-inbox', 'specialInbox');  // 08 ( Special Inbox Applications )
        Route::post('market/banquet-marriage-hall/post-next-level', 'postNextLevel');  // 09 ( Forward or Backward Application )
        Route::post('market/banquet-marriage-hall/comment-independent', 'commentIndependent');  // 10 ( Independent Comment )
        Route::post('market/banquet-marriage-hall/banquet-marriage-hall-document-view', 'uploadDocumentsView');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('market/banquet-marriage-hall/approval-rejection', 'finalApprovalRejection');          // 12 ( Approve or Reject )
        Route::post('market/banquet-marriage-hall/approved-list', 'approvedList');          // 13 ( Approved list for Citizen)
        Route::post('market/banquet-marriage-hall/rejected-list', 'rejectedList');          // 14 ( Rejected list for Citizen)
        Route::post('market/banquet-marriage-hall/get-jsk-applications', 'getJSKApplications');          // 15 ( Get Applied Applications List By JSK )
        Route::post('market/banquet-marriage-hall/jsk-approved-list', 'jskApprovedList');          // 16 ( Approved list for JSK)
        Route::post('market/banquet-marriage-hall/jsk-rejected-list', 'jskRejectedList');          // 17 ( Rejected list for JSK)  
        Route::post('market/banquet-marriage-hall/generate-payment-order-id', 'generatePaymentOrderId');          // 17 ( Generate Payment Order ID)
        Route::post('market/banquet-marriage-hall/application-details-for-payment', 'applicationDetailsForPayment');          // 18 ( Application Details For Payments )
    });
});
