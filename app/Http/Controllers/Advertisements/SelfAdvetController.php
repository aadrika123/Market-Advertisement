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
use App\Models\Workflows\WfWorkflow;
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
use App\MicroServices\IdGenerator\PrefixIdGenerator;
use App\Models\Param\AdvMarTransaction;
use App\Models\Param\AdvMartransactions;
use App\Models\Payment\TempTransaction;

// use App\Repository\WorkflowMaster\Concrete\WorkflowMap;


/**
 * | Created On-14-12-2022 
 * | Created By-Anshu Kumar
 * | Created for Operations on Self Advertisements
 * | Workflow ID=129
 * | Ulb Workflow ID=245
 * | Changes By Bikash 
 * | Status - Open, By - Bikash 24 Apr 2023 (Total No of Lines - 1733), Total Function - 41 , Total API- 38
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

    protected $_wfMasterId;
    protected $_fileUrl;
    protected $_offlineMode;

    //Constructor
    public function __construct(iSelfAdvetRepo $self_repo)
    {
        // DB::enableQueryLog();
        $this->_modelObj = new AdvActiveSelfadvertisement();
        // $this->_workflowIds = Config::get('workflow-constants.ADVERTISEMENT_WORKFLOWS');
        $this->_moduleIds = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
        $this->_repository = $self_repo;
        $this->_docCode = Config::get('workflow-constants.SELF_ADVERTISMENT_DOC_CODE');
        $this->_paramId = Config::get('workflow-constants.SELF_ID');
        $this->_tempParamId = Config::get('workflow-constants.TEMP_SELF_ID');
        $this->_fileUrl = Config::get('workflow-constants.FILE_URL');
        $this->_baseUrl = Config::get('constants.BASE_URL');
        $this->_offlineMode                 = Config::get("workflow-constants.OFFLINE_PAYMENT_MODE");

        $this->_wfMasterId = Config::get('workflow-constants.ADVERTISEMENT_WF_MASTER_ID');
    }

    /**
     * | Apply Application for Self Advertisements 
     * | @param StoreRequest 
     * | Function - 01
     * | API No. - 01
     * Modified by prity pandey
     */
    public function addNew(StoreRequest $req)
    {
        try {
            // Variable initialization
            $mAdvActiveSelfadvertisement = $this->_modelObj;
            // if ($req->auth['user_type'] == 'JSK') {
            //     $userId = ['userId' => $req->auth['id']];                            // Find Jsk Id
            //     $req->request->add($userId);
            // } else {
            //     $citizenId = ['citizenId' => $req->auth['id']];                       // Find CItizen Id
            //     $req->request->add($citizenId);
            // }
            $user = authUser($req);
            $ulbId = $req->ulbId ?? $user->ulb_id;
            if (!$ulbId)
                throw new Exception("Ulb Not Found");
            if ($user->user_type == 'JSK') {
                $userId = ['userId' => $user->id];
                $req->request->add($userId);
            } else {
                $citizenId = ['citizenId' => $req->auth['id']];
                $req->request->add($citizenId);
            }
            $req->request->add(['ulbId' => $ulbId]);

            $idGeneration = new PrefixIdGenerator($this->_tempParamId, $req->ulbId);
            $applicationNo = $idGeneration->generate();
            $applicationNo = ['application_no' => $applicationNo];
            $req->request->add($applicationNo);

            // $mWfWorkflow=new WfWorkflow();
            $WfMasterId = ['WfMasterId' =>  $this->_wfMasterId];
            $req->request->add($WfMasterId);

            DB::beginTransaction();
            DB::connection('pgsql_masters')->beginTransaction();
            $applicationNo = $mAdvActiveSelfadvertisement->addNew($req);                                       //<--------------- Model function to store 
            DB::commit();
            DB::connection('pgsql_masters')->commit();
            return responseMsgs(true, "Successfully Submitted the application !!", ['status' => true, 'ApplicationNo' => $applicationNo], "050101", "1.0", responseTime(), 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            DB::connection('pgsql_masters')->rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050101", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Get Application Details For Renew
     * | Function - 02
     * | API - 02
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
            $mAdvSelfadvertisement = new AdvSelfadvertisement();
            $details = $mAdvSelfadvertisement->applicationDetailsForRenew($req->applicationId);  // Get Renew Application Details
            if (!$details)
                throw new Exception("Application Not Found !!!");

            return responseMsgs(true, "Application Fetched !!!", remove_null($details), "050102", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050102", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Renewal for Self Advertisements 
     * | @param StoreRequest 
     * | Function - 03
     * | API - 03
     */
    public function renewalSelfAdvt(RenewalRequest $req)
    {
        try {
            // Variable initialization
            $auth = authUserDetails($req);
            $citizenId = ['citizenId' => $auth['id']];
            $req->request->add($citizenId);
            // $startTime = microtime(true);
            $mAdvActiveSelfadvertisement = $this->_modelObj;
            $idGeneration = new PrefixIdGenerator($this->_tempParamId, $req->ulbId);
            $applicationNo = $idGeneration->generate();
            $applicationNo = ['application_no' => $applicationNo];
            $req->request->add($applicationNo);

            $WfMasterId = ['WfMasterId' =>  $this->_wfMasterId];
            $req->request->add($WfMasterId);

            DB::beginTransaction();
            DB::connection('pgsql_masters')->beginTransaction();
            $applicationNo = $mAdvActiveSelfadvertisement->renewalSelfAdvt($req);       //<--------------- Model function to store 
            DB::commit();
            DB::connection('pgsql_masters')->commit();
            return responseMsgs(true, "Successfully Submitted the application !!", ['status' => true, 'ApplicationNo' => $applicationNo['renew_no']], "050103", "1.0", responseTime(), 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            DB::connection('pgsql_masters')->rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050103", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Get Self Advertisement Category List
     * | Function - 04
     * | API - 04
     */
    public function listSelfAdvtCategory()
    {
        $list = AdvSelfadvCategory::select('id', 'type', 'descriptions')
            ->where('status', '1')
            ->orderBy('id', 'ASC')
            ->get();
        return responseMsgs(true, "Advertisement Category", remove_null($list->toArray()), "050104", "1.0", responseTime(), "POST",  "");
    }

    /**
     * | Inbox List
     * | @param Request $req
     * | Function - 05
     * | API - 05
     * | Query Cost - 70 ms
     * | Max Records - 10
     */
    public function listInbox(Request $req)
    {
        try {
            // Variable initialization
            $mAdvActiveSelfadvertisement = $this->_modelObj;
            $ulbId = $req->auth['ulb_id'];
            $workflowRoles = collect($this->getRoleByUserId($req->auth['id']));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });

            $inboxList = $mAdvActiveSelfadvertisement->listInbox($roleIds, $ulbId);             // <------ Get List From Model
            if (trim($req->key))
                $inboxList =  searchFilter($inboxList, $req);
            $list = paginator($inboxList, $req);
            return responseMsgs(true, "Inbox Applications", $list, "050105", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050105", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Outbox List
     * | Function - 06
     * | Query Cost - 65 ms
     * | Max Records - 4
     * | API - 06
     */
    public function listOutbox(Request $req)
    {
        try {
            // Variable initialization
            $mAdvActiveSelfadvertisement = $this->_modelObj;
            $ulbId = $req->auth['ulb_id'];
            // DB::enableQueryLog();
            $workflowRoles = collect($this->getRoleByUserId($req->auth['id']));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });

            $outboxList = $mAdvActiveSelfadvertisement->listOutbox($roleIds, $ulbId);           // <------ Get List From Model 
            if (trim($req->key))
                $outboxList =  searchFilter($outboxList, $req);
            $list = paginator($outboxList, $req);
            // return [(DB::getQueryLog())];
            return responseMsgs(true, "Outbox Lists", $list, "050106", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050106", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Get Application Details By Id
     * | Function - 07
     * | API - 07
     */
    public function getDetailsById(Request $req)
    {
        // return $req;
        try {
            // Variable initialization
            $mAdvActiveSelfadvertisement = new AdvActiveSelfadvertisement();
            $mWorkflowTracks        = new WorkflowTrack();
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

            # Level comment
            $mtableId = $req->applicationId;
            $mRefTable = "adv_active_selfadvertisements.id";                         // Static

            // DB::connection('pgsql_masters')->enableQueryLog();
            $fullDetailsData['levelComment'] = $mWorkflowTracks->getTracksByRefId($mRefTable, $mtableId);
            //   return ([DB::connection('pgsql_masters')->getQueryLog()]);

            #citizen comment
            $refCitizenId = $data['citizen_id'];
            // $fullDetailsData['citizenComment'] = $mWorkflowTracks->getCitizenTracks($mRefTable, $mtableId, $refCitizenId);

            $metaReqs['customFor'] = 'SELF';
            $metaReqs['wfRoleId'] = $data['current_role_id'];
            $metaReqs['workflowId'] = $data['workflow_id'];
            $metaReqs['lastRoleId'] = $data['last_role_id'];

            $req->request->add($metaReqs);
            $forwardBackward = $this->getRoleDetails($req);
            $fullDetailsData['roleDetails'] = collect($forwardBackward)['original']['data'];

            $fullDetailsData = remove_null($fullDetailsData);

            $fullDetailsData['application_no'] = $data['application_no'];
            $fullDetailsData['apply_date'] = Carbon::createFromFormat('Y-m-d',  $data['application_date'])->format('d-m-Y');
            $fullDetailsData['doc_verify_status'] = $data['doc_verify_status'];
            $fullDetailsData['doc_upload_status'] = $data['doc_upload_status'];
            if (isset($data['payment_amount'])) {
                $fullDetailsData['payment_amount'] = $data['payment_amount'];
            }
            $fullDetailsData['timelineData'] = collect($metaReqs);
            $fullDetailsData['workflowId'] = $data['workflow_id'];
            return responseMsgs(true, 'Data Fetched', $fullDetailsData, "050107", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050107", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Get Role Details
     * | Function - 08
     */
    public function getRoleDetails(Request $request)
    {
        // $ulbId = $request->auth['ulb_id'];
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
     * | Function - 09
     * | Query Cost - 45 ms
     * | Max Record - 6 
     * | API - 08
     */
    public function listAppliedApplications(Request $req)
    {
        try {
            // Variable Initialization
            $citizenId = $req->auth['id'];
            $selfAdvets = new AdvActiveSelfadvertisement();

            $applications = $selfAdvets->listAppliedApplications($citizenId);             //<-------  Get Applied Application
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            return responseMsgs(true, "Applied Applications", $data1, "050108", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050108", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Escalate Application
     * | Function - 10
     * | API - 09
     */
    public function escalateApplication(Request $request)
    {
        $request->validate([
            "escalateStatus" => "required|int",
            "applicationId" => "required|int",
        ]);
        try {
            // Variable Initialization
            $userId = $request->auth['id'];
            $applicationId = $request->applicationId;

            $data = AdvActiveSelfadvertisement::find($applicationId);
            $data->is_escalate = $request->escalateStatus;
            $data->escalate_by = $userId;
            $data->save();

            return responseMsgs(true, $request->escalateStatus == 1 ? 'Self Advertisment is Escalated' : "Self Advertisment is removed from Escalated", '', "050109", "1.0", responseTime(), "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050109", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }

    /**
     * | Escalate Application List
     * | Function - 11
     * | API - 10
     * | Query Cost - 38 ms
     * | Max Record - 1
     */
    public function listEscalated(Request $req)
    {
        try {
            // Variable Initialization
            $mWfWardUser = new WfWardUser();
            $userId = $req->auth['id'];
            $ulbId = $req->auth['ulb_id'];

            $occupiedWard = $mWfWardUser->getWardsByUserId($userId);                        // Get All Occupied Ward By user id using trait
            $wardId = $occupiedWard->map(function ($item, $key) {                           // Filter All ward_id in an array using laravel collections
                return $item->ward_id;
            });

            $mWfWorkflow = new WfWorkflow();
            // DB::enableQueryLog();
            $workflowId = $mWfWorkflow->getulbWorkflowId($this->_wfMasterId, $ulbId);      // get workflow Id

            $advData = $this->_repository->specialInbox($workflowId)                      // Repository function to get Advertiesment Details
                ->where('is_escalate', 1)
                ->where('adv_active_selfadvertisements.ulb_id', $ulbId)
                ->whereIn('ward_id', $wardId);

            if (trim($req->key))
                $advData =  searchFilter($advData, $req);
            $list = paginator($advData, $req);
            // return [(DB::getQueryLog())];
            return responseMsgs(true, "Data Fetched",  $list, "050110", "1.0", responseTime(), "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050110", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Forward or Backward Application
     * | Function - 12
     * | API - 11
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
            $adv = AdvActiveSelfadvertisement::find($request->applicationId);
            if ($adv->parked == NULL && $adv->doc_upload_status == '0')
                throw new Exception("Document Rejected Please Send Back to Citizen !!!");
            if ($adv->parked == '1' && $adv->doc_upload_status == '0')
                throw new Exception("Document Are Not Re-upload By Citizen !!!");
            if ($adv->doc_verify_status == '0' && $adv->parked == NULL)
                throw new Exception("Please Verify All Documents To Forward The Application !!!");
            if ($adv->parked == '1')
                throw new Exception("Document Rejected Please Send Back to Citizen !!!");
            $adv->last_role_id = $adv->current_role_id;
            $adv->current_role_id = $request->receiverRoleId;
            $adv->save();

            $metaReqs['moduleId'] = $this->_moduleIds;
            $metaReqs['workflowId'] = $adv->workflow_id;
            $metaReqs['refTableDotId'] = "adv_active_selfadvertisements.id";
            $metaReqs['refTableIdValue'] = $request->applicationId;
            $request->request->add($metaReqs);

            $track = new WorkflowTrack();
            // Advertisment Application Update Current Role Updation
            DB::beginTransaction();
            DB::connection('pgsql_masters')->beginTransaction();
            $track->saveTrack($request);
            DB::commit();
            DB::connection('pgsql_masters')->commit();
            return responseMsgs(true, "Successfully Forwarded The Application!!", "", "050111", "1.0", responseTime(), "POST", $request->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            DB::connection('pgsql_masters')->rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050111", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }

    /**
     * |  Post Independent Comment
     * |  Function - 13
     * |  API - 12
     */
    public function commentApplication(Request $request)
    {
        $request->validate([
            'comment' => 'required',
            'applicationId' => 'required|integer',
            'senderRoleId' => 'nullable|integer'
        ]);
        try {
            // Variable Initialazition
            $userId = $request->auth['id'];
            $userType = $request->auth['user_type'];
            $workflowTrack = new WorkflowTrack();
            $mWfRoleUsermap = new WfRoleusermap();
            $adv = AdvActiveSelfadvertisement::find($request->applicationId);                // Advertisment Details
            $mModuleId = $this->_moduleIds;
            $metaReqs = array();
            $metaReqs = [
                'workflowId' => $adv->workflow_id,
                'moduleId' => $mModuleId,
                'refTableDotId' => "adv_active_selfadvertisements.id",
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
            DB::beginTransaction();
            DB::connection('pgsql_masters')->beginTransaction();
            // Save On Workflow Track For Level Independent
            $workflowTrack->saveTrack($request);
            DB::commit();
            DB::connection('pgsql_masters')->commit();
            return responseMsgs(true, "You Have Commented Successfully!!", ['Comment' => $request->comment], "050112", "1.0", responseTime(), "POST", "");
        } catch (Exception $e) {
            DB::rollBack();
            DB::connection('pgsql_masters')->rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050112", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }


    /**
     * | Get License By User ID
     * |  Function - 14
     * | API - 13
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
            $tradeLicence = new TradeLicence();
            $licenseList = $tradeLicence->getLicenceByUserId($req->user_id);
            return responseMsgs(true, "Licenses", remove_null($licenseList->toArray()), "050113", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050113", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Get License By Holding No
     * |  Function - 15
     * | API - 14
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
            $tradeLicense = new TradeLicence();
            $licenseList = $tradeLicense->getLicenceByHoldingNo($req->holding_no);

            return responseMsgs(true, "Licenses", remove_null($licenseList->toArray()), "050114", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050114", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Get Uploaded Document by application ID
     * |  Function - 16
     * | API - 15
     */
    public function viewAdvertDocument(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors(), "", "050115", "1.0", "", "POST", $req->deviceId ?? "");
        }
        $appUrl = $this->_fileUrl;
        if ($req->type == 'Active')
            $workflowId = AdvActiveSelfadvertisement::find($req->applicationId)->workflow_id;
        elseif ($req->type == 'Approve')
            $workflowId = AdvSelfadvertisement::find($req->applicationId)->workflow_id;
        elseif ($req->type == 'Reject')
            $workflowId = AdvRejectedSelfadvertisement::find($req->applicationId)->workflow_id;
        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        $data = $mWfActiveDocument->uploadDocumentsViewById($req->applicationId, $workflowId);
        // $data1['data'] = collect($data)->map(function ($value) use ($appUrl) {
        //     $value->doc_path = $appUrl . $value->doc_path;
        //     return $value;
        // });
        $data = (new DocumentUpload())->getDocUrl($data);
        return responseMsgs(true, "Data Fetched", remove_null($data), "050118", "1.0", responseTime(), "POST", "");
    }

    /**
     * | Get Uploaded Active Document by application ID
     * |  Function - 17
     * | API - 16
     */
    public function viewActiveDocument(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|digits_between:1,9223372036854775807'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        $details = AdvActiveSelfadvertisement::find($req->applicationId);
        $workflowId = $details->workflow_id;
        $appUrl = $this->_fileUrl;
        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        $status = collect();
        $data = $mWfActiveDocument->uploadedActiveDocumentsViewById($req->applicationId, $workflowId);
        // $data1['data'] = collect($data)->map(function ($value) use ($appUrl, $status) {
        //     $value->doc_path = $appUrl . $value->doc_path;
        //     // $status->push($value->verify_status);
        //     return $value;
        // });
        // if($status->contains('0')){
        //     $data1['doc_upload_status']=0;
        // }else{
        //     $data1['doc_upload_status']=1;
        // }
        //$data1['doc_upload_status'] = $details->doc_upload_status;
        $data = (new DocumentUpload())->getDocUrl($data);
        return responseMsgs(true, "Uploaded Documents", $data, "010102", "1.0", "", "POST", $req->deviceId ?? "");
        //return $data;
    }


    /**
     * | Get Details By License NO
     * |  Function - 18
     * |  Function - 17
     */
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
     * | Workflow View Uploaded Document by application ID
     * |  Function - 19
     * |  API - 18
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
        if (isset($req->type) && $req->type == 'Approve') {
            $details = AdvSelfadvertisement::find($req->applicationId);
        } else {
            $details = AdvActiveSelfadvertisement::find($req->applicationId);
        }
        if (!$details)
            throw new Exception("Application Not Found !!!!");
        $workflowId = $details->workflow_id;
        $mWfActiveDocument = new WfActiveDocument();
        $appUrl = $this->_fileUrl;
        $data = array();
        $data = $mWfActiveDocument->uploadDocumentsOnWorkflowViewById($req->applicationId, $workflowId);                    // Get All Documents Against Application
        $roleId = WfRoleusermap::select('wf_role_id')->where('user_id', $req->auth['id'])->first()->wf_role_id;             // Get Current Role Id 
        $wfLevel = Config::get('constants.SELF-LABEL');
        if ($roleId == $wfLevel['DA']) {
            $data = $data->get();                                                                                           // If DA Then show all docs
        } else {
            $data = $data->where('current_status', '1')->get();                                                              // Other Than DA show only Active docs
        }
        // $data1 = collect($data)->map(function ($value) use ($appUrl) {
        //     $value->doc_path = $appUrl . $value->doc_path;
        //     return $value;
        // });
        $data = (new DocumentUpload())->getDocUrl($data);
        return responseMsgs(true, "Data Fetched", remove_null($data), "050118", "1.0", responseTime(), "POST", "");
    }


    /**
     * | Final Approval and Rejection of the Application
     * |  Function - 20
     * |  API - 19
     * | Status- Closed
     */
    public function approvalOrRejection(Request $req)
    {
        $req->validate([
            'roleId' => 'required',
            'applicationId' => 'required|integer',
            'status' => 'required|integer',
            'remarks' => 'nullable|string'

        ]);
        try {
            // Variable initialization
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
                $data['demand_amount'] = ['demand_amount' => 0];
                if ($mAdvActiveSelfadvertisement->advt_category > 10) {
                    $payment_amount = $mCalculateRate->getAdvertisementPayment($mAdvActiveSelfadvertisement->display_area, $mAdvActiveSelfadvertisement->ulb_id);   // Calculate Price
                    $data['payment_status'] = ['payment_status' => 0];
                    $data['payment_amount'] = ['payment_amount' => round($payment_amount)];
                    $data['demand_amount'] = ['demand_amount' => $payment_amount];
                }
                $req->request->add($data['payment_amount']);
                $req->request->add($data['payment_status']);
                $req->request->add($data['demand_amount']);

                // $generatedId = $mCalculateRate->generateId($req->bearerToken(), $this->_paramId, $mAdvActiveSelfadvertisement->ulb_id); 
                $idGeneration = new PrefixIdGenerator($this->_paramId, $mAdvActiveSelfadvertisement->ulb_id);                      // Generate License No
                $generatedId = $idGeneration->generate();
                if ($mAdvActiveSelfadvertisement->renew_no == NULL) {
                    // Selfadvertisement Application replication
                    $approvedSelfadvertisement = $mAdvActiveSelfadvertisement->replicate();
                    $approvedSelfadvertisement->setTable('adv_selfadvertisements');
                    $temp_id = $approvedSelfadvertisement->id = $mAdvActiveSelfadvertisement->id;
                    $approvedSelfadvertisement->payment_amount = round($req->payment_amount);
                    $approvedSelfadvertisement->demand_amount = $req->payment_amount;
                    $approvedSelfadvertisement->payment_status = $req->payment_status;
                    $approvedSelfadvertisement->demand_amount = $req->demand_amount;
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
                    $approvedSelfadvertisement->payment_amount = round($req->payment_amount);
                    $approvedSelfadvertisement->demand_amount = $req->payment_amount;
                    $approvedSelfadvertisement->payment_status = $req->payment_status;
                    $approvedSelfadvertisement->demand_amount = $req->demand_amount;
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
                $rejectedSelfadvertisement->remarks = $req->comment;
                $rejectedSelfadvertisement->save();
                $mAdvActiveSelfadvertisement->delete();
                $msg = "Application Successfully Rejected !!";
            }
            $metaReqs['moduleId'] = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
            $metaReqs['workflowId'] = $mAdvActiveSelfadvertisement->workflow_id;
            $metaReqs['refTableDotId'] = "adv_active_hoardings.id";
            $metaReqs['refTableIdValue'] = $req->applicationId;
            $metaReqs['verificationStatus'] = $req->status;

            $track = new WorkflowTrack();
            $req->request->add($metaReqs);
            $track->saveTrack($req);
            DB::commit();
            return responseMsgs(true, $msg, "", '050119', 01, responseTime(), 'POST', $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false,  $e->getMessage(), "", '050119', 01, "", 'POST', $req->deviceId);
        }
    }


    /**
     * | Approve Application List for Citzen
     * | @param Request $req
     * |  Function - 21
     * |  API - 20
     * |  Query Cost - 27.10 ms
     * |  Max Record - 11
     */
    public function listApproved(Request $req)
    {
        try {
            // Variable initialization
            $citizenId = $req->auth['id'];
            $userType = $req->auth['user_type'];
            $mAdvSelfadvertisements = new AdvSelfadvertisement();
            $applications = $mAdvSelfadvertisements->listApproved($citizenId, $userType);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            return responseMsgs(true, "Approved Application List", $data1, "050120", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050120", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Reject Application List for Citizen
     * | @param Request $req
     * |  Function - 22
     * |  API - 21
     * |  Query Cost - 23 ms
     * |  Max Records - 4
     */
    public function listRejected(Request $req)
    {
        try {
            // Variable initialization
            // $startTime = microtime(true);
            // $citizenId = authUser()->id;
            $citizenId = $req->auth['id'];
            $mAdvRejectedSelfadvertisement = new AdvRejectedSelfadvertisement();
            $applications = $mAdvRejectedSelfadvertisement->listRejected($citizenId);

            $totalApplication = $applications->count();
            if ($totalApplication == 0) {
                $applications = null;
            }
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            return responseMsgs(true, "Rejected Application List", $data1, "050121", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050121", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Get Applied Applications by Logged In JSK
     * |  Function - 23
     * |  API - 22
     */
    public function getJSKApplications(Request $req)
    {
        try {
            // Variable initialization
            // $userId = authUser()->id;
            $userId = $req->auth['id'];
            $mAdvActiveSelfadvertisement = new AdvActiveSelfadvertisement();

            $applications = $mAdvActiveSelfadvertisement->getJSKApplications($userId);
            $totalApplication = $applications->count();

            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            return responseMsgs(true, "Applied Applications", $data1, "050122", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050122", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Approve Application List for JSK
     * | @param Request $req
     * |  Function - 24
     * |  API - 23
     * //writen by prity pandey
     */

    public function listJskApprovedApplication(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'filterBy'  => 'nullable|in:mobileNo,applicantName,applicationNo',
                'parameter' => 'nullable',
            ]
        );

        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $key = $request->filterBy;
            $parameter = $request->parameter;
            $pages = $request->perPage ?? 10;
            $msg = "Pending application list";
            $userId = $request->auth['id'];

            $mAdvSelfadvertisements = new AdvSelfadvertisement();
            $applications = $mAdvSelfadvertisements->listJskApprovedApplication();
            if ($key && $parameter) {
                $msg = "Self Advertisement application details according to $key";
                switch ($key) {
                    case 'mobileNo':
                        $applications = $applications->where('adv_selfadvertisements.mobile_no', 'LIKE', "%$parameter%");
                        break;
                    case 'applicantName':
                        $applications = $applications->where('adv_selfadvertisements.applicant', 'LIKE', "%$parameter%");
                        break;
                    case 'applicationNo':
                        $applications = $applications->where('adv_selfadvertisements.application_no', 'LIKE', "%$parameter%");
                        break;
                    default:
                        throw new Exception("Invalid Data");
                }
            }

            $paginatedData = $applications->paginate($pages);

            // Customize the pagination response
            $customData = [
                'current_page' => $paginatedData->currentPage(),
                'data' => $paginatedData->items(),
                'last_page' => $paginatedData->lastPage(),
                'per_page' => $paginatedData->perPage(),
                'total' => $paginatedData->total()
            ];

            if ($paginatedData->isEmpty()) {
                $msg = "No data found";
            }

            return responseMsgs(true, $msg, $customData, "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }



    /**
     * | Reject Application List for JSK
     * | @param Request $req
     * |  Function - 25
     * |  API - 24
     */
    public function listJskRejectedApplication(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'filterBy'  => 'nullable|in:mobileNo,applicantName,applicationNo',
                'parameter' => 'nullable',
            ]
        );

        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $key = $request->filterBy;
            $parameter = $request->parameter;
            $pages = $request->perPage ?? 10;
            $msg = "Rejected application list";
            $mAdvRejectedSelfadvertisement = new AdvRejectedSelfadvertisement();
            $applications = $mAdvRejectedSelfadvertisement->listJskRejectedApplication();

            if ($key && $parameter) {
                $msg = "Self Advertisement application details according to $key";
                switch ($key) {
                    case 'mobileNo':
                        $applications = $applications->where('adv_rejected_selfadvertisements.mobile_no', 'LIKE', "%$parameter%");
                        break;
                    case 'applicantName':
                        $applications = $applications->where('adv_rejected_selfadvertisements.applicant', 'LIKE', "%$parameter%");
                        break;
                    case 'applicationNo':
                        $applications = $applications->where('adv_rejected_selfadvertisements.application_no', 'LIKE', "%$parameter%");
                        break;
                    default:
                        throw new Exception("Invalid Data");
                }
            }

            $paginatedData = $applications->paginate($pages);

            // Customize the pagination response
            $customData = [
                'current_page' => $paginatedData->currentPage(),
                'data' => $paginatedData->items(),
                'last_page' => $paginatedData->lastPage(),
                'per_page' => $paginatedData->perPage(),
                'total' => $paginatedData->total()
            ];

            if ($paginatedData->isEmpty()) {
                $msg = "No data found";
            }

            return responseMsgs(true, $msg, $customData, "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }



    public function listJskAppliedApplication(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'filterBy'  => 'nullable|in:mobileNo,applicantName,applicationNo',
                'parameter' => 'nullable',
                'dateFrom'  => 'nullable|date',
                'dateUpto'  => 'nullable|date'
            ]
        );

        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $key = $request->filterBy;
            $parameter = $request->parameter;
            $pages = $request->perPage ?? 10;
            $msg = "Applied application list";
            $mAdvRejectedSelfadvertisement = new AdvActiveSelfadvertisement();
            $applications = $mAdvRejectedSelfadvertisement->listAppliedApplicationsJsk();

            if ($key && $parameter) {
                $msg = "Self Advertisement application details according to $key";
                switch ($key) {
                    case 'mobileNo':
                        $applications = $applications->where('adv_active_selfadvertisements.mobile_no', 'LIKE', "%$parameter%");
                        break;
                    case 'applicantName':
                        $applications = $applications->where('adv_active_selfadvertisements.applicant', 'LIKE', "%$parameter%");
                        break;
                    case 'applicationNo':
                        $applications = $applications->where('adv_active_selfadvertisements.application_no', 'LIKE', "%$parameter%");
                        break;
                    default:
                        throw new Exception("Invalid Data");
                }
            } elseif ($request->dateFrom && $request->dateUpto != null) {
                $applications = $applications->whereBetween('adv_active_selfadvertisements.application_date', [$request->dateFrom, $request->dateUpto]);
            }

            $paginatedData = $applications->paginate($pages);

            // Customize the pagination response
            $customData = [
                'current_page' => $paginatedData->currentPage(),
                'data' => $paginatedData->items(),
                'last_page' => $paginatedData->lastPage(),
                'per_page' => $paginatedData->perPage(),
                'total' => $paginatedData->total()
            ];

            if ($paginatedData->isEmpty()) {
                $msg = "No data found";
            }

            return responseMsgs(true, $msg, $customData, "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }

    /**
     * | Generate Payment Order ID
     * | @param Request $req
     * |  Function - 26
     * |  API - 25
     */

    public function generatePaymentOrderId(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            // return responseMsgs(false, $validator->errors(), "", "050115", "1.0", "", "POST", $req->deviceId ?? "");
            return $validator->errors();
        }
        try {
            // Variable initialization
            $mAdvSelfadvertisement = AdvSelfadvertisement::find($req->id);
            $reqData = [
                "id" => $mAdvSelfadvertisement->id,
                'amount' => $mAdvSelfadvertisement->payment_amount,
                'workflowId' => $mAdvSelfadvertisement->workflow_id,
                'ulbId' => $mAdvSelfadvertisement->ulb_id,
                'departmentId' => $this->_moduleIds,
                'auth' => $req->auth,
            ];
            $paymentUrl = Config::get('constants.PAYMENT_URL');
            $refResponse = Http::withHeaders([
                "api-key" => "eff41ef6-d430-4887-aa55-9fcf46c72c99"
            ])
                ->withToken($req->bearerToken())
                ->post($paymentUrl . 'api/payment/generate-orderid', $reqData);

            $data = json_decode($refResponse);
            $data = $data->data;
            if (!$data)
                throw new Exception("Payment Order Id Not Generate");

            $data->name = $mAdvSelfadvertisement->applicant;
            $data->email = $mAdvSelfadvertisement->email;
            $data->contact = $mAdvSelfadvertisement->mobile_no;
            $data->type = "Self Advertisement";

            return responseMsgs(true, "Payment OrderId Generated Successfully !!!", $data, "050125", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050125", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * Summary of application Details For Payment
     * @param Request $req
     * |  Function - 27
     * |  API - 26
     * @return void
     */
    public function applicationDetailsForPayment(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            $mAdvSelfadvertisement = new AdvSelfadvertisement();

            if ($req->applicationId) {
                $data = $mAdvSelfadvertisement->applicationDetailsForPayment($req->applicationId);
            }
            if (!$data)
                throw new Exception("Application Not Found");

            $data['type'] = "Self Advertisement";

            return responseMsgs(true, 'Data Fetched',  $data, "050126", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050126", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * | Payment via cash 
     * |  Function - 28
     * |  API - 27
     */
    // public function paymentByCash(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'applicationId' => 'required|string',
    //         'status' => 'required|integer'
    //     ]);
    //     if ($validator->fails()) {
    //         return ['status' => false, 'message' => $validator->errors()];
    //     }
    //     try {
    //         // Variable initialization
    //         $userId = $req->auth['id'];
    //         $mAdvSelfadvertisement = new AdvSelfadvertisement();
    //         $mAdvMarTransaction = new AdvMarTransaction();
    //         DB::beginTransaction();
    //         $data = $mAdvSelfadvertisement->paymentByCash($req);
    //         $appDetails = AdvSelfadvertisement::find($req->applicationId);
    //         # Water Transactions
    //         $req->merge(['userId' => $userId]);
    //         $mAdvMarTransaction->addTransaction($appDetails, $this->_moduleIds, "Advertisement", "Cash");
    //         DB::commit();

    //         if ($req->status == '1' && $data['status'] == 1) {
    //             return responseMsgs(true, "Payment Successfully !!", ['status' => true, 'transactionNo' => $data['payment_id'], 'workflowId' => $appDetails->workflow_id], "050127", "1.0", responseTime(), 'POST', $req->deviceId ?? "");
    //         } else {
    //             return responseMsgs(false, "Payment Rejected !!", '', "050127", "1.0", responseTime(), 'POST', $req->deviceId ?? "");
    //         }
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return responseMsgs(false, $e->getMessage(), "", "050127", "1.0", "", "POST", $req->deviceId ?? "");
    //     }
    // }

    /**
     * | Entry Cheque or DD
     * |  Function - 29
     * |  API - 28
     */
    public function entryChequeDd(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|integer',               //  id of Application
            'bankName' => 'required|string',
            'branchName' => 'required|string',
            'chequeNo' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            $wfId = AdvSelfadvertisement::find($req->applicationId)->workflow_id;
            $mAdvCheckDtl = new AdvChequeDtl();
            $workflowId = ['workflowId' => $wfId];

            $req->request->add($workflowId);
            $transNo = $mAdvCheckDtl->entryChequeDd($req);                              // Store Cheque Details In Model

            return responseMsgs(true, "Check Entry Successfully !!", ['status' => true, 'TransactionNo' => $transNo], "050128", "1.0", responseTime(), 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050128", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    /**
     * | Clear or Bounce Cheque or DD
     * |  Function - 30
     * |  API - 29
     */
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
            $mAdvCheckDtl = new AdvChequeDtl();
            $mAdvMarTransaction = new AdvMarTransaction();
            DB::beginTransaction();
            $data = $mAdvCheckDtl->clearOrBounceCheque($req);
            $appDetails = AdvSelfadvertisement::find($req->applicationId);
            $mAdvMarTransaction->addTransaction($appDetails, $this->_moduleIds, "Advertisement", "Cheque/DD");
            DB::commit();

            if ($req->status == '1' && $data['status'] == 1) {
                return responseMsgs(true, "Payment Successfully !!", ['status' => true, 'transactionNo' => $data['payment_id'], 'workflowId' => $appDetails->workflow_id], "050129", "1.0", responseTime(), 'POST', $req->deviceId ?? "");
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
     * |  Function - 31
     * |  API - 30
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
            $mWfDocument = new WfActiveDocument();
            $mAdvActiveSelfadvertisement = new AdvActiveSelfadvertisement();
            $mWfRoleusermap = new WfRoleusermap();
            $wfDocId = $req->id;
            $userId = $req->auth['id'];
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
            DB::connection('pgsql_masters')->beginTransaction();
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

            DB::commit();
            DB::connection('pgsql_masters')->commit();
            return responseMsgs(true, $req->docStatus . " Successfully", responseTime(), "050130", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            DB::connection('pgsql_masters')->rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050130", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Check if the Document is Fully Verified or Not (4.1)
     * |  Function - 32
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
        $citizenId = $mAdvActiveSelfadvertisement->citizen_id;
        $totalNoOfDoc = $mWfActiveDocument->totalNoOfDocs($this->_docCode, $citizenId);
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
     * | send back to citizen
     * |  Function - 33
     * |  API - 31
     */
    public function backToCitizen(Request $req)
    {
        $req->validate([
            'applicationId' => "required"
        ]);
        try {
            // Variable initialization
            $redis = Redis::connection();
            $mAdvActiveSelfadvertisement = AdvActiveSelfadvertisement::find($req->applicationId);
            if ($mAdvActiveSelfadvertisement->doc_verify_status == 1)
                throw new Exception("All Documents Are Approved, So Application is Not BTC !!!");
            if ($mAdvActiveSelfadvertisement->doc_upload_status == 1)
                throw new Exception("No Any Document Rejected, So Application is Not BTC !!!");
            $workflowId = $mAdvActiveSelfadvertisement->workflow_id;
            $backId = json_decode(Redis::get('workflow_initiator_' . $workflowId));
            if (!$backId) {
                $backId = WfWorkflowrolemap::where('workflow_id', $workflowId)
                    ->where('is_initiator', true)
                    ->first();
                $redis->set('workflow_initiator_' . $workflowId, json_encode($backId));
            }

            $mAdvActiveSelfadvertisement->current_role_id = $backId->wf_role_id;
            $mAdvActiveSelfadvertisement->btc_date =  Carbon::now()->format('Y-m-d');
            $mAdvActiveSelfadvertisement->remarks = $req->comment;
            $mAdvActiveSelfadvertisement->parked = 1;
            $mAdvActiveSelfadvertisement->save();

            $metaReqs['moduleId'] = $this->_moduleIds;
            $metaReqs['workflowId'] = $mAdvActiveSelfadvertisement->workflow_id;
            $metaReqs['refTableDotId'] = "adv_active_selfadvertisements.id";
            $metaReqs['refTableIdValue'] = $req->applicationId;
            $metaReqs['verificationStatus'] = $req->verificationStatus;
            $metaReqs['senderRoleId'] = $req->currentRoleId;
            $req->request->add($metaReqs);

            $req->request->add($metaReqs);
            $track = new WorkflowTrack();
            $track->saveTrack($req);

            return responseMsgs(true, "Successfully Done", "", "", '050131', '01', responseTime(), 'POST', '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050131", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Back To Citizen Inbox
     * | Function - 34
     * | API - 32
     * | Query Cost - 51 ms
     * | Max Records - 2
     */
    public function listBtcInbox(Request $req)
    {
        try {
            // Variable initialization
            // $auth = auth()->user();
            $userId = $req->auth['id'];
            $ulbId = $req->auth['ulb_id'];
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
                // ->whereIn('adv_active_selfadvertisements.current_role_id', $roleId)
                // ->whereIn('a.ward_mstr_id', $occupiedWards)
                ->where('parked', '1')
                ->orderByDesc('adv_active_selfadvertisements.id');
            // ->get();
            if (trim($req->key))
                $btcList =  searchFilter($btcList, $req);
            $list = paginator($btcList, $req);

            return responseMsgs(true, "BTC Inbox List", $list, "050132", 1.0, responseTime(), "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050132", 1.0, "", "POST", "", "");
        }
    }

    /**
     * | Cheque full upload document or not
     * |  Function - 35
     */
    public function checkFullUpload($applicationId)
    {
        $docCode = $this->_docCode;
        $mWfActiveDocument = new WfActiveDocument();
        $mAdvActiveSelfadvertisement = new AdvActiveSelfadvertisement();
        $mAdvActiveSelfadvertisement = $mAdvActiveSelfadvertisement->getSelfAdvertNo($applicationId);
        $moduleId = $this->_moduleIds;
        $citizenId = $mAdvActiveSelfadvertisement->citizen_id;
        $totalRequireDocs = $mWfActiveDocument->totalNoOfDocs($docCode, $citizenId);
        $appDetails = AdvActiveSelfadvertisement::find($applicationId);
        $totalUploadedDocs = $mWfActiveDocument->totalUploadedDocs($applicationId, $appDetails->workflow_id, $moduleId);
        if ($totalRequireDocs == $totalUploadedDocs) {
            $appDetails->doc_upload_status = '1';
            $appDetails->doc_verify_status = '0';
            $appDetails->parked = NULL;
            $appDetails->save();
        } else {
            $appDetails->doc_upload_status = '0';
            $appDetails->doc_verify_status = '0';
            $appDetails->save();
        }
    }

    /**
     * | Reuploaded rejected document
     * | Function - 36
     * | API - 33
     */
    public function reuploadDocument(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|digits_between:1,9223372036854775807',
            'image' => 'required|mimes:png,jpeg,pdf,jpg,2048 kB'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            $mMarActiveSelfAdvt = new AdvActiveSelfadvertisement();
            $Image                   = $req->image;
            $docId                   = $req->id;
            DB::beginTransaction();
            DB::connection('pgsql_masters')->beginTransaction();
            $appId = $mMarActiveSelfAdvt->reuploadDocument($req, $Image, $docId);
            $this->checkFullUpload($appId, $mMarActiveSelfAdvt);
            DB::commit();
            DB::connection('pgsql_masters')->commit();
            return responseMsgs(true, "Document Uploaded Successfully", "", "050721", 1.0, responseTime(), "POST", "", "");
        } catch (Exception $e) {
            DB::rollBack();
            DB::connection('pgsql_masters')->rollBack();
            return responseMsgs(false, "Document Not Uploaded", "", "050721", 1.0, "271ms", "POST", "", "");
        }
    }
    /**
     * |Arshad 
     */

    public function reuploadDocumentAdv($req)
    {
        try {
            #initiatialise variable 
            $Image                   = $req->image;
            $docId                   = $req->id;
            $data = [];
            $docUpload = new DocumentUpload;
            $relativePath = Config::get('constants.SELF_ADVET_RELATIVE_PATH');
            $mWfActiveDocument = new WfActiveDocument();
            $user = collect(authUser($req));


            $file = $Image;
            $req->merge([
                'document' => $file
            ]);
            #_Doc Upload through a DMS
            $imageName = $docUpload->upload($req);
            $metaReqs = [
                'moduleId' => Config::get('workflow-constants.ADVERTISMENT_MODULE_ID'),
                'unique_id' => $imageName['data']['uniqueId'] ?? null,
                'reference_no' => $imageName['data']['ReferenceNo'] ?? null,
                'relative_path' => $relativePath
            ];

            // Save document metadata in wfActiveDocuments
            $activeId = $mWfActiveDocument->updateDocuments(new Request($metaReqs), $user, $docId);
            return $activeId;

            // return $data;
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }

    /**
     * | Search application by name or mobile
     * | Function - 37
     * | API - 34
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
            $mAdvSelfadvertisement = new AdvSelfadvertisement();
            $listApplications = $mAdvSelfadvertisement->searchByNameorMobile($req);

            if (!$listApplications)
                throw new Exception("Application Not Found !!!");

            return responseMsgs(true, "Application Fetched Successfully", $listApplications, "050134", 1.0, responseTime(), "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, "Application Not Fetched", $e->getMessage(), "050134", 1.0, "271ms", "POST", "", "");
        }
    }

    /**
     * | Get Application Between Two Dates
     * | Function - 38
     * | API - 35
     */
    public function getApplicationBetweenDate(Request $req)
    {
        if ($req->auth['ulb_id'] < 1)
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050135", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = $req->auth['ulb_id'];
        $validator = Validator::make($req->all(), [
            'applicationType' => 'required|in:New Apply,Renew',
            'applicationStatus' => 'required|in:All,Approve,Reject',
            'entityWard' => 'nullable|integer',
            'dateFrom' => 'required|date_format:Y-m-d',
            'dateUpto' => 'required|date_format:Y-m-d',
            'perPage' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            #=============================================================
            $mAdvSelfAdvertisement = new AdvSelfAdvertisement();
            $approveList = $mAdvSelfAdvertisement->approveListForReport();

            $approveList = $approveList->where('application_type', $req->applicationType)->where('ulb_id', $ulbId)
                ->whereBetween('application_date', [$req->dateFrom, $req->dateUpto]);
            if ($req->entityWard != null) {
                $approveList = $approveList->where('entity_ward_id', $req->entityWard);
            }


            $mAdvActiveSelfadvertisement = new AdvActiveSelfadvertisement();
            $pendingList = $mAdvActiveSelfadvertisement->pendingListForReport();

            $pendingList = $pendingList->where('application_type', $req->applicationType)->where('ulb_id', $ulbId)->whereBetween('application_date', [$req->dateFrom, $req->dateUpto]);
            if ($req->entityWard != null) {
                $pendingList = $pendingList->where('entity_ward_id', $req->entityWard);
            }


            $mAdvRejectedSelfadvertisement = new AdvRejectedSelfadvertisement();
            $rejectList = $mAdvRejectedSelfadvertisement->rejectListForReport();

            $rejectList = $rejectList->where('application_type', $req->applicationType)->where('ulb_id', $ulbId)->whereBetween('application_date', [$req->dateFrom, $req->dateUpto]);
            if ($req->entityWard != null) {
                $rejectList = $rejectList->where('entity_ward_id', $req->entityWard);
            }

            $data = collect(array());
            if ($req->applicationStatus == 'All') {
                $data = $approveList->union($pendingList)->union($rejectList);
            }
            if ($req->applicationStatus == 'Reject') {
                $data = $rejectList;
            }
            if ($req->applicationStatus == 'Approve') {
                $data = $approveList;
            }
            $data = $data->paginate($req->perPage);
            #=============================================================

            return responseMsgs(true, "Application Fetched Successfully", $data, "050135", 1.0, responseTime(), "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, "Application Not Fetched", $e->getMessage(), "050135", 1.0, "271ms", "POST", "", "");
        }
    }


    /**
     * | Get Application Financial Year Wise
     * | Function - 39
     * | API - 36
     */
    public function getApplicationFinancialYearWise(Request $req)
    {
        if ($req->auth['ulb_id'] < 1)
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050136", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = $req->auth['ulb_id'];

        $validator = Validator::make($req->all(), [
            'applicationType' => 'required|in:New Apply,Renew',
            'entityWard' => 'nullable|integer',
            'perPage' => 'required|integer',
            'financialYear' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization

            $mAdvSelfAdvertisement = new AdvSelfAdvertisement();
            $approveList = $mAdvSelfAdvertisement->approveListForReport();

            $approveList = $approveList->where('application_type', $req->applicationType)->where('ulb_id', $ulbId)->where('license_year', $req->financialYear);
            if ($req->entityWard != null) {
                $approveList  = $approveList->where('entity_ward_id', $req->entityWard);
            }

            $mAdvActiveSelfadvertisement = new AdvActiveSelfadvertisement();
            $pendingList = $mAdvActiveSelfadvertisement->pendingListForReport();

            $pendingList = $pendingList->where('application_type', $req->applicationType)->where('ulb_id', $ulbId)
                ->where('entity_ward_id', $req->entityWard)->where('license_year', $req->financialYear);

            $mAdvRejectedSelfadvertisement = new AdvRejectedSelfadvertisement();
            $rejectList = $mAdvRejectedSelfadvertisement->rejectListForReport();

            $rejectList = $rejectList->where('application_type', $req->applicationType)->where('ulb_id', $ulbId)
                ->where('entity_ward_id', $req->entityWard)->where('license_year', $req->financialYear);

            $data = collect(array());
            $data = $approveList->union($pendingList)->union($rejectList);
            $data = $data->paginate($req->perPage);

            return responseMsgs(true, "Application Fetched Successfully", $data, "050136", 1.0, responseTime(), "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, "Application Not Fetched", $e->getMessage(), "050136", 1.0, "271ms", "POST", "", "");
        }
    }

    /**
     * | Get Application Display Wise
     * | Function - 40
     * | API - 37
     */
    public function getApplicationDisplayWise(Request $req)
    {
        if ($req->auth['ulb_id'] < 1)
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050137", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = $req->auth['ulb_id'];

        $validator = Validator::make($req->all(), [
            'applicationType' => 'nullable',
            'applicationStatus' => 'nullable',
            'entityWard' => 'nullable',
            'dateFrom' => 'required|date_format:Y-m-d',
            'dateUpto' => 'required|date_format:Y-m-d',
            'displayType' => 'nullable|integer',
            'perPage' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization

            $mAdvSelfAdvertisement = new AdvSelfAdvertisement();
            $approveList = $mAdvSelfAdvertisement->approveListForReport();

            $approveList = $approveList->where('ulb_id', $ulbId)
                ->whereBetween('application_date', [$req->dateFrom, $req->dateUpto]);
            if ($req->entityWard != null) {
                $approveList->where('entity_ward_id', $req->entityWard);
            }
            if ($req->applicationType != null) {
                $approveList->where('application_type', $req->applicationType);
            }
            if ($req->displayType != null) {
                $approveList->where('display_type', $req->displayType);
            }

            $mAdvActiveSelfadvertisement = new AdvActiveSelfadvertisement();
            $pendingList = $mAdvActiveSelfadvertisement->pendingListForReport();

            $pendingList = $pendingList->where('ulb_id', $ulbId)
                ->whereBetween('application_date', [$req->dateFrom, $req->dateUpto]);
            if ($req->entityWard != null) {
                $pendingList->where('entity_ward_id', $req->entityWard);
            }
            if ($req->applicationType != null) {
                $pendingList->where('application_type', $req->applicationType);
            }
            if ($req->displayType != null) {
                $pendingList->where('display_type', $req->displayType);
            }


            $mAdvRejectedSelfadvertisement = new AdvRejectedSelfadvertisement();
            $rejectList = $mAdvRejectedSelfadvertisement->rejectListForReport();

            $rejectList = $rejectList->where('ulb_id', $ulbId)
                ->whereBetween('application_date', [$req->dateFrom, $req->dateUpto]);
            if ($req->entityWard != null) {
                $rejectList->where('entity_ward_id', $req->entityWard);
            }
            if ($req->applicationType != null) {
                $rejectList->where('application_type', $req->applicationType);
            }
            if ($req->displayType != null) {
                $rejectList->where('display_type', $req->displayType);
            }


            $data = collect(array());
            if ($req->applicationStatus == 'All') {
                $data = $approveList->union($pendingList)->union($rejectList);
            }
            if ($req->applicationStatus == 'Reject') {
                $data = $rejectList;
            }
            if ($req->applicationStatus == 'Approve') {
                $data = $approveList;
            }
            $data = $data->paginate($req->perPage);

            return responseMsgs(true, "Application Fetched Successfully", $data, "050137", 1.0, responseTime(), "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, "Application Not Fetched", $e->getMessage(), "050137", 1.0, "271ms", "POST", "", "");
        }
    }

    /**
     * | COllection From New or Renew Application
     * | Function - 41
     * | API - 38
     */
    // public function paymentCollection(Request $req)
    // {
    //     if ($req->auth['ulb_id'] < 1)
    //         return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050138", 1.0, "271ms", "POST", "", "");
    //     else
    //         $ulbId = $req->auth['ulb_id'];

    //     $validator = Validator::make($req->all(), [
    //         'applicationType' => 'required|in:New Apply,Renew',
    //         'entityWard' => 'required|integer',
    //         'dateFrom' => 'required|date_format:Y-m-d',
    //         'dateUpto' => 'required|date_format:Y-m-d',
    //         'perPage' => 'required|integer',
    //         'payMode' => 'required|in:All,Online,Cash,Cheque/DD',
    //     ]);
    //     if ($validator->fails()) {
    //         return ['status' => false, 'message' => $validator->errors()];
    //     }
    //     try {
    //         // Variable initialization

    //         $approveList = DB::table('adv_selfadvet_renewals')
    //             ->select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', DB::raw("'Approve' as application_status"), 'payment_amount', 'payment_date', 'payment_mode')->where('entity_ward_id', $req->entityWard)->where('application_type', $req->applicationType)->where('payment_status', '1')->where('ulb_id', $ulbId)
    //             ->whereBetween('payment_date', [$req->dateFrom, $req->dateUpto]);

    //         $data = collect(array());
    //         if ($req->payMode == 'All') {
    //             $data = $approveList;
    //         }
    //         if ($req->payMode == 'Online') {
    //             $data = $approveList->where('payment_mode', $req->payMode);
    //         }
    //         if ($req->payMode == 'Cash') {
    //             $data = $approveList->where('payment_mode', $req->payMode);
    //         }
    //         if ($req->payMode == 'Cheque/DD') {
    //             $data = $approveList->where('payment_mode', $req->payMode);
    //         }
    //         $data = $data->paginate($req->perPage);

    //         $ap = $data->toArray();

    //         $amounts = collect();
    //         $data1 = collect($ap['data'])->map(function ($item, $key) use ($amounts) {
    //             $amounts->push($item->payment_amount);
    //         });

    //         return responseMsgs(true, "Application Fetched Successfully", $data, "050138", 1.0, responseTime(), "POST", "", "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, "Application Not Fetched", $e->getMessage(), "050138", 1.0, "271ms", "POST", "", "");
    //     }
    // }
    public function paymentCollection(Request $req)
    {
        if ($req->auth['ulb_id'] < 1) {
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050138", 1.0, "271ms", "POST", "", "");
        } else {
            $ulbId = $req->auth['ulb_id'];
        }
        $userType = $req->auth['user_type'];

        $validator = Validator::make($req->all(), [
            'applicationType' => 'nullable|',
            'entityWard' => 'nullable|integer',
            'dateFrom' => 'required|date_format:Y-m-d',
            'dateUpto' => 'required|date_format:Y-m-d',
            'perPage' => 'required|integer',
            'payMode' => 'nullable|',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $selfAdvertisementworkflow = Config::get('workflow-constants.SELF-ADVERTISEMENT');
            $approveListQuery = DB::table('adv_selfadvet_renewals')
                ->select(
                    'adv_selfadvet_renewals.id',
                    'adv_selfadvet_renewals.application_no',
                    'adv_selfadvet_renewals.applicant',
                    'adv_selfadvet_renewals.application_date',
                    'adv_selfadvet_renewals.application_type',
                    'adv_selfadvet_renewals.entity_ward_id',
                    DB::raw("'Approve' as application_status"),
                    'adv_selfadvet_renewals.payment_amount',
                    'adv_selfadvet_renewals.payment_date',
                    'adv_selfadvet_renewals.payment_mode',
                    'adv_selfadvet_renewals.entity_name',
                    'adv_mar_transactions.transaction_no'
                )
                ->leftjoin('adv_mar_transactions', 'adv_mar_transactions.application_id', 'adv_selfadvet_renewals.id')
                ->where('adv_selfadvet_renewals.payment_status', 1)
                ->where('adv_selfadvet_renewals.ulb_id', $ulbId)
                ->where('adv_mar_transactions.workflow_id', $selfAdvertisementworkflow)
                ->where('adv_mar_transactions.status', 1)
                ->whereBetween('adv_selfadvet_renewals.payment_date', [$req->dateFrom, $req->dateUpto]);
            // Apply payment mode filter
            if ($req->payMode != 'All' && $req->payMode != null) {
                if ($req->payMode == 'Cheque/DD') {
                    $approveListQuery->whereIn('adv_selfadvet_renewals.payment_mode', ['CHEQUE', 'DD']);
                } else {
                    $approveListQuery->where('adv_selfadvet_renewals.payment_mode', $req->payMode);
                }
            }
            if ($req->entityWard != null) {
                $approveListQuery->where('adv_selfadvet_renewals.entity_ward_id', $req->entityWard);
            }
            if ($req->applicationType != null) {
                $approveListQuery->where('adv_selfadvet_renewals.application_type',  $req->applicationType);
            }
            if ($req->paidBy != null) {
                switch ($req->paidBy) {
                    case 'Citizen':
                        $approveListQuery = $approveListQuery->where('adv_mar_transactions.is_jsk', false);
                        break;
                    case 'JSK':
                        $approveListQuery = $approveListQuery->where('adv_mar_transactions.is_jsk', true);
                        break;
                    default:
                        throw new Exception("Invalid Data");
                }
            }

            // Paginate the main query
            $paginator = $approveListQuery->paginate($req->perPage);

            // Clone the query for counts and sums
            $approveListForCounts = clone $approveListQuery;
            $approveListForSums = clone $approveListQuery;

            // Count of transactions
            $cashCount = (clone $approveListForCounts)->where('adv_selfadvet_renewals.payment_mode', 'CASH')->count();
            $ddCount = (clone $approveListForCounts)->where('adv_selfadvet_renewals.payment_mode', 'DD')->count();
            $chequeCount = (clone $approveListForCounts)->where('adv_selfadvet_renewals.payment_mode', 'CHEQUE')->count();
            $onlineCount = (clone $approveListForCounts)->where('adv_selfadvet_renewals.payment_mode', 'ONLINE')->count();

            // Sum of transactions
            $cashPayment = (clone $approveListForSums)->where('adv_selfadvet_renewals.payment_mode', 'CASH')->sum('payment_amount');
            $ddPayment = (clone $approveListForSums)->where('adv_selfadvet_renewals.payment_mode', 'DD')->sum('payment_amount');
            $chequePayment = (clone $approveListForSums)->where('adv_selfadvet_renewals.payment_mode', 'CHEQUE')->sum('payment_amount');
            $onlinePayment = (clone $approveListForSums)->where('adv_selfadvet_renewals.payment_mode', 'ONLINE')->sum('payment_amount');

            # transaction by jsk 
            $cashCountJsk = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', true)->where('adv_mar_transactions.payment_mode', 'CASH')->count();
            $chequeCountJsk = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', true)->where('adv_mar_transactions.payment_mode', 'CHEQUE')->count();
            $ddCountJsk = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', true)->where('adv_mar_transactions.payment_mode', 'DD')->count();
            $onlineCountJsk = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', true)->where('adv_mar_transactions.payment_mode', 'ONLINE')->count();
            #transaction by citizen
            $cashCountCitizen = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', false)->where('adv_mar_transactions.payment_mode', 'CASH')->count();
            $chequeCountCitizen = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', false)->where('adv_mar_transactions.payment_mode', 'CHEQUE')->count();
            $ddCountCitizen = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', false)->where('adv_mar_transactions.payment_mode', 'DD')->count();
            $onlineCountcitizen = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', false)->where('adv_mar_transactions.payment_mode', 'ONLINE')->count();

            $totalCountJsk = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', true)->count();
            $totalCountCitizen = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', false)->count();

            $totalAmount  = (clone $approveListForSums)->sum('payment_amount');

            $response = [
                "current_page" => $paginator->currentPage(),
                "last_page" => $paginator->lastPage(),
                "data" => $paginator->items(),
                "total" => $paginator->total(),
                'CashCount' => $cashCount,
                'ddCount' => $ddCount,
                'chequeCount' => $chequeCount,
                'onlineCount' => $onlineCount,
                'cashPayment' => $cashPayment,
                'ddPayment' => $ddPayment,
                'chequePayment' => $chequePayment,
                'onlinePayment' => $onlinePayment,
                'cashCountJsk' => $cashCountJsk,
                'chequeCountJsk' => $chequeCountJsk,
                'ddCountJsk' => $ddCountJsk,
                'onlineCountJsk' => $onlineCountJsk,
                'cashCountCitizen' => $cashCountCitizen,
                'chequeCountCitizen' => $chequeCountCitizen,
                'ddCountCitizen' => $ddCountCitizen,
                'onlineCountcitizen' => $onlineCountcitizen,
                'totalAmount' => $totalAmount,
                'totalCountJsk' => $totalCountJsk,
                'totalCountCitizen' => $totalCountCitizen,
                'userType' => $userType,
            ];

            // Return formatted response
            return responseMsgs(true, "Application Fetched Successfully", $response, "050138", 1.0, responseTime(), "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, "Application Not Fetched", $e->getMessage(), "050138", 1.0, "271ms", "POST", "", "");
        }
    }




    //written by prity pandey
    public function getApproveDetailsById(Request $req)
    {
        // Validate the request
        $validated = Validator::make(
            $req->all(),
            [
                'applicationId' => 'required|integer'
            ]
        );

        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $applicationId = $req->applicationId;
            $mAdvActiveSelfadvertisement = new AdvSelfadvertisement();
            $mtransaction = new AdvMarTransaction();

            // Fetch details from the model
            $data = $mAdvActiveSelfadvertisement->getDetailsById($applicationId)->first();

            if (!$data) {
                throw new Exception("Application Not Found");
            }

            // Fetch transaction details
            $tranDetails = $mtransaction->getTranByApplicationId($applicationId, $data)->first();

            $approveApplicationDetails['basicDetails'] = $data;

            if ($tranDetails) {
                $approveApplicationDetails['paymentDetails'] = $tranDetails;
            } else {
                $approveApplicationDetails['paymentDetails'] = null;
            }

            // Return success response with the data
            return responseMsgs(true, "Application Details Found", $approveApplicationDetails, "", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            // Handle exception and return error message
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }


    public function getUploadDocuments(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'applicationId' => 'required|numeric'
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $mWfActiveDocument      = new WfActiveDocument();
            $mAdvActiveRegistration = new AdvSelfadvertisement();
            $refDocUpload               = new DocumentUpload;
            $applicationId          = $req->applicationId;

            $AdvDetails = $mAdvActiveRegistration->getDetailsById($applicationId)->first();
            if (!$AdvDetails)
                throw new Exception("Application not found for this ($applicationId) application Id!");

            $workflowId = $AdvDetails->workflow_id;
            $data = $mWfActiveDocument->uploadedActiveDocumentsViewById($req->applicationId, $workflowId);
            $data = $refDocUpload->getDocUrl($data);
            return responseMsgs(true, "Uploaded Documents", $data, "010102", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010202", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    public function selfAdvPayment(Request $req)
    {
        $mAdvSelfadvertisement = new AdvSelfadvertisement();
        $rules = [
            'applicationId' => "required|digits_between:1,9223372036854775807|exists:" . $mAdvSelfadvertisement->getConnectionName() . "." . $mAdvSelfadvertisement->getTable() . ",id",
            "paymentMode"   => "required|string|in:" . collect(Config::get("constants.PAYMENT_MODE_OFFLINE"))->implode(","),
            'status'        => 'required|integer'
        ];
        if (isset($req->paymentMode) && $req->paymentMode != "CASH") {
            $rules["chequeNo"] = "required";
            $rules["chequeDate"] = "required|date|date_format:Y-m-d|after_or_equal:" . Carbon::now()->format("Y-m-d");
            $rules["bankName"] = "required";
            $rules["branchName"] = "required";
        }
        $validator = Validator::make($req->all(), $rules);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $user = Auth()->user();
            $todayDate = Carbon::now();
            $userId = $user->id ?? null;
            $isCitizen = $user && $user->getTable() != "users" ? true : false;
            $isJsk = (!$isCitizen) && $user->user_type == "JSK" ? true : false;
            $req->merge([
                "isJsk" => $isJsk,
                "userId" => !$isCitizen ? $userId : null,
                "citizenId" => $isCitizen ? $userId : null,
            ]);
            // Variable initialization
            $mAdvMarTransaction = new AdvMarTransaction();
            DB::beginTransaction();

            $data = $mAdvSelfadvertisement->offlinePayment($req);
            $appDetails = AdvSelfadvertisement::find($req->applicationId);
            $req->merge($appDetails->toArray());

            $transactionId = $mAdvMarTransaction->addTransactions($req, $appDetails, $this->_moduleIds, "Advertisement", $req->paymentMode);

            $req->merge([
                'empId' => $user->id,
                'userType' => $user->user_type,
                'todayDate' => $todayDate->format('Y-m-d'),
                'tranNo' => $appDetails->payment_id,
                'ulbId' => $user->ulb_id,
                'isJsk' => true,
                'tranType' => $appDetails->application_type,
                'amount' => $appDetails->payment_amount,
                'applicationId' => $appDetails->id,
                'workflowId' => $appDetails->workflow_id,
                'transactionId' => $transactionId,
                'applicationNo' => $appDetails->application_no
            ]);
            // Save data in temp transaction
            $this->postOtherPaymentModes($req);
            DB::commit();
            if ($req->status == '1' && $data['status'] == 1) {
                return responseMsgs(true, "Payment Successfully !!", ['status' => true, 'transactionNo' => $data['payment_id'], 'workflowId' => $appDetails->workflow_id], "050127", "1.0", responseTime(), 'POST', $req->deviceId ?? "");
            } else {
                return responseMsgs(false, "Payment Rejected !!", '', "050127", "1.0", responseTime(), 'POST', $req->deviceId ?? "");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050127", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }
    # save Transaction data 
    public function postOtherPaymentModes($req)
    {
        $paymentMode = $this->_offlineMode;
        $moduleId = $this->_moduleIds;
        $mTempTransaction = new TempTransaction();
        $mChequeDtl = new AdvChequeDtl();

        if ($req->paymentMode != $paymentMode[3]) {  // Not Cash
            $chequeReqs = [
                'user_id' => $req['empId'],
                'application_id' => $req->applicationId,
                'transaction_id' => $req['transactionId'],
                'cheque_date' => $req['chequeDate'],
                'bank_name' => $req['bankName'],
                'branch_name' => $req['branchName'],
                'cheque_no' => $req['chequeNo'],
                'workflow_id' => $req['workflowId'],
                'transaction_no' => $req['tranNo']
            ];
            $mChequeDtl->entryChequeDd($chequeReqs);
        }

        $tranReqs = [
            'transaction_id' => $req['transactionId'],
            'application_id' => $req->applicationId,
            'module_id' => $moduleId,
            'workflow_id' => $req['workflowId'],
            'transaction_no' => $req['tranNo'],
            'application_no' => $req['applicationNo'],
            'amount' => $req['amount'],
            'payment_mode' => strtoupper($req['paymentMode']),
            'cheque_dd_no' => $req['chequeNo'],
            'bank_name' => $req['bankName'],
            'tran_date' => $req['todayDate'],
            'user_id' => $req['empId'],
            'ulb_id' => $req['ulbId'],
            'ward_no' => $req['ref_ward_id']
        ];

        $mTempTransaction->tempTransaction($tranReqs);
    }

    public function listBtcInboxJsk(Request $req)
    {
        try {
            // Variable initialization
            $key = $req->filterBy;
            $parameter = $req->parameter;
            $startTime = microtime(true);

            // $auth = auth()->user();
            $userId = $req->auth['id'];
            $ulbId = $req->auth['ulb_id'] ?? 2;
            $wardId = $this->getWardByUserId($userId);

            $occupiedWards = collect($wardId)->map(function ($ward) {                               // Get Occupied Ward of the User
                return $ward->ward_id;
            });

            $roles = $this->getRoleIdByUserId($userId);

            $roleId = collect($roles)->map(function ($role) {                                       // get Roles of the user
                return $role->wf_role_id;
            });

            $mMarActiveLodge = new AdvActiveSelfadvertisement();
            $btcList = $mMarActiveLodge->getLodgeListJsk($ulbId)
                //->whereIn('mar_active_lodges.current_role_id', $roleId)
                // ->whereIn('a.ward_mstr_id', $occupiedWards)
                ->where('parked', true)
                ->orderByDesc('adv_active_selfadvertisements.id');
            // ->get();
            if ($key && $parameter) {
                $msg = "Self Advertisement application details according to $key";
                switch ($key) {
                    case 'mobileNo':
                        $applications = $btcList->where('adv_active_selfadvertisements.mobile_no', 'LIKE', "%$parameter%");
                        break;
                    case 'applicantName':
                        $applications = $btcList->where('adv_active_selfadvertisements.applicant', 'LIKE', "%$parameter%");
                        break;
                    case 'applicationNo':
                        $applications = $btcList->where('adv_active_selfadvertisements.application_no', 'LIKE', "%$parameter%");
                        break;
                    default:
                        throw new Exception("Invalid Data");
                }
            }
            $list = paginator($btcList, $req);

            return responseMsgs(true, "BTC Inbox List", $list, "050720", 1.0, responseTime(), "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050720", 1.0, "271ms", "POST", "", "");
        }
    }

    public function getRejectedDetailsById(Request $req)
    {
        // Validate the request
        $validated = Validator::make(
            $req->all(),
            [
                'applicationId' => 'required|integer'
            ]
        );

        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $applicationId = $req->applicationId;
            $mAdvSelfadvertisement = new AdvSelfadvertisement();
            $mtransaction = new AdvMarTransaction();

            // Fetch details from the model
            $data = $mAdvSelfadvertisement->getAllById($applicationId);

            if (!$data) {
                throw new Exception("Application Not Found");
            }

            // Fetch transaction details
            //$tranDetails = $mtransaction->getTranByApplicationId($applicationId)->first();

            $approveApplicationDetails['basicDetails'] = $data;

            // if ($tranDetails) {
            //     $approveApplicationDetails['paymentDetails'] = $tranDetails;
            // } else {
            //     $approveApplicationDetails['paymentDetails'] = null;
            // }

            // Return success response with the data
            return responseMsgs(true, "Application Details Found", $approveApplicationDetails, "", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            // Handle exception and return error message
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    public function getUploadDocumentsBtc(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'applicationId' => 'required|numeric'
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $mWfActiveDocument      = new WfActiveDocument();
            $mAdvAgency             = new AdvActiveSelfadvertisement();
            $refDocUpload           = new DocumentUpload;
            $applicationId          = $req->applicationId;

            $AdvDetails = $mAdvAgency->getDetailsByIdjsk($applicationId)->first();
            if (!$AdvDetails)
                throw new Exception("Application not found for this ($applicationId) application Id!");

            $workflowId = $AdvDetails->workflow_id;
            $data = $mWfActiveDocument->uploadedActiveDocumentsViewById($req->applicationId, $workflowId);
            $data = $refDocUpload->getDocUrl($data);
            return responseMsgs(true, "Uploaded Documents", $data, "010102", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010202", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function forwardNextLevelBtc(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'applicationId' => 'required|integer'
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            // Variable initialization
            // Marriage Banqute Hall Application Update Current Role Updation
            $mMarActiveLodge = AdvActiveSelfadvertisement::find($request->applicationId);
            $mMarActiveLodge->parked = null;
            $mMarActiveLodge->save();
            return responseMsgs(true, "Successfully Forwarded The Application!!", "", "050708", "1.0", responseTime(), "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050708", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }

    /**
     * |Function For get Details By Id  for Admin Panel
     */
    public function searchApplicationViewById(Request $req)
    {
        // Validate the request
        $validated = Validator::make(
            $req->all(),
            [
                'applicationId' => 'required|integer'
            ]
        );

        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $applicationId = $req->applicationId;
            $mWfActiveDocument      = new WfActiveDocument();
            $refDocUpload           = new DocumentUpload;
            $mAdvSelfadvertisement = new AdvSelfadvertisement();
            $mtransaction = new AdvMarTransaction();

            // Fetch details from the model
            $data = $mAdvSelfadvertisement->getAllById($applicationId);
            if (!$data) {
                throw new Exception("Application Not Found");
            }
            // Fetch transaction details
            $tranDetails = $mtransaction->getTranByApplicationId($applicationId, $data)->first();

            $approveApplicationDetails['basicDetails'] = $data;

            if ($tranDetails) {
                $approveApplicationDetails['paymentDetails'] = $tranDetails;
            } else {
                $approveApplicationDetails['paymentDetails'] = null;
            }
            $workflowId = $data->workflow_id;
            $docdetail = $mWfActiveDocument->uploadedActiveDocumentsViewById($req->applicationId, $workflowId);
            $docdetail = $refDocUpload->getDocUrl($docdetail);
            $approveApplicationDetails['docdetail'] = $docdetail;
            return responseMsgs(true, "Application Details Found", $approveApplicationDetails, "", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            // Handle exception and return error message
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }
}
