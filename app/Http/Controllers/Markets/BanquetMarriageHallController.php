<?php

namespace App\Http\Controllers\Markets;

use App\Http\Controllers\Controller;
use App\Models\Markets\MarketPriceMstrs;
use Illuminate\Http\Request;
use App\Http\Requests\BanquetMarriageHall\StoreRequest;
use App\Models\Advertisements\WfActiveDocument;
use App\Models\Markets\MarActiveBanquteHall;
use App\Models\Markets\MarBanquteHall;
use App\Models\Markets\MarketPriceMstr;
use App\Models\Markets\MarRejectedBanquteHall;
use App\Models\Workflows\WfWardUser;
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
use Illuminate\Support\Facades\Validator;

/**
 * | Created on - 06-02-2023
 * | Created By - Bikash Kumar
 * | Banquet Marriage Hall
 * | Status - Open
 */
class BanquetMarriageHallController extends Controller
{

    use WorkflowTrait;
    use MarDetailsTraits;
    
    protected $_modelObj;  //  Generate Model Instance
    protected $_repository;
    protected $_workflowIds;
    protected $_moduleIds;

    //Constructor
    public function __construct(iMarketRepo $mar_repo)
    {
        $this->_modelObj = new MarActiveBanquteHall();
        $this->_workflowIds = Config::get('workflow-constants.BANQUTE_MARRIGE_HALL_WORKFLOWS');
        $this->_moduleIds = Config::get('workflow-constants.MARKET_MODULE_ID');
        $this->_repository = $mar_repo;
    }

    /**
     * | Store 
     * | @param StoreRequest Request
     */
    public function addNew(StoreRequest $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mMarActiveBanquteHall = $this->_modelObj;
            if (authUser()->user_type == 'JSK') {
                $userId = ['userId' => authUser()->id];
                $req->request->add($userId);
            } else {
                $citizenId = ['citizenId' => authUser()->id];
                $req->request->add($citizenId);
            }

            DB::beginTransaction();
            $applicationNo = $mMarActiveBanquteHall->addNew($req);       //<--------------- Model function to store 
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
     * | Inbox List
     * | @param Request $req
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
            return responseMsgs(true, "Inbox Applications", remove_null($inboxList->toArray()), "050103", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050103", "1.0", "", 'POST', $req->deviceId ?? "");
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
            $mMarActiveBanquteHall = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });

