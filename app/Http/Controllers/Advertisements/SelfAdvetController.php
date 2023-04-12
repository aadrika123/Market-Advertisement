<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelfAdvets\RenewalRequest;
use App\Http\Requests\SelfAdvets\StoreRequest;
use App\MicroServices\DocumentUpload;
use App\Models\Advertisements\AdvActiveSelfadvertisement;
use App\Models\Advertisements\AdvChequeDtl;
use App\Models\Advertisements\AdvSelfadvertisement;
use App\Models\Advertisements\AdvRejectedSelfadvertisement;
use App\Models\Advertisements\AdvSelfadvCategory;
use App\Models\Advertisements\RefRequiredDocument;
use App\Models\Advertisements\WfActiveDocument;
use App\Models\TradeLicence;
use App\Models\Workflows\WfRoleusermap;
use App\Traits\WorkflowTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Traits\AdvDetailsTraits;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Workflows\WorkflowTrack;
use Illuminate\Support\Facades\Config;
use App\Models\Workflows\WfWardUser;
use App\Models\Workflows\WfWorkflowrolemap;
use App\Repositories\SelfAdvets\iSelfAdvetRepo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\Process\ExecutableFinder;

use App\BLL\Advert\CalculateRate;

// use App\Repository\WorkflowMaster\Concrete\WorkflowMap;


/**
 * | Created On-14-12-2022 
 * | Created By-Anshu Kumar
 * | Created for Operations on Self Advertisements
 * | Workflow ID=129
 * | Ulb Workflow ID=245
 * | Changes By Bikash 
 * | Status - Open (17 Jan 2023)
 */

class SelfAdvetController extends Controller
{
    use WorkflowTrait;
    use AdvDetailsTraits;
    protected $_modelObj;  //  Generate Model Instance
    protected $_repository;
    protected $_workflowIds;
    protected $_moduleIds;
    protected $_docCode;
    protected $_paramId;
    protected $_tempParamId;
    protected $_baseUrl;

    //Constructor
    public function __construct(iSelfAdvetRepo $self_repo)
    {
        $this->_modelObj = new AdvActiveSelfadvertisement();
        $this->_workflowIds = Config::get('workflow-constants.ADVERTISEMENT_WORKFLOWS');
        $this->_moduleIds = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
        $this->_repository = $self_repo;
        $this->_docCode = Config::get('workflow-constants.SELF_ADVERTISMENT_DOC_CODE');
        $this->_paramId = Config::get('workflow-constants.SELF_ID');
        $this->_tempParamId = Config::get('workflow-constants.TEMP_SELF_ID');
        $this->_baseUrl = Config::get('constants.BASE_URL');
    }


