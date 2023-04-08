<?php

namespace App\Http\Controllers\Markets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lodge\RenewalRequest;
use App\Http\Requests\Lodge\StoreRequest;
use App\Http\Requests\Lodge\UpdateRequest;
use App\Models\Advertisements\AdvChequeDtl;
use App\Models\Advertisements\WfActiveDocument;
use App\Models\Markets\MarActiveLodge;
use App\Models\Markets\MarketPriceMstr;
use App\Models\Markets\MarLodge;
use App\Models\Markets\MarRejectedLodge;
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

class LodgeController extends Controller
{

    use WorkflowTrait;

    use MarDetailsTraits;

    protected $_modelObj;
    protected $_workflowIds;
    protected $_moduleIds;
    protected $_repository;
    protected $_docCode;
    protected $_docCodeRenew;
    protected $_baseUrl;
    protected $_tempParamId;
    protected $_paramId;

    //Constructor
    public function __construct(iMarketRepo $mar_repo)
    {
        $this->_modelObj = new MarActiveLodge();
        $this->_workflowIds = Config::get('workflow-constants.LODGE_WORKFLOWS');
        $this->_moduleIds = Config::get('workflow-constants.MARKET_MODULE_ID');
        $this->_repository = $mar_repo;
        $this->_docCode = config::get('workflow-constants.LODGE_DOC_CODE');
        $this->_docCodeRenew = config::get('workflow-constants.LODGE_DOC_CODE_RENEW');

        $this->_paramId = Config::get('workflow-constants.LOD_ID');
        $this->_tempParamId = Config::get('workflow-constants.T_LOD_ID');
        $this->_baseUrl = Config::get('constants.BASE_URL');
    }
    /**
     * | Apply for Lodge
     * | @param StoreRequest 
     */
    public function addNew(StoreRequest $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mMarActiveLodge = $this->_modelObj;
            $citizenId = ['citizenId' => authUser()->id];
            $req->request->add($citizenId);

            
            // Generate Application No
            $reqData = [
                "paramId" => $this->_tempParamId,
                'ulbId' => $req->ulbId
            ];
            $refResponse = Http::withToken($req->bearerToken())
                ->post($this->_baseUrl . 'api/id-generator', $reqData);
            $idGenerateData = json_decode($refResponse);
            $applicationNo = ['application_no' => $idGenerateData->data];
            $req->request->add($applicationNo);

            DB::beginTransaction();
            $applicationNo = $mMarActiveLodge->addNew($req);       //<--------------- Model function to store 
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Successfully Submitted the application !!", ['status' => true, 'ApplicationNo' => $applicationNo], "050701", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050701", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }



