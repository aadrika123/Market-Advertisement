<?php

namespace App\Http\Controllers\Markets;

use App\BLL\Advert\CalculateRate;
use App\Http\Controllers\Controller;
use App\Http\Requests\BanquetMarriageHall\RenewalRequest;
use App\Models\Markets\MarketPriceMstrs;
use Illuminate\Http\Request;
use App\Http\Requests\BanquetMarriageHall\StoreRequest;
use App\Http\Requests\BanquetMarriageHall\UpdateRequest;
use App\Models\Advertisements\AdvChequeDtl;
use App\Models\Advertisements\WfActiveDocument;
use App\Models\Markets\MarActiveBanquteHall;
use App\Models\Markets\MarBanquteHall;
use App\Models\Markets\MarketPriceMstr;
use App\Models\Markets\MarRejectedBanquteHall;
use App\Models\Param\AdvMarTransaction;
use App\Models\Workflows\WfRoleusermap;
use App\Models\Workflows\WfWardUser;
use App\Models\Workflows\WfWorkflowrolemap;
use App\Models\Workflows\WorkflowTrack;
use App\Repositories\Markets\iMarketRepo;
use App\Traits\WorkflowTrait;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;


use App\Traits\MarDetailsTraits;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

/**
 * | Created on - 06-02-2023
 * | Created By - Bikash Kumar
 * | Banquet Marriage Hall operations
 * | Status - Closed, by Bikash - 17 Apr 2023, Total API - 31, Total Function - 34, Total No of Lines - 1471
 */
class BanquetMarriageHallController extends Controller
{

    use WorkflowTrait;
    use MarDetailsTraits;

    protected $_modelObj;  //  Generate Model Instance
    protected $_repository;
    protected $_workflowIds;
    protected $_moduleIds;
    protected $_docCode;
    protected $_docCodeRenew;
    protected $_paramId;
    protected $_tempParamId;
    protected $_baseUrl;

    //Constructor
    public function __construct(iMarketRepo $mar_repo)
    {
        $this->_modelObj = new MarActiveBanquteHall();
        $this->_workflowIds = Config::get('workflow-constants.BANQUTE_MARRIGE_HALL_WORKFLOWS');
        $this->_moduleIds = Config::get('workflow-constants.MARKET_MODULE_ID');
        $this->_repository = $mar_repo;
        $this->_docCode = config::get('workflow-constants.BANQUTE_MARRIGE_HALL_DOC_CODE');
        $this->_docCodeRenew = config::get('workflow-constants.BANQUTE_MARRIGE_HALL_DOC_CODE_RENEW');

        $this->_paramId = Config::get('workflow-constants.BQT_ID');
        $this->_tempParamId = Config::get('workflow-constants.T_BQT_ID');
        $this->_baseUrl = Config::get('constants.BASE_URL');
    }

    /**
     * | Store 
     * | @param StoreRequest Request
     * | Function - 01
     * | API - 01
     */
    public function addNew(StoreRequest $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mMarActiveBanquteHall = $this->_modelObj;
            $citizenId = ['citizenId' => authUser()->id];
            $req->request->add($citizenId);

            $mCalculateRate = new CalculateRate;
            $generatedId = $mCalculateRate->generateId($req->bearerToken(), $this->_tempParamId, $req->ulbId); // Generate Application No
            $applicationNo = ['application_no' => $generatedId];
            $req->request->add($applicationNo);

            DB::beginTransaction();
            $applicationNo = $mMarActiveBanquteHall->addNew($req);       //<--------------- Model function to store 
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Successfully Submitted the application !!", ['status' => true, 'ApplicationNo' => $applicationNo], "050801", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050801", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }



