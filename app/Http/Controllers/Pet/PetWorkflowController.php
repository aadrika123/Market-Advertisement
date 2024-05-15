<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\MicroServices\IdGenerator\PrefixIdGenerator;
use App\Models\Advertisements\WfActiveDocument;
use App\Models\Pet\PetActiveApplicant;
use App\Models\Pet\PetActiveDetail;
use App\Models\Pet\PetActiveRegistration;
use App\Models\Pet\PetApproveApplicant;
use App\Models\Pet\PetApproveDetail;
use App\Models\Pet\PetApprovedRegistration;
use App\Models\Pet\PetRejectedRegistration;
use App\Models\Pet\PetRenewalRegistration;
use App\Models\Workflows\WfRoleusermap;
use App\Models\Workflows\WfWardUser;
use App\Models\Workflows\WfWorkflow;
use App\Models\Workflows\WfWorkflowrolemap;
use App\Models\Workflows\WorkflowTrack;
use App\Pipelines\Pet\SearchByApplicantName;
use App\Traits\Workflow\Workflow;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Pipeline\Pipeline;
use App\Pipelines\Pet\SearchByApplicationNo;

class PetWorkflowController extends Controller
{

    use Workflow;

    private $_masterDetails;
    private $_propertyType;
    private $_occupancyType;
    private $_workflowMasterId;
    private $_petParamId;
    private $_petModuleId;
    private $_userType;
    private $_petWfRoles;
    private $_docReqCatagory;
    private $_dbKey;
    private $_fee;
    private $_applicationType;
    private $_offlineVerificationModes;
    private $_paymentMode;
    private $_offlineMode;
    protected $_DB_NAME;
    protected $_DB;
    protected $_DB_NAME2;
    protected $_DB2;
    # Class constructer 
    public function __construct()
    {
        $this->_masterDetails           = Config::get("pet.MASTER_DATA");
        $this->_propertyType            = Config::get("pet.PROP_TYPE");
        $this->_occupancyType           = Config::get("pet.PROP_OCCUPANCY_TYPE");
        $this->_workflowMasterId        = Config::get("pet.WORKFLOW_MASTER_ID");
        $this->_petParamId              = Config::get("pet.PARAM_ID");
        $this->_petModuleId             = Config::get('pet.PET_MODULE_ID');
        $this->_userType                = Config::get("pet.REF_USER_TYPE");
        $this->_petWfRoles              = Config::get("pet.ROLE_LABEL");
        $this->_docReqCatagory          = Config::get("pet.DOC_REQ_CATAGORY");
        $this->_dbKey                   = Config::get("pet.DB_KEYS");
        $this->_fee                     = Config::get("pet.FEE_CHARGES");
        $this->_applicationType         = Config::get("pet.APPLICATION_TYPE");
        $this->_offlineVerificationModes = Config::get("pet.VERIFICATION_PAYMENT_MODES");
        $this->_paymentMode             = Config::get("pet.PAYMENT_MODE");
        $this->_offlineMode             = Config::get("pet.OFFLINE_PAYMENT_MODE");
        # Database connectivity
        $this->_DB_NAME    = "pgsql_masters";
        $this->_DB         = DB::connection($this->_DB_NAME);
        $this->_DB_NAME2   = "pgsql_property";
        $this->_DB2        = DB::connection($this->_DB_NAME2);
    }


    /**
     * | Database transaction connection
     */
    public function begin()
    {
        $db1 = DB::connection()->getDatabaseName();
        $db2 = $this->_DB->getDatabaseName();
        // $db3 = $this->_DB2->getDatabaseName();
        DB::beginTransaction();
        if ($db1 != $db2)
            $this->_DB->beginTransaction();
        // if ($db1 != $db3 && $db2 != $db3)
            // $this->_DB2->beginTransaction();
    }
    /**
     * | Database transaction connection
     */
    public function rollback()
    {
        $db1 = DB::connection()->getDatabaseName();
        $db2 = $this->_DB->getDatabaseName();
        // $db3 = $this->_DB2->getDatabaseName();
        DB::rollBack();
        if ($db1 != $db2)
            $this->_DB->rollBack();
        // if ($db1 != $db3 && $db2 != $db3)
            // $this->_DB2->rollBack();
    }
    /**
     * | Database transaction connection
     */
    public function commit()
    {
        $db1 = DB::connection()->getDatabaseName();
        $db2 = $this->_DB->getDatabaseName();
        // $db3 = $this->_DB2->getDatabaseName();
        DB::commit();
        if ($db1 != $db2)
            $this->_DB->commit();
        // if ($db1 != $db3 && $db2 != $db3)
            // $this->_DB2->commit();
    }

    #-----------------------------------------------------------------------------------------------------------------------------------------------#