    /**
     * | Inbox List
     * | @param Request $req
     */
    public function listInbox(Request $req)
    {
        try {
            $startTime = microtime(true);
            $mMarActiveLodge = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $inboxList = $mMarActiveLodge->listInbox($roleIds);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Inbox Applications", remove_null($inboxList->toArray()), "050702", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050702", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * | Outbox List
     */
    public function listOutbox(Request $req)
    {
        try {
            $startTime = microtime(true);
            $mMarActiveLodge = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $outboxList = $mMarActiveLodge->listOutbox($roleIds);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Outbox Lists", remove_null($outboxList->toArray()), "050703", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050703", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * | Application Details
     */

    public function getDetailsById(Request $req)
    {
        try {
            $startTime = microtime(true);
            $mMarActiveLodge = $this->_modelObj;
            $fullDetailsData = array();
            if (isset($req->type)) {
                $type = $req->type;
            } else {
                $type = NULL;
            }
            if ($req->applicationId) {
               $data = $mMarActiveLodge->getDetailsById($req->applicationId, $type);
            } else {
                throw new Exception("Not Pass Application Id");
            }

            if (!$data)
                throw new Exception("Application Not Found");
            // Basic Details
            $basicDetails = $this->generateBasicDetailsForLodge($data); // Trait function to get Basic Details
            $basicElement = [
                'headerTitle' => "Basic Details",
                "data" => $basicDetails
            ];

            $cardDetails = $this->generateCardDetails($data);
            $cardElement = [
                'headerTitle' => "About Lodge",
                'data' => $cardDetails
            ];
            $fullDetailsData['fullDetailsData']['dataArray'] = new Collection([$basicElement]);
            $fullDetailsData['fullDetailsData']['cardArray'] = new Collection($cardElement);

            // return ($data);

            $metaReqs['customFor'] = 'LODGE';
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
            return responseMsgs(true, 'Data Fetched', $fullDetailsData, "050704", "1.0", "$executionTime Sec", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050704", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

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
     */
    public function listAppliedApplications(Request $req)
    {
        try {
            $startTime = microtime(true);
            $citizenId = authUser()->id;
            $mMarActiveLodge = $this->_modelObj;
            $applications = $mMarActiveLodge->listAppliedApplications($citizenId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Applied Applications", $data1, "050705", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050705", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     *  | Escalate
     * @param Request $request
     * @return void
     */
    public function escalateApplication(Request $request)
    {
        $request->validate([
            "escalateStatus" => "required|int",
            "applicationId" => "required|int",
        ]);
        try {
            $startTime = microtime(true);
            $userId = auth()->user()->id;
            $applicationId = $request->applicationId;
            $data = MarActiveLodge::find($applicationId);
            $data->is_escalate = $request->escalateStatus;
            $data->escalate_by = $userId;
            $data->save();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, $request->escalateStatus == 1 ? 'Lodge is Escalated' : "Lodge is removed from Escalated", '', "050706", "1.0", "$executionTime Sec", "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050706", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }

    /**
     *  Special Inbox List
     * @param Request $req
     * @return void
     */
    public function listEscalated(Request $req)
    {
        try {
            $startTime = microtime(true);
            $mWfWardUser = new WfWardUser();
            $userId = authUser()->id;
            $ulbId = authUser()->ulb_id;

            $occupiedWard = $mWfWardUser->getWardsByUserId($userId);                        // Get All Occupied Ward By user id using trait
            $wardId = $occupiedWard->map(function ($item, $key) {                           // Filter All ward_id in an array using laravel collections
                return $item->ward_id;
            });

            $advData = $this->_repository->specialInboxLodge($this->_workflowIds)                      // Repository function to get Markets Details
                ->where('is_escalate', 1)
                ->where('mar_active_lodges.ulb_id', $ulbId)
                // ->whereIn('ward_mstr_id', $wardId)
                ->get();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Data Fetched", remove_null($advData), "050707", "1.0", "$executionTime Sec", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050707", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }




    /**
     * Forward or Backward Application
     * @param Request $request
     * @return void
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
            $startTime = microtime(true);
            // Marriage Banqute Hall Application Update Current Role Updation
            DB::beginTransaction();
            $mMarActiveLodge = MarActiveLodge::find($request->applicationId);
            if($mMarActiveLodge->doc_verify_status=='0')
                throw new Exception("Please Verify All Documents To Forward The Application !!!");

            $mMarActiveLodge->last_role_id = $mMarActiveLodge->current_role_id;
            $mMarActiveLodge->current_role_id = $request->receiverRoleId;
            $mMarActiveLodge->save();

            $metaReqs['moduleId'] = $this->_moduleIds;
            $metaReqs['workflowId'] = $mMarActiveLodge->workflow_id;
            $metaReqs['refTableDotId'] = "mar_active_lodges.id";
            $metaReqs['refTableIdValue'] = $request->applicationId;
            $request->request->add($metaReqs);

            $track = new WorkflowTrack();
            $track->saveTrack($request);
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Successfully Forwarded The Application!!", "", "050708", "1.0", "$executionTime Sec", "POST", $request->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050708", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }



    /**
     * Post Independent Comment
     * @param Request $request
     * @return void
     */
    public function commentApplication(Request $request)
    {
        $request->validate([
            'comment' => 'required',
            'applicationId' => 'required|integer',
            'senderRoleId' => 'nullable|integer'
        ]);

        try {
            $startTime = microtime(true);
            $workflowTrack = new WorkflowTrack();
            $mMarActiveLodge = MarActiveLodge::find($request->applicationId);                // Advertisment Details
            $mModuleId = $this->_moduleIds;
            $metaReqs = array();
            DB::beginTransaction();
            // Save On Workflow Track For Level Independent
            $metaReqs = [
                'workflowId' => $mMarActiveLodge->workflow_id,
                'moduleId' => $mModuleId,
                'refTableDotId' => "mar_active_lodges.id",
                'refTableIdValue' => $mMarActiveLodge->id,
                'message' => $request->comment
            ];
            // For Citizen Independent Comment
            if (!$request->senderRoleId) {
                $metaReqs = array_merge($metaReqs, ['citizenId' => $mMarActiveLodge->user_id]);
            }

            $request->request->add($metaReqs);
            $workflowTrack->saveTrack($request);

            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "You Have Commented Successfully!!", ['Comment' => $request->comment], "050709", "1.0", " $executionTime Sec", "POST", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050709", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }


    /**
     * Get Uploaded Document by application ID
     * @param Request $req
     * @return void
     */
    public function viewLodgeDocuments(Request $req)
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
        $startTime = microtime(true);
        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        if ($req->applicationId) {
            $data = $mWfActiveDocument->uploadDocumentsViewById($req->applicationId, $this->_workflowIds);
        }
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        return responseMsgs(true, "Data Fetched", remove_null($data), "050712", "1.0", "$executionTime Sec", "POST", "");
    }


    /**
     * Final Approval and Rejection of the Application
     * @param Request $req
     * @return void
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
            $startTime = microtime(true);
            // Check if the Current User is Finisher or Not         
            $mMarActiveLodge = MarActiveLodge::find($req->applicationId);
            $getFinisherQuery = $this->getFinisherId($mMarActiveLodge->workflow_id);                                 // Get Finisher using Trait
            $refGetFinisher = collect(DB::select($getFinisherQuery))->first();
            if ($refGetFinisher->role_id != $req->roleId) {
                return responseMsgs(false, " Access Forbidden", "");
            }

            DB::beginTransaction();
            // Approval
            if ($req->status == 1) {
                $mMarketPriceMstr = new MarketPriceMstr();
                $amount = $mMarketPriceMstr->getMarketTaxPrice($mMarActiveLodge->workflow_id, $mMarActiveLodge->no_of_beds, $mMarActiveLodge->ulb_id);
                $payment_amount = ['payment_amount' => $amount];
                $req->request->add($payment_amount);

                   // License NO Generate
                   $reqData = [
                    "paramId" => $this->_paramId,
                    'ulbId' => $mMarActiveLodge->ulb_id
                ];
                $refResponse = Http::withToken($req->bearerToken())
                    ->post($this->_baseUrl . 'api/id-generator', $reqData);

                $idGenerateData = json_decode($refResponse);

                if ($mMarActiveLodge->renew_no == NULL) {
                    // Lodge Application replication
                    $approvedlodge = $mMarActiveLodge->replicate();
                    $approvedlodge->setTable('mar_lodges');
                    $temp_id = $approvedlodge->id = $mMarActiveLodge->id;
                    $approvedlodge->payment_amount = $req->payment_amount;
                    $approvedlodge->license_no = $idGenerateData->data;
                    $approvedlodge->approve_date = Carbon::now();
                    $approvedlodge->save();

                    // Save in Lodge Renewal
                    $approvedlodge = $mMarActiveLodge->replicate();
                    $approvedlodge->approve_date = Carbon::now();
                    $approvedlodge->setTable('mar_lodge_renewals');
                    $approvedlodge->license_no = $idGenerateData->data;
                    $approvedlodge->app_id = $temp_id;
                    $approvedlodge->save();

                    $mMarActiveLodge->delete();

                    // Update in mar_lodges (last_renewal_id)
                    DB::table('mar_lodges')
                        ->where('id', $temp_id)
                        ->update(['last_renewal_id' => $approvedlodge->id]);

                    $msg = "Application Successfully Approved !!";
                } else {
                     //  Renewal Case
                     // Lodge Application replication
                     $application_no=$mMarActiveLodge->application_no;
                     MarLodge::where('application_no', $application_no)->delete();
 
                      $approvedlodge = $mMarActiveLodge->replicate();
                      $approvedlodge->setTable('mar_lodges');
                      $temp_id = $approvedlodge->id = $mMarActiveLodge->id;
                      $approvedlodge->payment_amount = $req->payment_amount;
                      $approvedlodge->payment_status = $req->payment_status;
                      $approvedlodge->approve_date = Carbon::now();
                      $approvedlodge->save(); 

                      // Save in Lodge Renewal
                      $approvedlodge = $mMarActiveLodge->replicate();
                      $approvedlodge->approve_date = Carbon::now();
                      $approvedlodge->setTable('mar_lodge_renewals');
                      $approvedlodge->app_id = $temp_id;
                      $approvedlodge->save();
  
                      $mMarActiveLodge->delete();
  
                      // Update in amar_lodges (last_renewal_id)
                      DB::table('mar_lodges')
                          ->where('id', $temp_id)
                          ->update(['last_renewal_id' => $approvedlodge->id]);
                      $msg = "Application Successfully Renewal !!";
                }
            }
            // Rejection
            if ($req->status == 0) {
                //Lodge Application replication
                $rejectedlodge = $mMarActiveLodge->replicate();
                $rejectedlodge->setTable('mar_rejected_lodges');
                $rejectedlodge->id = $mMarActiveLodge->id;
                $rejectedlodge->rejected_date = Carbon::now();
                $rejectedlodge->save();
                $mMarActiveLodge->delete();
                $msg = "Application Successfully Rejected !!";
            }
            DB::commit();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, $msg, "", '050713', 01, "$executionTime Sec", 'Post', $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false,  $e->getMessage(), "", '050713', 01, "", 'Post', $req->deviceId);
        }
    }

    /**
     * Approved Application List for Citizen
     * @param Request $req
     * @return void
     */
    public function listApproved(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $userType = authUser()->user_type;
            $mMarLodge = new MarLodge();
            $applications = $mMarLodge->listApproved($citizenId, $userType);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            if ($data1['arrayCount'] == 0) {
                $data1 = null;
            }
            return responseMsgs(true, "Approved Application List", $data1, "050714", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050714", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }



    /**
     * Rejected Application List
     * @param Request $req
     * @return void
     */
    public function listRejected(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $mMarRejectedLodge = new MarRejectedLodge();
            $applications = $mMarRejectedLodge->listRejected($citizenId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            if ($data1['arrayCount'] == 0) {
                $data1 = null;
            }
            return responseMsgs(true, "Rejected Application List", $data1, "050715", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050715", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * generate Payment OrderId for Payment
     * @param Request $req
     * @return void
     */
    public function generatePaymentOrderId(Request $req)
    {
        $req->validate([
            'id' => 'required|integer',
        ]);
        try {
            $startTime = microtime(true);
            $mMarLodge = MarLodge::find($req->id);
            $reqData = [
                "id" => $mMarLodge->id,
                'amount' => $mMarLodge->payment_amount,
                'workflowId' => $mMarLodge->workflow_id,
                'ulbId' => $mMarLodge->ulb_id,
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

            $data->name = $mMarLodge->applicant;
            $data->email = $mMarLodge->email;
            $data->contact = $mMarLodge->mobile_no;
            $data->type = "Lodge";
            // return $data;
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Payment OrderId Generated Successfully !!!", $data, "050716", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050716", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * Get application Details For Payment
     * @return void
     */
    public function getApplicationDetailsForPayment(Request $req)
    {
        $req->validate([
            'applicationId' => 'required|integer',
        ]);
        try {
            $startTime = microtime(true);
            $mMarLodge = new MarLodge();
            if ($req->applicationId) {
                $data = $mMarLodge->getApplicationDetailsForPayment($req->applicationId);
            }

            if (!$data)
                throw new Exception("Application Not Found");

            $data['type'] = "Lodge";
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, 'Data Fetched',  $data, "050717", "1.0", "$executionTime Sec", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050717", "1.0", "", 'POST', $req->deviceId ?? "");
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
            $mWfDocument = new WfActiveDocument();
            $mMarActiveLodge = new MarActiveLodge();
            $mWfRoleusermap = new WfRoleusermap();
            $wfDocId = $req->id;
            $userId = authUser()->id;
            $applicationId = $req->applicationId;

            $wfLevel = Config::get('constants.MARKET-LABEL');
            // Derivative Assigments
            $appDetails = $mMarActiveLodge->getLodgeDetails($applicationId);

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
            return responseMsgs(true, $req->docStatus . " Successfully", "", "050718", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050718", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Check if the Document is Fully Verified or Not (4.1)
     */
    public function ifFullDocVerified($applicationId)
    {
        $mMarActiveLodge = new MarActiveLodge();
        $mWfActiveDocument = new WfActiveDocument();
        $mMarActiveLodge = $mMarActiveLodge->getLodgeDetails($applicationId);                      // Get Application Details
        $refReq = [
            'activeId' => $applicationId,
            'workflowId' => $mMarActiveLodge->workflow_id,
            'moduleId' =>  $this->_moduleIds
        ];
        $req = new Request($refReq);
        $refDocList = $mWfActiveDocument->getDocsByActiveId($req);
        $totalApproveDoc=$refDocList->count();
        // self Advertiesement List Documents
        $ifAdvDocUnverified = $refDocList->contains('verify_status', 0);
        $totalNoOfDoc=$mWfActiveDocument->totalNoOfDocs($this->_docCode);
        // $totalNoOfDoc=$mWfActiveDocument->totalNoOfDocs($this->_docCodeRenew);
        // if($mMarActiveLodge->renew_no==NULL){
        //     $totalNoOfDoc=$mWfActiveDocument->totalNoOfDocs($this->_docCode);
        // }
        if($totalApproveDoc==$totalNoOfDoc){
            if ($ifAdvDocUnverified == 1)
                return 0;
            else
                return 1;
        }else{
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
            $redis = Redis::connection();
            $mMarActiveLodge = MarActiveLodge::find($req->applicationId);

            $workflowId = $mMarActiveLodge->workflow_id;
            $backId = json_decode(Redis::get('workflow_initiator_' . $workflowId));
            if (!$backId) {
                $backId = WfWorkflowrolemap::where('workflow_id', $workflowId)
                    ->where('is_initiator', true)
                    ->first();
                $redis->set('workflow_initiator_' . $workflowId, json_encode($backId));
            }

            $mMarActiveLodge->current_role_id = $backId->wf_role_id;
            $mMarActiveLodge->parked = 1;
            $mMarActiveLodge->save();


            $metaReqs['moduleId'] = $this->_moduleIds;
            $metaReqs['workflowId'] = $mMarActiveLodge->workflow_id;
            $metaReqs['refTableDotId'] = "mar_active_lodges.id";
            $metaReqs['refTableIdValue'] = $req->applicationId;
            $metaReqs['verificationStatus'] = $req->verificationStatus;
            $metaReqs['senderRoleId'] = $req->currentRoleId;
            $req->request->add($metaReqs);

            $req->request->add($metaReqs);
            $track = new WorkflowTrack();
            $track->saveTrack($req);

            return responseMsgs(true, "Successfully Done", "", "", '050719', '01', '358ms', 'Post', '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050719", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    /**
     * | Back To Citizen Inbox
     */
    public function listBtcInbox()
    {
        try {
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

            $mMarActiveLodge = new MarActiveLodge();
            $btcList = $mMarActiveLodge->getLodgeList($ulbId)
                ->whereIn('mar_active_lodges.current_role_id', $roleId)
                // ->whereIn('a.ward_mstr_id', $occupiedWards)
                ->where('parked', true)
                ->orderByDesc('mar_active_lodges.id')
                ->get();

            return responseMsgs(true, "BTC Inbox List", remove_null($btcList), "050720", 1.0, "271ms", "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050720", 1.0, "271ms", "POST", "", "");
        }
    }

    public function checkFullUpload($applicationId)
    {
        
        $appDetails = MarActiveLodge::find($applicationId);
        $docCode = $this->_docCode;
        // $docCode = $this->_docCodeRenew;
        // if($appDetails->renew_no==NULL){
        //     $docCode = $this->_docCode;
        // }
        $docCode = $this->_docCode;
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
            $mMarActiveLodge = new MarActiveLodge();
            DB::beginTransaction();
            $appId = $mMarActiveLodge->reuploadDocument($req);
            $this->checkFullUpload($appId);
            DB::commit();
            return responseMsgs(true, "Document Uploaded Successfully", "", "050721", 1.0, "271ms", "POST", "", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, "Document Not Uploaded", "","050721", 1.0, "271ms", "POST", "", "");
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
            $mMarLodge = new MarLodge();
            DB::beginTransaction();
            $data = $mMarLodge->paymentByCash($req);
            DB::commit();
            if ($req->status == '1' && $data['status'] == 1) {
                return responseMsgs(true, "Payment Successfully !!",['status' => true, 'transactionNo' => $data['payment_id'], 'workflowId' => $this->_workflowIds], "050722", "1.0", "", 'POST', $req->deviceId ?? "");
            } else {
                return responseMsgs(false, "Payment Rejected !!", '', "050722", "1.0", "", 'POST', $req->deviceId ?? "");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050722", "1.0", "", "POST", $req->deviceId ?? "");
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
            $mAdvCheckDtl = new AdvChequeDtl();
            $workflowId = ['workflowId' => $this->_workflowIds];
            $req->request->add($workflowId);
            $transNo = $mAdvCheckDtl->entryChequeDd($req);
            return responseMsgs(true, "Check Entry Successfully !!", ['status' => true, 'TransactionNo' => $transNo], "050723", "1.0", "", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050723", "1.0", "", "POST", $req->deviceId ?? "");
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
            $mAdvCheckDtl = new AdvChequeDtl();
            DB::beginTransaction();
            $status = $mAdvCheckDtl->clearOrBounceCheque($req);
            DB::commit();
            if ($req->status == '1' && $status == 1) {
                return responseMsgs(true, "Payment Successfully !!", '', "050724", "1.0", "", 'POST', $req->deviceId ?? "");
            } else {
                return responseMsgs(false, "Payment Rejected !!", '', "050724", "1.0", "", 'POST', $req->deviceId ?? "");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050724", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    
    /**
     * | Get Application Details For Renew
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
            $mMarLodge = new MarLodge();
            $details = $mMarLodge->applicationDetailsForRenew($req->applicationId);
            if (!$details)
                throw new Exception("Application Not Found !!!");

            return responseMsgs(true, "Application Fetched !!!", remove_null($details), "050725", "1.0", "200 ms", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050725", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Apply for Lodge
     * | @param StoreRequest 
     */
    public function renewApplication(RenewalRequest $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mMarActiveLodge = $this->_modelObj;
            $citizenId = ['citizenId' => authUser()->id];
            $req->request->add($citizenId);

            
             // Generate Application No
             $reqData = [
                "paramId" => $this->_tempParamId,
                'ulbId' => $req->ulbId
            ];
            $refResponse = Http::withToken($req->bearerToken())
                ->post($this->_baseUrl . 'api/id-generator', $reqData);
            $idGenerateData = json_decode($refResponse);
            $applicationNo = ['application_no' => $idGenerateData->data];
            $req->request->add($applicationNo);

            DB::beginTransaction();
            $applicationNo = $mMarActiveLodge->renewApplication($req);       //<--------------- Model function to store 
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Successfully Submitted the application !!", ['status' => true, 'ApplicationNo' => $applicationNo], "050726", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050726", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }
 /**
     * | Get Application Details For Update Application
     */
    public function getApplicationDetailsForEdit(Request $req){
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|digits_between:1,9223372036854775807'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $mMarActiveLodge = new MarActiveLodge();
            $details = $mMarActiveLodge->getApplicationDetailsForEdit($req->applicationId);
            if (!$details)
                throw new Exception("Application Not Found !!!");
            return responseMsgs(true, "Application Featch Successfully !!!", $details, "050827", 1.0, "271ms", "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, "Application Not Featched !!!", "", "050827", 1.0, "271ms", "POST", "", "");
        }
    }

    public function editApplication(UpdateRequest $req){
        try {
            $mMarActiveHostel = $this->_modelObj;
            DB::beginTransaction();
            $res = $mMarActiveHostel->updateApplication($req);       //<--------------- Update Banquet Hall Application
            DB::commit();
            if ($res)
                return responseMsgs(true, "Application Update Successfully !!!", "", "050828", 1.0, "271ms", "POST", "", "");
            else
                return responseMsgs(false, "Application Not Updated !!!", "", "050828", 1.0, "271ms", "POST", "", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, "Application Not Updated !!!",$e->getMessage(), "050828", 1.0, "271ms", "POST", "", "");
        }
    }
}
