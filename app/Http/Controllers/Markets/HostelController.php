<?php

namespace App\Http\Controllers\Markets;

use App\BLL\Advert\CalculateRate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hostel\RenewalRequest;
use App\Http\Requests\Hostel\StoreRequest;
use App\Http\Requests\Hostel\UpdateRequest;
use App\Models\Advertisements\AdvChequeDtl;
use App\Models\Advertisements\WfActiveDocument;
use App\Models\Markets\MarActiveHostel;
use App\Models\Markets\MarHostel;
use App\Models\Markets\MarketPriceMstr;
use App\Models\Markets\MarRejectedHostel;
use App\Models\Param\AdvMarTransaction;
use App\Models\Workflows\WfRoleusermap;
use App\Models\Workflows\WfWardUser;
use App\Models\Workflows\WfWorkflowrolemap;
use App\Models\Workflows\WorkflowTrack;
use App\Repositories\Markets\iMarketRepo;
use App\Traits\MarDetailsTraits;
use App\Traits\WorkflowTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

/**
 * | Created By- Bikash Kumar 
 * | Created for the Hostel Operations
 * | Status - Open (14 Apr 2023)  Total no. of lines - 
 */
class HostelController extends Controller
{

    use WorkflowTrait;

    use MarDetailsTraits;
    protected $_modelObj;
    protected $_moduleIds;
    protected $_workflowIds;
    protected $_repository;
    protected $_docCode;
    protected $_docCodeRenew;
    protected $_baseUrl;
    protected $_tempParamId;
    protected $_paramId;

    //Constructor
    public function __construct(iMarketRepo $mar_repo)
    {
        $this->_modelObj = new MarActiveHostel();
        $this->_workflowIds = Config::get('workflow-constants.HOSTEL_WORKFLOWS');
        $this->_moduleIds = Config::get('workflow-constants.MARKET_MODULE_ID');
        $this->_repository = $mar_repo;
        $this->_docCode = Config::get('workflow-constants.HOSTEL_DOC_CODE');
        $this->_docCodeRenew = Config::get('workflow-constants.HOSTEL_DOC_CODE_RENEW');
        $this->_paramId = Config::get('workflow-constants.HOS_ID');
        $this->_tempParamId = Config::get('workflow-constants.T_HOS_ID');
        $this->_baseUrl = Config::get('constants.BASE_URL');
    }

    /**
     * | Apply for Hostel 
     * | @param StoreRequest 
     * | Function - 01
     * | API - 01
     */
    public function addNew(StoreRequest $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);

            $mMarActiveHostel = $this->_modelObj;
            $citizenId = ['citizenId' => authUser()->id];
            $req->request->add($citizenId);

            // Generate Application No
            $mCalculateRate = new CalculateRate;
            $generatedId = $mCalculateRate->generateId($req->bearerToken(), $this->_tempParamId, $req->ulbId); // Generate Application No
            $applicationNo = ['application_no' => $generatedId];
            $req->request->add($applicationNo);

