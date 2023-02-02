<?php

use App\Http\Controllers\Advertisements\AgencyController;
use App\Http\Controllers\Advertisements\PrivateLandController;
use App\Http\Controllers\Advertisements\SelfAdvetController;
use App\Http\Controllers\Advertisements\VehicleAdvetController;
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

Route::group(['middleware' => 'auth:sanctum'], function () {
    /**
     * | Self Advertisements
     * | Controller-01
     */
    Route::controller(SelfAdvetController::class)->group(function () {
        Route::post('advertisement/self-advert/save', 'store');       // 01 ( Save Application )
        Route::post('advertisement/self-advert/edit', 'edit');        // 02 ( Edit Application )
        Route::post('advertisement/self-advert/inbox', 'inbox');      // 03 ( Application Inbox Lists )
        Route::post('advertisement/self-advert/outbox', 'outbox');    // 04 ( Application Outbox Lists )
        Route::post('advertisement/self-advert/details', 'details');  // 05 ( Get Application Details By Application ID )
        Route::post('advertisement/self-advert/get-citizen-applications', 'getCitizenApplications');     // 06 ( Get Applied Applications List By CityZen )
        Route::post('advertisement/self-advert/escalate', 'escalate');  // 07 ( Escalate or De-escalate Application )
        Route::post('advertisement/self-advert/special-inbox', 'specialInbox');  // 08 ( Special Inbox Applications )
        Route::post('advertisement/self-advert/post-next-level', 'postNextLevel');  // 09 ( Forward or Backward Application )
        Route::post('advertisement/self-advert/comment-independent', 'commentIndependent');  // 10 ( Independent Comment )
        Route::post('advertisement/self-advert/get-license-by-userid', 'getLicense');  // 11 ( Get License By User ID )
        Route::post('advertisement/self-advert/get-license-by-holding-no', 'getLicenseByHoldingNo');  // 11 ( Get License By Holding No )
        Route::post('advertisement/self-advert/get-license-details-by-license-no', 'getLicenseDetailso');  // 12 ( Get License Details By Licence No )
        Route::post('advertisement/self-advert/advertisement-document-view', 'uploadDocumentsView');  // 13 ( Get Uploaded Document By Advertisement ID )
        Route::post('advertisement/self-advert/get-details-by-license-no', 'getDetailsByLicenseNo');  // 14 ( Get Uploaded Document By Advertisement ID )
        Route::post('advertisement/self-advert/workflow-view-documents', 'workflowViewDocuments');  // 15 ( View Uploaded Document By Advertisement ID )
        Route::post('advertisement/self-advert/workflow-upload-document', 'workflowUploadDocument');  // 16 ( Workflow Upload Document )
        Route::post('advertisement/self-advert/approval-rejection', 'finalApprovalRejection');          // 17 ( Approve or Reject )
        Route::post('advertisement/self-advert/approved-list', 'approvedList');          // 18 ( Approved list for Citizen)
        Route::post('advertisement/self-advert/rejected-list', 'rejectedList');          // 19 ( Rejected list for Citizen)
        Route::post('advertisement/self-advert/get-jsk-applications', 'getJSKApplications');          // 20 ( Get Applied Applications List By JSK )
        Route::post('advertisement/self-advert/jsk-approved-list', 'jskApprovedList');          // 21 ( Approved list for JSK)
        Route::post('advertisement/self-advert/jsk-rejected-list', 'jskRejectedList');          // 22 ( Rejected list for JSK)    
    });

    /**
     * | Param Strings 
     * | Controller-02
     */
    Route::controller(ParamController::class)->group(function () {
        Route::post('crud/param-strings', 'paramStrings');          // 01
        Route::post('advertisements/crud/v1/document-mstrs', 'documentMstrs');      // 02
        Route::post('advertisements/document-verification', 'documentVerification');
        Route::post('advertisements/upload-document', 'uploadDocument');
    });

    /**
     * | Movable Vehicles 
     * | Controller-03
     */
    Route::controller(VehicleAdvetController::class)->group(function () {
        Route::post('advertisement/movable-vehicle/save', 'store');    // 01 ( Save Application )
        Route::post('advertisement/movable-vehicle/edit', 'edit');    // 02 ( Edit Application )
        Route::post('advertisement/movable-vehicle/inbox', 'inbox');    // 03 ( Application Inbox Lists )
        Route::post('advertisement/movable-vehicle/outbox', 'outbox');    // 04 ( Application Outbox Lists )
        Route::post('advertisement/movable-vehicle/details', 'details');  // 05 ( Get Application Details By Application ID )
        Route::post('advertisement/movable-vehicle/get-citizen-applications', 'getCitizenApplications');     // 06 ( Get Applied Applications List )
        Route::post('advertisement/movable-vehicle/escalate', 'escalate');  // 07 ( Escalate or De-escalate Application )
        Route::post('advertisement/movable-vehicle/special-inbox', 'specialInbox');  // 08 ( Special Inbox Applications )
        Route::post('advertisement/movable-vehicle/post-next-level', 'postNextLevel');  // 09 ( Forward or Backward Application )
        Route::post('advertisement/movable-vehicle/comment-independent', 'commentIndependent');  // 10 ( Independent Comment )
        Route::post('advertisement/movable-vehicle/vehicle-document-view', 'uploadDocumentsView');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('advertisement/movable-vehicle/approval-rejection', 'finalApprovalRejection');          // 12 ( Approve or Reject )
        Route::post('advertisement/movable-vehicle/approved-list', 'approvedList');          // 13 ( Approved list for Citizen)
        Route::post('advertisement/movable-vehicle/rejected-list', 'rejectedList');          // 14 ( Rejected list for Citizen)
        Route::post('advertisement/movable-vehicle/get-jsk-applications', 'getJSKApplications');          // 20 ( Get Applied Applications List By JSK )
        Route::post('advertisement/movable-vehicle/jsk-approved-list', 'jskApprovedList');          // 15 ( Approved list for JSK)
        Route::post('advertisement/movable-vehicle/jsk-rejected-list', 'jskRejectedList');          // 16 ( Rejected list for JSK)  
    });

    /**
     * | Private Lands
     * | Controller-04 
     */
    Route::controller(PrivateLandController::class)->group(function () {
        Route::post('advertisement/private-land/save', 'store'); // 01   ( Save Application )  
        Route::post('advertisement/private-land/inbox', 'inbox');    // 03 ( Application Inbox Lists )
        Route::post('advertisement/private-land/outbox', 'outbox');    // 04 ( Application Outbox Lists )
        Route::post('advertisement/private-land/details', 'details');  // 05 ( Get Application Details By Application ID )
        Route::post('advertisement/private-land/get-citizen-applications', 'getCitizenApplications');     // 06 ( Get Applied Applications List )
        Route::post('advertisement/private-land/escalate', 'escalate');  // 07 ( Escalate or De-escalate Application )
        Route::post('advertisement/private-land/special-inbox', 'specialInbox');  // 08 ( Special Inbox Applications )
        Route::post('advertisement/private-land/post-next-level', 'postNextLevel');  // 09 ( Forward or Backward Application )
        Route::post('advertisement/private-land/comment-independent', 'commentIndependent');  // 10 ( Independent Comment )
        Route::post('advertisement/private-land/private-land-document-view', 'uploadDocumentsView');  // 11 ( Get Uploaded Document By Application ID )
        Route::post('advertisement/private-land/approval-rejection', 'finalApprovalRejection');          // 12 ( Approve or Reject )
        Route::post('advertisement/private-land/approved-list', 'approvedList');          // 13 ( Approved list for Citizen)
        Route::post('advertisement/private-land/rejected-list', 'rejectedList');          // 14 ( Rejected list for Citizen)
        Route::post('advertisement/private-land/get-jsk-applications', 'getJSKApplications');          // 15 ( Get Applied Applications List By JSK )
        Route::post('advertisement/private-land/jsk-approved-list', 'jskApprovedList');          // 16 ( Approved list for JSK)
        Route::post('advertisement/private-land/jsk-rejected-list', 'jskRejectedList');          // 17 ( Rejected list for JSK)  
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
 
        /*------------ Apply For Hording License -------------------*/
        Route::post('advertisement/agency/get-typology-list', 'getTypologyList');  // 18 ( Get Typology List )
        Route::post('advertisement/agency/save-for-licence', 'saveForLicence');  // 19 ( Save Application For Licence )
        Route::post('advertisement/agency/license-inbox', 'licenseInbox');             // 20 ( Application Inbox Lists )
        Route::post('advertisement/agency/license-outbox', 'licenseOutbox');    // 21 ( Application Outbox Lists )
        Route::post('advertisement/agency/license-details', 'licenseDetails');  // 22 ( Get Application Details By Application ID )
        Route::post('advertisement/agency/license-get-citizen-applications', 'licenseGetCitizenApplications');     // 23 ( Get Applied Applications List )
        Route::post('advertisement/agency/license-escalate', 'licenseEscalate');  // 24 ( Escalate or De-escalate Application )
        Route::post('advertisement/agency/license-special-inbox', 'licenseSpecialInbox');  // 25 ( Special Inbox Applications )
        Route::post('advertisement/agency/license-post-next-level', 'licensePostNextLevel');  // 26 ( Forward or Backward Application )
        Route::post('advertisement/agency/license-comment-independent', 'LicenseCommentIndependent');  // 27 ( Independent Comment )
        Route::post('advertisement/agency/license-hording-document-view', 'licenseUploadDocumentsView');  // 28 ( Get Uploaded Document By Application ID )
        Route::post('advertisement/agency/license-approval-rejection', 'licenseFinalApprovalRejection');          // 29 ( Approve or Reject )
        Route::post('advertisement/agency/license-approved-list', 'licenseApprovedList');          // 30 ( License Approved list for Citizen)
        Route::post('advertisement/agency/license-rejected-list', 'licenseRejectedList');          // 31 ( License Rejected list for Citizen)
        Route::post('advertisement/agency/license-get-jsk-applications', 'licenseGetJSKApplications');          // 15 ( Get Applied Applications List By JSK )
        Route::post('advertisement/agency/license-jsk-approved-list', 'licenseJskApprovedList');          // 32 ( Approved list for JSK)
        Route::post('advertisement/agency/license-jsk-rejected-list', 'licenseJskRejectedList');          // 33 ( Rejected list for JSK)  
 
    });
});
