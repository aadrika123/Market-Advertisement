<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrivateLand\StoreRequest;
use App\Models\Advertisements\AdvActivePrivateland;
use App\Models\Advertisements\AdvPrivateland;
use App\Models\Advertisements\AdvRejectedPrivateland;
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
    public function store(StoreRequest $req)
    {
        try {
            $privateLand = new AdvActivePrivateland();
            // $citizenId = ['citizenId' => authUser()->id];
            // $req->request->add($citizenId);
            if( authUser()->user_type=='JSK'){
                $userId = ['userId' => authUser()->id];
                $req->request->add($userId);
            }else{
                $citizenId = ['citizenId' => authUser()->id];
                $req->request->add($citizenId);
            }
            $applicationNo = $privateLand->store($req);       //<--------------- Model function to store 
            return responseMsgs(
                true,
                "Successfully Submitted the application !!",
                [
                    'status' => true,
                    'ApplicationNo' => $applicationNo
                ],
                "040401",
                "1.0",
                "",
                'POST',
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "040401",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        }
    }


        /**
     * | Inbox List
     * | @param Request $req
     */
    public function inbox(Request $req)
    {
        try {
            $mAdvActivePrivateland = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $inboxList = $mAdvActivePrivateland->inbox($roleIds);
            return responseMsgs(
                true,
                "Inbox Applications",
                remove_null($inboxList->toArray()),
                "040103",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "040103",
                "1.0",
                "",
                'POST',
                $req->deviceId ?? ""
            );
        }
    }

    /**
     * | Outbox List
     */
    public function outbox(Request $req)
    {
        try {
            $selfAdvets = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $outboxList = $selfAdvets->outbox($roleIds);
            return responseMsgs(
                true,
                "Outbox Lists",
                remove_null($outboxList->toArray()),
                "040104",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "040104",
                "1.0",
                "",
                'POST',
                $req->deviceId ?? ""
            );
        }
    }

    /**
     * | Application Details
     */

    public function details(Request $req)
    {
        try {
            $mAdvActivePrivateland = new AdvActivePrivateland();
            // $forwardBackward = new WorkflowMap;
            // $data = array();
            $fullDetailsData = array();
            if ($req->applicationId) {
                $data = $mAdvActivePrivateland->details($req->applicationId, $this->_workflowIds);
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

            $req->request->add($metaReqs);
            $forwardBackward = $this->getRoleDetails($req);
            $fullDetailsData['roleDetails'] = collect($forwardBackward)['original']['data'];

            $fullDetailsData = remove_null($fullDetailsData);

            $fullDetailsData['application_no'] = $data['application_no'];
            $fullDetailsData['apply_date'] = $data['application_date'];

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
    public function getCitizenApplications(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $mAdvActivePrivateland = new AdvActivePrivateland();
            $applications = $mAdvActivePrivateland->getCitizenApplications($citizenId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            return responseMsgs(
                true,
                "Applied Applications",
                $data1,
                "040106",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "040106",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        }
    }

    
    /**
     * | Escalate
     */
    public function escalate(Request $request)
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

    
    public function specialInbox(Request $req)
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
    public function postNextLevel(Request $request)
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
    public function commentIndependent(Request $request)
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
    public function uploadDocumentsView(Request $req)
    {
        $mAdvActivePrivateland = new AdvActivePrivateland();
        $data = array();
        $fullDetailsData = array();
        if ($req->applicationId) {
            $data = $mAdvActivePrivateland->details($req->applicationId, $this->_workflowIds);
        }

        // $fullDetailsData['application_no'] = $data['application_no'];
        // $fullDetailsData['apply_date'] = $data['application_date'];
        $fullDetailsData = $data['documents'];


        $data1['data'] = $fullDetailsData;
        return $data1;
    }

    

    
    
    /**
     * |-------------------------------------Final Approval and Rejection of the Application ------------------------------------------------|
     * | Rating-
     * | Status- Open
     */
    public function finalApprovalRejection(Request $req)
    {
        try {
            $req->validate([
                'roleId' => 'required',
                'applicationId' => 'required|integer',
                'status' => 'required|integer',
                // 'payment_amount' => 'required',

            ]);

            // Check if the Current User is Finisher or Not         
           $mAdvActivePrivateland = AdvActivePrivateland::find( $req->applicationId);
            $getFinisherQuery = $this->getFinisherId($mAdvActivePrivateland->workflow_id);                                 // Get Finisher using Trait
            $refGetFinisher = collect(DB::select($getFinisherQuery))->first();
            if ($refGetFinisher->role_id != $req->roleId) {
                return responseMsgs(false, " Access Forbidden", "");
            }

            DB::beginTransaction();
            // Approval
            if ($req->status == 1) {

                $payment_amount = ['payment_amount' =>1000];
                $req->request->add($payment_amount);
                
                // approved Private Land Application replication

                $approvedPrivateland = $mAdvActivePrivateland->replicate();
                $approvedPrivateland->setTable('adv_privatelands');
                $temp_id=$approvedPrivateland->temp_id = $mAdvActivePrivateland->id;
                $approvedPrivateland->payment_amount = $req->payment_amount;
                $approvedPrivateland->approve_date =Carbon::now();
                $approvedPrivateland->save();

                // Save in Priate Land Application Advertisement Renewal
                $approvedPrivateland = $mAdvActivePrivateland->replicate();
                $approvedPrivateland->approve_date =Carbon::now();
                $approvedPrivateland->setTable('adv_privateland_renewals');
                $approvedPrivateland->privateland_id = $temp_id;
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

                $payment_amount = ['payment_amount' =>0];
                $req->request->add($payment_amount);


                // Privateland advertisement Application replication
                $rejectedPrivateland = $mAdvActivePrivateland->replicate();
                $rejectedPrivateland->setTable('adv_rejected_privatelands');
                $rejectedPrivateland->temp_id = $mAdvActivePrivateland->id;
                $rejectedPrivateland->rejected_date =Carbon::now();
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

    /**
     * | Approve Application List for Citzen
     * | @param Request $req
     */
    public function approvedList(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $mAdvPrivateland = new AdvPrivateland();
            $applications = $mAdvPrivateland->approvedList($citizenId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            if($data1['arrayCount']==0){
                $data1 = null;
            }

            return responseMsgs(
                true,
                "Approved Application List",
                $data1,
                "040103",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "040103",
                "1.0",
                "",
                'POST',
                $req->deviceId ?? ""
            );
        }
    }
    

    /**
     * | Reject Application List for Citizen
     * | @param Request $req
     */
    public function rejectedList(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $mAdvRejectedPrivateland = new AdvRejectedPrivateland();
            $applications = $mAdvRejectedPrivateland->rejectedList($citizenId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            if($data1['arrayCount']==0){
                $data1 = null;
            }

            return responseMsgs(
                true,
                "Approved Application List",
                $data1,
                "040103",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "040103",
                "1.0",
                "",
                'POST',
                $req->deviceId ?? ""
            );
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
            if($data1['arrayCount']==0){
                $data1 = null;
            }

            return responseMsgs(
                true,
                "Applied Applications",
                $data1,
                "040106",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "040106",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        }
    }

    
    /**
     * | Approve Application List for JSK
     * | @param Request $req
     */
    public function jskApprovedList(Request $req)
    {
        try {
            $userId = authUser()->id;
            $mAdvPrivateland = new AdvPrivateland();
            $applications = $mAdvPrivateland->jskApprovedList($userId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            if($data1['arrayCount']==0){
                $data1 = null;
            }

            return responseMsgs(
                true,
                "Approved Application List",
                $data1,
                "040103",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "040103",
                "1.0",
                "",
                'POST',
                $req->deviceId ?? ""
            );
        }
    }
    

    /**
     * | Reject Application List for JSK
     * | @param Request $req
     */
    public function jskRejectedList(Request $req)
    {
        try {
            $userId = authUser()->id;
            $mAdvRejectedPrivateland = new AdvRejectedPrivateland();
            $applications = $mAdvRejectedPrivateland->jskRejectedList($userId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            if($data1['arrayCount']==0){
                $data1 = null;
            }

            return responseMsgs(
                true,
                "Rejected Application List",
                $data1,
                "040103",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "040103",
                "1.0",
                "",
                'POST',
                $req->deviceId ?? ""
            );
        }
    }





}
