<?php

namespace App\Http\Controllers\Advertisements;

use App\BLL\Advert\CalculateRate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicles\RenewalRequest;
use App\Http\Requests\Vehicles\StoreRequest;
use App\MicroServices\DocumentUpload;
use App\MicroServices\IdGenerator\PrefixIdGenerator;
use App\Models\Advertisements\AdvActiveVehicle;
use App\Models\Advertisements\AdvChequeDtl;
use App\Models\Advertisements\AdvVehicle;
use App\Models\Advertisements\AdvRejectedVehicle;
use App\Models\Advertisements\WfActiveDocument;
use App\Models\Param\AdvMarTransaction;
use App\Models\Param\AdvMarTransactions;
use App\Models\Payment\TempTransaction;
use App\Models\Workflows\WfRoleusermap;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\AdvDetailsTraits;
use Illuminate\Support\Facades\DB;
use App\Models\Workflows\WorkflowTrack;
use App\Models\Workflows\WfWardUser;
use App\Models\Workflows\WfWorkflow;
use App\Models\Workflows\WfWorkflowrolemap;
use App\Repositories\SelfAdvets\iSelfAdvetRepo;

use Carbon\Carbon;


use App\Traits\WorkflowTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class VehicleAdvetController extends Controller
{
    /**
     * | Created On-31-12-2022 
     * | Created By- Anshu Kumar 
     * | Changes By- Bikash Kumar 
     * | Created for the Movable Vehicles Operations
     * | Status - Closed, By Bikash on 24 Apr 2023,  Total no. of lines - 1512, Total Function - 35, Total API - 32
     */


    use WorkflowTrait;
    use AdvDetailsTraits;

    protected $_modelObj;
    protected $Repository;
    protected $_workflowIds;
    protected $_moduleIds;
    protected $_docCode;
    protected $_tempParamId;
    protected $_paramId;
    protected $_baseUrl;
    protected $_wfMasterId;
    protected $_fileUrl;
    protected $_offlineMode;
    public function __construct(iSelfAdvetRepo $self_repo)
    {
        $this->_modelObj = new AdvActiveVehicle();
        // $this->_workflowIds = Config::get('workflow-constants.MOVABLE_VEHICLE_WORKFLOWS');
        $this->_moduleIds = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
        $this->_docCode = Config::get('workflow-constants.MOVABLE_VEHICLE_DOC_CODE');
        $this->_tempParamId = Config::get('workflow-constants.TEMP_VCL_ID');
        $this->_paramId = Config::get('workflow-constants.VCL_ID');
        $this->_baseUrl = Config::get('constants.BASE_URL');
        $this->_fileUrl = Config::get('workflow-constants.FILE_URL');
        $this->_offlineMode                 = Config::get("workflow-constants.OFFLINE_PAYMENT_MODE");
        $this->Repository = $self_repo;

        $this->_wfMasterId = Config::get('workflow-constants.VEHICLE_WF_MASTER_ID');
    }

    /**
     * | Apply for new document
     * | Function - 01
     * | API - 01
     * Modified by prity pandey
     */
    public function addNew(StoreRequest $req)
    {
        try {
            // Variable Initialization
            $advVehicle = new AdvActiveVehicle();
            // if ($req->auth['user_type'] == 'JSK') {
            //     $userId = ['userId' => $req->auth['id']];
            //     $req->request->add($userId);
            // } else {
            //     $citizenId = ['citizenId' => $req->auth['id']];
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

            // $mCalculateRate = new CalculateRate;
            // $generatedId = $mCalculateRate->generateId($req->bearerToken(), $this->_tempParamId, $req->ulbId); // Generate Application No
            $idGeneration = new PrefixIdGenerator($this->_tempParamId, $req->ulbId);
            $generatedId = $idGeneration->generate();
            $applicationNo = ['application_no' => $generatedId];
            $req->request->add($applicationNo);

            // $mWfWorkflow=new WfWorkflow();
            $WfMasterId = ['WfMasterId' =>  $this->_wfMasterId];
            $req->request->add($WfMasterId);

            DB::beginTransaction();
            DB::connection('pgsql_masters')->beginTransaction();
            $applicationNo = $advVehicle->addNew($req);                             // Apply Vehicle Application 
            DB::commit();
            DB::connection('pgsql_masters')->commit();
            return responseMsgs(true, "Successfully Applied the Application !!", ["status" => true, "ApplicationNo" => $applicationNo], "050301", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            DB::connection('pgsql_masters')->rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050301", "1.0", "", "POST", $req->deviceId ?? "");
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
            // Variable Initialization
            $mAdvVehicle = new AdvVehicle();
            $details = $mAdvVehicle->applicationDetailsForRenew($req->applicationId);
            if (!$details)
                throw new Exception("Application Not Found !!!");

            return responseMsgs(true, "Application Fetched !!!", remove_null($details), "050302", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050302", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Vehicle Application Renewal
     * | Function - 03
     * | API - 03
     */
    public function renewalApplication(RenewalRequest $req)
    {
        try {
            // Variable Initialization
            $advVehicle = new AdvActiveVehicle();
            if ($req->auth['user_type'] == 'JSK') {
                $userId = ['userId' => $req->auth['id']];
                $req->request->add($userId);
            } else {
                $citizenId = ['citizenId' => $req->auth['id']];
                $req->request->add($citizenId);
            }
            $idGeneration = new PrefixIdGenerator($this->_tempParamId, $req->ulbId);
            $generatedId = $idGeneration->generate();
            $applicationNo = ['application_no' => $generatedId];
            $req->request->add($applicationNo);

            $WfMasterId = ['WfMasterId' =>  $this->_wfMasterId];
            $req->request->add($WfMasterId);

            DB::beginTransaction();
            DB::connection('pgsql_masters')->beginTransaction();
            $applicationNo = $advVehicle->renewalApplication($req);               // Renewal Vehicle Application
            DB::commit();
            DB::connection('pgsql_masters')->commit();
            return responseMsgs(true, "Successfully Applied the Application !!", ["status" => true, "ApplicationNo" => $applicationNo], "050303", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            DB::connection('pgsql_masters')->rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050303", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Inbox List
     * | @param Request $req
     * | Function - 04
     * | API - 04
     * | Query Cost - 24.12 ms
     * | Max Records - 4
     */
    public function listInbox(Request $req)
    {
        try {
            // Variable Initialization
            $mvehicleAdvets = $this->_modelObj;
            $ulbId = $req->auth['ulb_id'];
            $workflowRoles = collect($this->getRoleByUserId($req->auth['id']));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $inboxList = $mvehicleAdvets->listInbox($roleIds, $ulbId);                          // <----- get Inbox list
            if (trim($req->key))
                $inboxList =  searchFilter($inboxList, $req);
            $list = paginator($inboxList, $req);

            return responseMsgs(true, "Inbox Applications", $list, "050304", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050304", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Outbox List
     * | Function - 05
     * | API - 05
     * | Query Cost - 26.8 ms
     * | Max Records - 2
     */
    public function listOutbox(Request $req)
    {
        try {
            // Variable Initialization
            $mvehicleAdvets = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $ulbId = $req->auth['ulb_id'];
            $workflowRoles = collect($this->getRoleByUserId($req->auth['id']));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $outboxList = $mvehicleAdvets->listOutbox($roleIds, $ulbId);                       // <----- Get Outbox list
            if (trim($req->key))
                $outboxList =  searchFilter($outboxList, $req);
            $list = paginator($outboxList, $req);

            return responseMsgs(true, "Outbox Lists", $list, "050305", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050305", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Application Details
     * | Function - 06
     * | API - 06
     */
    public function getDetailsById(Request $req)
    {
        try {
            // Variable Initialization
            $mvehicleAdvets = new AdvActiveVehicle();
            $mWorkflowTracks        = new WorkflowTrack();
            // $data = array();
            $type = NULL;
            $fullDetailsData = array();
            if (isset($req->type)) {
                $type = $req->type;
            }
            if ($req->applicationId) {
                $data = $mvehicleAdvets->getDetailsById($req->applicationId, $type);
            } else {
                throw new Exception("Not Pass Application Id");
            }
            if (!$data) {
                throw new Exception("Not Application Details Found");
            }

            // Basic Details
            $basicDetails = $this->generateVehicleBasicDetails($data); // Trait function to get Vehicle Basic Details
            $basicElement = [
                'headerTitle' => "Basic Details",
                "data" => $basicDetails
            ];

            $cardDetails = $this->generateVehicleCardDetails($data);
            $cardElement = [
                'headerTitle' => "Movables Vehicle Advertisment",
                'data' => $cardDetails
            ];
            $fullDetailsData['fullDetailsData']['dataArray'] = new Collection([$basicElement]);
            $fullDetailsData['fullDetailsData']['cardArray'] = new Collection($cardElement);

            $metaReqs['customFor'] = 'MOVABLE';
            $metaReqs['wfRoleId'] = $data['current_roles'];
            $metaReqs['workflowId'] = $data['workflow_id'];
            $metaReqs['lastRoleId'] = $data['last_role_id'];

            # Level comment
            $mtableId = $req->applicationId;
            $mRefTable = "adv_active_vehicles.id";                         // Static
            $fullDetailsData['levelComment'] = $mWorkflowTracks->getTracksByRefId($mRefTable, $mtableId);

            #citizen comment
            $refCitizenId = $data['citizen_id'];
            // $fullDetailsData['citizenComment'] = $mWorkflowTracks->getCitizenTracks($mRefTable, $mtableId, $refCitizenId);

            $req->request->add($metaReqs);
            $forwardBackward = $this->getRoleDetails($req);
            $fullDetailsData['roleDetails'] = collect($forwardBackward)['original']['data'];

            $fullDetailsData = remove_null($fullDetailsData);

            $fullDetailsData['application_no'] = $data['application_no'];
            $fullDetailsData['apply_date'] = Carbon::createFromFormat('Y-m-d H:i:s',  $data['created_at'])->format('d-m-Y');
            $fullDetailsData['zone'] = $data['zone'];
            $fullDetailsData['doc_verify_status'] = $data['doc_verify_status'];
            $fullDetailsData['doc_upload_status'] = $data['doc_upload_status'];
            if (isset($data['payment_amount'])) {
                $fullDetailsData['payment_amount'] = $data['payment_amount'];
            }
            $fullDetailsData['timelineData'] = collect($req);
            $fullDetailsData['workflowId'] = $data['workflow_id'];

            return responseMsgs(true, 'Data Fetched', $fullDetailsData, "050306", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050306", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Get Role Details
     * | Function - 07
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
     * | Function - 08
     * | API - 07
     * | Query Cost 23.3 ms
     * | Max Records - 4
     */
    public function listAppliedApplications(Request $req)
    {
        try {
            // Variable Initialization
            $citizenId = $req->auth['id'];
            $mvehicleAdvets = new AdvActiveVehicle();
            $applications = $mvehicleAdvets->listAppliedApplications($citizenId);

            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            return responseMsgs(true, "Applied Applications", $data1, "050307", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050307", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /** 
     * | Escalate Application
     * | Function - 09
     * | API - 08
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
            $data = AdvActiveVehicle::find($applicationId);
            $data->is_escalate = $request->escalateStatus;
            $data->escalate_by = $userId;
            $data->save();

            return responseMsgs(true, $request->escalateStatus == 1 ? 'Movable Vechicle is Escalated' : "Movable Vechicle is removed from Escalated", '', "050308", "1.0", responseTime(), "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050308", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }

    /**
     * | Escalated Application List
     * | Function - 10
     * | API - 09
     * | Query Cost - 44.93 ms
     * | Max Records - 1
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
            $workflowId = $mWfWorkflow->getulbWorkflowId($this->_wfMasterId, $ulbId);      // get workflow Id

            $advData = $this->Repository->specialVehicleInbox($workflowId)                      // Repository function to get Advertiesment Details
                ->where('is_escalate', 1)
                ->where('adv_active_vehicles.ulb_id', $ulbId);
            // ->whereIn('ward_mstr_id', $wardId)
            // ->get();
            if (trim($req->key))
                $advData =  searchFilter($advData, $req);
            $list = paginator($advData, $req);

            return responseMsgs(true, "Data Fetched", $list, "050309", "1.0", responseTime(), "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050309", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Forward or Backward Application
     * | Function - 11
     * | API - 10
     */
    public function forwardNextLevel(Request $request)
    {
        $request->validate([
            'applicationId' => 'required|integer',
            'senderRoleId' => 'required|integer',
            'receiverRoleId' => 'required|integer',
            'comment' => 'required',
        ]);

        try {
            // Variable Initialization
            $mAdvActiveVehicle = AdvActiveVehicle::find($request->applicationId);
            if ($mAdvActiveVehicle->parked == NULL && $mAdvActiveVehicle->doc_upload_status == '0')
                throw new Exception("Document Rejected Please Send Back to Citizen !!!");
            if ($mAdvActiveVehicle->parked == '1' && $mAdvActiveVehicle->doc_upload_status == '0')
                throw new Exception("Document Are Not Re-upload By Citizen !!!");
            if ($mAdvActiveVehicle->doc_verify_status == '0' && $mAdvActiveVehicle->parked == NULL)
                throw new Exception("Please Verify All Documents To Forward The Application !!!");
            if ($mAdvActiveVehicle->zone == NULL)
                throw new Exception("Zone Not Selected !!!");
            $mAdvActiveVehicle->last_role_id = $request->current_roles;
            $mAdvActiveVehicle->current_roles = $request->receiverRoleId;
            $mAdvActiveVehicle->save();

            $metaReqs['moduleId'] = $this->_moduleIds;
            $metaReqs['workflowId'] = $mAdvActiveVehicle->workflow_id;
            $metaReqs['refTableDotId'] = "adv_active_vehicles.id";
            $metaReqs['refTableIdValue'] = $request->applicationId;
            $request->request->add($metaReqs);

            $track = new WorkflowTrack();
            // Vehicle Application Update Current Role Updation
            DB::beginTransaction();
            DB::connection('pgsql_masters')->beginTransaction();
            $track->saveTrack($request);
            DB::commit();
            DB::connection('pgsql_masters')->commit();
            return responseMsgs(true, "Successfully Forwarded The Application!!", "", "050310", "1.0", responseTime(), "POST", $request->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            DB::connection('pgsql_masters')->rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050310", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }

    /**
     * | Post Independent Comment
     * | Function - 12
     * | API - 11
     */
    public function commentApplication(Request $request)
    {
        $request->validate([
            'comment' => 'required',
            'applicationId' => 'required|integer',
            'senderRoleId' => 'nullable|integer'
        ]);
        try {
            // Variable Initialization
            $userId = $request->auth['id'];
            $userType = $request->auth['user_type'];
            $workflowTrack = new WorkflowTrack();
            $mWfRoleUsermap = new WfRoleusermap();
            $mAdvActiveVehicle = AdvActiveVehicle::find($request->applicationId);                // Advertisment Details
            $mModuleId = $this->_moduleIds;
            $metaReqs = array();
            $metaReqs = [
                'workflowId' => $mAdvActiveVehicle->workflow_id,
                'moduleId' => $mModuleId,
                'refTableDotId' => "adv_active_vehicles.id",
                'refTableIdValue' => $mAdvActiveVehicle->id,
                'message' => $request->comment
            ];
            // For Citizen Independent Comment
            if ($userType != 'Citizen') {
                $roleReqs = new Request([
                    'workflowId' => $mAdvActiveVehicle->workflow_id,
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
            return responseMsgs(true, "You Have Commented Successfully!!", ['Comment' => $request->comment], "050311", "1.0", responseTime(), "POST", "");
        } catch (Exception $e) {
            DB::rollBack();
            DB::connection('pgsql_masters')->rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050311", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }


    /**
     * | View Vehicle upload document
     * | Function - 13
     * | API - 12
     */
    public function viewVehicleDocuments(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors(), "", "050311", "1.0", "", "POST", $req->deviceId ?? "");
        }
        if ($req->type == 'Active')
            $workflowId = AdvActiveVehicle::find($req->applicationId)->workflow_id;
        elseif ($req->type == 'Approve')
            $workflowId = AdvVehicle::find($req->applicationId)->workflow_id;
        elseif ($req->type == 'Reject')
            $workflowId = AdvRejectedVehicle::find($req->applicationId)->workflow_id;
        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        if ($req->applicationId && $req->type) {
            $data = $mWfActiveDocument->uploadDocumentsViewById($req->applicationId, $workflowId);
        } else {
            throw new Exception("Required Application Id And Application Type ");
        }
        //$appUrl = $this->_fileUrl;
        $data = (new DocumentUpload())->getDocUrl($data);
        // $data1['data'] = collect($data)->map(function ($value) use  ($appUrl,$mWfActiveDocument) {
        //     //$value->doc_path = $appUrl . $value->doc_path;
        //     $value->doc_path = $appUrl . $value->doc_path;
        //     return $value;
        // });
        return responseMsgs(true, "Data Fetched", remove_null($data), "050118", "1.0", responseTime(), "POST", "");
    }

    /**
     * | Get Uploaded Active Document by application ID
     * | Function - 14
     * | API - 13
     */
    public function viewActiveDocument(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|digits_between:1,9223372036854775807'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }

        $details = AdvActiveVehicle::find($req->applicationId);
        $workflowId = $details->workflow_id;
        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        $data = $mWfActiveDocument->uploadedActiveDocumentsViewById($req->applicationId, $workflowId);

        //$appUrl = $this->_fileUrl;
        $data = (new DocumentUpload())->getDocUrl($data);
        // $data1['data'] = collect($data)->map(function ($value) use ($appUrl) {
        //     $value->doc_path = $appUrl . $value->doc_path;
        //     return $value;
        // });
        return responseMsgs(true, "Uploaded Documents", $data, "010102", "1.0", "", "POST", $req->deviceId ?? "");
    }

    /**
     * | Workflow View Uploaded Document by application ID
     * | Function - 15
     * | API - 14
     */
    // public function viewDocumentsOnWorkflow(Request $req)
    // {
    //     // Variable Initialization
    //     if (isset($req->type) && $req->type == 'Approve')
    //         $workflowId = AdvVehicle::find($req->applicationId)->workflow_id;
    //     else
    //         $workflowId = AdvActiveVehicle::find($req->applicationId)->workflow_id;
    //     $mWfActiveDocument = new WfActiveDocument();
    //     $data = array();
    //     if ($req->applicationId) {
    //         $data = $mWfActiveDocument->uploadDocumentsViewById($req->applicationId, $workflowId);
    //     }
    //     $appUrl = $this->_fileUrl;
    //     $data1 = collect($data)->map(function ($value) use ($appUrl) {
    //         $value->doc_path = $appUrl . $value->doc_path;
    //         return $value;
    //     });
    //     return responseMsgs(true, "Data Fetched", remove_null($data1), "050314", "1.0", responseTime(), "POST", "");
    // }
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
            $details = AdvVehicle::find($req->applicationId);
        } else {
            $details = AdvActiveVehicle::find($req->applicationId);
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
        $data = (new DocumentUpload())->getDocUrl($data);
        // $data1 = collect($data)->map(function ($value) use ($appUrl) {
        //     $value->doc_path = $appUrl . $value->doc_path;
        //     return $value;
        // });
        return responseMsgs(true, "Data Fetched", remove_null($data), "050118", "1.0", responseTime(), "POST", "");
    }

    /**
     * | Final Approval and Rejection of the Application 
     * | Function - 16
     * | Status- closed
     * | API - 15
     */
    public function approvedOrReject(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'roleId' => 'required',
            'applicationId' => 'required|integer',
            'status' => 'required|integer',
            'remarks' => 'nullable|string'

        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable Initialization
            // Check if the Current User is Finisher or Not         
            $mAdvActiveVehicle = AdvActiveVehicle::find($req->applicationId);
            $getFinisherQuery = $this->getFinisherId($mAdvActiveVehicle->workflow_id);                                 // Get Finisher using Trait
            $refGetFinisher = collect(DB::select($getFinisherQuery))->first();
            if ($refGetFinisher->role_id != $req->roleId) {
                return responseMsgs(false, " Access Forbidden", "");
            }
            DB::beginTransaction();
            // Approval
            if ($req->status == 1) {
                $typology = $mAdvActiveVehicle->typology;
                $zone = $mAdvActiveVehicle->zone;
                if ($zone == NULL) {
                    throw new Exception("Zone Not Selected !!!");
                }
                $mCalculateRate = new CalculateRate();
                $amount = $mCalculateRate->getMovableVehiclePayment($typology, $zone, $mAdvActiveVehicle->license_from, $mAdvActiveVehicle->license_to);
                $payment_amount = ['payment_amount' => $amount];
                $req->request->add($payment_amount);

                // $mCalculateRate = new CalculateRate;
                // $generatedId = $mCalculateRate->generateId($req->bearerToken(), $this->_paramId, $mAdvActiveVehicle->ulb_id); // Generate Application No
                $idGeneration = new PrefixIdGenerator($this->_paramId, $mAdvActiveVehicle->ulb_id);
                $generatedId = $idGeneration->generate();
                // approved Vehicle Application replication
                if ($mAdvActiveVehicle->renew_no == NULL) {
                    $approvedVehicle = $mAdvActiveVehicle->replicate();
                    $approvedVehicle->setTable('adv_vehicles');
                    $temp_id = $approvedVehicle->id = $mAdvActiveVehicle->id;
                    $approvedVehicle->payment_amount = round($req->payment_amount);
                    $approvedVehicle->demand_amount = $req->payment_amount;
                    $approvedVehicle->license_no = $generatedId;
                    $approvedVehicle->approve_date = Carbon::now();
                    $approvedVehicle->zone = $zone;
                    $approvedVehicle->save();

                    // Save in vehicle Advertisement Renewal
                    $approvedVehicle = $mAdvActiveVehicle->replicate();
                    $approvedVehicle->approve_date = Carbon::now();
                    $approvedVehicle->setTable('adv_vehicle_renewals');
                    $approvedVehicle->license_no = $generatedId;
                    $approvedVehicle->id = $temp_id;
                    $approvedVehicle->zone = $zone;
                    $approvedVehicle->save();


                    $mAdvActiveVehicle->delete();

                    // Update in adv_vehicles (last_renewal_id)

                    DB::table('adv_vehicles')
                        ->where('id', $temp_id)
                        ->update(['last_renewal_id' => $approvedVehicle->id]);

                    $msg = "Application Successfully Approved !!";
                } else {
                    //  Renewal Case
                    // Vehicle Advert Application replication
                    $license_no = $mAdvActiveVehicle->license_no;
                    AdvVehicle::where('license_no', $license_no)->delete();

                    $approvedVehicle = $mAdvActiveVehicle->replicate();
                    $approvedVehicle->setTable('adv_vehicles');
                    $temp_id = $approvedVehicle->id = $mAdvActiveVehicle->id;
                    $approvedVehicle->payment_amount = round($req->payment_amount);
                    $approvedVehicle->demand_amount = $req->payment_amount;
                    $approvedVehicle->approve_date = Carbon::now();
                    $approvedVehicle->save();

                    // Save in Vehicle Advertisement Renewal
                    $approvedVehicle = $mAdvActiveVehicle->replicate();
                    $approvedVehicle->approve_date = Carbon::now();
                    $approvedVehicle->setTable('adv_vehicle_renewals');
                    $approvedVehicle->id = $temp_id;
                    $approvedVehicle->save();

                    $mAdvActiveVehicle->delete();

                    // Update in adv_vehicles (last_renewal_id)
                    DB::table('adv_vehicles')
                        ->where('id', $temp_id)
                        ->update(['last_renewal_id' => $approvedVehicle->id]);
                    $msg = "Application Successfully Renewal !!";
                }
            }
            // Rejection
            if ($req->status == 0) {
                $payment_amount = ['payment_amount' => 0];
                $req->request->add($payment_amount);

                // Vehicles advertisement Application replication
                $rejectedVehicle = $mAdvActiveVehicle->replicate();
                $rejectedVehicle->setTable('adv_rejected_vehicles');
                $rejectedVehicle->id = $mAdvActiveVehicle->id;
                $rejectedVehicle->rejected_date = Carbon::now();
                $rejectedVehicle->remarks = $req->comment;
                $rejectedVehicle->save();
                $mAdvActiveVehicle->delete();
                $msg = "Application Successfully Rejected !!";
            }
            DB::commit();

            return responseMsgs(true, $msg, "", '011111', 01, responseTime(), 'POST', $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), '', "050315", "1.0", "", "POST", "");
        }
    }

    /**
     * | Approve Application List for Citzen
     * | @param Request $req
     * | Function - 17
     * | API - 16
     * | Query Cost - 33.02 ms
     * | Max Records - 3
     */
    public function listApproved(Request $req)
    {
        try {
            // Variable Initialization
            $citizenId = $req->auth['id'];
            $userType = $req->auth['user_type'];
            $mAdvVehicle = new AdvVehicle();
            $applications = $mAdvVehicle->listApproved($citizenId, $userType);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            return responseMsgs(true, "Approved Application List", $data1, "050316", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050316", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Reject Application List for Citizen
     * | @param Request $req
     * | Function - 18
     * | API - 17
     * | Query Cost - 23 ms
     * | Max Records - 2
     */
    public function listRejected(Request $req)
    {
        try {
            // Variable Initialization
            $citizenId = $req->auth['id'];
            $mAdvRejectedVehicle = new AdvRejectedVehicle();
            $applications = $mAdvRejectedVehicle->listRejected($citizenId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            return responseMsgs(true, "Approved Application List", $data1, "050317", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050317", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Get Applied Applications by Logged In JSK
     * | Function - 19
     * | API - 18
     */
    public function getJSKApplications(Request $req)
    {
        try {
            // Variable Initialization

            $userId = $req->auth['id'];
            $mAdvActiveVehicle = new AdvActiveVehicle();
            $applications = $mAdvActiveVehicle->getJSKApplications($userId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            return responseMsgs(true, "Applied Applications", $data1, "050318", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050318", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Approve Application List for JSK
     * | @param Request $req
     * | Function - 20
     * | API - 19
     */
    public function listjskApprovedApplication(Request $request)
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
            //$userId = $request->auth['id'];
            $mAdvVehicle = new AdvVehicle();
            $applications = $mAdvVehicle->listjskApprovedApplication();
            if ($key && $parameter) {
                $msg = "Self Advertisement application details according to $key";
                switch ($key) {
                    case 'mobileNo':
                        $applications = $applications->where('adv_vehicles.mobile_no', 'LIKE', "%$parameter%");
                        break;
                    case 'applicantName':
                        $applications = $applications->where('adv_vehicles.applicant', 'LIKE', "%$parameter%");
                        break;
                    case 'applicationNo':
                        $applications = $applications->where('adv_vehicles.application_no', 'LIKE', "%$parameter%");
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
     * | Function - 21
     * | API - 20
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
            $mAdvRejectedVehicle = new AdvRejectedVehicle();
            $applications = $mAdvRejectedVehicle->listJskRejectedApplication();
            if ($key && $parameter) {
                $msg = "Self Advertisement application details according to $key";
                switch ($key) {
                    case 'mobileNo':
                        $applications = $applications->where('adv_rejected_vehicles.mobile_no', 'LIKE', "%$parameter%");
                        break;
                    case 'applicantName':
                        $applications = $applications->where('adv_rejected_vehicles.applicant', 'LIKE', "%$parameter%");
                        break;
                    case 'applicationNo':
                        $applications = $applications->where('adv_rejected_vehicles.application_no', 'LIKE', "%$parameter%");
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
            $mAdvActiveVehicle = new AdvActiveVehicle();
            $applications = $mAdvActiveVehicle->listAppliedApplicationsJsk();
            if ($key && $parameter) {
                $msg = "Self Advertisement application details according to $key";
                switch ($key) {
                    case 'mobileNo':
                        $applications = $applications->where('adv_active_vehicles.mobile_no', 'LIKE', "%$parameter%");
                        break;
                    case 'applicantName':
                        $applications = $applications->where('adv_active_vehicles.applicant', 'LIKE', "%$parameter%");
                        break;
                    case 'applicationNo':
                        $applications = $applications->where('adv_active_vehicles.application_no', 'LIKE', "%$parameter%");
                        break;
                    default:
                        throw new Exception("Invalid Data");
                }
            } elseif ($request->dateFrom && $request->dateUpto != null) {
                $applications = $applications->whereBetween('adv_active_vehicles.application_date', [$request->dateFrom, $request->dateUpto]);
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
     * | Function - 22
     * | API - 21
     */
    public function generatePaymentOrderId(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return $validator->errors();
        }
        try {
            // Variable Initialization
            $mAdvVehicle = AdvVehicle::find($req->id);
            $reqData = [
                "id" => $mAdvVehicle->id,
                'amount' => $mAdvVehicle->payment_amount,
                'workflowId' => $mAdvVehicle->workflow_id,
                'ulbId' => $mAdvVehicle->ulb_id,
                'departmentId' => Config::get('workflow-constants.ADVERTISMENT_MODULE_ID'),
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

            $data->name = $mAdvVehicle->applicant;
            $data->email = $mAdvVehicle->email;
            $data->contact = $mAdvVehicle->mobile_no;
            $data->type = "Movable Vehicles";

            return responseMsgs(true, "Payment OrderId Generated Successfully !!!", $data, "050321", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050321", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * Summary of application Details For Payment
     * @param Request $req
     * @return void
     * | Function - 23
     * | API - 22
     */
    public function getApplicationDetailsForPayment(Request $req)
    {
        $req->validate([
            'applicationId' => 'required|integer',
        ]);
        try {
            // Variable Initialization
            $mAdvVehicle = new AdvVehicle();

            if ($req->applicationId) {
                $data = $mAdvVehicle->detailsForPayments($req->applicationId);
            }

            if (!$data)
                throw new Exception("Application Not Found");

            $data['type'] = "Movable Vehicles";

            return responseMsgs(true, 'Data Fetched',  $data, "050322", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050322", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Payment Via Cash
     * | Function - 24
     * | API - 23
     */
    public function vehiclePayment(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|string',
            'status' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable Initialization
            $user = Auth()->user();
            $todayDate = Carbon::now();
            $mAdvVehicle = new AdvVehicle();
            $todayDate = Carbon::now();
            $mAdvMarTransaction = new AdvMarTransaction();
            //  $req->all();
            DB::beginTransaction();
            $data = $mAdvVehicle->offlinePayment($req);
            $appDetails = AdvVehicle::find($req->applicationId);

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
                return responseMsgs(true, "Payment Successfully !!", ['status' => true, 'transactionNo' => $data['payment_id'], 'workflowId' =>  $appDetails->workflow_id], "050323", "1.0", responseTime(), 'POST', $req->deviceId ?? "");
            } else {
                return responseMsgs(false, "Payment Rejected !!", '', "050323", "1.0", "", 'POST', $req->deviceId ?? "");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050323", "1.0", "", "POST", $req->deviceId ?? "");
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




    /**
     * | Entry Cheque or DD
     * | Function - 25
     * | API - 24
     */
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
            // Variable Initialization
            $wfId = AdvVehicle::find($req->applicationId)->workflow_id;
            $mAdvCheckDtl = new AdvChequeDtl();
            $workflowId = ['workflowId' => $wfId];
            $req->request->add($workflowId);
            $transNo = $mAdvCheckDtl->entryChequeDd($req);                     // Entry Cheque And DD in Model

            return responseMsgs(true, "Check Entry Successfully !!", ['status' => true, 'TransactionNo' => $transNo], "050324", "1.0", responseTime(), 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050324", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Clear or bounce Cheque or DD
     * | Function - 26
     * | API - 25
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
            // Variable Initialization
            $mAdvCheckDtl = new AdvChequeDtl();
            $mAdvMarTransaction = new AdvMarTransaction();
            DB::beginTransaction();
            $data = $mAdvCheckDtl->clearOrBounceCheque($req);
            $appDetails = AdvVehicle::find($req->applicationId);
            $mAdvMarTransaction->addTransaction($appDetails, $this->_moduleIds, "Advertisement", "Cheque/DD");
            DB::commit();
            if ($req->status == '1' && $data['status'] == 1) {
                return responseMsgs(true, "Payment Successfully !!", ['status' => true, 'transactionNo' => $data['payment_id'], 'workflowId' => $appDetails->workflow_id], "050325", "1.0", responseTime(), 'POST', $req->deviceId ?? "");
            } else {
                return responseMsgs(false, "Payment Rejected !!", '', "050325", "1.0", "", 'POST', $req->deviceId ?? "");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050325", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Entry Zone of the Application 
     * | Function - 27
     * | API - 26
     */
    public function entryZone(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|integer',
            'zone' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable Initialization
            $mAdvActiveVehicle = new AdvActiveVehicle();
            $status = $mAdvActiveVehicle->entryZone($req);                   // Entry Zone In Model

            if ($status == '1') {
                return responseMsgs(true, 'Data Fetched',  "Zone Added Successfully", "050326", "1.0", responseTime(), "POST", $req->deviceId);
            } else {
                throw new Exception("Zone Not Added !!!");
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050326", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Verify Single Application Approve or reject
     * | Function - 28
     * | API - 27
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
            // Variable Initialization
            $mWfDocument = new WfActiveDocument();
            $mAdvActiveVehicle = new AdvActiveVehicle();
            $mWfRoleusermap = new WfRoleusermap();
            $wfDocId = $req->id;
            $userId = $req->auth['id'];
            $applicationId = $req->applicationId;

            $wfLevel = Config::get('constants.SELF-LABEL');
            // Derivative Assigments
            $appDetails = $mAdvActiveVehicle->getVehicleNo($applicationId);

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
            return responseMsgs(true, $req->docStatus . " Successfully", "", "050327", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            DB::connection('pgsql_masters')->rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050327", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Check if the Document is Fully Verified or Not (4.1)
     * | Function - 29
     */
    public function ifFullDocVerified($applicationId)
    {
        $mAdvActiveVehicle = new AdvActiveVehicle();
        $mWfActiveDocument = new WfActiveDocument();
        $mAdvActiveVehicle = $mAdvActiveVehicle->getVehicleNo($applicationId);                      // Get Application Details
        $refReq = [
            'activeId' => $applicationId,
            'workflowId' => $mAdvActiveVehicle->workflow_id,
            'moduleId' =>  $this->_moduleIds
        ];
        $req = new Request($refReq);
        $refDocList = $mWfActiveDocument->getDocsByActiveId($req);
        $totalApproveDoc = $refDocList->count();
        $ifAdvDocUnverified = $refDocList->contains('verify_status', 0);
        $citizenId = $mAdvActiveVehicle->citizen_id;
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
     * | Function - 30
     * | API - 28
     */
    public function backToCitizen(Request $req)
    {
        $req->validate([
            'applicationId' => "required"
        ]);
        try {
            // Variable Initialization
            $redis = Redis::connection();
            $mAdvActiveVehicle = AdvActiveVehicle::find($req->applicationId);
            if ($mAdvActiveVehicle->doc_verify_status == 1)
                throw new Exception("All Documents Are Approved, So Application is Not BTC !!!");
            if ($mAdvActiveVehicle->doc_upload_status == 1)
                throw new Exception("No Any Document Rejected, So Application is Not BTC !!!");

            $workflowId = $mAdvActiveVehicle->workflow_id;
            $backId = json_decode(Redis::get('workflow_initiator_' . $workflowId));
            if (!$backId) {
                $backId = WfWorkflowrolemap::where('workflow_id', $workflowId)
                    ->where('is_initiator', true)
                    ->first();
                $redis->set('workflow_initiator_' . $workflowId, json_encode($backId));
            }

            $mAdvActiveVehicle->current_roles = $backId->wf_role_id;
            $mAdvActiveVehicle->btc_date =  Carbon::now()->format('Y-m-d');
            $mAdvActiveVehicle->remarks = $req->comment;
            $mAdvActiveVehicle->parked = 1;
            $mAdvActiveVehicle->save();

            $metaReqs['moduleId'] = $this->_moduleIds;
            $metaReqs['workflowId'] = $mAdvActiveVehicle->workflow_id;
            $metaReqs['refTableDotId'] = "adv_active_vehicles.id";
            $metaReqs['refTableIdValue'] = $req->applicationId;
            $metaReqs['verificationStatus'] = $req->verificationStatus;
            $metaReqs['senderRoleId'] = $req->currentRoleId;
            $req->request->add($metaReqs);

            $req->request->add($metaReqs);
            $track = new WorkflowTrack();
            $track->saveTrack($req);

            return responseMsgs(true, "Successfully Done", "", "", '050328', '01', responseTime(), 'Post', '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050328", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Back To Citizen Inbox
     * | Function - 31
     * | API - 29
     * | Query Cost - 43.56 ms
     * | Max Records - 2
     */
    public function listBtcInbox(Request $req)
    {
        try {
            // Variable Initialization
            // $auth = auth()->user();
            $userId = $req->auth['id'];
            $ulbId = $req->auth['ulb_id'] ?? null;
            $wardId = $this->getWardByUserId($userId);

            $occupiedWards = collect($wardId)->map(function ($ward) {                               // Get Occupied Ward of the User
                return $ward->ward_id;
            });

            $roles = $this->getRoleIdByUserId($userId);

            $roleId = collect($roles)->map(function ($role) {                                       // get Roles of the user
                return $role->wf_role_id;
            });

            $mAdvActiveVehicle = new AdvActiveVehicle();
            $btcList = $mAdvActiveVehicle->getVehicleList($ulbId)
                ->whereIn('adv_active_vehicles.current_roles', $roleId)
                // ->whereIn('a.ward_mstr_id', $occupiedWards)
                ->where('parked', true)
                ->orderByDesc('adv_active_vehicles.id');
            // ->get();

            if (trim($req->key))
                $btcList =  searchFilter($btcList, $req);
            $list = paginator($btcList, $req);

            return responseMsgs(true, "BTC Inbox List", $list, "050329", 1.0, responseTime(), "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050329", 1.0, "271ms", "POST", "", "");
        }
    }

    /**
     * | Check all documents of apllication uploaded or not
     * | Function - 32
     */
    public function checkFullUpload($applicationId)
    {
        $docCode = $this->_docCode;
        $mWfActiveDocument = new WfActiveDocument();
        $mAdvActiveVehicle = new AdvActiveVehicle();
        $moduleId = $this->_moduleIds;
        $mAdvActiveVehicle = $mAdvActiveVehicle->getVehicleNo($applicationId);
        $citizenId = $mAdvActiveVehicle->citizen_id;
        $totalRequireDocs = $mWfActiveDocument->totalNoOfDocs($docCode, $citizenId);
        $appDetails = AdvActiveVehicle::find($applicationId);
        $totalUploadedDocs = $mWfActiveDocument->totalUploadedDocs($applicationId, $appDetails->workflow_id, $moduleId);
        if ($totalRequireDocs == $totalUploadedDocs) {
            $appDetails->doc_upload_status = '1';
            $appDetails->doc_verify_status = '0';
            // $appDetails->doc_verify_status = '1';
            $appDetails->parked = NULL;
            $appDetails->save();
        } else {
            $appDetails->doc_upload_status = '0';
            $appDetails->doc_verify_status = '0';
            $appDetails->save();
        }
    }

    /**
     * | Reupload Rejected Documents
     * | Function - 33
     * | API - 30
     */
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
            $mMarActiveLodge = new AdvActiveVehicle();
            $Image                   = $req->image;
            $docId                   = $req->id;
            DB::beginTransaction();
            DB::connection('pgsql_masters')->beginTransaction();
            $appId = $mMarActiveLodge->reuploadDocument($req, $Image, $docId);
            $this->checkFullUpload($appId);
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

    public function reuploadDocumentveh($req)
    {
        try {
            #initiatialise variable 
            $Image                   = $req->image;
            $docId                   = $req->id;
            $data = [];
            $docUpload = new DocumentUpload;
            $relativePath =  Config::get('constants.VEHICLE_ADVET.RELATIVE_PATH');
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
     * | Get Application Between Two Dates
     * | Function - 34
     * | API - 31
     */
    public function getApplicationBetweenDate(Request $req)
    {
        if ($req->auth['ulb_id'] < 1)
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050331", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = $req->auth['ulb_id'];
        $validator = Validator::make($req->all(), [
            'applicationType' => 'required|in:New Apply,Renew',
            'applicationStatus' => 'required|in:All,Approve,Reject',
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
            $mAdvVehicle = new AdvVehicle();
            $approveList = $mAdvVehicle->approveListForReport();

            $approveList = $approveList->where('application_type', $req->applicationType)->where('ulb_id', $ulbId)
                ->whereBetween('application_date', [$req->dateFrom, $req->dateUpto]);

            $mAdvActiveVehicle = new AdvActiveVehicle();
            $pendingList = $mAdvActiveVehicle->pendingListForReport();

            $pendingList = $pendingList->where('application_type', $req->applicationType)->where('ulb_id', $ulbId)
                ->whereBetween('application_date', [$req->dateFrom, $req->dateUpto]);

            $mAdvRejectedVehicle = new AdvRejectedVehicle();
            $rejectList = $mAdvRejectedVehicle->rejectListForReport();

            $rejectList = $rejectList->where('application_type', $req->applicationType)->where('ulb_id', $ulbId)
                ->whereBetween('application_date', [$req->dateFrom, $req->dateUpto]);

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
            return responseMsgs(true, "Application Fetched Successfully", $data, "050331", 1.0, responseTime(), "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, "Application Not Fetched", $e->getMessage(), "050331", 1.0, "271ms", "POST", "", "");
        }
    }

    /**
     * | COllection From New or Renew Application
     * | Function - 35
     * | API - 32
     */
    // public function paymentCollection(Request $req)
    // {
    //     if ($req->auth['ulb_id'] < 1)
    //         return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050332", 1.0, "271ms", "POST", "", "");
    //     else
    //         $ulbId = $req->auth['ulb_id'];

    //     $validator = Validator::make($req->all(), [
    //         'applicationType' => 'required|in:New Apply,Renew',
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

    //         $approveList = DB::table('adv_vehicle_renewals')
    //             ->select('id', 'application_no', 'applicant', 'application_date', 'application_type', DB::raw("'Approve' as application_status"), 'payment_amount', 'payment_date', 'payment_mode')->where('application_type', $req->applicationType)->where('payment_status', '1')->where('ulb_id', $ulbId)
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

    //         return responseMsgs(true, "Application Fetched Successfully", $data, "050332", 1.0, responseTime(), "POST", "", "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, "Application Not Fetched", $e->getMessage(), "050332", 1.0, "271ms", "POST", "", "");
    //     }
    // }

    public function paymentCollection(Request $req)
    {
        if ($req->auth['ulb_id'] < 1)
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050332", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = $req->auth['ulb_id'];
        $userType = $req->auth['user_type'];

        $validator = Validator::make($req->all(), [
            'applicationType' => 'nullable',
            'dateFrom' => 'required|date_format:Y-m-d',
            'dateUpto' => 'required|date_format:Y-m-d',
            'perPage' => 'required|integer',
            'payMode' => 'nullable',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            $movablevehicleWorkflow = Config::get('workflow-constants.MOVABLE-VEHICLE');
            $approveListQuery = DB::table('adv_vehicle_renewals')
                ->select(
                    'adv_vehicle_renewals.id',
                    'adv_vehicle_renewals.application_no',
                    'adv_vehicle_renewals.applicant',
                    'adv_vehicle_renewals.application_date',
                    'adv_vehicle_renewals.application_type',
                    DB::raw("'Approve' as application_status"),
                    'adv_vehicle_renewals.payment_amount',
                    'adv_vehicle_renewals.payment_date',
                    'adv_vehicle_renewals.payment_mode',
                    'adv_vehicle_renewals.entity_name'
                )
                ->leftjoin('adv_mar_transactions', 'adv_mar_transactions.application_id', 'adv_vehicle_renewals.id')
                ->where('adv_vehicle_renewals.payment_status', 1)
                ->where('adv_vehicle_renewals.ulb_id', $ulbId)
                ->where('adv_mar_transactions.workflow_id', $movablevehicleWorkflow)
                ->where('adv_mar_transactions.status', 1)
                ->whereBetween('adv_vehicle_renewals.payment_date', [$req->dateFrom, $req->dateUpto]);
            // Apply payment mode filter
            if ($req->payMode == 'All' && $req->payMode != null) {
                if ($req->payMode == 'Cheque/DD') {
                    $approveListQuery->whereIn('adv_vehicle_renewals.payment_mode', ['CHEQUE', 'DD']);
                } else {
                    $approveListQuery->where('adv_vehicle_renewals.payment_mode', $req->payMode);
                }
            }
            if ($req->applicationType != null) {
                $approveListQuery->where('adv_vehicle_renewals.application_type',  $req->applicationType);
            }
            $paginator = $approveListQuery->paginate($req->perPage);
            // Clone the query for counts and sums
            $approveListForCounts = clone $approveListQuery;
            $approveListForSums = clone $approveListQuery;
            // Count of transactions
            $cashCount = (clone $approveListForCounts)->where('adv_vehicle_renewals.payment_mode', 'CASH')->count();
            $ddCount = (clone $approveListForCounts)->where('adv_vehicle_renewals.payment_mode', 'DD')->count();
            $chequeCount = (clone $approveListForCounts)->where('adv_vehicle_renewals.payment_mode', 'CHEQUE')->count();
            $onlineCount = (clone $approveListForCounts)->where('adv_vehicle_renewals.payment_mode', 'ONLINE')->count();

            // Sum of transactions
            $cashPayment = (clone $approveListForSums)->where('adv_vehicle_renewals.payment_mode', 'CASH')->sum('payment_amount');
            $ddPayment = (clone $approveListForSums)->where('adv_vehicle_renewals.payment_mode', 'DD')->sum('payment_amount');
            $chequePayment = (clone $approveListForSums)->where('adv_vehicle_renewals.payment_mode', 'CHEQUE')->sum('payment_amount');
            $onlinePayment = (clone $approveListForSums)->where('adv_vehicle_renewals.payment_mode', 'ONLINE')->sum('payment_amount');

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


            return responseMsgs(true, "Application Fetched Successfully", $response, "050332", 1.0, responseTime(), "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, "Application Not Fetched", $e->getMessage(), "050332", 1.0, "271ms", "POST", "", "");
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
            $mAdvActiveSelfadvertisement = new AdvVehicle();
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
            $mAdvActiveRegistration = new AdvVehicle();
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

            $mMarActiveLodge = new AdvActiveVehicle();
            $btcList = $mMarActiveLodge->getLodgeListJsk($ulbId)
                ->where('parked', 1)
                ->orderByDesc('adv_active_vehicles.id');
            // ->get();
            if ($key && $parameter) {
                $msg = "Self Advertisement application details according to $key";
                switch ($key) {
                    case 'mobileNo':
                        $applications = $btcList->where('adv_active_vehicles.mobile_no', 'LIKE', "%$parameter%");
                        break;
                    case 'applicantName':
                        $applications = $btcList->where('adv_active_vehicles.applicant', 'LIKE', "%$parameter%");
                        break;
                    case 'applicationNo':
                        $applications = $btcList->where('adv_active_vehicles.application_no', 'LIKE', "%$parameter%");
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
            $mAdvActiveSelfadvertisement = new AdvActiveVehicle();
            $mtransaction = new AdvMarTransaction();

            // Fetch details from the model
            $data = $mAdvActiveSelfadvertisement->getDetailsByIdjsk($applicationId)->first();

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
            $mAdvAgency             = new AdvActiveVehicle();
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
            $mMarActiveLodge = AdvActiveVehicle::find($request->applicationId);
            $mMarActiveLodge->parked = null;
            $mMarActiveLodge->save();
            return responseMsgs(true, "Successfully Forwarded The Application!!", "", "050708", "1.0", responseTime(), "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050708", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }

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
            $mAdvVehicle = new AdvVehicle();
            $mtransaction = new AdvMarTransaction();

            // Fetch details from the model
            $data = $this->Repository->getAllById($applicationId);   // Repository function to get Advertiesment Details

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
    /**
     * | Get Application Display Wise
     * | Function - 35
     * | API - 32
     */
    public function getApplicationDisplayWise(Request $req)
    {
        if ($req->auth['ulb_id'] < 1)
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050432", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = $req->auth['ulb_id'];

        $validator = Validator::make($req->all(), [
            'applicationType' => 'nullable',
            'applicationStatus' => 'nullable',
            'entityWard' => 'nullable|integer',
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

            $mAdvVehicle = new AdvVehicle();
            $approveList = $mAdvVehicle->approveListForReport();

            $approveList = $approveList->where('ulb_id', $ulbId)
                ->whereBetween('application_date', [$req->dateFrom, $req->dateUpto]);
            if ($req->entityWard != null) {
                $approveList = $approveList->where('ward_id', $req->entityWard);
            }
            if ($req->applicationType != null) {
                $approveList = $approveList->where('application_type', $req->applicationType);
            }
            if ($req->displayType != null) {
                $approveList = $approveList->where('display_type', $req->displayType);
            }

            $mAdvActiveVehicle = new AdvActiveVehicle();
            $pendingList = $mAdvActiveVehicle->pendingListForReport();

            $pendingList = $pendingList->where('ulb_id', $ulbId)
                ->whereBetween('application_date', [$req->dateFrom, $req->dateUpto]);
            if ($req->entityWard != null) {
                $pendingList = $pendingList->where('ward_id', $req->entityWard);
            }
            if ($req->applicationType != null) {
                $pendingList = $pendingList->where('application_type', $req->applicationType);
            }
            if ($req->displayType != null) {
                $pendingList = $pendingList->where('display_type', $req->displayType);
            }

            $mAdvRejectedVehicle = new AdvRejectedVehicle();
            $rejectList = $mAdvRejectedVehicle->rejectListForReport();

            $rejectList = $rejectList->where('ulb_id', $ulbId)
                ->whereBetween('application_date', [$req->dateFrom, $req->dateUpto]);
            if ($req->entityWard != null) {
                $rejectList = $rejectList->where('ward_id', $req->entityWard);
            }
            if ($req->applicationType != null) {
                $rejectList = $rejectList->where('application_type', $req->applicationType);
            }
            if ($req->displayType != null) {
                $rejectList = $rejectList->where('display_type', $req->displayType);
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

            return responseMsgs(true, "Application Fetched Successfully", $data, "050432", 1.0, responseTime(), "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, "Application Not Fetched", $e->getMessage(), "050432", 1.0, "271ms", "POST", "", "");
        }
    }
}
