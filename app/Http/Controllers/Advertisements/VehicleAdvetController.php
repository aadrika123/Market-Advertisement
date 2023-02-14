<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicles\StoreRequest;
use App\Models\Advertisements\AdvActiveVehicle;
use App\Models\Advertisements\AdvVehicle;
use App\Models\Advertisements\AdvRejectedVehicle;
use App\Models\Advertisements\WfActiveDocument;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\AdvDetailsTraits;
use Illuminate\Support\Facades\DB;
use App\Models\Workflows\WorkflowTrack;
use App\Models\Workflows\WfWardUser;
use App\Repositories\SelfAdvets\iSelfAdvetRepo;

use Carbon\Carbon;


use App\Traits\WorkflowTrait;
use Illuminate\Support\Facades\Http;

class VehicleAdvetController extends Controller
{
    /**
     * | Created On-31-12-2022 
     * | Created By-Anshu Kumar
     * | Created for the Movable Vehicles Operations
     */

     
    use WorkflowTrait;
    use AdvDetailsTraits;

    protected $_modelObj;
    protected $Repository;
    protected $_workflowIds;
    public function __construct(iSelfAdvetRepo $self_repo)
    {
        $this->_modelObj = new AdvActiveVehicle();
        $this->_workflowIds = Config::get('workflow-constants.MOVABLE_VEHICLE_WORKFLOWS');
        $this->Repository = $self_repo;
    }
    public function addNew(StoreRequest $req)
    {
        try {
            $advVehicle = new AdvActiveVehicle();
            if( authUser()->user_type=='JSK'){
                $userId = ['userId' => authUser()->id];
                $req->request->add($userId);
            }else{
                $citizenId = ['citizenId' => authUser()->id];
                $req->request->add($citizenId);
            }
            DB::beginTransaction();
            $applicationNo = $advVehicle->addNew($req);               // Store Vehicle 
            DB::commit();
           
            return responseMsgs(true,"Successfully Applied the Application !!",["status" => true,"ApplicationNo" => $applicationNo],"040301","1.0","","POST",$req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040301", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    
    /**
     * | Inbox List
     * | @param Request $req
     */
    public function listInbox(Request $req)
    {
        try {
            $mvehicleAdvets = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
           
            $inboxList = $mvehicleAdvets->listInbox($roleIds);
            return responseMsgs(true,"Inbox Applications",remove_null($inboxList->toArray()),"040103","1.0","","POST",$req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false,$e->getMessage(),"","040103","1.0","",'POST',$req->deviceId ?? "");
        }
    }


    /**
     * | Outbox List
     */
    public function listOutbox(Request $req)
    {
        try {
            $mvehicleAdvets = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $outboxList = $mvehicleAdvets->listOutbox($roleIds);
            return responseMsgs(true,"Outbox Lists",remove_null($outboxList->toArray()),"040104","1.0","","POST",$req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false,$e->getMessage(),"","040104","1.0","",'POST',$req->deviceId ?? "");
        }
    }

     /**
     * | Application Details
     */

    public function getDetailsById(Request $req)
    {
        try {
            $mvehicleAdvets = new AdvActiveVehicle();
            // $data = array();
            $fullDetailsData = array();
            if(isset($req->type)){
                $type = $req->type;
            }else{
                $type = NULL;
            }
            if ($req->applicationId) {
                $data = $mvehicleAdvets->getDetailsById($req->applicationId,$type);
            }else{
                throw new Exception("Not Pass Application Id");
            }
            if(!$data){
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
                'headerTitle' => "About Vehicle Advertisment",
                'data' => $cardDetails
            ];
            $fullDetailsData['fullDetailsData']['dataArray'] = new Collection([$basicElement]);
            $fullDetailsData['fullDetailsData']['cardArray'] = new Collection($cardElement);

            $metaReqs['customFor'] = 'Movable Vehical Advertisement';
            $metaReqs['wfRoleId'] = $data['current_roles'];
            $metaReqs['workflowId'] = $data['workflow_id'];
            $metaReqs['lastRoleId'] = $data['last_role_id'];
            $req->request->add($metaReqs);
            $forwardBackward = $this->getRoleDetails($req);
            $fullDetailsData['roleDetails'] = collect($forwardBackward)['original']['data'];

            $fullDetailsData = remove_null($fullDetailsData);

            $fullDetailsData['application_no'] = $data['application_no'];
            $fullDetailsData['apply_date'] = $data['created_at'];
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
            $mvehicleAdvets = new AdvActiveVehicle();
            $applications = $mvehicleAdvets->listAppliedApplications($citizenId);
            $totalApplication=$applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            return responseMsgs(true,"Applied Applications",$data1,"040106","1.0","","POST",$req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false,$e->getMessage(),"","040106","1.0","","POST",$req->deviceId ?? "");
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
            // Vehicle Application Update Current Role Updation
            DB::beginTransaction();
            $mAdvActiveVehicle = AdvActiveVehicle::find($request->applicationId);
            $mAdvActiveVehicle->last_role_id= $request->current_roles;
            $mAdvActiveVehicle->current_roles= $request->receiverRoleId;
            $mAdvActiveVehicle->save();

            $metaReqs['moduleId'] = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
            $metaReqs['workflowId'] = $mAdvActiveVehicle->workflow_id;
            $metaReqs['refTableDotId'] = "adv_active_vehicles.id";
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
            $data = AdvActiveVehicle::find($applicationId);
            $data->is_escalate = $request->escalateStatus;
            $data->escalate_by = $userId;
            $data->save();
            return responseMsgs(true, $request->escalateStatus == 1 ? 'Movable Vechicle is Escalated' : "Movable Vechicle is removed from Escalated", '', "010106", "1.0", "353ms", "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $request->all());
        }
    }

    
    /**
     * | Post Independent Comment
     */
    public function commentApplication(Request $request)
    {
        $request->validate([
            'comment' => 'required',
            'applicationId' => 'required|integer',
            'senderRoleId' => 'nullable|integer'
        ]);

        try {
            $workflowTrack = new WorkflowTrack();
            $mAdvActiveVehicle = AdvActiveVehicle::find($request->applicationId);                // Advertisment Details
            $mModuleId = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
            $metaReqs = array();
            DB::beginTransaction();
            // Save On Workflow Track For Level Independent
            $metaReqs = [
                'workflowId' => $mAdvActiveVehicle->workflow_id,
                'moduleId' => $mModuleId,
                'refTableDotId' => "adv_active_vehicles.id",
                'refTableIdValue' => $mAdvActiveVehicle->id,
                'message' => $request->comment
            ];
            // For Citizen Independent Comment
            if (!$request->senderRoleId) {
                $metaReqs = array_merge($metaReqs, ['citizenId' => $mAdvActiveVehicle->user_id]);
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
            
            $advData = $this->Repository->specialVehicleInbox($this->_workflowIds)                      // Repository function to get Advertiesment Details
                ->where('is_escalate', 1)
                ->where('adv_active_vehicles.ulb_id', $ulbId)
                // ->whereIn('ward_mstr_id', $wardId)
                ->get();
            return responseMsgs(true, "Data Fetched", remove_null($advData), "010107", "1.0", "251ms", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }


    /**
     * | Get Uploaded Document by application ID
     */
    // public function uploadDocumentsView(Request $req)
    // {
    //     $selfAdvets = new AdvActiveVehicle();
    //     $data = array();
    //     $fullDetailsData = array();
    //     if ($req->applicationId) {
    //         $data = $selfAdvets->details($req->applicationId, $this->_workflowIds);
    //     }
    //     // Uploads Documents Details
    //     $fullDetailsData = $data['documents'];


    //     $data1['data'] = $fullDetailsData;
    //     return $data1;
    // }

    public function viewVehicleDocuments(Request $req)
    {
        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        if ($req->applicationId && $req->type) {
            if($req->type=='Active'){
                $appId=$req->applicationId;
            }elseif($req->type=='Reject'){
                $appId=AdvRejectedVehicle::find($req->applicationId)->temp_id;
            }elseif($req->type=='Approve'){
                $appId=AdvVehicle::find($req->applicationId)->temp_id;
            }
            $data = $mWfActiveDocument->uploadDocumentsViewById($appId, $this->_workflowIds);
        }else{
            throw new Exception("Required Application Id And Application Type ");
        }
        $data1['data'] = $data;
        return $data1;
    }


    
    
    /**
     * |Final Approval and Rejection of the Application 
     * | Rating-
     * | Status- Open
     */
    public function approvedOrReject(Request $req)
    {
        try {
            $req->validate([
                'roleId' => 'required',
                'applicationId' => 'required|integer',
                'status' => 'required|integer',
                // 'payment_amount' => 'required',

            ]);

            // Check if the Current User is Finisher or Not         
           $mAdvActiveVehicle = AdvActiveVehicle::find( $req->applicationId);
            $getFinisherQuery = $this->getFinisherId($mAdvActiveVehicle->workflow_id);                                 // Get Finisher using Trait
            $refGetFinisher = collect(DB::select($getFinisherQuery))->first();
            if ($refGetFinisher->role_id != $req->roleId) {
                return responseMsgs(false, " Access Forbidden", "");
            }

            DB::beginTransaction();
            // Approval
            if ($req->status == 1) {

                $payment_amount = ['payment_amount' =>1000];
                $req->request->add($payment_amount);
                
                // approved Vehicle Application replication

                $approvedVehicle = $mAdvActiveVehicle->replicate();
                $approvedVehicle->setTable('adv_vehicles');
                $temp_id=$approvedVehicle->temp_id = $mAdvActiveVehicle->id;
                $approvedVehicle->payment_amount = $req->payment_amount;
                $approvedVehicle->approve_date =Carbon::now();
                $approvedVehicle->save();

                // Save in vehicle Advertisement Renewal
                $approvedVehicle = $mAdvActiveVehicle->replicate();
                $approvedVehicle->approve_date =Carbon::now();
                $approvedVehicle->setTable('adv_vehicle_renewals');
                $approvedVehicle->vechcleadvet_id = $temp_id;
                $approvedVehicle->save();

                
                $mAdvActiveVehicle->delete();

                // Update in adv_vehicles (last_renewal_id)

                DB::table('adv_vehicles')
                ->where('temp_id', $temp_id)
                ->update(['last_renewal_id' => $approvedVehicle->id]);

                $msg = "Application Successfully Approved !!";
            }
            // Rejection
            if ($req->status == 0) {

                $payment_amount = ['payment_amount' =>0];
                $req->request->add($payment_amount);


                // Vehicles advertisement Application replication
                $rejectedVehicle = $mAdvActiveVehicle->replicate();
                $rejectedVehicle->setTable('adv_rejected_vehicles');
                $rejectedVehicle->temp_id = $mAdvActiveVehicle->id;
                $rejectedVehicle->rejected_date =Carbon::now();
                $rejectedVehicle->save();
                $mAdvActiveVehicle->delete();
                $msg = "Application Successfully Rejected !!";
            }
            DB::commit();
            return responseMsgs(true, $msg, "", '011111', 01, '391ms', 'Post', $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "");
        }
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
            $mAdvVehicle = new AdvVehicle();
            $applications = $mAdvVehicle->listApproved($citizenId,$userType);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            return responseMsgs(true,"Approved Application List",$data1,"040103","1.0","","POST",$req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false,$e->getMessage(),"","040103","1.0","",'POST',$req->deviceId ?? "");
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
            $mAdvRejectedVehicle = new AdvRejectedVehicle();
            $applications = $mAdvRejectedVehicle->listRejected($citizenId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            return responseMsgs(true,"Approved Application List",$data1,"040103","1.0","","POST",$req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false,$e->getMessage(),"","040103","1.0","",'POST',$req->deviceId ?? "");
        }
    }

    

    /**
     * | Get Applied Applications by Logged In JSK
     */
    public function getJSKApplications(Request $req)
    {
        try {
            $userId = authUser()->id;
            $mAdvActiveVehicle = new AdvActiveVehicle();
            $applications = $mAdvActiveVehicle->getJSKApplications($userId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            return responseMsgs(true,"Applied Applications",$data1,"040106","1.0","","POST",$req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false,$e->getMessage(),"","040106","1.0","","POST",$req->deviceId ?? "");
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
            $mAdvVehicle = new AdvVehicle();
            $applications = $mAdvVehicle->listjskApprovedApplication($userId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            return responseMsgs(true,"Approved Application List",$data1,"040103","1.0","","POST",$req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false,$e->getMessage(),"","040103","1.0","",'POST',$req->deviceId ?? "");
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
            $mAdvRejectedVehicle = new AdvRejectedVehicle();
            $applications = $mAdvRejectedVehicle->listJskRejectedApplication($userId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;

            return responseMsgs(true,"Rejected Application List",$data1,"040103","1.0","","POST",$req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false,$e->getMessage(),"","040103","1.0","",'POST',$req->deviceId ?? "");
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
                 ->post($paymentUrl . 'api/payment/generate-orderid',$reqData);
 
             $data = json_decode($refResponse);
                        
             if (!$data)
             throw new Exception("Payment Order Id Not Generate");
 
             $data->name = $mAdvVehicle->applicant;
             $data->email = $mAdvVehicle->email;
             $data->contact = $mAdvVehicle->mobile_no;
             $data->type = "Movable Vehicles";
             $endTime = microtime(true);
             $executionTime = $endTime - $startTime;
 
             return responseMsgs(true,"Payment OrderId Generated Successfully !!!",$data,"050317","1.0","$executionTime Sec","POST",$req->deviceId ?? "");
         } catch (Exception $e) {
             return responseMsgs(false,$e->getMessage(),"","050317","1.0","",'POST',$req->deviceId ?? "");
         }
     }


     
    /**
     * Summary of application Details For Payment
     * @param Request $req
     * @return void
     */
    public function getApplicationDetailsForPayment(Request $req){
        $req->validate([
            'applicationId' => 'required|integer',
        ]);
        try {
            $startTime = microtime(true);
            $mAdvVehicle = new AdvVehicle();
            $workflowId = $this->_workflowIds;
            if ($req->applicationId) {
                $data = $mAdvVehicle->detailsForPayments($req->applicationId, $workflowId);
            }
              
            if (!$data)
                 throw new Exception("Application Not Found");
                 
            $data['type']="Movable Vehicles";
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, 'Data Fetched',  $data, "050318", "1.0", "$executionTime Sec", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

}
