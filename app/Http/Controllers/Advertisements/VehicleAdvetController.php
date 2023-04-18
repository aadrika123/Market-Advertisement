<?php

namespace App\Http\Controllers\Advertisements;

use App\BLL\Advert\CalculateRate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicles\RenewalRequest;
use App\Http\Requests\Vehicles\StoreRequest;
use App\Models\Advertisements\AdvActiveVehicle;
use App\Models\Advertisements\AdvChequeDtl;
use App\Models\Advertisements\AdvVehicle;
use App\Models\Advertisements\AdvRejectedVehicle;
use App\Models\Advertisements\WfActiveDocument;
use App\Models\Workflows\WfRoleusermap;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\AdvDetailsTraits;
use Illuminate\Support\Facades\DB;
use App\Models\Workflows\WorkflowTrack;
use App\Models\Workflows\WfWardUser;
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
     * | Status - Closed, By Bikash on 15 Apr 2023,  Total no. of lines - 1349, Total Function - 33, Total API - 30
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
    public function __construct(iSelfAdvetRepo $self_repo)
    {
        $this->_modelObj = new AdvActiveVehicle();
        $this->_workflowIds = Config::get('workflow-constants.MOVABLE_VEHICLE_WORKFLOWS');
        $this->_moduleIds = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
        $this->_docCode = Config::get('workflow-constants.MOVABLE_VEHICLE_DOC_CODE');
        $this->_tempParamId = Config::get('workflow-constants.TEMP_VCL_ID');
        $this->_paramId = Config::get('workflow-constants.VCL_ID');
        $this->_baseUrl = Config::get('constants.BASE_URL');
        $this->Repository = $self_repo;
    }

    /**
     * | Apply for new document
     * | Function - 01
     * | API - 01
     */
    public function addNew(StoreRequest $req)
    {
        try {
            // Variable Initialization
            $startTime = microtime(true);
            $advVehicle = new AdvActiveVehicle();
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
            $applicationNo = $advVehicle->addNew($req);                             // Apply Vehicle Application 
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Successfully Applied the Application !!", ["status" => true, "ApplicationNo" => $applicationNo], "050301", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
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
            $startTime = microtime(true);
            $mAdvVehicle = new AdvVehicle();
            $details = $mAdvVehicle->applicationDetailsForRenew($req->applicationId);
            if (!$details)
                throw new Exception("Application Not Found !!!");

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Application Fetched !!!", remove_null($details), "050302", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
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
            $startTime = microtime(true);
            $advVehicle = new AdvActiveVehicle();
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
            $applicationNo = $advVehicle->renewalApplication($req);               // Renewal Vehicle Application
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Successfully Applied the Application !!", ["status" => true, "ApplicationNo" => $applicationNo], "050303", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050303", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Inbox List
     * | @param Request $req
     * | Function - 04
     * | API - 04
     */
    public function listInbox(Request $req)
    {
        try {
            // Variable Initialization
            $startTime = microtime(true);
            $mvehicleAdvets = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $inboxList = $mvehicleAdvets->listInbox($roleIds);                          // <----- get Inbox list

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Inbox Applications", remove_null($inboxList->toArray()), "050304", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050304", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Outbox List
     * | Function - 05
     * | API - 05
     */
    public function listOutbox(Request $req)
    {
        try {
            // Variable Initialization
            $startTime = microtime(true);
            $mvehicleAdvets = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $outboxList = $mvehicleAdvets->listOutbox($roleIds);                       // <----- Get Outbox list
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Outbox Lists", remove_null($outboxList->toArray()), "050305", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
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
            $startTime = microtime(true);
            $mvehicleAdvets = new AdvActiveVehicle();
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
            $req->request->add($metaReqs);
            $forwardBackward = $this->getRoleDetails($req);
            $fullDetailsData['roleDetails'] = collect($forwardBackward)['original']['data'];

            $fullDetailsData = remove_null($fullDetailsData);

            $fullDetailsData['application_no'] = $data['application_no'];
            $fullDetailsData['apply_date'] = $data['created_at'];
            $fullDetailsData['zone'] = $data['zone'];
            $fullDetailsData['doc_verify_status'] = $data['doc_verify_status'];
            if (isset($data['payment_amount'])) {
                $fullDetailsData['payment_amount'] = $data['payment_amount'];
            }
            $fullDetailsData['timelineData'] = collect($req);

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, 'Data Fetched', $fullDetailsData, "050306", "1.0", "$executionTime Sec", "POST", $req->deviceId);
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
     * | Function - 08
     * | API - 07
     */
    public function listAppliedApplications(Request $req)
    {
        try {
            // Variable Initialization
            $startTime = microtime(true);
            $citizenId = authUser()->id;
            $mvehicleAdvets = new AdvActiveVehicle();
            $applications = $mvehicleAdvets->listAppliedApplications($citizenId);

            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Applied Applications", $data1, "050307", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
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
            $startTime = microtime(true);

            $userId = auth()->user()->id;
            $applicationId = $request->applicationId;
            $data = AdvActiveVehicle::find($applicationId);
            $data->is_escalate = $request->escalateStatus;
            $data->escalate_by = $userId;
            $data->save();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, $request->escalateStatus == 1 ? 'Movable Vechicle is Escalated' : "Movable Vechicle is removed from Escalated", '', "050308", "1.0", " $executionTime Sec", "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050308", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }

    /**
     * | Escalated Application List
     * | Function - 10
     * | API - 09
     */
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

            $advData = $this->Repository->specialVehicleInbox($this->_workflowIds)                      // Repository function to get Advertiesment Details
                ->where('is_escalate', 1)
                ->where('adv_active_vehicles.ulb_id', $ulbId)
                // ->whereIn('ward_mstr_id', $wardId)
                ->get();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Data Fetched", remove_null($advData), "050309", "1.0", "$executionTime Sec", "POST", "");
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
            $startTime = microtime(true);
            $mAdvActiveVehicle = AdvActiveVehicle::find($request->applicationId);
            if ($mAdvActiveVehicle->doc_verify_status == '0')
                throw new Exception("Please Verify All Documents To Forward The Application !!!");
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
            $track->saveTrack($request);
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Successfully Forwarded The Application!!", "", "050310", "1.0", "$executionTime Sec", "POST", $request->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
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
            $startTime = microtime(true);

            $userId = authUser()->id;
            $userType = authUser()->user_type;
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
            // Save On Workflow Track For Level Independent
            $workflowTrack->saveTrack($request);
            DB::commit();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "You Have Commented Successfully!!", ['Comment' => $request->comment], "050311", "1.0", "$executionTime Sec", "POST", "");
        } catch (Exception $e) {
            DB::rollBack();
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
        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        if ($req->applicationId && $req->type) {
            $data = $mWfActiveDocument->uploadDocumentsViewById($req->applicationId, $this->_workflowIds);
        } else {
            throw new Exception("Required Application Id And Application Type ");
        }
        $data1['data'] = $data;
        return $data1;
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
        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        $data = $mWfActiveDocument->uploadedActiveDocumentsViewById($req->applicationId, $this->_workflowIds);
        $data1['data'] = $data;
        return $data1;
    }

    /**
     * | Workflow View Uploaded Document by application ID
     * | Function - 15
     * | API - 14
     */
    public function viewDocumentsOnWorkflow(Request $req)
    {
        // Variable Initialization
        $startTime = microtime(true);

        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        if ($req->applicationId) {
            $data = $mWfActiveDocument->uploadDocumentsViewById($req->applicationId, $this->_workflowIds);
        }
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        return responseMsgs(true, "Data Fetched", remove_null($data), "050314", "1.0", "$executionTime Sec", "POST", "");
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
            'status' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable Initialization
            $startTime = microtime(true);
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
                if ($zone === NULL) {
                    throw new Exception("Zone Not Selected !!!");
                }
                $mCalculateRate = new CalculateRate();
                $amount = $mCalculateRate->getMovableVehiclePayment($typology, $zone, $mAdvActiveVehicle->license_from, $mAdvActiveVehicle->license_to);
                $payment_amount = ['payment_amount' => $amount];
                $req->request->add($payment_amount);

                $mCalculateRate = new CalculateRate;
                $generatedId = $mCalculateRate->generateId($req->bearerToken(), $this->_paramId, $mAdvActiveVehicle->ulb_id); // Generate Application No
                // approved Vehicle Application replication
                if ($mAdvActiveVehicle->renew_no == NULL) {
                    $approvedVehicle = $mAdvActiveVehicle->replicate();
                    $approvedVehicle->setTable('adv_vehicles');
                    $temp_id = $approvedVehicle->id = $mAdvActiveVehicle->id;
                    $approvedVehicle->payment_amount = $req->payment_amount;
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
                    $approvedVehicle->payment_amount = $req->payment_amount;
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
                $rejectedVehicle->save();
                $mAdvActiveVehicle->delete();
                $msg = "Application Successfully Rejected !!";
            }
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, $msg, "", '011111', 01, "$executionTime Sec", 'POST', $req->deviceId);
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
     */
    public function listApproved(Request $req)
    {
        try {
            // Variable Initialization
            $startTime = microtime(true);

            $citizenId = authUser()->id;
            $userType = authUser()->user_type;
            $mAdvVehicle = new AdvVehicle();
            $applications = $mAdvVehicle->listApproved($citizenId, $userType);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Approved Application List", $data1, "050316", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050316", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * | Reject Application List for Citizen
     * | @param Request $req
     * | Function - 18
     * | API - 17
     */
    public function listRejected(Request $req)
    {
        try {
            // Variable Initialization
            $startTime = microtime(true);

            $citizenId = authUser()->id;
            $mAdvRejectedVehicle = new AdvRejectedVehicle();
            $applications = $mAdvRejectedVehicle->listRejected($citizenId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Approved Application List", $data1, "050317", "1.0", " $executionTime Sec", "POST", $req->deviceId ?? "");
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
            $startTime = microtime(true);

            $userId = authUser()->id;
            $mAdvActiveVehicle = new AdvActiveVehicle();
            $applications = $mAdvActiveVehicle->getJSKApplications($userId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Applied Applications", $data1, "050318", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
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
    public function listjskApprovedApplication(Request $req)
    {
        try {
            // Variable Initialization
            $startTime = microtime(true);

            $userId = authUser()->id;
            $mAdvVehicle = new AdvVehicle();
            $applications = $mAdvVehicle->listjskApprovedApplication($userId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Approved Application List", $data1, "050319", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050319", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * | Reject Application List for JSK
     * | @param Request $req
     * | Function - 21
     * | API - 20
     */
    public function listJskRejectedApplication(Request $req)
    {
        try {
            // Variable Initialization
            $startTime = microtime(true);

            $userId = authUser()->id;
            $mAdvRejectedVehicle = new AdvRejectedVehicle();
            $applications = $mAdvRejectedVehicle->listJskRejectedApplication($userId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Rejected Application List", $data1, "050320", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050320", "1.0", "", 'POST', $req->deviceId ?? "");
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
        $req->validate([
            'id' => 'required|integer',
        ]);
        try {
            // Variable Initialization
            $startTime = microtime(true);

            $mAdvVehicle = AdvVehicle::find($req->id);
            $reqData = [
                "id" => $mAdvVehicle->id,
                'amount' => $mAdvVehicle->payment_amount,
                'workflowId' => $mAdvVehicle->workflow_id,
                'ulbId' => $mAdvVehicle->ulb_id,
                'departmentId' => Config::get('workflow-constants.ADVERTISMENT_MODULE_ID'),
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

            $data->name = $mAdvVehicle->applicant;
            $data->email = $mAdvVehicle->email;
            $data->contact = $mAdvVehicle->mobile_no;
            $data->type = "Movable Vehicles";

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Payment OrderId Generated Successfully !!!", $data, "050321", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
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
            $startTime = microtime(true);
            $mAdvVehicle = new AdvVehicle();
            $workflowId = $this->_workflowIds;
            if ($req->applicationId) {
                $data = $mAdvVehicle->detailsForPayments($req->applicationId, $workflowId);
            }

            if (!$data)
                throw new Exception("Application Not Found");

            $data['type'] = "Movable Vehicles";

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, 'Data Fetched',  $data, "050322", "1.0", "$executionTime Sec", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050322", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * | Payment Via Cash
     * | Function - 24
     * | API - 23
     */
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
            // Variable Initialization
            $startTime = microtime(true);
            $mAdvVehicle = new AdvVehicle();
            DB::beginTransaction();
            $data = $mAdvVehicle->paymentByCash($req);
            DB::commit();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            if ($req->status == '1' && $data['status'] == 1) {
                return responseMsgs(true, "Payment Successfully !!", ['status' => true, 'transactionNo' => $data['payment_id'], 'workflowId' => $this->_workflowIds], "050323", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
            } else {
                return responseMsgs(false, "Payment Rejected !!", '', "050323", "1.0", "", 'POST', $req->deviceId ?? "");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050323", "1.0", "", "POST", $req->deviceId ?? "");
        }
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
            $startTime = microtime(true);
            $mAdvCheckDtl = new AdvChequeDtl();
            $workflowId = ['workflowId' => $this->_workflowIds];
            $req->request->add($workflowId);
            $transNo = $mAdvCheckDtl->entryChequeDd($req);                     // Entry Cheque And DD in Model

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Check Entry Successfully !!", ['status' => true, 'TransactionNo' => $transNo], "050324", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
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
            $startTime = microtime(true);
            $mAdvCheckDtl = new AdvChequeDtl();
            DB::beginTransaction();
            $data = $mAdvCheckDtl->clearOrBounceCheque($req);
            DB::commit();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            if ($req->status == '1' && $data['status'] == 1) {
                return responseMsgs(true, "Payment Successfully !!", ['status' => true, 'transactionNo' => $data['payment_id'], 'workflowId' => $this->_workflowIds], "050325", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
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
            $startTime = microtime(true);
            $mAdvActiveVehicle = new AdvActiveVehicle();
            $status = $mAdvActiveVehicle->entryZone($req);                   // Entry Zone In Model

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            if ($status == '1') {
                return responseMsgs(true, 'Data Fetched',  "Zone Added Successfully", "050326", "1.0", "$executionTime Sec", "POST", $req->deviceId);
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
            $startTime = microtime(true);
            $mWfDocument = new WfActiveDocument();
            $mAdvActiveVehicle = new AdvActiveVehicle();
            $mWfRoleusermap = new WfRoleusermap();
            $wfDocId = $req->id;
            $userId = authUser()->id;
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
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, $req->docStatus . " Successfully", "", "050327", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
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
            $startTime = microtime(true);
            $redis = Redis::connection();
            $mAdvActivePrivateland = AdvActiveVehicle::find($req->applicationId);

            $workflowId = $mAdvActivePrivateland->workflow_id;
            $backId = json_decode(Redis::get('workflow_initiator_' . $workflowId));
            if (!$backId) {
                $backId = WfWorkflowrolemap::where('workflow_id', $workflowId)
                    ->where('is_initiator', true)
                    ->first();
                $redis->set('workflow_initiator_' . $workflowId, json_encode($backId));
            }

            $mAdvActivePrivateland->current_roles = $backId->wf_role_id;
            $mAdvActivePrivateland->parked = 1;
            $mAdvActivePrivateland->save();

            $metaReqs['moduleId'] = $this->_moduleIds;
            $metaReqs['workflowId'] = $mAdvActivePrivateland->workflow_id;
            $metaReqs['refTableDotId'] = "adv_active_vehicles.id";
            $metaReqs['refTableIdValue'] = $req->applicationId;
            $metaReqs['verificationStatus'] = $req->verificationStatus;
            $metaReqs['senderRoleId'] = $req->currentRoleId;
            $req->request->add($metaReqs);

            $req->request->add($metaReqs);
            $track = new WorkflowTrack();
            $track->saveTrack($req);

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Successfully Done", "", "", '050328', '01', "$executionTime Sec", 'Post', '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050328", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    /**
     * | Back To Citizen Inbox
     * | Function - 31
     * | API - 29
     */
    public function listBtcInbox()
    {
        try {
            // Variable Initialization
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

            $mAdvActiveVehicle = new AdvActiveVehicle();
            $btcList = $mAdvActiveVehicle->getVehicleList($ulbId)
                ->whereIn('adv_active_vehicles.current_roles', $roleId)
                // ->whereIn('a.ward_mstr_id', $occupiedWards)
                ->where('parked', true)
                ->orderByDesc('adv_active_vehicles.id')
                ->get();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "BTC Inbox List", remove_null($btcList), "050329", 1.0, "$executionTime Sec", "POST", "", "");
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
        $moduleId = $this->_moduleIds;
        $totalRequireDocs = $mWfActiveDocument->totalNoOfDocs($docCode);
        $appDetails = AdvActiveVehicle::find($applicationId);
        $totalUploadedDocs = $mWfActiveDocument->totalUploadedDocs($applicationId, $appDetails->workflow_id, $moduleId);
        if ($totalRequireDocs == $totalUploadedDocs) {
            $appDetails->doc_upload_status = '1';
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
            // Variable Initialization
            $startTime = microtime(true);
            $mAdvActiveVehicle = new AdvActiveVehicle();
            DB::beginTransaction();
            $appId = $mAdvActiveVehicle->reuploadDocument($req);
            $this->checkFullUpload($appId);
            DB::commit();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Document Uploaded Successfully", "", "050330", 1.0, " $executionTime Sec", "POST", "", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, "Document Not Uploaded", "", "050330", 1.0, "271ms", "POST", "", "");
        }
    }
}