    /**
     * | Inbox
     * | workflow
        | Serial No :
        | Working
     */
    public function inbox(Request $request)
    {
        try {
            $user   = authUser($request);
            $userId = $user->id;
            $ulbId  = $user->ulb_id;
            $pages  = $request->perPage ?? 10;
            $mWfWorkflowRoleMaps = new WfWorkflowrolemap();
            $msg = "Inbox List Details!";

            $occupiedWards = $this->getWardByUserId($userId)->pluck('ward_id');
            $roleId = $this->getRoleIdByUserId($userId)->pluck('wf_role_id');
            $workflowIds = $mWfWorkflowRoleMaps->getWfByRoleId($roleId)->pluck('workflow_id');

            $waterList = $this->getPetApplicatioList($workflowIds, $ulbId)
                ->whereIn('pet_active_registrations.current_role_id', $roleId)
                // ->whereIn('pet_active_registrations.ward_id', $occupiedWards)
                // ->where('pet_active_registrations.is_escalate', false)
                ->where('pet_active_registrations.parked', false);
            //->paginate($pages);

            if (collect($waterList)->last() == 0 || !$waterList) {
                $msg = "Data not found!";
            }
            $inbox = app(Pipeline::class)
                ->send(
                    $waterList
                )
                ->through([
                    SearchByApplicationNo::class,
                    SearchByApplicantName::class
                ])
                ->thenReturn()
                ->paginate($pages);
            return responseMsgs(true, $msg, remove_null($inbox), '', '02', '', 'Post', '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $request->deviceId);
        }
    }

    /**
     * | Common function
        | Move the function in trait 
        | Caution move the function 
     */
    public function getPetApplicatioList($workflowIds, $ulbId)
    {
        return PetActiveRegistration::select(
            'pet_active_registrations.id',
            'pet_active_registrations.application_no',
            'pet_active_applicants.id as owner_id',
            'pet_active_applicants.applicant_name as owner_name',
            'pet_active_registrations.ward_id',
            'u.ward_name as ward_no',
            'pet_active_registrations.workflow_id',
            'pet_active_registrations.current_role_id as role_id',
            'pet_active_registrations.application_apply_date',
            'pet_active_registrations.parked',
            'pet_active_registrations.is_escalate'
        )
            ->join('ulb_ward_masters as u', 'u.id', '=', 'pet_active_registrations.ward_id')
            ->join('pet_active_applicants', 'pet_active_applicants.application_id', 'pet_active_registrations.id')
            ->join('pet_active_details', 'pet_active_details.application_id', 'pet_active_registrations.id')
            ->where('pet_active_registrations.status', 1)
            ->where('pet_active_registrations.ulb_id', $ulbId)
            ->whereIn('pet_active_registrations.workflow_id', $workflowIds)
            ->orderByDesc('pet_active_applicants.id');
    }


    /**
     * | OutBox
     * | Outbox details for display
        | Serial No :
        | Working
     */
    public function outbox(Request $req)
    {
        try {
            $user                   = authUser($req);
            $userId                 = $user->id;
            $ulbId                  = $user->ulb_id;
            $pages                  = $req->perPage ?? 10;
            $mWfWorkflowRoleMaps    = new WfWorkflowrolemap();
            $msg = "Outbox List!";

            $occupiedWards  = $this->getWardByUserId($userId)->pluck('ward_id');
            $roleId         = $this->getRoleIdByUserId($userId)->pluck('wf_role_id');
            $workflowIds    = $mWfWorkflowRoleMaps->getWfByRoleId($roleId)->pluck('workflow_id');

            $waterList = $this->getPetApplicatioList($workflowIds, $ulbId)
                ->whereNotIn('pet_active_registrations.current_role_id', $roleId)
                ->orderByDesc('pet_active_registrations.id');
                // ->whereIn('pet_active_registrations.ward_id', $occupiedWards)
                // ->paginate($pages);

            if (collect($waterList)->last() == 0 || !$waterList) {
                $msg = "Data not found!";
            }

            $outbox = app(Pipeline::class)
                ->send(
                    $waterList
                )
                ->through([
                    SearchByApplicationNo::class,
                    SearchByApplicantName::class
                ])
                ->thenReturn()
                ->paginate($pages);
            return responseMsgs(true, $msg, remove_null($outbox), '', '01', '.ms', 'Post', '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }


    /**
     * | Post next level in workflow 
        | Serial No :
        | Working
        | Check for forward date and backward date
     */
    public function postNextLevel(Request $req)
    {
        $wfLevels = $this->_petWfRoles;
        $validated = Validator::make(
            $req->all(),
            [
                'applicationId'     => 'required',
                'senderRoleId'      => 'nullable',
                'receiverRoleId'    => 'nullable',
                'action'            => 'required|In:forward,backward',
                'comment'           => $req->senderRoleId == $wfLevels['BO'] ? 'nullable' : 'required',
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $mWfRoleMaps        = new WfWorkflowrolemap();
            $wfLevels           = $wfLevels;
            $petApplication     = PetActiveRegistration::findOrFail($req->applicationId);

            # Derivative Assignments
            $senderRoleId = $petApplication->current_role_id;
            $ulbWorkflowId = $petApplication->workflow_id;
            $ulbWorkflowMaps = WfWorkflow::findOrFail($ulbWorkflowId);
            $roleMapsReqs = new Request([
                'workflowId'    => $ulbWorkflowMaps->id,
                'roleId'        => $senderRoleId
            ]);
            $forwardBackwardIds = $mWfRoleMaps->getWfBackForwardIds($roleMapsReqs);

            $this->begin();
            if ($req->action == 'forward') {
                $this->checkPostCondition($senderRoleId, $wfLevels, $petApplication);            // Check Post Next level condition
                $metaReqs['verificationStatus']     = 1;
                $metaReqs['receiverRoleId']         = $forwardBackwardIds->forward_role_id;
                $petApplication->current_role_id    = $forwardBackwardIds->forward_role_id;
                $petApplication->last_role_id       = $forwardBackwardIds->forward_role_id;                                      // Update Last Role Id
                $msg = "Application Forwaded Succesfully.";
            }
            if ($req->action == 'backward') {
                $petApplication->current_role_id    = $forwardBackwardIds->backward_role_id;
                $metaReqs['verificationStatus']     = 0;
                $metaReqs['receiverRoleId']         = $forwardBackwardIds->backward_role_id;
                $msg = "Application has been sent back succesfully.";
            }
            $petApplication->save();

            $metaReqs['moduleId']           = $this->_petModuleId;
            $metaReqs['workflowId']         = $petApplication->workflow_id;
            $metaReqs['refTableDotId']      = 'pet_active_registrations.id';                                                // Static
            $metaReqs['refTableIdValue']    = $req->applicationId;
            $metaReqs['user_id']            = authUser($req)->id;
            $req->request->add($metaReqs);

            $workflowTrack = new WorkflowTrack();
            $workflowTrack->saveTrack($req);

            # Check in all the cases the data if entered in the track table 
            # Updation of Received Date
            // $preWorkflowReq = [
            //     'workflowId'        => $petApplication->workflow_id,
            //     'refTableDotId'     => "pet_active_registrations.id",
            //     'refTableIdValue'   => $req->applicationId,
            //     'receiverRoleId'    => $senderRoleId
            // ];

            // $previousWorkflowTrack = $workflowTrack->getWfTrackByRefId($preWorkflowReq);
            // $previousWorkflowTrack->update([
            //     'forward_date' => $current->format('Y-m-d'),
            //     'forward_time' => $current->format('H:i:s')
            // ]);
            $this->commit();
            return responseMsgs(true, $msg, [], "", "", '01', responseTime(), 'Post', '');
        } catch (Exception $e) {
            $this->rollback();
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), "POST", $req->deviceId);
        }
    }


    /**
     * | Check the condition before forward
        | Serial No :
        | Working
     */
    public function checkPostCondition($senderRoleId, $wfLevels, $application)
    {
        switch ($senderRoleId) {
            case $wfLevels['BO']:                                                                       // Back Office Condition
                if ($application->doc_upload_status == false)
                    throw new Exception("The full document has not been uploaded");                                                                      // DA Condition
                if ($application->payment_status != 1)
                    throw new Exception("Payment Not Done");
                break;
            case $wfLevels['DA']:
                if ($application->doc_upload_status == false)
                    throw new Exception("The full document has not been uploaded");                                                                      // DA Condition
                if ($application->payment_status != 1)
                    throw new Exception("Payment Not Done");                                                                      // DA Condition
                if ($application->doc_verify_status == false)
                    throw new Exception("Document Not Fully Verified!");
                break;
        }
    }


    /**
     * | Verify, Reject document in workflow
        | Serial No :
        | Working
     */
    public function docVerifyRejects(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'id'            => 'required|digits_between:1,9223372036854775807',
                'applicationId' => 'required|digits_between:1,9223372036854775807',
                'docRemarks'    =>  $req->docStatus == "Rejected" ? 'required|regex:/^[a-zA-Z1-9][a-zA-Z1-9\. \s]+$/' : "nullable",
                'docStatus'     => 'required|in:Verified,Rejected'
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            # Variable Assignments
            $mWfDocument                = new WfActiveDocument();
            $mPetActiveRegistration     = new PetActiveRegistration();
            $mWfRoleusermap             = new WfRoleusermap();
            $wfDocId                    = $req->id;
            $applicationId              = $req->applicationId;
            $userId                     = authUser($req)->id;
            $wfLevel                    = $this->_petWfRoles;

            # validating application
            $petApplicationDtl = $mPetActiveRegistration->getPetApplicationById($applicationId)
                ->first();
            if (!$petApplicationDtl || collect($petApplicationDtl)->isEmpty())
                throw new Exception("Application Details Not Found");

            # validating roles
            $waterReq = new Request([
                'userId'        => $userId,
                'workflowId'    => $petApplicationDtl['workflow_id']
            ]);
            $senderRoleDtls = $mWfRoleusermap->getRoleByUserWfAndId($waterReq);
            if (!$senderRoleDtls || collect($senderRoleDtls)->isEmpty())
                throw new Exception("Role Not Available");

            # validating role for DA
            $senderRoleId = $senderRoleDtls->wf_role_id;
            if ($senderRoleId != $wfLevel['DA'])                                    // Authorization for Dealing Assistant Only
                throw new Exception("You are not Authorized");

            # validating if full documet is uploaded
            $ifFullDocVerified = $this->ifFullDocVerified($applicationId);          // (Current Object Derivative Function 0.1)
            if ($ifFullDocVerified == 1)
                throw new Exception("Document Fully Verified");

            $this->begin();
            if ($req->docStatus == "Verified") {
                $status = 1;
            }
            if ($req->docStatus == "Rejected") {
                # For Rejection Doc Upload Status and Verify Status will disabled 
                $status = 2;
                $petApplicationDtl->doc_upload_status = 0;
                $petApplicationDtl->save();
            }
            $reqs = [
                'remarks'           => $req->docRemarks,
                'verify_status'     => $status,
                'action_taken_by'   => $userId
            ];
            $mWfDocument->docVerifyReject($wfDocId, $reqs);
            if ($req->docStatus == 'Verified')
                $ifFullDocVerifiedV1 = $this->ifFullDocVerified($applicationId);
            else
                $ifFullDocVerifiedV1 = 0;

            if ($ifFullDocVerifiedV1 == 1) {                                        // If The Document Fully Verified Update Verify Status
                $status = true;
                $mPetActiveRegistration->updateDocStatus($applicationId, $status);
            }
            $this->commit();
            return responseMsgs(true, $req->docStatus . " Successfully", "", "010204", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            $this->rollback();
            return responseMsgs(false, $e->getMessage(), "", "010204", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    /**
     * | Check if the Document is Fully Verified or Not (0.1) | up
     * | @param
     * | @var 
     * | @return
        | Serial No :  
        | Working 
     */
    public function ifFullDocVerified($applicationId)
    {
        $mPetActiveRegistration = new PetActiveRegistration();
        $mWfActiveDocument      = new WfActiveDocument();
        $refapplication = $mPetActiveRegistration->getPetApplicationById($applicationId)
            ->firstOrFail();

        $refReq = [
            'activeId'      => $applicationId,
            'workflowId'    => $refapplication['workflow_id'],
            'moduleId'      => $this->_petModuleId,
        ];

        $req = new Request($refReq);
        $refDocList = $mWfActiveDocument->getVerifiedDocsByActiveId($refReq);
        return $this->isAllDocs($applicationId, $refDocList, $refapplication);
    }

    public function isAllDocs($applicationId, $refDocList, $refapplication)
    {
        $docList = array();
        $verifiedDocList = array();
        $petController = new PetRegistrationController;
        $documentLists = $petController->getPetDocLists($refapplication);

        $docList['petDocs'] = $documentLists->map(function ($documentList) {
            $filteredDocs = $documentList['requirements'];
            $document   = explode(',', $filteredDocs);
            $key        = array_shift($document);
            $label      = array_shift($document);
            return $document[0];
        });
        # var defining

        $verifiedDocList['petDocs'] = $refDocList->where('owner_dtl_id', null)->values();
        $collectUploadDocList = collect();
        collect($verifiedDocList['petDocs'])->map(function ($item) use ($collectUploadDocList) {
            return $collectUploadDocList->push($item['doc_code']);
        });

        // $petDocs = collect();
        // Property List Documents
        $flag = 1;
        foreach ($docList['petDocs'] as $item) {
            // $explodeDocs = explode(',', $item);
            // array_shift($explodeDocs);
            // foreach ($explodeDocs as $explodeDoc) {
            $changeStatus = 0;
            if (in_array($item, $collectUploadDocList->toArray())) {
                $changeStatus = 1;
                // break;
            }
            // }
            if ($changeStatus == 0) {
                $flag = 0;
                break;
            }
        }

        if ($flag == 0)
            return 0;
        else
            return 1;
    }


    /**
     * | Get details for the pet special inbox
        | Serial No :
        | Working
     */
    public function waterSpecialInbox(Request $request)
    {
        try {
            $user   = authUser($request);
            $userId = $user->id;
            $ulbId  = $user->ulb_id;
            $pages  = $request->perPage ?? 10;
            $msg    = "Inbox List Details!";
            $mWfWorkflowRoleMaps = new WfWorkflowrolemap();

            $occupiedWards = $this->getWardByUserId($userId)->pluck('ward_id');
            $roleId = $this->getRoleIdByUserId($userId)->pluck('wf_role_id');
            $workflowIds = $mWfWorkflowRoleMaps->getWfByRoleId($roleId)->pluck('workflow_id');

            $waterList = $this->getPetApplicatioList($workflowIds, $ulbId)
                // ->whereIn('pet_active_registrations.ward_id', $occupiedWards)
                ->where('pet_active_registrations.is_escalate', true)
                ->paginate($pages);
            if (collect($waterList)->last() == 0 || !$waterList) {
                $msg = "Data not found!";
            }
            return responseMsgs(true, $msg, remove_null($waterList), '', '02', '', 'Post', '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $request->deviceId);
        }
    }


    /**
     * | Post escalte Details of Pet Application
        | Serial No :
        | Working
     */
    public function postEscalate(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                "escalateStatus"    => "required|int",
                "applicationId"     => "required|int",
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $userId = authUser($request)->id;
            $applicationId = $request->applicationId;
            $mPetActiveRegistration = new PetActiveRegistration();
            $applicationsData = $mPetActiveRegistration->getApplicationDetailsById($applicationId)->first();
            if (!$applicationsData) {
                throw new Exception("Application details not found!");
            }
            $applicationsData->is_escalate = $request->escalateStatus;
            $applicationsData->escalate_by = $userId;
            $applicationsData->save();
            return responseMsgs(true, $request->escalateStatus == 1 ? 'Pet application is Escalated' : "Pet application is removed from Escalated", [], '', "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }


    /**
     * | Workflow final approvale for the application
     * | Also adjust the renewal process
        | Serial No : 
        | Parent function
        | Working
     */
    public function finalApprovalRejection(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'applicationId' => 'required|digits_between:1,9223372036854775807',
                'status'        => 'required'
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $approveDetails         = [];
            $userId                 = authUser($request)->id;
            $applicationId          = $request->applicationId;
            $mPetActiveRegistration = new PetActiveRegistration();
            $mWfRoleUsermap         = new WfRoleusermap();

            # Get Application details 
            $application = $mPetActiveRegistration->getPetApplicationById($applicationId)->first();
            if (!$application) {
                throw new Exception("Application details not found.");
            }

            # Check the workflow role 
            $workflowId     = $application->workflow_id;
            $applicationNo  = $application->application_no;
            $getRoleReq = new Request([                                                                 // make request to get role id of the user
                'userId'        => $userId,
                'workflowId'    => $workflowId
            ]);
            $readRoleDtls = $mWfRoleUsermap->getRoleByUserWfId($getRoleReq);

            # Check params 
            $this->checkParamForApproval($readRoleDtls, $application);

            $this->begin();
            # Approval of grievance application 
            if ($request->status == 1) {                                                                // Static
                # If application is approved for the first time or renewal
                if ($application->renewal == 0) {                                                       // Static
                    $approveDetails = $this->finalApproval($request, $application);
                    $returnData['uniqueTokenId'] = $approveDetails['registrationId'] ?? null;
                } else {
                    $approveDetails =  $this->finalApprovalRenewal($request, $application);
                    $returnData['uniqueTokenId'] = $approveDetails['registrationId'] ?? null;
                }
                $msg = "Application Successfully Approved of Application No ".$applicationNo." with Registration No:" . $approveDetails['registrationId'];
            }
            # Rejection of grievance application
            if ($request->status == 0) {                                                                // Static
                $this->finalRejectionOfAppication($request, $application);
                $msg = "Application Rejected of Application No $applicationNo";
            }
            $this->commit();
            $returnData["applicationNo"] = $applicationNo;
            return responseMsgs(true, $msg, $returnData, "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            $this->rollback();
            return responseMsgs(false, $e->getMessage(), [$e->getFile(),$e->getLine(),$e->getCode()], "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }


    /**
     * | Check param For final approval and rejection 
        | Serial No :
        | Working
     */
    public function checkParamForApproval($readRoleDtls, $application)
    {
        if (!$readRoleDtls) {
            throw new Exception("Role details not found");
        }
        if ($readRoleDtls->wf_role_id != $application->finisher_role_id) {
            throw new Exception("You are not the finisher");
        }
        if ($application->doc_upload_status == false) {
            throw new Exception("Document Not Fully Uploaded");
        }
        if ($application->payment_status != 1) {
            throw new Exception("Payment not Done");
        }
        if ($application->doc_verify_status == false) {
            throw new Exception("Document Not Fully Verified");
        }
    }


    /**
     * | Final approval process for pet application 
        | Serial No :
        | Working
        | Caution performing Deletion of active application
     */
    public function finalApproval($request, $applicationDetails)
    {
        $now                        = Carbon::now();
        $status                     = 2;
        $applicationId              = $request->applicationId;
        $workflowTrack                 = new WorkflowTrack();
        $mPetActiveRegistration     = new PetActiveRegistration();
        $mPetApprovedRegistration   = new PetApprovedRegistration();
        $mPetActiveApplicant        = new PetActiveApplicant();
        $mPetActiveDetail           = new PetActiveDetail();
        $lastLicenceDate            = $now->copy()->addYear()->subDay();
        
        # Check if the approve application exist
        $someDataExist = $mPetApprovedRegistration->getApproveAppByAppId($applicationId)
        ->whereNot('status', 0)
        ->first();
        if ($someDataExist) {
            throw new Exception("Approve application details exist in active table ERROR!");
        }
        
        # Data formating for save the consumer details 
        $refApplicationDetial   = $mPetActiveRegistration->getApplicationDetailsById($applicationId)->first();
        $refOwnerDetails        = $mPetActiveApplicant->getApplicationDetails($applicationId)->first();
        $refPetDetails          = $mPetActiveDetail->getPetDetailsByApplicationId($applicationId)->first();
        
        $idGeneration           = new PrefixIdGenerator(45, $refApplicationDetial->ulb_id);
        $registrationId         = $idGeneration->generate();

        # Saving the data in the approved application table
        $approvedPetRegistration = $refApplicationDetial->replicate();
        $approvedPetRegistration->setTable('pet_approved_registrations');                           // Static
        $approvedPetRegistration->application_id    = $applicationId;
        $approvedPetRegistration->approve_date      = $now;
        $approvedPetRegistration->registration_id   = $registrationId;
        $approvedPetRegistration->approve_end_date  = $lastLicenceDate;
        $approvedPetRegistration->approve_user_id   = authUser($request)->id;
        $approvedPetRegistration->save();

        # Save the pet owner details 
        $approvedPetApplicant = $refOwnerDetails->replicate();
        $approvedPetApplicant->setTable('pet_approve_applicants');                                  // Static
        $approvedPetApplicant->created_at = $now;
        $approvedPetApplicant->save();

        # Save the pet detials 
        $approvedPetDetails = $refPetDetails->replicate();
        $approvedPetDetails->setTable('pet_approve_details');                                       // Static
        $approvedPetDetails->created_at = $now;
        $approvedPetDetails->save();

        # Send record in the track table 
        $metaReqs = [
            'moduleId'          => $this->_petModuleId,
            'workflowId'        => $applicationDetails->workflow_id,
            'refTableDotId'     => 'pet_active_registrations.id',                                   // Static
            'refTableIdValue'   => $applicationId,
            'user_id'           => authUser($request)->id,
        ];
        $request->request->add($metaReqs);
        $workflowTrack->saveTrack($request);

        # Delete the details form the active table
        $refAppReq = [
            "status" => $status
        ];
        $mPetActiveRegistration->saveApplicationStatus($applicationId, $refAppReq);
        $mPetActiveApplicant->updateApplicantDetials($refOwnerDetails->id, $refAppReq);
        $mPetActiveDetail->updatePetStatus($refPetDetails->id, $refAppReq);
        return [
            "approveDetails" => $approvedPetRegistration,
            "registrationId" => $registrationId
        ];
    }

    /**
     * | Final Approval of a renewal application 
        | Serial No :
        | Working
     */
    public function finalApprovalRenewal($request, $applicationDetails)
    {
        $now                        = Carbon::now();
        $status                     = 0;                                        // Static
        $applicationId              = $request->applicationId;
        $workflowTrack              = new WorkflowTrack();
        $mPetActiveRegistration     = new PetActiveRegistration();
        $mPetActiveApplicant        = new PetActiveApplicant();
        $mPetActiveDetail           = new PetActiveDetail();
        $mPetApprovedRegistration   = new PetApprovedRegistration();
        $mPetApproveApplicant       = new PetApproveApplicant();
        $mPetApproveDetail          = new PetApproveDetail();
        $lastLicenceDate            = $now->addYear()->subDay();
        $registrationId             = $applicationDetails->registration_id;

        # Data formating for save the consumer details 
        $refApplicationDetial   = $mPetActiveRegistration->getApplicationDetailsById($applicationId)->first();
        $refOwnerDetails        = $mPetActiveApplicant->getApplicationDetails($applicationDetails->ref_application_id)->first();
        $refPetDetails          = $mPetActiveDetail->getPetDetailsByApplicationId($applicationDetails->ref_application_id)->first();

        # Check data existence
        $approveDataExist = $mPetApprovedRegistration->getApproveAppByRegId($applicationDetails->registration_id)
            ->where('status', 2)                                                // Static
            ->first();
        if (!$approveDataExist) {
            throw new Exception("renewal application details dont exist, table ERROR!");
        }

        # get approve application detials 
        $approveApplicantDetail = $mPetApproveApplicant->getApproveApplicant($approveDataExist->application_id)->first();
        $approvePetDetail = $mPetApproveDetail->getPetDetailsById($approveDataExist->application_id)->first();

        # Saving the data in the approved application table
        $approvedPetRegistration = $refApplicationDetial->replicate();
        $approvedPetRegistration->setTable('pet_approved_registrations');                           // Static
        $approvedPetRegistration->application_id    = $applicationDetails->ref_application_id;
        $approvedPetRegistration->approve_date      = $now;
        $approvedPetRegistration->registration_id   = $registrationId;
        $approvedPetRegistration->approve_end_date  = $lastLicenceDate;
        $approvedPetRegistration->approve_user_id   = authUser($request)->id;
        $approvedPetRegistration->save();

        # Save the pet owner details 
        $approvedPetApplicant = $refOwnerDetails->replicate();
        $approvedPetApplicant->setTable('pet_approve_applicants');                                  // Static
        $approvedPetApplicant->created_at = $now;
        $approvedPetApplicant->save();

        # Save the pet detials 
        $approvedPetDetails = $refPetDetails->replicate();
        $approvedPetDetails->setTable('pet_approve_details');                                       // Static
        $approvedPetDetails->created_at = $now;
        $approvedPetDetails->save();

        # Delete the details form the active table # updating the status
        $activeData = [
            "status" => $status
        ];
        $mPetActiveRegistration->saveApplicationStatus($applicationDetails->ref_application_id, $activeData);
        $mPetActiveApplicant->updateApplicantDetials($refOwnerDetails->id, $activeData);
        $mPetActiveDetail->updatePetStatus($refPetDetails->id, $activeData);

        # Save approved renewal data in renewal table
        $renewalPetRegistration = $approveDataExist->replicate();
        $renewalPetRegistration->setTable('pet_renewal_registrations');                             // Static  
        $renewalPetRegistration->created_at = $now;
        $renewalPetRegistration->save();

        # Save the approved applicant data in renewal table
        $renewalApplicantReg = $approveApplicantDetail->replicate();
        $renewalApplicantReg->setTable('pet_renewal_applicants');                                   // Static
        $renewalApplicantReg->created_at = $now;
        $renewalApplicantReg->save();

        # Save the approved pet data in renewal details 
        $renewalPetDetails = $approvePetDetail->replicate();
        $renewalPetDetails->setTable('pet_renewal_details');                                        // Static
        $renewalPetDetails->created_at = $now;
        $renewalPetDetails->save();

        # Delete the details form the active table # Updating the status
        $approveData = [
            "status" => $status
        ];
        $mPetApprovedRegistration->updateApproveAppStatus($approveDataExist->id, $approveData);
        $mPetApproveApplicant->updateAproveApplicantDetials($approveApplicantDetail->id, $approveData);  /// Not done
        $mPetApproveDetail->updateApprovePetStatus($approvePetDetail->id, $approveData);             /// Not done   

        # Send record in the track table 
        $metaReqs = [
            'moduleId'          => $this->_petModuleId,
            'workflowId'        => $applicationDetails->workflow_id,
            'refTableDotId'     => 'pet_active_registrations.id',                                   // Static
            'refTableIdValue'   => $applicationDetails->ref_application_id,
            'user_id'           => authUser($request)->id,
        ];
        $request->request->add($metaReqs);
        $workflowTrack->saveTrack($request);
        return [
            "approveDetails" => $approvedPetRegistration,
            "registrationId" => $registrationId
        ];
    }


    /**
     * | Fianl rejection of the application 
        | Serial No :
        | Under Con
        | Recheck
     */
    public function finalRejectionOfAppication($request, $applicationDetails)
    {
        $now                        = Carbon::now();
        $status                     = 0;                                               // Static     
        $applicationId              = $request->applicationId;
        $workflowTrack                 = new WorkflowTrack();
        $mPetRejectedRegistration   = new PetRejectedRegistration();
        $mPetActiveRegistration     = new PetActiveRegistration();
        $mPetActiveApplicant        = new PetActiveApplicant();
        $mPetActiveDetail           = new PetActiveDetail();

        # Check if the rejected application exist
        $someDataExist = $mPetRejectedRegistration->getRejectedAppByAppId($applicationId)
            ->whereNot('status', '<>', 0)
            ->first();
        if ($someDataExist) {
            throw new Exception("Rejected application details exist in rejected table ERROR!");
        }

        # Data formating for save the consumer details 
        $refApplicationDetial   = $mPetActiveRegistration->getApplicationDetailsById($applicationId)->first();
        $refOwnerDetails        = $mPetActiveApplicant->getApplicationDetails($applicationId)->first();
        $refPetDetails          = $mPetActiveDetail->getPetDetailsByApplicationId($applicationId)->first();

        # Saving the data in the rejected application table
        $rejectedPetRegistration = $refApplicationDetial->replicate();
        $rejectedPetRegistration->setTable('pet_rejected_registrations');                           // Static
        $rejectedPetRegistration->application_id    = $applicationId;
        $rejectedPetRegistration->rejected_date     = $now;
        $rejectedPetRegistration->rejected_user_id  = authUser($request)->id;
        $rejectedPetRegistration->save();

        # Save the pet owner details 
        $approvedPetApplicant = $refOwnerDetails->replicate();
        $approvedPetApplicant->setTable('pet_rejected_applicants');                                  // Static
        $approvedPetApplicant->created_at = $now;
        $approvedPetApplicant->save();

        # Save the pet detials 
        $approvedPetDetails = $refPetDetails->replicate();
        $approvedPetDetails->setTable('pet_rejected_details');                                       // Static
        $approvedPetDetails->created_at = $now;
        $approvedPetDetails->save();

        # Send record in the track table 
        $metaReqs = [
            'moduleId'          => $this->_petModuleId,
            'workflowId'        => $applicationDetails->workflow_id,
            'refTableDotId'     => 'pet_active_registrations.id',                                   // Static
            'refTableIdValue'   => $applicationId,
            'user_id'           => authUser($request)->id,
        ];
        $request->request->add($metaReqs);
        $workflowTrack->saveTrack($request);

        # Delete the details form the active table
        $refAppReq = [
            "status" => $status
        ];
        $mPetActiveRegistration->saveApplicationStatus($applicationId, $refAppReq);
        $mPetActiveApplicant->updateApplicantDetials($refOwnerDetails->id, $refAppReq);
        $mPetActiveDetail->updatePetStatus($refPetDetails->id, $refAppReq);
        return $rejectedPetRegistration;
    }


    /**
     * | Generate Order Id
        | Serial No :
        | Working
     */
    protected function getUniqueId($key)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < 10; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        $uniqueId = (($key . date('dmyhism') . $randomString));
        $uniqueId = explode("=", chunk_split($uniqueId, 26, "="))[0];
        return $uniqueId;
    }

    /**
     * | Get approved and rejected application list by the finisher
        | Serial No :
        | Working
     */
    public function listfinisherApproveApplications(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'filterBy'  => 'nullable|in:mobileNo,applicantName,applicationNo,holdingNo,safNo',              // Static
                'parameter' => 'nullable',
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $user                       = authUser($request);
            $userId                     = $user->id;
            $confWorkflowMasterId       = $this->_workflowMasterId;
            $key                        = $request->filterBy;
            $paramenter                 = $request->parameter;
            $pages                      = $request->perPage ?? 10;
            $refstring                  = Str::snake($key);
            $msg                        = "Approve application list!";
            $mPetApprovedRegistration   = new PetApprovedRegistration();

            # Check params for role user 
            $roleDetails = $this->getUserRollV2($userId, $user->ulb_id, $confWorkflowMasterId);
            $this->checkParamForUser($user, $roleDetails);

            try {
                $baseQuerry = $mPetApprovedRegistration->getAllApprovdApplicationDetails()
                    ->select(
                        DB::raw("REPLACE(pet_approved_registrations.application_type, '_', ' ') AS ref_application_type"),
                        DB::raw("TO_CHAR(pet_approved_registrations.application_apply_date, 'DD-MM-YYYY') as ref_application_apply_date"),
                        "pet_approved_registrations.*",
                        "pet_approve_applicants.applicant_name",
                        "pet_approve_applicants.mobile_no",
                        "wf_roles.role_name",
                        "pet_approved_registrations.status as registrationSatus",
                        DB::raw("CASE 
                        WHEN pet_approved_registrations.status = 1 THEN 'Approved'
                        WHEN pet_approved_registrations.status = 2 THEN 'Under Renewal Process'
                        END as current_status")
                    )
                    ->where('pet_approved_registrations.status', '<>', 0)
                    ->where('pet_approve_applicants.status', '<>', 0)
                    ->where('pet_approve_details.status', '<>', 0)
                    ->where('pet_approved_registrations.approve_user_id', $userId)
                    ->where('pet_approved_registrations.finisher_role_id', $roleDetails->role_id)
                    ->where('pet_approved_registrations.current_role_id', $roleDetails->role_id)
                    ->orderByDesc('pet_approved_registrations.id');

                # Collect querry Exceptions 
            } catch (QueryException $qurry) {
                return responseMsgs(false, "An error occurred during the query!", $qurry->getMessage(), "", "01", ".ms", "POST", $request->deviceId);
            }

            if ($request->filterBy && $request->parameter) {
                $msg = "Pet approved appliction details according to $key!";
                # Distrubtion of search category  ❗❗ Static
                switch ($key) {
                    case ("mobileNo"):
                        $activeApplication = $baseQuerry->where('pet_approve_applicants.' . $refstring, 'LIKE', '%' . $paramenter . '%')
                            ->paginate($pages);
                        break;
                    case ("applicationNo"):
                        $activeApplication = $baseQuerry->where('pet_approved_registrations.' . $refstring, 'ILIKE', '%' . $paramenter . '%')
                            ->paginate($pages);
                        break;
                    case ("applicantName"):
                        $activeApplication = $baseQuerry->where('pet_approve_applicants.' . $refstring, 'ILIKE', '%' . $paramenter . '%')
                            ->paginate($pages);
                        break;
                    case ("holdingNo"):
                        $activeApplication = $baseQuerry->where('pet_approved_registrations.' . $refstring, 'LIKE', '%' . $paramenter . '%')
                            ->paginate($pages);
                        break;
                    case ("safNo"):
                        $activeApplication = $baseQuerry->where('pet_approved_registrations.' . $refstring, 'LIKE', '%' . $paramenter . '%')
                            ->paginate($pages);
                        break;
                    default:
                        throw new Exception("Data provided in filterBy is not valid!");
                }
                # Check if data not exist
                $checkVal = collect($activeApplication)->last();
                if (!$checkVal || $checkVal == 0) {
                    $msg = "Data Not found!";
                }
                return responseMsgs(true, $msg, remove_null($activeApplication), "", "01", responseTime(), $request->getMethod(), $request->deviceId);
            }

            # Get the latest data for Finisher
            $returnData = $baseQuerry->orderBy('pet_approved_registrations.approve_date')->paginate($pages);
            return responseMsgs(true, $msg, remove_null($returnData), "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }

    /**
     * | Check the user details 
        | Serial No:
        | Working
     */
    public function checkParamForUser($user, $roleDetails)
    {
        if (!$roleDetails) {
            throw new Exception("user Dont have role in Pet workflow!");
        }
        if ($roleDetails->is_finisher == false) {
            throw new Exception("You are not the finisher!");
        }
    }


    /**
     * | Get the rejected application list 
        | Serial No :
        | Working
     */
    public function listfinisherRejectApplications(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'filterBy'  => 'nullable|in:mobileNo,applicantName,applicationNo,holdingNo,safNo',              // Static
                'parameter' => 'nullable',
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $user                       = authUser($request);
            $userId                     = $user->id;
            $confWorkflowMasterId       = $this->_workflowMasterId;
            $key                        = $request->filterBy;
            $paramenter                 = $request->parameter;
            $pages                      = $request->perPage ?? 10;
            $refstring                  = Str::snake($key);
            $msg                        = "Rejected application list!";
            $mPetRejectedRegistration   = new PetRejectedRegistration();

            # Check params for role user 
            $roleDetails = $this->getUserRollV2($userId, $user->ulb_id, $confWorkflowMasterId);
            $this->checkParamForUser($user, $roleDetails);

            try {
                $baseQuerry = $mPetRejectedRegistration->getAllRejectedApplicationDetails()
                    ->select(
                        DB::raw("REPLACE(pet_rejected_registrations.application_type, '_', ' ') AS ref_application_type"),
                        DB::raw("TO_CHAR(pet_rejected_registrations.application_apply_date, 'DD-MM-YYYY') as ref_application_apply_date"),
                        "pet_rejected_registrations.*",
                        "pet_rejected_applicants.applicant_name",
                        "pet_rejected_applicants.mobile_no",
                        "wf_roles.role_name",
                        "pet_rejected_registrations.status as registrationSatus",
                        DB::raw("CASE 
                        WHEN pet_rejected_registrations.status = 1 THEN 'Approved'
                        WHEN pet_rejected_registrations.status = 2 THEN 'Under Renewal Process'
                        END as current_status")
                    )
                    ->where('pet_rejected_registrations.status', '<>', 0)
                    ->where('pet_rejected_applicants.status', '<>', 0)
                    ->where('pet_rejected_details.status', '<>', 0)
                    ->where('pet_rejected_registrations.rejected_user_id', $userId)
                    ->where('pet_rejected_registrations.finisher_role_id', $roleDetails->role_id)
                    ->where('pet_rejected_registrations.current_role_id', $roleDetails->role_id)
                    ->orderByDesc('pet_rejected_registrations.id');

                # Collect querry Exceptions 
            } catch (QueryException $qurry) {
                return responseMsgs(false, "An error occurred during the query!", $qurry->getMessage(), "", "01", ".ms", "POST", $request->deviceId);
            }

            if ($request->filterBy && $request->parameter) {
                $msg = "Pet rejected appliction details according to $key!";
                # Distrubtion of search category  ❗❗ Static
                switch ($key) {
                    case ("mobileNo"):
                        $activeApplication = $baseQuerry->where('pet_rejected_applicants.' . $refstring, 'LIKE', '%' . $paramenter . '%')
                            ->paginate($pages);
                        break;
                    case ("applicationNo"):
                        $activeApplication = $baseQuerry->where('pet_rejected_registrations.' . $refstring, 'ILIKE', '%' . $paramenter . '%')
                            ->paginate($pages);
                        break;
                    case ("applicantName"):
                        $activeApplication = $baseQuerry->where('pet_rejected_applicants.' . $refstring, 'ILIKE', '%' . $paramenter . '%')
                            ->paginate($pages);
                        break;
                    case ("holdingNo"):
                        $activeApplication = $baseQuerry->where('pet_rejected_registrations.' . $refstring, 'LIKE', '%' . $paramenter . '%')
                            ->paginate($pages);
                        break;
                    case ("safNo"):
                        $activeApplication = $baseQuerry->where('pet_rejected_registrations.' . $refstring, 'LIKE', '%' . $paramenter . '%')
                            ->paginate($pages);
                        break;
                    default:
                        throw new Exception("Data provided in filterBy is not valid!");
                }
                # Check if data not exist
                $checkVal = collect($activeApplication)->last();
                if (!$checkVal || $checkVal == 0) {
                    $msg = "Data Not found!";
                }
                return responseMsgs(true, $msg, remove_null($activeApplication), "", "01", responseTime(), $request->getMethod(), $request->deviceId);
            }

            # Get the latest data for Finisher
            $returnData = $baseQuerry->orderBy('pet_rejected_registrations.rejected_date')->paginate($pages);
            return responseMsgs(true, $msg, remove_null($returnData), "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }
}
