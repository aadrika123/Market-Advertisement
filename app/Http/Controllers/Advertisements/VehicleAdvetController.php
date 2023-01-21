<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicles\StoreRequest;
use App\Models\Advertisements\AdvActiveVehicle;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\AdvDetailsTraits;
use Illuminate\Support\Facades\DB;
use App\Models\Workflows\WorkflowTrack;
use App\Models\Workflows\WfWardUser;
use App\Repositories\SelfAdvets\iSelfAdvetRepo;


use App\Traits\WorkflowTrait;


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
    public function store(StoreRequest $req)
    {
        // echo $workflow_id = $this->_workflowIds;
        try {
            $advVehicle = new AdvActiveVehicle();
            $citizenId = ['citizenId' => authUser()->id];
            $req->request->add($citizenId);
            $applicationNo = $advVehicle->store($req);               // Store Vehicle 
            return responseMsgs(
                true,
                "Successfully Applied the Application !!",
                [
                    "status" => true,
                    "ApplicationNo" => $applicationNo
                ],
                "040301",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040301", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    
    /**
     * | Application Update 
     * | @param Request $req
     */
    public function edit(Request $req)
    {
        $documents = collect($req->documents)->first();
        if (empty($documents)) {
            return 'Collection is Empty';
        }
        return 'Not Empty';
    }


    
    /**
     * | Inbox List
     * | @param Request $req
     */
    public function inbox(Request $req)
    {
        try {
            $mvehicleAdvets = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
           
            $inboxList = $mvehicleAdvets->inbox($roleIds);
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
            $mvehicleAdvets = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $outboxList = $mvehicleAdvets->outbox($roleIds);
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
            $mvehicleAdvets = new AdvActiveVehicle();
            // $data = array();
            $fullDetailsData = array();
            if ($req->applicationId) {
                $data = $mvehicleAdvets->details($req->applicationId);
            }

            // return $data;

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

            $fullDetailsData = remove_null($fullDetailsData);

            $fullDetailsData['application_no'] = $data['application_no'];
            $fullDetailsData['apply_date'] = $data['created_at'];

            return responseMsgs(true, 'Data Fetched', $fullDetailsData, "010104", "1.0", "303ms", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }


    
    /**
     * | Get Applied Applications by Logged In Citizen
     */
    public function getCitizenApplications(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $mvehicleAdvets = new AdvActiveVehicle();
            $applications = $mvehicleAdvets->getCitizenApplications($citizenId);
            $totalApplication=$applications->count();
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
            $mAdvActiveVehicle = AdvActiveVehicle::find($request->applicationId);
            $mAdvActiveVehicle->current_roles= $request->receiverRoleId;
            $mAdvActiveVehicle->save();

            $metaReqs['moduleId'] = Config::get('workflow-constants.MOVABLE_VEHICLE_MODULE_ID');
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
    public function escalate(Request $request)
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
    public function commentIndependent(Request $request)
    {
        $request->validate([
            'comment' => 'required',
            'applicationId' => 'required|integer',
            'senderRoleId' => 'nullable|integer'
        ]);

        try {
            $workflowTrack = new WorkflowTrack();
            $mAdvActiveVehicle = AdvActiveVehicle::find($request->applicationId);                // Advertisment Details
            $mModuleId = Config::get('workflow-constants.MOVABLE_VEHICLE_MODULE_ID');
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
    public function uploadDocumentsView(Request $req)
    {
        $selfAdvets = new AdvActiveVehicle();
        $data = array();
        $fullDetailsData = array();
        if ($req->applicationId) {
            $data = $selfAdvets->details($req->applicationId);
        }
        // Uploads Documents Details

        $uploadDocuments = $this->generateUploadDocDetails($data['documents']);
        $uploadDocs = [
            'headerTitle' => 'Upload Documents',
            'tableHead' => ["#", "Document Name", "Verified By", "Verified On", "Document Path"],
            'tableData' => $uploadDocuments
        ];

        $fullDetailsData['application_no'] = $data['application_no'];
        $fullDetailsData['apply_date'] = $data['application_date'];

        $fullDetailsData['fullDetailsData']['dataArray'] = new Collection([$uploadDocs]);

        $data1['data'] = $fullDetailsData;
        return $data1;
    }




}