            $outboxList = $mMarActiveBanquteHall->listOutbox($roleIds);                 // <----- Get Outbox List

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Outbox Lists", remove_null($outboxList->toArray()), "050104", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050104", "1.0", "", 'POST', $req->deviceId ?? "");
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

            $metaReqs['customFor'] = 'Banqute-Marrige Hall';
            $metaReqs['wfRoleId'] = $data['current_role_id'];
            $metaReqs['workflowId'] = $data['workflow_id'];
            $metaReqs['lastRoleId'] = $data['last_role_id'];

            $req->request->add($metaReqs);
            $forwardBackward = $this->getRoleDetails($req);                                      // Get Role Details
            $fullDetailsData['roleDetails'] = collect($forwardBackward)['original']['data'];

            $fullDetailsData = remove_null($fullDetailsData);

            $fullDetailsData['application_no'] = $data['application_no'];
            $fullDetailsData['apply_date'] = $data['application_date'];
            $fullDetailsData['timelineData'] = collect($req);                                     // Get Timeline Data
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, 'Data Fetched', $fullDetailsData, "050105", "1.0", "$executionTime Sec", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
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
            // Variable initialization
            $startTime = microtime(true);
            $citizenId = authUser()->id;
            $mMarActiveBanquteHall = $this->_modelObj;

            $applications = $mMarActiveBanquteHall->listAppliedApplications($citizenId);                // Get Citizen Apply List

            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            if($totalApplication==0){
                $data1['data'] = null;
            }
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true,"Applied Applications",$data1,"050106","1.0","$executionTime Sec","POST",$req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(false,$e->getMessage(),"","050106","1.0","","POST",$req->deviceId ?? "");
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
            $data = MarActiveBanquteHall::find($applicationId);
            $data->is_escalate = $request->escalateStatus;
            $data->escalate_by = $userId;
            $data->save();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, $request->escalateStatus == 1 ? 'Banqute Marriage Hall is Escalated' : "Banqute Marriage Hall is removed from Escalated", '', "050107", "1.0", "$executionTime Sec", "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $request->all());
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

            $advData = $this->_repository->specialInbox($this->_workflowIds)                      // Repository function to get Markets Details
                ->where('is_escalate', 1)
                ->where('mar_active_banqute_halls.ulb_id', $ulbId)
                // ->whereIn('ward_mstr_id', $wardId)
                ->get();
                $endTime = microtime(true);
                $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Data Fetched", remove_null($advData), "050108", "1.0", "$executionTime Sec", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
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
            $adv = MarActiveBanquteHall::find($request->applicationId);
            $adv->last_role_id = $adv->current_role_id;
            $adv->current_role_id = $request->receiverRoleId;
            $adv->save();

            $metaReqs['moduleId'] = $this->_moduleIds;
            $metaReqs['workflowId'] = $adv->workflow_id;
            $metaReqs['refTableDotId'] = "mar_active_banqute_halls.id";
            $metaReqs['refTableIdValue'] = $request->applicationId;
            $request->request->add($metaReqs);

            $track = new WorkflowTrack();
            $track->saveTrack($request);
            DB::commit();
                    
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Successfully Forwarded The Application!!", "", "050109", "1.0", "$executionTime Sec", "POST", $request->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), $request->all());
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
            $mMarActiveBanquteHall = MarActiveBanquteHall::find($request->applicationId);                // Advertisment Details
            $mModuleId = $this->_moduleIds;
            $metaReqs = array();
            DB::beginTransaction();
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
            $workflowTrack->saveTrack($request);

            DB::commit();
            
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "You Have Commented Successfully!!", ['Comment' => $request->comment], "050110", "1.0", " $executionTime Sec", "POST", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "");
        }
    }


    /**
     * Get Uploaded Document by application ID
     * @param Request $req
     * @return void
     */
    public function viewBmHallDocuments(Request $req)
    {
        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        if ($req->applicationId && $req->type) {
            if($req->type=='Active'){
                $appId=$req->applicationId;
            }elseif($req->type=='Reject'){
                $appId=MarRejectedBanquteHall::find($req->applicationId)->temp_id;
            }elseif($req->type=='Approve'){
                $appId=MarBanquteHall::find($req->applicationId)->temp_id;
            }
            $data = $mWfActiveDocument->uploadDocumentsViewById($appId, $this->_workflowIds);
        }else{
            throw new Exception("Required Application Id And Application Type");
        }
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

       return responseMsgs(true, "Data Fetched", remove_null($data), "050115", "1.0", "$executionTime Sec", "POST", "");
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
            'status' => 'required|integer',
            // 'payment_amount' => 'required',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
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
                // $payment_amount = ['payment_amount' => 1000];
                // $req->request->add($payment_amount);


                // Banqute Hall Application replication

                $approvedbanqutehall = $mMarActiveBanquteHall->replicate();
                $approvedbanqutehall->setTable('mar_banqute_halls');
                $temp_id = $approvedbanqutehall->temp_id = $mMarActiveBanquteHall->id;
                $approvedbanqutehall->payment_amount = $req->payment_amount;
                $approvedbanqutehall->approve_date = Carbon::now();
                $approvedbanqutehall->save();

                // Save in Banqute Hall Renewal
                $approvedbanqutehall = $mMarActiveBanquteHall->replicate();
                $approvedbanqutehall->approve_date = Carbon::now();
                $approvedbanqutehall->setTable('mar_banqute_hall_renewals');
                $approvedbanqutehall->app_id = $temp_id;
                $approvedbanqutehall->save();


                $mMarActiveBanquteHall->delete();

                // Update in mar_banqute_halls (last_renewal_id)

                DB::table('mar_banqute_halls')
                    ->where('temp_id', $temp_id)
                    ->update(['last_renewal_id' => $approvedbanqutehall->id]);

                $msg = "Application Successfully Approved !!";
            }
            // Rejection
            if ($req->status == 0) {

                // $payment_amount = ['payment_amount' => 0];
                // $req->request->add($payment_amount);
                // Banqute Hall Application replication
                $rejectedbanqutehall = $mMarActiveBanquteHall->replicate();
                $rejectedbanqutehall->setTable('mar_rejected_banqute_halls');
                $rejectedbanqutehall->temp_id = $mMarActiveBanquteHall->id;
                $rejectedbanqutehall->rejected_date = Carbon::now();
                $rejectedbanqutehall->save();
                $mMarActiveBanquteHall->delete();
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
     */
    public function listApproved(Request $req)
    {
        try {
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
            return responseMsgs(true, "Approved Application List", $data1, "040103", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
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
            $mMarRejectedBanquteHall = new MarRejectedBanquteHall();
            $applications = $mMarRejectedBanquteHall->listRejected($citizenId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            if ($data1['arrayCount'] == 0) {
                $data1 = null;
            }
            return responseMsgs(true, "Rejected Application List", $data1, "040103", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
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
            // return $data;
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
     */
    public function getApplicationDetailsForPayment(Request $req)
    {
        $req->validate([
            'applicationId' => 'required|integer',
        ]);
        try {
            $startTime = microtime(true);
            $mMarBanquteHall = new MarBanquteHall();
            if ($req->applicationId) {
                $data = $mMarBanquteHall->getApplicationDetailsForPayment($req->applicationId);
            }

            if (!$data)
                throw new Exception("Application Not Found");

            $data['type'] = "Agency";
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, 'Data Fetched',  $data, "050124", "1.0", "$executionTime Sec", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

     /**
     * Get Payment Details
     */
    // public function getPaymentDetails(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'paymentId' => 'required|string'
    //     ]);
    //     if ($validator->fails()) {
    //         return ['status' => false, 'message' => $validator->errors()];
    //     }
    //     try {
    //         $mMarBanquteHall = new MarBanquteHall();
    //         $paymentDetails = $mMarBanquteHall->getPaymentDetails($req->paymentId);
    //         if (empty($paymentDetails)) {
    //             throw new Exception("Payment Details Not Found By Given Paymenst Id !!!");
    //         }else{
    //             return responseMsgs(true, 'Data Fetched',  $paymentDetails, "050124", "1.0", "2 Sec", "POST", $req->deviceId);
    //         }
    //     } catch (Exception $e) {
    //         responseMsgs(false, $e->getMessage(), "");
    //     }
    // }
}