    /**
     * | Inbox List
     * | @param Request $req
     * | Function - 02
     * | API - 02
     */
    public function listInbox(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mMarActiveBanquteHall = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });

            $inboxList = $mMarActiveBanquteHall->listInbox($roleIds);                   // <----- Get Inbox List 

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Inbox Applications", remove_null($inboxList->toArray()), "050802", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050802", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }



    /**
     * | Outbox List
     * | Function - 03
     * | API - 03
     */
    public function listOutbox(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mMarActiveBanquteHall = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });

            $outboxList = $mMarActiveBanquteHall->listOutbox($roleIds);                 // <----- Get Outbox List

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Outbox Lists", remove_null($outboxList->toArray()), "050803", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050803", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * | Application Details
     * | Function - 04
     * | API - 04
     */
    public function getDetailsById(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);

            $mMarActiveBanquteHall = $this->_modelObj;
            $fullDetailsData = array();
            if (isset($req->type)) {
                $type = $req->type;
            } else {
                $type = NULL;
            }
            if ($req->applicationId) {
                $data = $mMarActiveBanquteHall->getDetailsById($req->applicationId, $type);
            } else {
                throw new Exception("Not Pass Application Id");
            }

            if (!$data)
                throw new Exception("Application Not Found");

            // Basic Details
            $basicDetails = $this->generateBasicDetails($data);                     // Trait function to get Basic Details
            $basicElement = [
                'headerTitle' => "Basic Details",
                "data" => $basicDetails
            ];

            $cardDetails = $this->generateCardDetails($data);                       // Trait function to get Card Details
            $cardElement = [
                'headerTitle' => "About Banqute-Marriage Hall",
                'data' => $cardDetails
            ];
            $fullDetailsData['fullDetailsData']['dataArray'] = new Collection([$basicElement]);
            $fullDetailsData['fullDetailsData']['cardArray'] = new Collection($cardElement);

            $metaReqs['customFor'] = 'BANQUET';
            $metaReqs['wfRoleId'] = $data['current_role_id'];
            $metaReqs['workflowId'] = $data['workflow_id'];
            $metaReqs['lastRoleId'] = $data['last_role_id'];

            $req->request->add($metaReqs);
            $forwardBackward = $this->getRoleDetails($req);                                      // Get Role Details
            $fullDetailsData['roleDetails'] = collect($forwardBackward)['original']['data'];

            $fullDetailsData = remove_null($fullDetailsData);

            $fullDetailsData['application_no'] = $data['application_no'];
            $fullDetailsData['doc_verify_status'] = $data['doc_verify_status'];
            $fullDetailsData['apply_date'] = $data['application_date'];
            $fullDetailsData['timelineData'] = collect($req);                                     // Get Timeline Data

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, 'Data Fetched', $fullDetailsData, "050804", "1.0", "$executionTime Sec", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050804", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Get Application Role Details
     * | Function - 05
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
     * Summary of getCitizenApplications
     * @param Request $req
     * @return void
     * | Function - 06
     * | API - 05
     */
    public function listAppliedApplications(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);

            $citizenId = authUser()->id;
            $mMarActiveBanquteHall = $this->_modelObj;

            $applications = $mMarActiveBanquteHall->listAppliedApplications($citizenId);                // Get Citizen Apply List

            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            if ($totalApplication == 0) {
                $data1['data'] = null;
            }
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Applied Applications", $data1, "050805", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050805", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    /**
     *  | Escalate
     * @param Request $request
     * @return void
     * | Function - 07
     * | API - 06
     */
    public function escalateApplication(Request $request)
    {
        $request->validate([
            "escalateStatus" => "required|int",
            "applicationId" => "required|int",
        ]);
        try {
            // Variable initialization
            $startTime = microtime(true);

            $userId = auth()->user()->id;
            $applicationId = $request->applicationId;
            $data = MarActiveBanquteHall::find($applicationId);
            $data->is_escalate = $request->escalateStatus;
            $data->escalate_by = $userId;
            $data->save();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, $request->escalateStatus == 1 ? 'Banqute Marriage Hall is Escalated' : "Banqute Marriage Hall is removed from Escalated", '', "050806", "1.0", "$executionTime Sec", "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050806", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    /**
     *  Special Inbox List
     * @param Request $req
     * @return void
     * | Function - 08
     * | API - 07
     */
    public function listEscalated(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mWfWardUser = new WfWardUser();
            $userId = authUser()->id;
            $ulbId = authUser()->ulb_id;

            $occupiedWard = $mWfWardUser->getWardsByUserId($userId);                        // Get All Occupied Ward By user id using trait
            $wardId = $occupiedWard->map(function ($item, $key) {                           // Filter All ward_id in an array using laravel collections
                return $item->ward_id;
            });

            $advData = $this->_repository->specialInbox($this->_workflowIds)                      // Repository function to get Markets Details
                ->where('is_escalate', 1)
                ->where('mar_active_banqute_halls.ulb_id', $ulbId)
                // ->whereIn('ward_mstr_id', $wardId)
                ->get();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Data Fetched", remove_null($advData), "050807", "1.0", "$executionTime Sec", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050807", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * Forward or Backward Application
     * @param Request $request
     * @return void
     * | Function - 09
     * | API - 08
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
            // Variable initialization
            $startTime = microtime(true);

            // Marriage Banqute Hall Application Update Current Role Updation
            $adv = MarActiveBanquteHall::find($request->applicationId);
            if ($adv->doc_verify_status == '0')
                throw new Exception("Please Verify All Documents To Forward The Application !!!");
            $adv->last_role_id = $adv->current_role_id;
            $adv->current_role_id = $request->receiverRoleId;
            $adv->save();

            $metaReqs['moduleId'] = $this->_moduleIds;
            $metaReqs['workflowId'] = $adv->workflow_id;
            $metaReqs['refTableDotId'] = "mar_active_banqute_halls.id";
            $metaReqs['refTableIdValue'] = $request->applicationId;
            $request->request->add($metaReqs);

            $track = new WorkflowTrack();
            DB::beginTransaction();
            $track->saveTrack($request);
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Successfully Forwarded The Application!!", "", "050108", "1.0", "$executionTime Sec", "POST", $request->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050808", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    /**
     * Post Independent Comment
     * @param Request $request
     * @return void
     * | Function - 10
     * | API - 09
     */
    public function commentApplication(Request $request)
    {
        $request->validate([
            'comment' => 'required',
            'applicationId' => 'required|integer',
            'senderRoleId' => 'nullable|integer'
        ]);

        try {
            // Variable initialization
            $startTime = microtime(true);
            $workflowTrack = new WorkflowTrack();
            $mMarActiveBanquteHall = MarActiveBanquteHall::find($request->applicationId);                // Advertisment Details
            $mModuleId = $this->_moduleIds;
            $metaReqs = array();
            // Save On Workflow Track For Level Independent
            $metaReqs = [
                'workflowId' => $mMarActiveBanquteHall->workflow_id,
                'moduleId' => $mModuleId,
                'refTableDotId' => "mar_active_banqute_halls.id",
                'refTableIdValue' => $mMarActiveBanquteHall->id,
                'message' => $request->comment
            ];
            // For Citizen Independent Comment
            if (!$request->senderRoleId) {
                $metaReqs = array_merge($metaReqs, ['citizenId' => $mMarActiveBanquteHall->user_id]);
            }

            $request->request->add($metaReqs);
            DB::beginTransaction();
            $workflowTrack->saveTrack($request);
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "You Have Commented Successfully!!", ['Comment' => $request->comment], "050809", "1.0", " $executionTime Sec", "POST", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050809", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    /**
     * Get Uploaded Document by application ID
     * @param Request $req
     * @return void
     * | Function - 11
     * | API - 10
     */
    public function viewBmHallDocuments(Request $req)
    {
        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        if ($req->applicationId && $req->type) {
            $data = $mWfActiveDocument->uploadDocumentsViewById($req->applicationId, $this->_workflowIds);
        } else {
            throw new Exception("Required Application Id And Application Type");
        }
        $data1['data'] = $data;
        return $data1;
    }

    /**
     * | Get Uploaded Active Document by application ID
     * | Function - 12
     * | API - 11
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
     * | Function - 13
     * | API - 12
     */
    public function viewDocumentsOnWorkflow(Request $req)
    {
        $startTime = microtime(true);
        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        if ($req->applicationId) {
            $data = $mWfActiveDocument->uploadDocumentsViewById($req->applicationId, $this->_workflowIds);
        }
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        return responseMsgs(true, "Data Fetched", remove_null($data), "050812", "1.0", "$executionTime Sec", "POST", "");
    }


    /**
     * Final Approval and Rejection of the Application
     * @param Request $req
     * @return void
     * | Function - 14
     * | API - 13
     */
    public function approvedOrReject(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'roleId' => 'required',
            'applicationId' => 'required|integer',
            'status' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            $startTime = microtime(true);

            // Check if the Current User is Finisher or Not         
            $mMarActiveBanquteHall = MarActiveBanquteHall::find($req->applicationId);
            $getFinisherQuery = $this->getFinisherId($mMarActiveBanquteHall->workflow_id);                                 // Get Finisher using Trait
            $refGetFinisher = collect(DB::select($getFinisherQuery))->first();
            if ($refGetFinisher->role_id != $req->roleId) {
                return responseMsgs(false, " Access Forbidden", "");
            }

            DB::beginTransaction();
            // Approval
            if ($req->status == 1) {

                $mMarketPriceMstr = new MarketPriceMstr();
                $amount = $mMarketPriceMstr->getMarketTaxPrice($mMarActiveBanquteHall->workflow_id, $mMarActiveBanquteHall->floor_area, $mMarActiveBanquteHall->ulb_id);
                $payment_amount = ['payment_amount' => $amount];
                $req->request->add($payment_amount);

                $mCalculateRate = new CalculateRate;
                $generatedId = $mCalculateRate->generateId($req->bearerToken(), $this->_paramId, $mMarActiveBanquteHall->ulb_id); // Generate Application No

                if ($mMarActiveBanquteHall->renew_no == NULL) {
                    // Banqute Hall Application replication
                    $approvedbanqutehall = $mMarActiveBanquteHall->replicate();
                    $approvedbanqutehall->setTable('mar_banqute_halls');
                    $temp_id = $approvedbanqutehall->id = $mMarActiveBanquteHall->id;
                    $approvedbanqutehall->payment_amount = $req->payment_amount;
                    $approvedbanqutehall->license_no = $generatedId;
                    $approvedbanqutehall->approve_date = Carbon::now();
                    $approvedbanqutehall->save();

                    // Save in Banqute Hall Renewal
                    $approvedbanqutehall = $mMarActiveBanquteHall->replicate();
                    $approvedbanqutehall->approve_date = Carbon::now();
                    $approvedbanqutehall->setTable('mar_banqute_hall_renewals');
                    $approvedbanqutehall->license_no =$generatedId;
                    $approvedbanqutehall->app_id = $temp_id;
                    $approvedbanqutehall->save();

                    $mMarActiveBanquteHall->delete();

                    // Update in mar_banqute_halls (last_renewal_id)
                    DB::table('mar_banqute_halls')
                        ->where('id', $temp_id)
                        ->update(['last_renewal_id' => $approvedbanqutehall->id]);

                    $msg = "Application Successfully Approved !!";
                } else {
                    //  Renewal Case
                    // BanquteHall Application replication
                    $application_no = $mMarActiveBanquteHall->application_no;
                    MarBanquteHall::where('application_no', $application_no)->delete();

                    $approvedBanquteHall = $mMarActiveBanquteHall->replicate();
                    $approvedBanquteHall->setTable('mar_banqute_halls');
                    $temp_id = $approvedBanquteHall->id = $mMarActiveBanquteHall->id;
                    $approvedBanquteHall->payment_amount = $req->payment_amount;
                    $approvedBanquteHall->payment_status = $req->payment_status;
                    $approvedBanquteHall->approve_date = Carbon::now();
                    $approvedBanquteHall->save();

                    // Save in BanquteHall Renewal
                    $approvedBanquteHall = $mMarActiveBanquteHall->replicate();
                    $approvedBanquteHall->approve_date = Carbon::now();
                    $approvedBanquteHall->setTable('mar_banqute_hall_renewals');
                    $approvedBanquteHall->app_id = $temp_id;
                    $approvedBanquteHall->save();

                    $mMarActiveBanquteHall->delete();

                    // Update in mar_banqute_halls (last_renewal_id)
                    DB::table('mar_banqute_halls')
                        ->where('id', $temp_id)
                        ->update(['last_renewal_id' => $approvedBanquteHall->id]);
                    $msg = "Application Successfully Renewal !!";
                }
            }
            // Rejection
            if ($req->status == 0) {
                // Banqute Hall Application replication
                $rejectedbanqutehall = $mMarActiveBanquteHall->replicate();
                $rejectedbanqutehall->setTable('mar_rejected_banqute_halls');
                $rejectedbanqutehall->id = $mMarActiveBanquteHall->id;
                $rejectedbanqutehall->rejected_date = Carbon::now();
                $rejectedbanqutehall->save();
                $mMarActiveBanquteHall->delete();
                $msg = "Application Successfully Rejected !!";
            }
            DB::commit();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, $msg, "", '050813', 01, "$executionTime Sec", 'POST', $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false,  $e->getMessage(), "", '050813', 01, "", 'POST', $req->deviceId);
        }
    }

    /**
     * Approved Application List for Citizen
     * @param Request $req
     * @return void
     * | Function - 15
     * | API - 14
     */
    public function listApproved(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);

            $citizenId = authUser()->id;
            $userType = authUser()->user_type;
            $mMarBanquteHall = new MarBanquteHall();
            $applications = $mMarBanquteHall->listApproved($citizenId, $userType);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            if ($data1['arrayCount'] == 0) {
                $data1 = null;
            }
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Approved Application List", $data1, "050814", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050814", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * Rejected Application List
     * @param Request $req
     * @return void
     * | Function - 16
     * | API - 15
     */
    public function listRejected(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);

            $citizenId = authUser()->id;
            $mMarRejectedBanquteHall = new MarRejectedBanquteHall();
            $applications = $mMarRejectedBanquteHall->listRejected($citizenId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            if ($data1['arrayCount'] == 0) {
                $data1 = null;
            }
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Rejected Application List", $data1, "050815", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050815", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * generate Payment OrderId for Payment
     * @param Request $req
     * @return void
     * | Function - 17
     * | API - 16
     */
    public function generatePaymentOrderId(Request $req)
    {
        $req->validate([
            'id' => 'required|integer',
        ]);
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mMarBanquteHall = MarBanquteHall::find($req->id);
            $reqData = [
                "id" => $mMarBanquteHall->id,
                'amount' => $mMarBanquteHall->payment_amount,
                'workflowId' => $mMarBanquteHall->workflow_id,
                'ulbId' => $mMarBanquteHall->ulb_id,
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

            $data->name = $mMarBanquteHall->applicant;
            $data->email = $mMarBanquteHall->email;
            $data->contact = $mMarBanquteHall->mobile_no;
            $data->type = "Marriage Banqute Hall";

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Payment OrderId Generated Successfully !!!", $data, "050816", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050816", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * Get application Details For Payment
     * @return void
     * | Function - 18
     * | API - 17
     */
    public function getApplicationDetailsForPayment(Request $req)
    {
        $req->validate([
            'applicationId' => 'required|integer',
        ]);
        try {
            // Variable initialization
            $startTime = microtime(true);

            $mMarBanquteHall = new MarBanquteHall();
            if ($req->applicationId) {
                $data = $mMarBanquteHall->getApplicationDetailsForPayment($req->applicationId);
            }

            if (!$data)
                throw new Exception("Application Not Found");

            $data['type'] = "Banquet Marriage Hall";

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, 'Data Fetched',  $data, "050817", "1.0", "$executionTime Sec", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050817", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Verify Single Application Approve or reject
     * | Function - 19
     * | API - 18
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
            $mMarActiveBanquteHall = new MarActiveBanquteHall();
            $mWfRoleusermap = new WfRoleusermap();
            $wfDocId = $req->id;
            $userId = authUser()->id;
            $applicationId = $req->applicationId;

            $wfLevel = Config::get('constants.MARKET-LABEL');
            // Derivative Assigments
            $appDetails = $mMarActiveBanquteHall->getBanquetMarriageHallDetails($applicationId);

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
            return responseMsgs(true, $req->docStatus . " Successfully", "", "050818", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050818", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Check if the Document is Fully Verified or Not (4.1)
     * | Function - 20
     */
    public function ifFullDocVerified($applicationId)
    {
        $mMarActiveBanquteHall = new MarActiveBanquteHall();
        $mWfActiveDocument = new WfActiveDocument();
        $mMarActiveBanquteHall = $mMarActiveBanquteHall->getBanquetMarriageHallDetails($applicationId);                      // Get Application Details
        $refReq = [
            'activeId' => $applicationId,
            'workflowId' => $mMarActiveBanquteHall->workflow_id,
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
     * | Send back to citizen
     * | Function - 21
     * | API - 19
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
            $mMarActiveBanquteHall = MarActiveBanquteHall::find($req->applicationId);

            $workflowId = $mMarActiveBanquteHall->workflow_id;
            $backId = json_decode(Redis::get('workflow_initiator_' . $workflowId));
            if (!$backId) {
                $backId = WfWorkflowrolemap::where('workflow_id', $workflowId)
                    ->where('is_initiator', true)
                    ->first();
                $redis->set('workflow_initiator_' . $workflowId, json_encode($backId));
            }

            $mMarActiveBanquteHall->current_role_id = $backId->wf_role_id;
            $mMarActiveBanquteHall->parked = 1;
            $mMarActiveBanquteHall->save();


            $metaReqs['moduleId'] = $this->_moduleIds;
            $metaReqs['workflowId'] = $mMarActiveBanquteHall->workflow_id;
            $metaReqs['refTableDotId'] = "mar_active_banqute_halls.id";
            $metaReqs['refTableIdValue'] = $req->applicationId;
            $metaReqs['verificationStatus'] = $req->verificationStatus;
            $metaReqs['senderRoleId'] = $req->currentRoleId;
            $req->request->add($metaReqs);

            $req->request->add($metaReqs);
            $track = new WorkflowTrack();
            $track->saveTrack($req);

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Successfully Done", "", "", '050819', '01', "$executionTime Sec", 'POST', '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050819", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    /**
     * | Back To Citizen Inbox
     * | Function - 22
     * | API - 20
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

            $mMarActiveBanquteHall = new MarActiveBanquteHall();
            $btcList = $mMarActiveBanquteHall->getBanquetMarriageHallList($ulbId)
                ->whereIn('mar_active_banqute_halls.current_role_id', $roleId)
                // ->whereIn('a.ward_mstr_id', $occupiedWards)
                ->where('parked', true)
                ->orderByDesc('mar_active_banqute_halls.id')
                ->get();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "BTC Inbox List", remove_null($btcList), "050820", 1.0, "$executionTime Sec", "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050820", 1.0, "", "POST", "", "");
        }
    }

    /**
     * | Check full document uploaded or not
     * | Function - 23
     */
    public function checkFullUpload($applicationId)
    {

        $appDetails = MarActiveBanquteHall::find($applicationId);
        $docCode = $this->_docCode;
        // $docCode = $this->_docCodeRenew;
        // if($appDetails->renew_no==NULL){
        //     $docCode = $this->_docCode;
        // }
        $mWfActiveDocument = new WfActiveDocument();
        $moduleId = $this->_moduleIds;
        $totalRequireDocs = $mWfActiveDocument->totalNoOfDocs($docCode);
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
     * | Reupload Rejected Documents
     * | Function - 24
     * | API - 21
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
            $startTime = microtime(true);
            $mMarActiveBanquteHall = new MarActiveBanquteHall();
            DB::beginTransaction();
            $appId = $mMarActiveBanquteHall->reuploadDocument($req);
            $this->checkFullUpload($appId);
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Document Uploaded Successfully", "", "050821", 1.0, "$executionTime Sec", "POST", "", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, "Document Not Uploaded", "050821", 010717, 1.0, "", "POST", "", "");
        }
    }

          /**
     * | Payment via cash for application
     * | Function - 25
     * | API - 22
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
            // Variable initialization
            $startTime = microtime(true);
            $mMarBanquteHall = new MarBanquteHall();
            $mAdvMarTransaction=new AdvMarTransaction();
            $appDetails=MarBanquteHall::find($req->applicationId);
            DB::beginTransaction();
            $data = $mMarBanquteHall->paymentByCash($req);
            $mAdvMarTransaction->addTransaction($appDetails, $this->_moduleIds,"Market","Cash");
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            if ($req->status == '1' && $data['status'] == 1) {
                return responseMsgs(true, "Payment Successfully !!", ['status' => true, 'transactionNo' => $data['payment_id'], 'workflowId' => $this->_workflowIds], "050822", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
            } else {
                return responseMsgs(false, "Payment Rejected !!", '', "050822", "1.0", "", 'POST', $req->deviceId ?? "");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050822", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    
    /**
     * | Entry Cheque or DD for Payment
     * | Function - 26
     * | API - 23
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
            // Variable initialization
            $startTime = microtime(true);

            $mAdvCheckDtl = new AdvChequeDtl();
            $workflowId = ['workflowId' => $this->_workflowIds];
            $req->request->add($workflowId);
            $transNo = $mAdvCheckDtl->entryChequeDd($req);

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Check Entry Successfully !!", ['status' => true, 'TransactionNo' => $transNo], "050823", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050823", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Clear or bounce cheque DD 
     * | Function - 27 
     * | API - 24
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
            $startTime = microtime(true);

            $mAdvCheckDtl = new AdvChequeDtl(); 
            $mAdvMarTransaction=new AdvMarTransaction();
            $appDetails=MarBanquteHall::find($req->applicationId);
            DB::beginTransaction();
            $status = $mAdvCheckDtl->clearOrBounceCheque($req);
            $mAdvMarTransaction->addTransaction($appDetails, $this->_moduleIds,"Market","Cheque/DD");
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            if ($req->status == '1' && $status == 1) {
                return responseMsgs(true, "Payment Successfully !!", '', "050824", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
            } else {
                return responseMsgs(false, "Payment Rejected !!", '', "050824", "1.0", "", 'POST', $req->deviceId ?? "");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050824", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    
    /**
     * | Get Application Details For Renew
     * | Function - 28
     * | API - 25
     */
    public function getApplicationDetailsForRenew(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|digits_between:1,9223372036854775807'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $mMarBanquteHall = new MarBanquteHall();
            $details = $mMarBanquteHall->applicationDetailsForRenew($req->applicationId);
            if (!$details)
                throw new Exception("Application Not Found !!!");

            return responseMsgs(true, "Application Fetched !!!", remove_null($details), "050825", "1.0", "200 ms", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050825", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Renew For Banquet Marriage Hall
     * | @param StoreRequest 
     * | Function - 29
     * | API - 26
     */
    public function renewApplication(RenewalRequest $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mMarActiveBanquteHall = $this->_modelObj;
            $citizenId = ['citizenId' => authUser()->id];
            $req->request->add($citizenId);

            $mCalculateRate = new CalculateRate;
            $generatedId = $mCalculateRate->generateId($req->bearerToken(), $this->_tempParamId, $req->ulbId); // Generate Application No
            $applicationNo = ['application_no' => $generatedId];
            $req->request->add($applicationNo);

            DB::beginTransaction();
            $applicationNo = $mMarActiveBanquteHall->renewApplication($req);       //<--------------- Model function to store 
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Successfully Renewal the application !!", ['status' => true, 'ApplicationNo' => $applicationNo], "050826", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050826", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }
  

        /**
     * | Get APplication Details For Edit
     * | Function - 30
     * | API - 27
     */
    public function getApplicationDetailsForEdit(Request $req)
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
            $mMarActiveBanquteHall = new MarActiveBanquteHall();
            $details = $mMarActiveBanquteHall->getApplicationDetailsForEdit($req->applicationId);
            if (!$details)
                throw new Exception("Application Not Found !!!");
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Application Featch Successfully !!!", $details, "050827", 1.0, "$executionTime Sec", "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, "Application Not Featched !!!", "", "050827", 1.0, "", "POST", "", "");
        }
    }

    /**
     * | Update Application 
     * | Function - 31
     * | API - 28
     */
    public function editApplication(UpdateRequest $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);

            $mMarActiveBanquteHall = $this->_modelObj;
            DB::beginTransaction();
            $res = $mMarActiveBanquteHall->updateApplication($req);       //<--------------- Update Banquet Hall Application
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            if ($res)
                return responseMsgs(true, "Application Update Successfully !!!", "", "050828", 1.0, "$executionTime Sec", "POST", "", "");
            else
                return responseMsgs(false, "Application Not Updated !!!", "", "050828", 1.0, "", "POST", "", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, "Application Not Updated !!!", $e->getMessage(), "050828", 1.0, "", "POST", "", "");
        }
    }

    /**
     * | Get Application Between Two Dates
     * | Function - 32
     * | API - 29
     */
    public function getApplicationBetweenDate(Request $req)
    {
        if (authUser()->ulb_id < 1)
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050829", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = authUser()->ulb_id;
        $validator = Validator::make($req->all(), [
            'applicationType' => 'required|in:New Apply,Renew',
            'applicationStatus' => 'required|in:All,Approve,Reject',
            'entityWard' => 'required|integer',
            'dateFrom' => 'required|date_format:Y-m-d',
            'dateUpto' => 'required|date_format:Y-m-d',
            'perPage' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            $startTime = microtime(true);
            #=============================================================
            $approveList = DB::table('mar_banqute_halls')
                ->select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', DB::raw("'Approve' as application_status"))->where('entity_ward_id', $req->entityWard)->where('application_type', $req->applicationType)->where('ulb_id', $ulbId)
                ->whereBetween('application_date', [$req->dateFrom, $req->dateUpto]);

            $pendingList = DB::table('mar_active_banqute_halls')
                ->select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', DB::raw("'Active' as application_status"))
                ->where('entity_ward_id', $req->entityWard)->where('application_type', $req->applicationType)->where('ulb_id', $ulbId)
                ->whereBetween('application_date', [$req->dateFrom, $req->dateUpto]);

            $rejectList = DB::table('mar_rejected_banqute_halls')
                ->select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', DB::raw("'Reject' as application_status"))
                ->where('entity_ward_id', $req->entityWard)->where('application_type', $req->applicationType)->where('ulb_id', $ulbId)
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
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Application Fetched Successfully", $data, "050829", 1.0, "$executionTime Sec", "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, "Application Not Fetched", $e->getMessage(), "050829", 1.0, "271ms", "POST", "", "");
        }
    }

    /**
     * | Get Application Financial Year Wise
     * | Function - 33
     * | API - 30
     */
    public function getApplicationFinancialYearWise(Request $req)
    {
        if (authUser()->ulb_id < 1)
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050830", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = authUser()->ulb_id;

        $validator = Validator::make($req->all(), [
            'applicationType' => 'required|in:New Apply,Renew',
            'entityWard' => 'required|integer',
            'perPage' => 'required|integer',
            'financialYear' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            $startTime = microtime(true);

            $approveList = DB::table('mar_banqute_halls')
                ->select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', DB::raw("'Approve' as application_status"))->where('application_type', $req->applicationType)->where('entity_ward_id', $req->entityWard)->where('ulb_id', $ulbId)->where('license_year', $req->financialYear);

            $pendingList = DB::table('mar_active_banqute_halls')
                ->select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', DB::raw("'Active' as application_status"))->where('application_type', $req->applicationType)->where('ulb_id', $ulbId)
                ->where('entity_ward_id', $req->entityWard)->where('license_year', $req->financialYear);

            $rejectList = DB::table('mar_rejected_banqute_halls')
                ->select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', DB::raw("'Reject' as application_status"))->where('application_type', $req->applicationType)->where('ulb_id', $ulbId)
                ->where('entity_ward_id', $req->entityWard)->where('license_year', $req->financialYear);

            $data = collect(array());
            $data = $approveList->union($pendingList)->union($rejectList);
            $data = $data->paginate($req->perPage);

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Application Fetched Successfully", $data, "050830", 1.0, "$executionTime Sec", "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, "Application Not Fetched", $e->getMessage(), "050830", 1.0, "271ms", "POST", "", "");
        }
    }

    /**
     * | COllection From New or Renew Application
     * | Function - 34
     * | API - 31
     */
    public function paymentCollection(Request $req)
    {
        if (authUser()->ulb_id < 1)
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050831", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = authUser()->ulb_id;

        $validator = Validator::make($req->all(), [
            'applicationType' => 'required|in:New Apply,Renew',
            'entityWard' => 'required|integer',
            'dateFrom' => 'required|date_format:Y-m-d',
            'dateUpto' => 'required|date_format:Y-m-d',
            'perPage' => 'required|integer',
            'payMode' => 'required|in:All,Online,Cash,Cheque/DD',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            $startTime = microtime(true);
            $endTime = microtime(true);

            $approveList = DB::table('mar_banqute_hall_renewals')
                ->select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', DB::raw("'Approve' as application_status"), 'payment_amount', 'payment_date', 'payment_mode')->where('entity_ward_id', $req->entityWard)->where('application_type', $req->applicationType)->where('payment_status', '1')->where('ulb_id', $ulbId)
                ->whereBetween('payment_date', [$req->dateFrom, $req->dateUpto]);

            $data = collect(array());
            if ($req->payMode == 'All') {
                $data = $approveList;
            }
            if ($req->payMode == 'Online') {
                $data = $approveList->where('payment_mode', $req->payMode);
            }
            if ($req->payMode == 'Cash') {
                $data = $approveList->where('payment_mode', $req->payMode);
            }
            if ($req->payMode == 'Cheque/DD') {
                $data = $approveList->where('payment_mode', $req->payMode);
            }
            $data = $data->paginate($req->perPage);

            $ap = $data->toArray();

            $amounts = collect();
            $data1 = collect($ap['data'])->map(function ($item, $key) use ($amounts) {
                $amounts->push($item->payment_amount);
            });

            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Application Fetched Successfully", $data, "050831", 1.0, "$executionTime Sec", "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, "Application Not Fetched", $e->getMessage(), "050831", 1.0, "271ms", "POST", "", "");
        }
    }
}