            DB::beginTransaction();
            $applicationNo = $mMarActiveHostel->addNew($req);       //<--------------- Model function to store 
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Successfully Submitted the application !!", ['status' => true, 'ApplicationNo' => $applicationNo], "050901", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050901", "1.0", "", 'POST', $req->deviceId ?? "");
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

            $mMarActiveHostel = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $inboxList = $mMarActiveHostel->listInbox($roleIds);                       // <----- Get Inbox List

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Inbox Applications", remove_null($inboxList->toArray()), "050902", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050902", "1.0", "", 'POST', $req->deviceId ?? "");
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

            $mMarActiveHostel = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $outboxList = $mMarActiveHostel->listOutbox($roleIds);                      // <----- Get Outbox List

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Outbox Lists", remove_null($outboxList->toArray()), "050903", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050903", "1.0", "", 'POST', $req->deviceId ?? "");
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

            $mMarActiveHostel = $this->_modelObj;
            $fullDetailsData = array();
            $type = NULL;
            if (isset($req->type)) {
                $type = $req->type;
            }
            if ($req->applicationId) {
                $data = $mMarActiveHostel->getDetailsById($req->applicationId, $type);
            } else {
                throw new Exception("Not Pass Application Id");
            }

            if (!$data)
                throw new Exception("Application Not Found");
            // Basic Details
            $basicDetails = $this->generateBasicDetailsForHostel($data); // Trait function to get Basic Details
            $basicElement = [
                'headerTitle' => "Basic Details",
                "data" => $basicDetails
            ];

            $cardDetails = $this->generateCardDetails($data);
            $cardElement = [
                'headerTitle' => "About Hostel",
                'data' => $cardDetails
            ];
            $fullDetailsData['fullDetailsData']['dataArray'] = new Collection([$basicElement]);
            $fullDetailsData['fullDetailsData']['cardArray'] = new Collection($cardElement);

            $metaReqs['customFor'] = 'HOSTEL';
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
            $fullDetailsData['timelineData'] = collect($req);

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, 'Data Fetched', $fullDetailsData, "050105", "1.0", "$executionTime Sec", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }
    /**
     * | Get Application role details
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
            $mMarActiveHostel = $this->_modelObj;
            $applications = $mMarActiveHostel->listAppliedApplications($citizenId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Applied Applications", $data1, "050106", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050106", "1.0", "", "POST", $req->deviceId ?? "");
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
            $data = MarActiveHostel::find($applicationId);
            $data->is_escalate = $request->escalateStatus;
            $data->escalate_by = $userId;
            $data->save();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, $request->escalateStatus == 1 ? 'Hostel is Escalated' : "Hostel is removed from Escalated", '', "050107", "1.0", "$executionTime Sec", "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050107", "1.0", "", "POST", $request->deviceId ?? "");
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

            $advData = $this->_repository->specialInboxHostel($this->_workflowIds)                      // Repository function to get Markets Details
                ->where('is_escalate', 1)
                ->where('mar_active_hostels.ulb_id', $ulbId)
                // ->whereIn('ward_mstr_id', $wardId)
                ->get();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Data Fetched", remove_null($advData), "050108", "1.0", "$executionTime Sec", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050108", "1.0", "", "POST", $req->deviceId ?? "");
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
            $mMarActiveHostel = MarActiveHostel::find($request->applicationId);
            if ($mMarActiveHostel->doc_verify_status == '0')
                throw new Exception("Please Verify All Documents To Forward The Application !!!");
            $mMarActiveHostel->last_role_id = $mMarActiveHostel->current_role_id;
            $mMarActiveHostel->current_role_id = $request->receiverRoleId;
            $mMarActiveHostel->save();

            $metaReqs['moduleId'] = $this->_moduleIds;
            $metaReqs['workflowId'] = $mMarActiveHostel->workflow_id;
            $metaReqs['refTableDotId'] = "mar_active_hostels.id";
            $metaReqs['refTableIdValue'] = $request->applicationId;
            $request->request->add($metaReqs);

            $track = new WorkflowTrack();
            DB::beginTransaction();
            $track->saveTrack($request);
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Successfully Forwarded The Application!!", "", "050109", "1.0", "$executionTime Sec", "POST", $request->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050109", "1.0", "", "POST", $request->deviceId ?? "");
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
            $mMarActiveHostel = MarActiveHostel::find($request->applicationId);                // Advertisment Details
            $mModuleId = $this->_moduleIds;
            $metaReqs = array();
            // Save On Workflow Track For Level Independent
            $metaReqs = [
                'workflowId' => $mMarActiveHostel->workflow_id,
                'moduleId' => $mModuleId,
                'refTableDotId' => "mar_active_hostels.id",
                'refTableIdValue' => $mMarActiveHostel->id,
                'message' => $request->comment
            ];
            // For Citizen Independent Comment
            if (!$request->senderRoleId) {
                $metaReqs = array_merge($metaReqs, ['citizenId' => $mMarActiveHostel->user_id]);
            }

            $request->request->add($metaReqs);

            DB::beginTransaction();
            $workflowTrack->saveTrack($request);
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "You Have Commented Successfully!!", ['Comment' => $request->comment], "050110", "1.0", " $executionTime Sec", "POST", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050110", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }


    /**
     * Get Uploaded Document by application ID
     * @param Request $req
     * @return void
     * | Function - 11
     * | API - 10
     */
    public function viewHostelDocuments(Request $req)
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
        // Variable initialization
        $startTime = microtime(true);

        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        if ($req->applicationId) {
            $data = $mWfActiveDocument->uploadDocumentsViewById($req->applicationId, $this->_workflowIds);
        }
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        return responseMsgs(true, "Data Fetched", remove_null($data), "050115", "1.0", "$executionTime Sec", "POST", "");
    }




    /**
     * Final Approval and Rejection of the Application
     * @param Request $req
     * @return void
     * | Function - 14
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
            $mMarActiveHostel = MarActiveHostel::find($req->applicationId);
            $getFinisherQuery = $this->getFinisherId($mMarActiveHostel->workflow_id);                                 // Get Finisher using Trait
            $refGetFinisher = collect(DB::select($getFinisherQuery))->first();
            if ($refGetFinisher->role_id != $req->roleId) {
                return responseMsgs(false, " Access Forbidden", "");
            }

            DB::beginTransaction();
            // Approval
            if ($req->status == 1) {

                $mMarketPriceMstr = new MarketPriceMstr();
                $amount = $mMarketPriceMstr->getMarketTaxPrice($mMarActiveHostel->workflow_id, $mMarActiveHostel->no_of_beds, $mMarActiveHostel->ulb_id);

                if ($mMarActiveHostel->is_approve_by_govt == true) {
                    $amount = $mMarketPriceMstr->getMarketTaxPriceGovtHostel($mMarActiveHostel->workflow_id, $mMarActiveHostel->ulb_id);
                }
                $payment_amount = ['payment_amount' => $amount];
                $req->request->add($payment_amount);

                $mCalculateRate = new CalculateRate;
                $generatedId = $mCalculateRate->generateId($req->bearerToken(), $this->_paramId, $mMarActiveHostel->ulb_id); // Generate License No

                if ($mMarActiveHostel->renew_no == NULL) {
                    // Hostel Application replication
                    $approvedhostel = $mMarActiveHostel->replicate();
                    $approvedhostel->setTable('mar_hostels');
                    $temp_id = $approvedhostel->id = $mMarActiveHostel->id;
                    $approvedhostel->payment_amount = $req->payment_amount;
                    $approvedhostel->license_no = $generatedId;
                    $approvedhostel->approve_date = Carbon::now();
                    $approvedhostel->save();

                    // Save in Hostel Renewal
                    $approvedhostel = $mMarActiveHostel->replicate();
                    $approvedhostel->approve_date = Carbon::now();
                    $approvedhostel->setTable('mar_hostel_renewals');
                    $approvedhostel->app_id = $mMarActiveHostel->id;
                    $approvedhostel->license_no = $generatedId;
                    $approvedhostel->save();

                    $mMarActiveHostel->delete();

                    // Update in mar_hostels (last_renewal_id)
                    DB::table('mar_hostels')
                        ->where('id', $temp_id)
                        ->update(['last_renewal_id' => $approvedhostel->id]);

                    $msg = "Application Successfully Approved !!";
                } else {
                    //  Renewal Case
                    // Hostel Application replication
                    $application_no = $mMarActiveHostel->application_no;
                    MarHostel::where('application_no', $application_no)->delete();

                    $approvedHostel = $mMarActiveHostel->replicate();
                    $approvedHostel->setTable('mar_hostels');
                    $temp_id = $approvedHostel->id = $mMarActiveHostel->id;
                    $approvedHostel->payment_amount = $req->payment_amount;
                    $approvedHostel->payment_status = $req->payment_status;
                    $approvedHostel->approve_date = Carbon::now();
                    $approvedHostel->save();

                    // Save in Hostel Renewal
                    $approvedHostel = $mMarActiveHostel->replicate();
                    $approvedHostel->approve_date = Carbon::now();
                    $approvedHostel->setTable('mar_hostel_renewals');
                    $approvedHostel->app_id = $temp_id;
                    $approvedHostel->save();

                    $mMarActiveHostel->delete();

                    // Update in mar_hostels (last_renewal_id)
                    DB::table('mar_hostels')
                        ->where('id', $temp_id)
                        ->update(['last_renewal_id' => $approvedHostel->id]);
                    $msg = "Application Successfully Renewal !!";
                }
            }
            // Rejection
            if ($req->status == 0) {
                //Hostel Application replication
                $rejectedhostel = $mMarActiveHostel->replicate();
                $rejectedhostel->setTable('mar_rejected_hostels');
                $rejectedhostel->id = $mMarActiveHostel->id;
                $rejectedhostel->rejected_date = Carbon::now();
                $rejectedhostel->save();
                $mMarActiveHostel->delete();
                $msg = "Application Successfully Rejected !!";
            }
            DB::commit();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, $msg, "", '050117', 01, "$executionTime Sec", 'Post', $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false,  $e->getMessage(), "", '050117', 01, "", 'Post', $req->deviceId);
        }
    }

    /**
     * Approved Application List for Citizen
     * @param Request $req
     * @return void
     * | Function - 15
     */
    public function listApproved(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);

            $citizenId = authUser()->id;
            $userType = authUser()->user_type;
            $mMarHostel = new MarHostel();
            $applications = $mMarHostel->listApproved($citizenId, $userType);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            if ($data1['arrayCount'] == 0) {
                $data1 = null;
            }

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Approved Application List", $data1, "040103", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * Rejected Application List
     * @param Request $req
     * @return void
     * | Function - 16
     */
    public function listRejected(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);

            $citizenId = authUser()->id;
            $mMarRejectedHostel = new MarRejectedHostel();
            $applications = $mMarRejectedHostel->listRejected($citizenId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            if ($data1['arrayCount'] == 0) {
                $data1 = null;
            }

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Rejected Application List", $data1, "040103", "1.0", "$executionTime ", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * generate Payment OrderId for Payment
     * @param Request $req
     * @return void
     * | Function - 17
     */
    public function generatePaymentOrderId(Request $req)
    {
        $req->validate([
            'id' => 'required|integer',
        ]);
        try {
            // Variable initialization
            $startTime = microtime(true);

            $mMarHostel = MarHostel::find($req->id);
            $reqData = [
                "id" => $mMarHostel->id,
                'amount' => $mMarHostel->payment_amount,
                'workflowId' => $mMarHostel->workflow_id,
                'ulbId' => $mMarHostel->ulb_id,
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

            $data->name = $mMarHostel->applicant;
            $data->email = $mMarHostel->email;
            $data->contact = $mMarHostel->mobile_no;
            $data->type = "Hostel";

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Payment OrderId Generated Successfully !!!", $data, "050123", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050123", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * Get application Details For Payment
     * @return void
     * | Function - 18
     */
    public function getApplicationDetailsForPayment(Request $req)
    {
        $req->validate([
            'applicationId' => 'required|integer',
        ]);
        try {
            // Variable initialization
            $startTime = microtime(true);

            $mMarHostel = new MarHostel();
            if ($req->applicationId) {
                $data = $mMarHostel->getApplicationDetailsForPayment($req->applicationId);
            }

            if (!$data)
                throw new Exception("Application Not Found");

            $data['type'] = "Hostel";

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, 'Data Fetched',  $data, "050124", "1.0", "$executionTime Sec", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050123", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Payment Application Via Cash
     * | Function - 19
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

            $mMarHostel = new MarHostel();
            $mAdvMarTransaction=new AdvMarTransaction();
            $appDetails=MarHostel::find($req->applicationId);
            DB::beginTransaction();
            $data = $mMarHostel->paymentByCash($req);
            $mAdvMarTransaction->addTransaction($appDetails, $this->_moduleIds,"Market","Cash");
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            if ($req->status == '1' && $data['status'] == 1) {
                return responseMsgs(true, "Payment Successfully !!", ['status' => true, 'transactionNo' => $data['payment_id'], 'workflowId' => $this->_workflowIds], "040501", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
            } else {
                return responseMsgs(false, "Payment Rejected !!", '', "040501", "1.0", "", 'POST', $req->deviceId ?? "");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Entry Cheque or DD for payment
     * | Function - 20
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

            return responseMsgs(true, "Check Entry Successfully !!", ['status' => true, 'TransactionNo' => $transNo], "040501", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Clear or Bounce cheque for payment
     * | Function - 21
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
            $appDetails=MarHostel::find($req->applicationId);
            DB::beginTransaction();
            $status = $mAdvCheckDtl->clearOrBounceCheque($req);
            $mAdvMarTransaction->addTransaction($appDetails, $this->_moduleIds,"Market","Cheque/DD");
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            if ($req->status == '1' && $status == 1) {
                return responseMsgs(true, "Payment Successfully !!", '', "040501", "1.0", " $executionTime Sec", 'POST', $req->deviceId ?? "");
            } else {
                return responseMsgs(false, "Payment Rejected !!", '', "040501", "1.0", "", 'POST', $req->deviceId ?? "");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    /**
     * | Verify Single Application Approve or reject
     * | Function - 22
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
            $mMarActiveHostel = new MarActiveHostel();
            $mWfRoleusermap = new WfRoleusermap();
            $wfDocId = $req->id;
            $userId = authUser()->id;
            $applicationId = $req->applicationId;

            $wfLevel = Config::get('constants.MARKET-LABEL');
            // Derivative Assigments
            $appDetails = $mMarActiveHostel->getHostelDetails($applicationId);

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

            return responseMsgs(true, $req->docStatus . " Successfully", "", "010204", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "010204", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Check if the Document is Fully Verified or Not (4.1)
     * | Function - 23
     */
    public function ifFullDocVerified($applicationId)
    {
        $mMarActiveHostel = new MarActiveHostel();
        $mWfActiveDocument = new WfActiveDocument();
        $mMarActiveHostel = $mMarActiveHostel->getHostelDetails($applicationId);                      // Get Application Details
        $refReq = [
            'activeId' => $applicationId,
            'workflowId' => $mMarActiveHostel->workflow_id,
            'moduleId' =>  $this->_moduleIds
        ];
        $req = new Request($refReq);
        $refDocList = $mWfActiveDocument->getDocsByActiveId($req);
        $totalApproveDoc = $refDocList->count();
        // self Advertiesement List Documents
        $ifAdvDocUnverified = $refDocList->contains('verify_status', 0);
        $totalNoOfDoc = $mWfActiveDocument->totalNoOfDocs($this->_docCode);
        // $totalNoOfDoc=$mWfActiveDocument->totalNoOfDocs($this->_docCodeRenew);
        // if($mMarActiveHostel->renew_no==NULL){
        //     $totalNoOfDoc=$mWfActiveDocument->totalNoOfDocs($this->_docCode);
        // }
        if ($totalApproveDoc >= $totalNoOfDoc) {
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
     * | Function - 24
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
            $mMarActiveHostel = MarActiveHostel::find($req->applicationId);

            $workflowId = $mMarActiveHostel->workflow_id;
            $backId = json_decode(Redis::get('workflow_initiator_' . $workflowId));
            if (!$backId) {
                $backId = WfWorkflowrolemap::where('workflow_id', $workflowId)
                    ->where('is_initiator', true)
                    ->first();
                $redis->set('workflow_initiator_' . $workflowId, json_encode($backId));
            }

            $mMarActiveHostel->current_role_id = $backId->wf_role_id;
            $mMarActiveHostel->parked = 1;
            $mMarActiveHostel->save();


            $metaReqs['moduleId'] = $this->_moduleIds;
            $metaReqs['workflowId'] = $mMarActiveHostel->workflow_id;
            $metaReqs['refTableDotId'] = "mar_active_hostels.id";
            $metaReqs['refTableIdValue'] = $req->applicationId;
            $metaReqs['verificationStatus'] = $req->verificationStatus;
            $metaReqs['senderRoleId'] = $req->currentRoleId;
            $req->request->add($metaReqs);

            $req->request->add($metaReqs);
            $track = new WorkflowTrack();
            $track->saveTrack($req);

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Successfully Done", "", "", '010710', '01', "$executionTime Sec", 'POST', '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010204", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Back To Citizen Inbox
     * | Function - 25
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

            $mMarActiveHostel = new MarActiveHostel();
            $btcList = $mMarActiveHostel->getHostelList($ulbId)
                ->whereIn('mar_active_hostels.current_role_id', $roleId)
                // ->whereIn('a.ward_mstr_id', $occupiedWards)
                ->where('parked', true)
                ->orderByDesc('mar_active_hostels.id')
                ->get();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "BTC Inbox List", remove_null($btcList), 010717, 1.0, "$executionTime Sec", "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", 010717, 1.0, "271ms", "POST", "", "");
        }
    }
    
    /**
     * | Check full document uploaded or not
     * | Function - 26
     */
    public function checkFullUpload($applicationId)
    {
        $appDetails = MarActiveHostel::find($applicationId);
        $docCode = $this->_docCode;
        // $docCode = $this->_docCodeRenew;
        // if($appDetails->renew_no==NULL){
        //     $docCode = $this->_docCode;
        // }
        $mWfActiveDocument = new WfActiveDocument();
        $moduleId = $this->_moduleIds;
        $totalRequireDocs = $mWfActiveDocument->totalNoOfDocs($docCode);
        $totalUploadedDocs = $mWfActiveDocument->totalUploadedDocs($applicationId, $appDetails->workflow_id, $moduleId);
        if ($totalUploadedDocs >= $totalRequireDocs) {
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
     * | Re-upload rejected document by citizen
     * | Function - 27
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

            $mMarActiveHostel = new MarActiveHostel();
            DB::beginTransaction();
            $appId = $mMarActiveHostel->reuploadDocument($req);
            $this->checkFullUpload($appId);
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Document Uploaded Successfully", "", 010717, 1.0, " $executionTime Sec", "POST", "", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, "Document Not Uploaded", "", 010717, 1.0, "271ms", "POST", "", "");
        }
    }

    /**
     * | Get Application Details For Renew
     * | Function - 28
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
            // Variable initialization
            $startTime = microtime(true);

            $mMarHostel = new MarHostel();
            $details = $mMarHostel->applicationDetailsForRenew($req->applicationId);
            if (!$details)
                throw new Exception("Application Not Found !!!");

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Application Fetched !!!", remove_null($details), "050103", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040301", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Apply for Lodge
     * | @param StoreRequest 
     * | Function - 29
     */
    public function renewApplication(RenewalRequest $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);

            $mMarActiveLodge = $this->_modelObj;
            $citizenId = ['citizenId' => authUser()->id];
            $req->request->add($citizenId);

            $mCalculateRate = new CalculateRate;
            $generatedId = $mCalculateRate->generateId($req->bearerToken(), $this->_tempParamId, $req->ulbId); // Generate Application No
            $applicationNo = ['application_no' => $generatedId];

            DB::beginTransaction();
            $applicationNo = $mMarActiveLodge->renewApplication($req);       //<--------------- Model function to store 
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Successfully Renewal the application !!", ['status' => true, 'ApplicationNo' => $applicationNo], "050101", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050101", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Get Application Details For Update Application
     * | Function - 30
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
            $mMarActiveHostel = new MarActiveHostel();
            $details = $mMarActiveHostel->getApplicationDetailsForEdit($req->applicationId);
            if (!$details)
                throw new Exception("Application Not Found !!!");
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Application Featch Successfully !!!", $details, "050827", 1.0, "$executionTime Sec", "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, "Application Not Featched !!!", "", "050827", 1.0, "271ms", "POST", "", "");
        }
    }
    /**
     * Application Updation
     * | Function - 31
     */
    public function editApplication(UpdateRequest $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);

            $mMarActiveHostel = $this->_modelObj;
            DB::beginTransaction();
            $res = $mMarActiveHostel->updateApplication($req);       //<--------------- Update Banquet Hall Application
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            if ($res)
                return responseMsgs(true, "Application Update Successfully !!!", "", "050828", 1.0, "$executionTime Sec", "POST", "", "");
            else
                return responseMsgs(false, "Application Not Updated !!!", "", "050828", 1.0, "271ms", "POST", "", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, "Application Not Updated !!!", $e->getMessage(), "050828", 1.0, "271ms", "POST", "", "");
        }
    }
}