    /**
     * | Apply Application for Self Advertisements 
     * | @param StoreRequest 
     */
    public function addNew(StoreRequest $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mAdvActiveSelfadvertisement = $this->_modelObj;
            if (authUser()->user_type == 'JSK') {
                $userId = ['userId' => authUser()->id];
                $req->request->add($userId);
            } else {
                $citizenId = ['citizenId' => authUser()->id];
                $req->request->add($citizenId);
            }

            $mCalculateRate = new CalculateRate;
            $generatedId = $mCalculateRate->generateId($req->bearerToken(), $this->_tempParamId, $req->ulbId); // Generate Application No
            $applicationNo = ['application_no' => $generatedId];
            $req->request->add($applicationNo);

            DB::beginTransaction();
            $applicationNo = $mAdvActiveSelfadvertisement->addNew($req);       //<--------------- Model function to store 
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Successfully Submitted the application !!", ['status' => true, 'ApplicationNo' => $applicationNo], "050101", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050101", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Get Application Details For Renew
     */
    public function applicationDetailsForRenew(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|digits_between:1,9223372036854775807'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mAdvSelfadvertisement = new AdvSelfadvertisement();
            $details = $mAdvSelfadvertisement->applicationDetailsForRenew($req->applicationId);  // Get Renew Application Details
            if (!$details)
                throw new Exception("Application Not Found !!!");

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Application Fetched !!!", remove_null($details), "050102", "1.0", " $executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050102", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Renewal for Self Advertisements 
     * | @param StoreRequest 
     */
    public function renewalSelfAdvt(RenewalRequest $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mAdvActiveSelfadvertisement = $this->_modelObj;
            if (authUser()->user_type == 'JSK') {
                $userId = ['userId' => authUser()->id];
                $req->request->add($userId);
            } else {
                $citizenId = ['citizenId' => authUser()->id];
                $req->request->add($citizenId);
            }

            $mCalculateRate = new CalculateRate;
            $generatedId = $mCalculateRate->generateId($req->bearerToken(), $this->_tempParamId, $req->ulbId); // Generate Application No
            $applicationNo = ['application_no' => $generatedId];
            $req->request->add($applicationNo);

            DB::beginTransaction();
            $applicationNo = $mAdvActiveSelfadvertisement->renewalSelfAdvt($req);       //<--------------- Model function to store 
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Successfully Submitted the application !!", ['status' => true, 'ApplicationNo' => $applicationNo['renew_no']], "050103", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050103", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Get Self Advertisement Category List
     */
    public function listSelfAdvtCategory()
    {
        $startTime = microtime(true);
        $list = AdvSelfadvCategory::select('id', 'type', 'descriptions')
            ->where('status', '1')
            ->orderBy('id', 'ASC')
            ->get();
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        return responseMsgs(true, "Advertisement Catrgory", remove_null($list->toArray()), "050104", "1.0", "$executionTime Sec", "POST",  "");
    }

    /**
     * | Inbox List
     * | @param Request $req
     */
    public function listInbox(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mAdvActiveSelfadvertisement = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });

            $inboxList = $mAdvActiveSelfadvertisement->listInbox($roleIds);             // <------ Get List From Model
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Inbox Applications", remove_null($inboxList->toArray()), "050105", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050105", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Outbox List
     */
    public function listOutbox(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mAdvActiveSelfadvertisement = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });

            $outboxList = $mAdvActiveSelfadvertisement->listOutbox($roleIds);           // <------ Get List From Model
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Outbox Lists", remove_null($outboxList->toArray()), "050106", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050106", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Application Details
     */
    public function getDetailsById(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mAdvActiveSelfadvertisement = new AdvActiveSelfadvertisement();
            $fullDetailsData = array();
            $type = NULL;
            if (isset($req->type)) {
                $type = $req->type;
            }
            if ($req->applicationId) {
                $data = $mAdvActiveSelfadvertisement->getDetailsById($req->applicationId, $type);   // Find Details From Model
            } else {
                throw new Exception("Not Pass Application Id");
            }

            if (!$data)
                throw new Exception("Application Not Found");

            // Basic Details
            $basicDetails = $this->generateBasicDetails($data);                             // Trait function to get Basic Details
            $basicElement = [
                'headerTitle' => "Basic Details",
                "data" => $basicDetails
            ];

            $cardDetails = $this->generateCardDetails($data);
            $cardElement = [
                'headerTitle' => "Self Advertisement Details",
                'data' => $cardDetails
            ];
            $fullDetailsData['fullDetailsData']['dataArray'] = new Collection([$basicElement]);
            $fullDetailsData['fullDetailsData']['cardArray'] = new Collection($cardElement);

            $metaReqs['customFor'] = 'SELF';
            $metaReqs['wfRoleId'] = $data['current_role_id'];
            $metaReqs['workflowId'] = $data['workflow_id'];
            $metaReqs['lastRoleId'] = $data['last_role_id'];

            $req->request->add($metaReqs);
            $forwardBackward = $this->getRoleDetails($req);
            $fullDetailsData['roleDetails'] = collect($forwardBackward)['original']['data'];

            $fullDetailsData = remove_null($fullDetailsData);

            $fullDetailsData['application_no'] = $data['application_no'];
            $fullDetailsData['apply_date'] = $data['application_date'];
            $fullDetailsData['doc_verify_status'] = $data['doc_verify_status'];
            if (isset($data['payment_amount'])) {
                $fullDetailsData['payment_amount'] = $data['payment_amount'];
            }
            $fullDetailsData['timelineData'] = collect($req);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, 'Data Fetched', $fullDetailsData, "050107", "1.0", "$executionTime Sec", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050107", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Get Role Details
     */
    public function getRoleDetails(Request $request)
    {
        $ulbId = auth()->user()->ulb_id;
        $request->validate([
            'workflowId' => 'required|int'
        ]);
        $roleDetails = DB::table('wf_workflowrolemaps')
            ->select(
                'wf_workflowrolemaps.id',
                'wf_workflowrolemaps.workflow_id',
                'wf_workflowrolemaps.wf_role_id',
                'wf_workflowrolemaps.forward_role_id',
                'wf_workflowrolemaps.backward_role_id',
                'wf_workflowrolemaps.is_initiator',
                'wf_workflowrolemaps.is_finisher',
                'r.role_name as forward_role_name',
                'rr.role_name as backward_role_name'
            )
            ->leftJoin('wf_roles as r', 'wf_workflowrolemaps.forward_role_id', '=', 'r.id')
            ->leftJoin('wf_roles as rr', 'wf_workflowrolemaps.backward_role_id', '=', 'rr.id')
            ->where('workflow_id', $request->workflowId)
            ->where('wf_role_id', $request->wfRoleId)
            ->first();
        return responseMsgs(true, "Data Retrived", remove_null($roleDetails));
    }


    /**
     * | Get Applied Applications by Logged In Citizen
     */
    public function listAppliedApplications(Request $req)
    {
        try {
            // Variable Initialization
            $startTime = microtime(true);
            $citizenId = authUser()->id;
            $selfAdvets = new AdvActiveSelfadvertisement();

            $applications = $selfAdvets->listAppliedApplications($citizenId);             //<-------  Get Applied Applications

            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            $endTime = microtime(true);

            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Applied Applications", $data1, "050108", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050108", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Escalate
     */
    public function escalateApplication(Request $request)
    {
        $request->validate([
            "escalateStatus" => "required|int",
            "applicationId" => "required|int",
        ]);
        try {
            // Variable Initialization
            $startTime = microtime(true);
            $userId = auth()->user()->id;
            $applicationId = $request->applicationId;

            $data = AdvActiveSelfadvertisement::find($applicationId);
            $data->is_escalate = $request->escalateStatus;
            $data->escalate_by = $userId;
            $data->save();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, $request->escalateStatus == 1 ? 'Self Advertisment is Escalated' : "Self Advertisment is removed from Escalated", '', "050109", "1.0", "$executionTime Sec", "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050109", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }


    public function listEscalated(Request $req)
    {
        try {
            // Variable Initialization
            $startTime = microtime(true);
            $mWfWardUser = new WfWardUser();
            $userId = authUser()->id;
            $ulbId = authUser()->ulb_id;

            $occupiedWard = $mWfWardUser->getWardsByUserId($userId);                        // Get All Occupied Ward By user id using trait
            $wardId = $occupiedWard->map(function ($item, $key) {                           // Filter All ward_id in an array using laravel collections
                return $item->ward_id;
            });

            $advData = $this->_repository->specialInbox($this->_workflowIds)                      // Repository function to get Advertiesment Details
                ->where('is_escalate', 1)
                ->where('adv_active_selfadvertisements.ulb_id', $ulbId)
                ->whereIn('ward_id', $wardId)
                ->get();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Data Fetched", remove_null($advData), "050110", "1.0", "$executionTime Sec", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050110", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Forward or Backward Application
     */
    public function forwordNextLevel(Request $request)
    {
        $request->validate([
            'applicationId' => 'required|integer',
            'senderRoleId' => 'required|integer',
            'receiverRoleId' => 'required|integer',
            'comment' => 'required',
        ]);
        try {
            $startTime = microtime(true);
            // Advertisment Application Update Current Role Updation
            DB::beginTransaction();
            $adv = AdvActiveSelfadvertisement::find($request->applicationId);
            if ($adv->doc_verify_status == '0')
                throw new Exception("Please Verify All Documents To Forward The Application !!!");
            $adv->last_role_id = $adv->current_role_id;
            $adv->current_role_id = $request->receiverRoleId;
            $adv->save();

            $metaReqs['moduleId'] = $this->_moduleIds;
            $metaReqs['workflowId'] = $adv->workflow_id;
            $metaReqs['refTableDotId'] = "adv_active_selfadvertisments.id";
            $metaReqs['refTableIdValue'] = $request->applicationId;
            $request->request->add($metaReqs);

            $track = new WorkflowTrack();
            $track->saveTrack($request);
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Successfully Forwarded The Application!!", "", "050111", "1.0", "$executionTime Sec", "POST", $request->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050111", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }



    // Post Independent Comment
    public function commentApplication(Request $request)
    {
        $request->validate([
            'comment' => 'required',
            'applicationId' => 'required|integer',
            'senderRoleId' => 'nullable|integer'
        ]);
        try {
            // Variable Initialazition
            $startTime = microtime(true);
            $userId = authUser()->id;
            $userType = authUser()->user_type;
            $workflowTrack = new WorkflowTrack();
            $mWfRoleUsermap = new WfRoleusermap();
            $adv = AdvActiveSelfadvertisement::find($request->applicationId);                // Advertisment Details
            $mModuleId = $this->_moduleIds;
            $metaReqs = array();
            DB::beginTransaction();
            // Save On Workflow Track For Level Independent
            $metaReqs = [
                'workflowId' => $adv->workflow_id,
                'moduleId' => $mModuleId,
                'refTableDotId' => "adv_active_selfadvertisments.id",
                'refTableIdValue' => $adv->id,
                'message' => $request->comment
            ];
            // For Citizen Independent Comment
            if ($userType != 'Citizen') {
                $roleReqs = new Request([
                    'workflowId' => $adv->workflow_id,
                    'userId' => $userId,
                ]);
                $wfRoleId = $mWfRoleUsermap->getRoleByUserWfId($roleReqs);
                $metaReqs = array_merge($metaReqs, ['senderRoleId' => $wfRoleId->wf_role_id]);
                $metaReqs = array_merge($metaReqs, ['user_id' => $userId]);
            }
            $request->request->add($metaReqs);
            $workflowTrack->saveTrack($request);
            DB::commit();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "You Have Commented Successfully!!", ['Comment' => $request->comment], "050112", "1.0", " $executionTime Sec", "POST", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050112", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }


    /**
     * | Get License By User ID
     */
    public function getLicenseById(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'user_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors(), "", "050111", "1.0", "", "POST", $req->deviceId ?? "");
        }
        try {
            // Variable initialization
            $startTime = microtime(true);
            $tradeLicence = new TradeLicence();
            $licenseList = $tradeLicence->getLicenceByUserId($req->user_id);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Licenses", remove_null($licenseList->toArray()), "050113", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050113", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Get License By Holding No
     */
    public function getLicenseByHoldingNo(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'holding_no' => 'required|string'
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors(), "", "050114", "1.0", "", "POST", $req->deviceId ?? "");
        }
        try {
            // Variable initialization
            $startTime = microtime(true);
            $tradeLicense = new TradeLicence();
            $licenseList = $tradeLicense->getLicenceByHoldingNo($req->holding_no);

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Licenses", remove_null($licenseList->toArray()), "050114", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050114", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Get Uploaded Document by application ID
     */
    public function viewAdvertDocument(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors(), "", "040105", "1.0", "", "POST", $req->deviceId ?? "");
        }
        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        $data = $mWfActiveDocument->uploadDocumentsViewById($req->applicationId, $this->_workflowIds);
        $data1['data'] = $data;
        return $data1;
    }

    /**
     * | Get Uploaded Active Document by application ID
     */
    public function viewActiveDocument(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|digits_between:1,9223372036854775807'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        $data = $mWfActiveDocument->uploadedActiveDocumentsViewById($req->applicationId, $this->_workflowIds);
        $data1['data'] = $data;
        return $data1;
    }

    /**
     * | Workflow View Uploaded Document by application ID
     */
    public function viewDocumentsOnWorkflow(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|digits_between:1,9223372036854775807'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        // Variable initialization
        $startTime = microtime(true);
        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        $data = $mWfActiveDocument->uploadDocumentsViewById($req->applicationId, $this->_workflowIds);
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        return responseMsgs(true, "Data Fetched", remove_null($data), "050118", "1.0", "$executionTime Sec", "POST", "");
    }


    public function getDetailsByLicenseNo(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'license_no' => 'required'
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors(), "", "040105", "1.0", "", "POST", $req->deviceId ?? "");
        }
        $mtradeLicense = new TradeLicence();
        $data = array();
        if ($req->license_no) {
            $data = $mtradeLicense->getDetailsByLicenceNo($req->license_no);
        }
        if (!empty($data)) {
            $licenseElement = ['status' => true, 'headerTitle' => "License Details", 'data' => $data];
        } else {
            $licenseElement = ['status' => false, 'headerTitle' => "License Details", 'data' => "Invalid License No"];
        }
        return $licenseElement;
    }

    /**
     * | Final Approval and Rejection of the Application
     * | Rating-
     * | Status- Open
     */
    public function approvalOrRejection(Request $req)
    {
        $req->validate([
            'roleId' => 'required',
            'applicationId' => 'required|integer',
            'status' => 'required|integer',
        ]);
        try {
            // Variable initialization
            $startTime = microtime(true);
            // Check if the Current User is Finisher or Not         
            $mAdvActiveSelfadvertisement = AdvActiveSelfadvertisement::find($req->applicationId);
            $getFinisherQuery = $this->getFinisherId($mAdvActiveSelfadvertisement->workflow_id);                                 // Get Finisher using Trait
            $refGetFinisher = collect(DB::select($getFinisherQuery))->first();
            if ($refGetFinisher->role_id != $req->roleId) {
                return responseMsgs(false, " Access Forbidden", "");
            }

            DB::beginTransaction();
            // Approval
            if ($req->status == 1) {
                $mCalculateRate = new CalculateRate;
                $data['payment_amount'] = ['payment_amount' => 0];
                $data['payment_status'] = ['payment_status' => 1];
                if ($mAdvActiveSelfadvertisement->advt_category > 10) {
                    $payment_amount = $mCalculateRate->getAdvertisementPayment($mAdvActiveSelfadvertisement->display_area);   // Calculate Price
                    $data['payment_status'] = ['payment_status' => 0];
                    $data['payment_amount'] = ['payment_amount' => $payment_amount];
                }
                $req->request->add($data['payment_amount']);
                $req->request->add($data['payment_status']);

                $generatedId = $mCalculateRate->generateId($req->bearerToken(), $this->_paramId, $mAdvActiveSelfadvertisement->ulb_id); // Generate License No

                if ($mAdvActiveSelfadvertisement->renew_no == NULL) {
                    // Selfadvertisement Application replication
                    $approvedSelfadvertisement = $mAdvActiveSelfadvertisement->replicate();
                    $approvedSelfadvertisement->setTable('adv_selfadvertisements');
                    $temp_id = $approvedSelfadvertisement->id = $mAdvActiveSelfadvertisement->id;
                    $approvedSelfadvertisement->payment_amount = $req->payment_amount;
                    $approvedSelfadvertisement->payment_status = $req->payment_status;
                    $approvedSelfadvertisement->license_no = $generatedId;
                    $approvedSelfadvertisement->approve_date = Carbon::now();
                    $approvedSelfadvertisement->save();

                    // Save in self Advertisement Renewal
                    $approvedSelfadvertisement = $mAdvActiveSelfadvertisement->replicate();
                    $approvedSelfadvertisement->approve_date = Carbon::now();
                    $approvedSelfadvertisement->setTable('adv_selfadvet_renewals');
                    $approvedSelfadvertisement->license_no = $generatedId;
                    $approvedSelfadvertisement->id = $temp_id;
                    $approvedSelfadvertisement->save();

                    $mAdvActiveSelfadvertisement->delete();

                    // Update in adv_selfadvertisements (last_renewal_id)
                    DB::table('adv_selfadvertisements')
                        ->where('id', $temp_id)
                        ->update(['last_renewal_id' => $approvedSelfadvertisement->id]);
                    $msg = "Application Successfully Approved !!";
                } else {
                    //  Renewal Case
                    // Selfadvertisement Application replication
                    $license_no = $mAdvActiveSelfadvertisement->license_no;
                    AdvSelfadvertisement::where('license_no', $license_no)->delete();

                    $approvedSelfadvertisement = $mAdvActiveSelfadvertisement->replicate();
                    $approvedSelfadvertisement->setTable('adv_selfadvertisements');
                    $temp_id = $approvedSelfadvertisement->id = $mAdvActiveSelfadvertisement->id;
                    $approvedSelfadvertisement->payment_amount = $req->payment_amount;
                    $approvedSelfadvertisement->payment_status = $req->payment_status;
                    $approvedSelfadvertisement->approve_date = Carbon::now();
                    $approvedSelfadvertisement->save();

                    // Save in self Advertisement Renewal
                    $approvedSelfadvertisement = $mAdvActiveSelfadvertisement->replicate();
                    $approvedSelfadvertisement->approve_date = Carbon::now();
                    $approvedSelfadvertisement->setTable('adv_selfadvet_renewals');
                    $approvedSelfadvertisement->id = $temp_id;
                    $approvedSelfadvertisement->save();

                    $mAdvActiveSelfadvertisement->delete();

                    // Update in adv_selfadvertisements (last_renewal_id)
                    DB::table('adv_selfadvertisements')
                        ->where('id', $temp_id)
                        ->update(['last_renewal_id' => $approvedSelfadvertisement->id]);
                    $msg = "Application Successfully Renewal !!";
                }
            }
            // Rejection
            if ($req->status == 0) {
                $payment_amount = ['payment_amount' => 0];
                $req->request->add($payment_amount);
                // Selfadvertisement Application replication
                $rejectedSelfadvertisement = $mAdvActiveSelfadvertisement->replicate();
                $rejectedSelfadvertisement->setTable('adv_rejected_selfadvertisements');
                $rejectedSelfadvertisement->id = $mAdvActiveSelfadvertisement->id;
                $rejectedSelfadvertisement->rejected_date = Carbon::now();
                $rejectedSelfadvertisement->save();
                $mAdvActiveSelfadvertisement->delete();
                $msg = "Application Successfully Rejected !!";
            }
            DB::commit();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, $msg, "", '050119', 01, "$executionTime Sec", 'POST', $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false,  $e->getMessage(), "", '050119', 01, "", 'POST', $req->deviceId);
        }
    }

    // public function getAdvertisementPayment($displayArea)
    // {
    //     return $displayArea * 10;   // Rs. 10  per Square ft.
    // }

    /**
     * | Approve Application List for Citzen
     * | @param Request $req
     */
    public function listApproved(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $citizenId = authUser()->id;
            $userType = authUser()->user_type;
            $mAdvSelfadvertisements = new AdvSelfadvertisement();
            $applications = $mAdvSelfadvertisements->listApproved($citizenId, $userType);
            $totalApplication = $applications->count();
            if ($totalApplication == 0) {
                $applications = NULL;
            }
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Approved Application List", $data1, "050120", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050120", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Reject Application List for Citizen
     * | @param Request $req
     */
    public function listRejected(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $citizenId = authUser()->id;
            $mAdvRejectedSelfadvertisement = new AdvRejectedSelfadvertisement();
            $applications = $mAdvRejectedSelfadvertisement->listRejected($citizenId);

            $totalApplication = $applications->count();
            if ($totalApplication == 0) {
                $applications = null;
            }
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Rejected Application List", $data1, "050121", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050121", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Get Applied Applications by Logged In JSK
     */
    public function getJSKApplications(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $userId = authUser()->id;
            $mAdvActiveSelfadvertisement = new AdvActiveSelfadvertisement();

            $applications = $mAdvActiveSelfadvertisement->getJSKApplications($userId);
            $totalApplication = $applications->count();

            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Applied Applications", $data1, "050122", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050122", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Approve Application List for JSK
     * | @param Request $req
     */
    public function listJskApprovedApplication(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $userId = authUser()->id;
            $mAdvSelfadvertisements = new AdvSelfadvertisement();
            $applications = $mAdvSelfadvertisements->listJskApprovedApplication($userId);

            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Approved Application List", $data1, "050123", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050123", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Reject Application List for JSK
     * | @param Request $req
     */
    public function listJskRejectedApplication(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $userId = authUser()->id;
            $mAdvRejectedSelfadvertisement = new AdvRejectedSelfadvertisement();
            $applications = $mAdvRejectedSelfadvertisement->listJskRejectedApplication($userId);

            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Rejected Application List", $data1, "050124", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050124", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }



    /**
     * | Generate Payment Order ID
     * | @param Request $req
     */

    public function generatePaymentOrderId(Request $req)
    {
        $req->validate([
            'id' => 'required|integer',
        ]);
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mAdvSelfadvertisement = AdvSelfadvertisement::find($req->id);


            $reqData = [
                "id" => $mAdvSelfadvertisement->id,
                'amount' => $mAdvSelfadvertisement->payment_amount,
                'workflowId' => $mAdvSelfadvertisement->workflow_id,
                'ulbId' => $mAdvSelfadvertisement->ulb_id,
                'departmentId' => $this->_moduleIds
            ];
            $paymentUrl = Config::get('constants.PAYMENT_URL');
            $refResponse = Http::withHeaders([
                "api-key" => "eff41ef6-d430-4887-aa55-9fcf46c72c99"
            ])
                ->withToken($req->bearerToken())
                ->post($paymentUrl . 'api/payment/generate-orderid', $reqData);

            $data = json_decode($refResponse);

            if (!$data)
                throw new Exception("Payment Order Id Not Generate");

            $data->name = $mAdvSelfadvertisement->applicant;
            $data->email = $mAdvSelfadvertisement->email;
            $data->contact = $mAdvSelfadvertisement->mobile_no;
            $data->type = "Self Advertisement";


            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Payment OrderId Generated Successfully !!!", $data, "050125", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050125", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * Summary of application Details For Payment
     * @param Request $req
     * @return void
     */
    public function applicationDetailsForPayment(Request $req)
    {
        $req->validate([
            'applicationId' => 'required|integer',
        ]);
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mAdvSelfadvertisement = new AdvSelfadvertisement();

            if ($req->applicationId) {
                $data = $mAdvSelfadvertisement->applicationDetailsForPayment($req->applicationId);
            }
            if (!$data)
                throw new Exception("Application Not Found");

            $data['type'] = "Self Advertisement";
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, 'Data Fetched',  $data, "050126", "1.0", "$executionTime Sec", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050126", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    public function paymentByCash(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|string',
            'status' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mAdvSelfadvertisement = new AdvSelfadvertisement();
            DB::beginTransaction();
            $data = $mAdvSelfadvertisement->paymentByCash($req);
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            if ($req->status == '1' && $data['status'] == 1) {
                return responseMsgs(true, "Payment Successfully !!", ['status' => true, 'transactionNo' => $data['payment_id'], 'workflowId' => $this->_workflowIds], "050127", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
            } else {
                return responseMsgs(false, "Payment Rejected !!", '', "050127", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050127", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    public function entryChequeDd(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|string',               //  temp_id of Application
            'bankName' => 'required|string',
            'branchName' => 'required|string',
            'chequeNo' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mAdvCheckDtl = new AdvChequeDtl();
            $workflowId = ['workflowId' => $this->_workflowIds];

            $req->request->add($workflowId);
            $transNo = $mAdvCheckDtl->entryChequeDd($req);                              // Store Cheque Details In Model
            $endTime = microtime(true);

            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Check Entry Successfully !!", ['status' => true, 'TransactionNo' => $transNo], "050128", "1.0", " $executionTime Sec", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050128", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function clearOrBounceCheque(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'paymentId' => 'required|string',
            'status' => 'required|string',
            'remarks' => $req->status == 1 ? 'nullable|string' : 'required|string',
            'bounceAmount' => $req->status == 1 ? 'nullable|numeric' : 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mAdvCheckDtl = new AdvChequeDtl();
            DB::beginTransaction();
            $data = $mAdvCheckDtl->clearOrBounceCheque($req);
            DB::commit();
            $endTime = microtime(true);

            $executionTime = $endTime - $startTime;
            if ($req->status == '1' && $data['status'] == 1) {
                return responseMsgs(true, "Payment Successfully !!", ['status' => true, 'transactionNo' => $data['payment_id'], 'workflowId' => $this->_workflowIds], "050129", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
            } else {
                return responseMsgs(false, "Payment Rejected !!", '', "050129", "1.0", "", 'POST', $req->deviceId ?? "");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050129", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Verify Single Application Approve or reject
     * |
     */
    public function verifyOrRejectDoc(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|digits_between:1,9223372036854775807',
            'applicationId' => 'required|digits_between:1,9223372036854775807',
            'docRemarks' =>  $req->docStatus == "Rejected" ? 'required|regex:/^[a-zA-Z1-9][a-zA-Z1-9\. \s]+$/' : "nullable",
            'docStatus' => 'required|in:Verified,Rejected'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mWfDocument = new WfActiveDocument();
            $mAdvActiveSelfadvertisement = new AdvActiveSelfadvertisement();
            $mWfRoleusermap = new WfRoleusermap();
            $wfDocId = $req->id;
            $userId = authUser()->id;
            $applicationId = $req->applicationId;

            $wfLevel = Config::get('constants.SELF-LABEL');
            // Derivative Assigments
            $appDetails = $mAdvActiveSelfadvertisement->getSelfAdvertNo($applicationId);

            if (!$appDetails || collect($appDetails)->isEmpty())
                throw new Exception("Application Details Not Found");

            $appReq = new Request([
                'userId' => $userId,
                'workflowId' => $appDetails->workflow_id
            ]);
            $senderRoleDtls = $mWfRoleusermap->getRoleByUserWfId($appReq);
            if (!$senderRoleDtls || collect($senderRoleDtls)->isEmpty())
                throw new Exception("Role Not Available");

            $senderRoleId = $senderRoleDtls->wf_role_id;

            if ($senderRoleId != $wfLevel['DA'])                                // Authorization for Dealing Assistant Only
                throw new Exception("You are not Authorized");

            $ifFullDocVerified = $this->ifFullDocVerified($applicationId);       // (Current Object Derivative Function 4.1)

            if ($ifFullDocVerified == 1)
                throw new Exception("Document Fully Verified");

            DB::beginTransaction();
            if ($req->docStatus == "Verified") {
                $status = 1;
            }
            if ($req->docStatus == "Rejected") {
                $status = 2;
                // For Rejection Doc Upload Status and Verify Status will disabled
                $appDetails->doc_upload_status = 0;
                $appDetails->doc_verify_status = 0;
                $appDetails->save();
            }

            $reqs = [
                'remarks' => $req->docRemarks,
                'verify_status' => $status,
                'action_taken_by' => $userId
            ];
            $mWfDocument->docVerifyReject($wfDocId, $reqs);
            $ifFullDocVerifiedV1 = $this->ifFullDocVerified($applicationId);

            if ($ifFullDocVerifiedV1 == 1) {                                     // If The Document Fully Verified Update Verify Status
                $appDetails->doc_verify_status = 1;
                $appDetails->save();
            }
            $endTime = microtime(true);

            $executionTime = $endTime - $startTime;
            DB::commit();
            return responseMsgs(true, $req->docStatus . " Successfully", "$executionTime Sec", "050130", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050130", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Check if the Document is Fully Verified or Not (4.1)
     */
    public function ifFullDocVerified($applicationId)
    {
        // Variable initialization
        $mAdvActiveSelfadvertisement = new AdvActiveSelfadvertisement();
        $mWfActiveDocument = new WfActiveDocument();
        $mAdvActiveSelfadvertisement = $mAdvActiveSelfadvertisement->getSelfAdvertNo($applicationId); // Get Application Details

        $refReq = [
            'activeId' => $applicationId,
            'workflowId' => $mAdvActiveSelfadvertisement->workflow_id,
            'moduleId' =>  $this->_moduleIds
        ];
        $req = new Request($refReq);
        $refDocList = $mWfActiveDocument->getDocsByActiveId($req);
        $totalApproveDoc = $refDocList->count();
        $ifAdvDocUnverified = $refDocList->contains('verify_status', 0);

        $totalNoOfDoc = $mWfActiveDocument->totalNoOfDocs($this->_docCode);
        // $totalNoOfDoc=$mWfActiveDocument->totalNoOfDocs($this->_docCodeRenew);
        // if($mMarActiveBanquteHall->renew_no==NULL){
        //     $totalNoOfDoc=$mWfActiveDocument->totalNoOfDocs($this->_docCode);
        // }
        if ($totalApproveDoc == $totalNoOfDoc) {
            if ($ifAdvDocUnverified == 1)
                return 0;
            else
                return 1;
        } else {
            return 0;
        }
    }

    /**
     *  send back to citizen
     */
    public function backToCitizen(Request $req)
    {
        $req->validate([
            'applicationId' => "required"
        ]);
        try {
            // Variable initialization
            $startTime = microtime(true);
            $redis = Redis::connection();
            $mAdvActiveSelfadvertisement = AdvActiveSelfadvertisement::find($req->applicationId);

            $workflowId = $mAdvActiveSelfadvertisement->workflow_id;
            $backId = json_decode(Redis::get('workflow_initiator_' . $workflowId));
            if (!$backId) {
                $backId = WfWorkflowrolemap::where('workflow_id', $workflowId)
                    ->where('is_initiator', true)
                    ->first();
                $redis->set('workflow_initiator_' . $workflowId, json_encode($backId));
            }

            $mAdvActiveSelfadvertisement->current_role_id = $backId->wf_role_id;
            $mAdvActiveSelfadvertisement->parked = 1;
            $mAdvActiveSelfadvertisement->save();

            $metaReqs['moduleId'] = $this->_moduleIds;
            $metaReqs['workflowId'] = $mAdvActiveSelfadvertisement->workflow_id;
            $metaReqs['refTableDotId'] = "adv_active_selfadvertisments.id";
            $metaReqs['refTableIdValue'] = $req->applicationId;
            $metaReqs['verificationStatus'] = $req->verificationStatus;
            $metaReqs['senderRoleId'] = $req->currentRoleId;
            $req->request->add($metaReqs);

            $req->request->add($metaReqs);
            $track = new WorkflowTrack();
            $track->saveTrack($req);
            $endTime = microtime(true);

            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Successfully Done", "", "", '050131', '01', "$executionTime Sec", 'Post', '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050131", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    /**
     * | Back To Citizen Inbox
     */
    public function listBtcInbox()
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $auth = auth()->user();
            $userId = $auth->id;
            $ulbId = $auth->ulb_id;
            $wardId = $this->getWardByUserId($userId);

            $occupiedWards = collect($wardId)->map(function ($ward) {                               // Get Occupied Ward of the User
                return $ward->ward_id;
            });

            $roles = $this->getRoleIdByUserId($userId);

            $roleId = collect($roles)->map(function ($role) {                                       // get Roles of the user
                return $role->wf_role_id;
            });

            $mAdvActiveSelfadvertisement = new AdvActiveSelfadvertisement();
            $btcList = $mAdvActiveSelfadvertisement->getSelfAdvertisementList($ulbId)
                ->whereIn('adv_active_selfadvertisements.current_role', $roleId)
                // ->whereIn('a.ward_mstr_id', $occupiedWards)
                ->where('parked', true)
                ->orderByDesc('adv_active_selfadvertisements.id')
                ->get();

            $endTime = microtime(true);

            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "BTC Inbox List", remove_null($btcList), "050132", 1.0, "$executionTime Sec", "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050132", 1.0, "271ms", "POST", "", "");
        }
    }

    public function checkFullUpload($applicationId)
    {
        $docCode = $this->_docCode;
        $mWfActiveDocument = new WfActiveDocument();
        $moduleId = $this->_moduleIds;
        $totalRequireDocs = $mWfActiveDocument->totalNoOfDocs($docCode);
        $appDetails = AdvActiveSelfadvertisement::find($applicationId);
        $totalUploadedDocs = $mWfActiveDocument->totalUploadedDocs($applicationId, $appDetails->workflow_id, $moduleId);
        if ($totalRequireDocs == $totalUploadedDocs) {
            $appDetails->doc_upload_status = '1';
            $appDetails->parked = NULL;
            $appDetails->save();
        } else {
            $appDetails->doc_upload_status = '0';
            $appDetails->doc_verify_status = '0';
            $appDetails->save();
        }
    }

    public function reuploadDocument(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|digits_between:1,9223372036854775807',
            'image' => 'required|mimes:png,jpeg,pdf,jpg'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mAdvActiveSelfadvertisement = new AdvActiveSelfadvertisement();
            DB::beginTransaction();
            $appId = $mAdvActiveSelfadvertisement->reuploadDocument($req);
            $this->checkFullUpload($appId);
            DB::commit();
            $endTime = microtime(true);

            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Document Uploaded Successfully", "", "050133", 1.0, "$executionTime Sec", "POST", "", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, "Document Not Uploaded", "", "050133", 1.0, "271ms", "POST", "", "");
        }
    }

    /**
     * | Search application by name or mobile
     */
    public function searchByNameorMobile(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'filterBy' => 'required|in:mobileNo,entityName',
            'parameter' => $req->filterBy == 'mobileNo' ? 'required|digits:10' : 'required|string',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mAdvSelfadvertisement = new AdvSelfadvertisement();
            $listApplications = $mAdvSelfadvertisement->searchByNameorMobile($req);

            if (!$listApplications)
                throw new Exception("Application Not Found !!!");
            $endTime = microtime(true);

            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Application Fetched Successfully", $listApplications, "050134", 1.0, "$executionTime Sec", "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, "Application Not Fetched", $e->getMessage(), "050134", 1.0, "271ms", "POST", "", "");
        }
    }
}
