<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrivateLand\StoreRequest;
use App\Models\Advertisements\AdvActivePrivateland;
use App\Models\Advertisements\AdvChequeDtl;
use App\Models\Advertisements\AdvPrivateland;
use App\Models\Advertisements\AdvRejectedPrivateland;
use App\Models\Advertisements\WfActiveDocument;
use Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\AdvDetailsTraits;


use App\Traits\WorkflowTrait;
use App\Models\Workflows\WorkflowTrack;
use App\Models\Workflows\WfWardUser;
use App\Repositories\SelfAdvets\iSelfAdvetRepo;


use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Expr\Empty_;

/**
 * | Created On-02-01-2022 
 * | Created By-Anshu Kumar
 * | Private Land Operations
 */

class PrivateLandController extends Controller
{

    use WorkflowTrait;
    use AdvDetailsTraits;
    protected $_modelObj;

    protected $_workflowIds;

    protected $Repository;
    public function __construct(iSelfAdvetRepo $privateland_repo)
    {
        $this->_modelObj = new AdvActivePrivateland();
        $this->_workflowIds = Config::get('workflow-constants.PRIVATE_LANDS_WORKFLOWS');
        $this->Repository = $privateland_repo;
    }

    /**
     * | Apply For Private Land Advertisement
     */
    public function addNew(StoreRequest $req)
    {
        try {
            $privateLand = new AdvActivePrivateland();
            if (authUser()->user_type == 'JSK') {
                $userId = ['userId' => authUser()->id];
                $req->request->add($userId);
            } else {
                $citizenId = ['citizenId' => authUser()->id];
                $req->request->add($citizenId);
            }
            $applicationNo = $privateLand->addNew($req);       //<--------------- Model function to store 
            return responseMsgs(true, "Successfully Submitted the application !!", ['status' => true, 'ApplicationNo' => $applicationNo], "040401", "1.0", "", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040401", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    /**
     * | Inbox List
     * | @param Request $req
     */
    public function listInbox(Request $req)
    {
        try {
            $mAdvActivePrivateland = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $inboxList = $mAdvActivePrivateland->listInbox($roleIds);
            return responseMsgs(true, "Inbox Applications", remove_null($inboxList->toArray()), "040103", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Outbox List
     */
    public function listOutbox(Request $req)
    {
        try {
            $selfAdvets = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $outboxList = $selfAdvets->listOutbox($roleIds);
            return responseMsgs(true, "Outbox Lists", remove_null($outboxList->toArray()), "040104", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040104", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Application Details
     */

    public function getDetailsById(Request $req)
    {
        try {
            $mAdvActivePrivateland = new AdvActivePrivateland();
            $fullDetailsData = array();
            if (isset($req->type)) {
                $type = $req->type;
            } else {
                $type = NULL;
            }
            if ($req->applicationId) {
                $data = $mAdvActivePrivateland->getDetailsById($req->applicationId, $type);
            } else {
                throw new Exception("Not Pass Application Id");
            }

            if (!$data) {
                throw new Exception("Not Application Details Found");
            }

            // return $data;
            //Basic Details
            $basicDetails = $this->generatePrivateLandBasicDetails($data); // Trait function to get Basic Details
            $basicElement = [
                'headerTitle' => "Basic Details",
                "data" => $basicDetails
            ];

            $cardDetails = $this->generatePrivateLandCardDetails($data);
            $cardElement = [
                'headerTitle' => "About Advertisement",
                'data' => $cardDetails
            ];
            $fullDetailsData['fullDetailsData']['dataArray'] = new Collection([$basicElement]);
            $fullDetailsData['fullDetailsData']['cardArray'] = new Collection($cardElement);

            // return ($data);

            $metaReqs['customFor'] = 'Private Land Advertisement';
            $metaReqs['wfRoleId'] = $data['current_role_id'];
            $metaReqs['workflowId'] = $data['workflow_id'];
            $metaReqs['lastRoleId'] = $data['last_role_id'];

            $req->request->add($metaReqs);
            $forwardBackward = $this->getRoleDetails($req);
            $fullDetailsData['roleDetails'] = collect($forwardBackward)['original']['data'];

            $fullDetailsData = remove_null($fullDetailsData);

            $fullDetailsData['application_no'] = $data['application_no'];
            $fullDetailsData['apply_date'] = $data['application_date'];
            $fullDetailsData['timelineData'] = collect($req);

            return responseMsgs(true, 'Data Fetched', $fullDetailsData, "010104", "1.0", "303ms", "POST", $req->deviceId);
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
     * | Get Applied Applications by Logged In Citizen
     */
    public function listAppliedApplications(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $mAdvActivePrivateland = new AdvActivePrivateland();
            $applications = $mAdvActivePrivateland->listAppliedApplications($citizenId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            return responseMsgs(true, "Applied Applications", $data1, "040106", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040106", "1.0", "", "POST", $req->deviceId ?? "");
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
            $userId = auth()->user()->id;
            $applicationId = $request->applicationId;
            $data = AdvActivePrivateland::find($applicationId);
            $data->is_escalate = $request->escalateStatus;
            $data->escalate_by = $userId;
            $data->save();
            return responseMsgs(true, $request->escalateStatus == 1 ? 'Private Lands is Escalated' : "Private Lands is removed from Escalated", '', "010106", "1.0", "353ms", "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $request->all());
        }
    }


    public function listEscalated(Request $req)
    {
        try {
            $mWfWardUser = new WfWardUser();
            $userId = authUser()->id;
            $ulbId = authUser()->ulb_id;

            $occupiedWard = $mWfWardUser->getWardsByUserId($userId);                        // Get All Occupied Ward By user id using trait
            $wardId = $occupiedWard->map(function ($item, $key) {                           // Filter All ward_id in an array using laravel collections
                return $item->ward_id;
            });

            // print_r($wardId);

            $advData = $this->Repository->specialPrivateLandInbox($this->_workflowIds)                      // Repository function to get Advertiesment Details
                ->where('is_escalate', 1)
                ->where('adv_active_privatelands.ulb_id', $ulbId)
                // ->whereIn('ward_mstr_id', $wardId)
                ->get();
            return responseMsgs(true, "Data Fetched", remove_null($advData), "010107", "1.0", "251ms", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    /**
     * | Forward or Backward Application
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
            // Advertisment Application Update Current Role Updation
            DB::beginTransaction();
            $adv = AdvActivePrivateland::find($request->applicationId);
            $adv->last_role_id = $request->current_role_id;
            $adv->current_role_id = $request->receiverRoleId;
            $adv->save();

            $metaReqs['moduleId'] = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
            $metaReqs['workflowId'] = $adv->workflow_id;
            $metaReqs['refTableDotId'] = "adv_active_privatelands.id";
            $metaReqs['refTableIdValue'] = $request->applicationId;
            $request->request->add($metaReqs);

            $track = new WorkflowTrack();
            $track->saveTrack($request);
            DB::commit();
            return responseMsgs(true, "Successfully Forwarded The Application!!", "", "010109", "1.0", "286ms", "POST", $request->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), $request->all());
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
            $workflowTrack = new WorkflowTrack();
            $mAdvActivePrivateland = AdvActivePrivateland::find($request->applicationId);                // Advertisment Details
            $mModuleId = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
            $metaReqs = array();
            DB::beginTransaction();
            // Save On Workflow Track For Level Independent
            $metaReqs = [
                'workflowId' => $mAdvActivePrivateland->workflow_id,
                'moduleId' => $mModuleId,
                'refTableDotId' => "adv_active_privatelands.id",
                'refTableIdValue' => $mAdvActivePrivateland->id,
                'message' => $request->comment
            ];
            // For Citizen Independent Comment
            if (!$request->senderRoleId) {
                $metaReqs = array_merge($metaReqs, ['citizenId' => $mAdvActivePrivateland->user_id]);
            }

            $request->request->add($metaReqs);
            $workflowTrack->saveTrack($request);

            DB::commit();
            return responseMsgs(true, "You Have Commented Successfully!!", ['Comment' => $request->comment], "010108", "1.0", "", "POST", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "");
        }
    }


    /**
     * | Get Uploaded Document by application ID
     */
    // public function uploadDocumentsView(Request $req)
    // {
    //     $mAdvActivePrivateland = new AdvActivePrivateland();
    //     $data = array();
    //     $fullDetailsData = array();
    //     if ($req->applicationId) {
    //         $data = $mAdvActivePrivateland->details($req->applicationId, $this->_workflowIds);
    //     }

    //     // $fullDetailsData['application_no'] = $data['application_no'];
    //     // $fullDetailsData['apply_date'] = $data['application_date'];
    //     $fullDetailsData = $data['documents'];


    //     $data1['data'] = $fullDetailsData;
    //     return $data1;
    // }


    public function viewPvtLandDocuments(Request $req)
    {
        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        if ($req->applicationId && $req->type) {
            if ($req->type == 'Active') {
                $appId = $req->applicationId;
            } elseif ($req->type == 'Reject') {
                $appId = AdvRejectedPrivateland::find($req->applicationId)->temp_id;
            } elseif ($req->type == 'Approve') {
                $appId = AdvPrivateland::find($req->applicationId)->temp_id;
            }
            $data = $mWfActiveDocument->uploadDocumentsViewById($appId, $this->_workflowIds);
        } else {
            throw new Exception("Required Application Id And Application Type ");
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
     * |-------------------------------------Final Approval and Rejection of the Application ------------------------------------------------|
     * | Rating-
     * | Status- Open
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

            // Check if the Current User is Finisher or Not         
            $mAdvActivePrivateland = AdvActivePrivateland::find($req->applicationId);
            $getFinisherQuery = $this->getFinisherId($mAdvActivePrivateland->workflow_id);                                 // Get Finisher using Trait
            $refGetFinisher = collect(DB::select($getFinisherQuery))->first();
            if ($refGetFinisher->role_id != $req->roleId) {
                return responseMsgs(false, " Access Forbidden", "");
            }

            DB::beginTransaction();
            // Approval
            if ($req->status == 1) {
                $typology = $mAdvActivePrivateland->typology;
                $zone = $mAdvActivePrivateland->zone;
                if($zone===NULL){
                    throw new Exception("Zone Not Selected !!!");
                }
                $amount = $this->getPrivateLandPayment($typology, $zone);
                $payment_amount = ['payment_amount' => $amount];

                // $payment_amount = ['payment_amount' =>1000];
                $req->request->add($payment_amount);

                // approved Private Land Application replication

                $approvedPrivateland = $mAdvActivePrivateland->replicate();
                $approvedPrivateland->setTable('adv_privatelands');
                $temp_id = $approvedPrivateland->temp_id = $mAdvActivePrivateland->id;
                $approvedPrivateland->payment_amount = $req->payment_amount;
                $approvedPrivateland->approve_date = Carbon::now();
                $approvedPrivateland->zone = $zone;
                $approvedPrivateland->save();

                // Save in Priate Land Application Advertisement Renewal
                $approvedPrivateland = $mAdvActivePrivateland->replicate();
                $approvedPrivateland->approve_date = Carbon::now();
                $approvedPrivateland->setTable('adv_privateland_renewals');
                $approvedPrivateland->privateland_id = $temp_id;
                $approvedPrivateland->zone = $zone;
                $approvedPrivateland->save();


                $mAdvActivePrivateland->delete();

                // Update in adv_privatelands (last_renewal_id)

                DB::table('adv_privatelands')
                    ->where('temp_id', $temp_id)
                    ->update(['last_renewal_id' => $approvedPrivateland->id]);

                $msg = "Application Successfully Approved !!";
            }
            // Rejection
            if ($req->status == 0) {

                $payment_amount = ['payment_amount' => 0];
                $req->request->add($payment_amount);


                // Privateland advertisement Application replication
                $rejectedPrivateland = $mAdvActivePrivateland->replicate();
                $rejectedPrivateland->setTable('adv_rejected_privatelands');
                $rejectedPrivateland->temp_id = $mAdvActivePrivateland->id;
                $rejectedPrivateland->rejected_date = Carbon::now();
                $rejectedPrivateland->save();
                $mAdvActivePrivateland->delete();
                $msg = "Application Successfully Rejected !!";
            }
            DB::commit();
            return responseMsgs(true, $msg, "", '011111', 01, '391ms', 'Post', $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "");
        }
    }


    public function getPrivateLandPayment($typology, $zone)
    {
        return DB::table('adv_typology_mstrs')
            ->select(DB::raw("case when $zone = 1 then rate_zone_a
                              when $zone = 2 then rate_zone_b
                              when $zone = 3 then rate_zone_c
                        else 0 end as rate"))
            ->where('id', $typology)
            ->first()->rate;
    }

    /**
     * | Approve Application List for Citzen
     * | @param Request $req
     */
    public function listApproved(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $userType = authUser()->user_type;
            $mAdvPrivateland = new AdvPrivateland();
            $applications = $mAdvPrivateland->listApproved($citizenId, $userType);
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
     * | Reject Application List for Citizen
     * | @param Request $req
     */
    public function listRejected(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $mAdvRejectedPrivateland = new AdvRejectedPrivateland();
            $applications = $mAdvRejectedPrivateland->listRejected($citizenId);
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
     * | Get Applied Applications by Logged In JSK
     */
    public function getJSKApplications(Request $req)
    {
        try {
            $userId = authUser()->id;
            $mAdvActivePrivateland = new AdvActivePrivateland();
            $applications = $mAdvActivePrivateland->getJSKApplications($userId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            if ($data1['arrayCount'] == 0) {
                $data1 = null;
            }

            return responseMsgs(true, "Applied Applications", $data1, "040106", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040106", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    /**
     * | Approve Application List for JSK
     * | @param Request $req
     */
    public function listjskApprovedApplication(Request $req)
    {
        try {
            $userId = authUser()->id;
            $mAdvPrivateland = new AdvPrivateland();
            $applications = $mAdvPrivateland->listjskApprovedApplication($userId);
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
     * | Reject Application List for JSK
     * | @param Request $req
     */
    public function listJskRejectedApplication(Request $req)
    {
        try {
            $userId = authUser()->id;
            $mAdvRejectedPrivateland = new AdvRejectedPrivateland();
            $applications = $mAdvRejectedPrivateland->listJskRejectedApplication($userId);
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
     * | Generate Payment Order ID
     * | @param Request $req
     */

    public function generatePaymentOrderId(Request $req)
    {
        $req->validate([
            'id' => 'required|integer',
        ]);
        try {
            $startTime = microtime(true);
            $mAdvPrivateland = AdvPrivateland::find($req->id);
            $reqData = [
                "id" => $mAdvPrivateland->id,
                'amount' => $mAdvPrivateland->payment_amount,
                'workflowId' => $mAdvPrivateland->workflow_id,
                'ulbId' => $mAdvPrivateland->ulb_id,
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

            $data->name = $mAdvPrivateland->applicant;
            $data->email = $mAdvPrivateland->email;
            $data->contact = $mAdvPrivateland->mobile_no;
            $data->type = "Private Lands";
            // return $data;
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Payment OrderId Generated Successfully !!!", $data, "050123", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050123", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * Summary of application Details For Payment
     * @param Request $req
     * @return void
     */
    public function getApplicationDetailsForPayment(Request $req)
    {
        $req->validate([
            'applicationId' => 'required|integer',
        ]);
        try {
            $startTime = microtime(true);
            $mAdvPrivateland = new AdvPrivateland();
            $workflowId = $this->_workflowIds;
            if ($req->applicationId) {
                $data = $mAdvPrivateland->getApplicationDetailsForPayment($req->applicationId, $workflowId);
            }

            if (!$data)
                throw new Exception("Application Not Found");

            $data['type'] = "Private Lands";
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
    //         $mAdvPrivateland = new AdvPrivateland();
    //         $paymentDetails = $mAdvPrivateland->getPaymentDetails($req->paymentId);
    //         if (empty($paymentDetails)) {
    //             throw new Exception("Payment Details Not Found By Given Paymenst Id !!!");
    //         } else {
    //             return responseMsgs(true, 'Data Fetched',  $paymentDetails, "050124", "1.0", "2 Sec", "POST", $req->deviceId);
    //         }
    //     } catch (Exception $e) {
    //         responseMsgs(false, $e->getMessage(), "");
    //     }
    // }

    
    public function paymentByCash(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|string'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $mAdvPrivateland = new AdvPrivateland();
            DB::beginTransaction();
            $status = $mAdvPrivateland->paymentByCash($req);
            DB::commit();
            if ($req->status == '1' && $status == 1) {
                return responseMsgs(true, "Payment Successfully !!", '', "040501", "1.0", "", 'POST', $req->deviceId ?? "");
            } else {
                return responseMsgs(true, "Payment Rejected !!", '', "040501", "1.0", "", 'POST', $req->deviceId ?? "");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(true, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
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
            return responseMsgs(true, "Check Entry Successfully !!", ['status' => true, 'TransactionNo' => $transNo], "040501", "1.0", "", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function clearOrBounceCheque(Request $req){
        $validator = Validator::make($req->all(), [
            'paymentId' => 'required|string',
            'status' => 'required|string',
            'remarks' => $req->status==1?'nullable|string':'required|string',
            'bounceAmount' => $req->status==1?'nullable|numeric':'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $mAdvCheckDtl = new AdvChequeDtl();
            DB::beginTransaction();
            $status = $mAdvCheckDtl->clearOrBounceCheque($req);
            DB::commit();
            if($req->status == '1' && $status == 1){
                return responseMsgs(true, "Payment Successfully !!",'', "040501", "1.0", "", 'POST', $req->deviceId ?? "");
            }else{
                return responseMsgs(false, "Payment Rejected !!", '', "040501", "1.0", "", 'POST', $req->deviceId ?? "");
            }
            
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

      /**
     * |Entry Zone of the Application 
     * | Status- Open
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
            $mAdvActivePrivateland = new AdvActivePrivateland();
            $status = $mAdvActivePrivateland->entryZone($req);
            if ($status=='1') {
                return responseMsgs(true, 'Data Fetched',  "Zone Added Successfully", "050124", "1.0", "2 Sec", "POST", $req->deviceId);                
            } else {
                throw new Exception("Zone Not Added !!!");
            }
        }catch(Exception $e){
            return responseMsgs(false, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }
}
