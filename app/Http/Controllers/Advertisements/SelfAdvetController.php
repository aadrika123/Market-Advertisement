<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelfAdvets\StoreRequest;
use App\Models\Advertisements\AdvActiveSelfadvertisement;
use App\Models\TradeLicence;
use App\Traits\WorkflowTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Traits\AdvDetailsTraits;
use Illuminate\Database\Eloquent\Collection;
use App\Models\WorkflowTrack;
use Illuminate\Support\Facades\Config;

/**
 * | Created On-14-12-2022 
 * | Created By-Anshu Kumar
 * | Created for Operations on Self Advertisements
 * | Workflow ID=129
 * | Ulb Workflow ID=245
 */

class SelfAdvetController extends Controller
{
    use WorkflowTrait;
    use AdvDetailsTraits;
    protected $_modelObj;
    public function __construct()
    {
        $this->_modelObj = new AdvActiveSelfadvertisement();
    }
    /**
     * | Apply for Self Advertisements 
     * | @param StoreRequest 
     */
    public function store(StoreRequest $req)
    {
        try {
            $selfAdvets = new AdvActiveSelfadvertisement();
            $citizenId = ['citizenId' => authUser()->id];
            $req->request->add($citizenId);
            DB::beginTransaction();
            $applicationNo = $selfAdvets->store($req);       //<--------------- Model function to store 
            DB::commit();
            return responseMsgs(
                true,
                "Successfully Submitted the application !!",
                [
                    'status' => true,
                    'ApplicationNo' => $applicationNo
                ],
                "040101",
                "1.0",
                "",
                'POST',
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "040101",
                "1.0",
                "",
                'POST',
                $req->deviceId ?? ""
            );
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
            $selfAdvets = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $inboxList = $selfAdvets->inbox($roleIds);
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
        $selfAdvets = new AdvActiveSelfadvertisement();
        $data = array();
        $fullDetailsData = array();
        if ($req->id) {
            $data = $selfAdvets->details($req->id);
        }

        // Basic Details
        $basicDetails = $this->generateBasicDetails($data);      // Trait function to get Basic Details
        $basicElement = [
            'headerTitle' => "Basic Details",
            "data" => $basicDetails
        ];

        $cardDetails = $this->generateCardDetails($data);
        $cardElement = [
            'headerTitle' => "About Advertisment",
            'data' => $cardDetails
        ];
        $fullDetailsData['fullDetailsData']['cardArray'] = new Collection($cardElement);

        // Uploads Documents Details

        // $uploadDocuments = $this->generateUploadDocDetails($data['documents']);
        // $uploadDocs = [
        //     'headerTitle' => 'Upload Documents',
        //     'tableHead' => ["#", "Document Name", "Verified By", "Verified On", "Document Path"],
        //     'tableData' => $uploadDocuments
        // ];

        $fullDetailsData['application_no'] = $data['application_no'];
        $fullDetailsData['apply_date'] = $data['application_date'];

        $fullDetailsData['fullDetailsData']['dataArray'] = new Collection([$basicElement]);

        $data1['data'] = $fullDetailsData;
        return $data1;
    }

    /**
     * | Forward or Backward Application
     */
    public function postNextLevel(Request $request)
    {
        $request->validate([
            'advId' => 'required|integer',
            'senderRoleId' => 'required|integer',
            'receiverRoleId' => 'required|integer',
            'comment' => 'required',
        ]);

        try {
            // Advertisment Application Update Current Role Updation
            DB::beginTransaction();
            $adv = AdvActiveSelfadvertisement::find($request->advId);
            $adv->current_role = $request->receiverRoleId;
            $adv->save();

            $metaReqs['moduleId'] = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
            $metaReqs['workflowId'] = $adv->workflow_id;
            $metaReqs['refTableDotId'] = "adv_active_selfadvertisments.id";
            $metaReqs['refTableIdValue'] = $request->advId;
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
            "advId" => "required|int",
        ]);
        try {
            $userId = auth()->user()->id;
            $adv_id = $request->advId;
            $data = AdvActiveSelfadvertisement::find($adv_id);
            $data->is_escalate = $request->escalateStatus;
            $data->escalate_by = $userId;
            $data->save();
            return responseMsgs(true, $request->escalateStatus == 1 ? 'Advertisment is Escalated' : "Advertisment is removed from Escalated", '', "010106", "1.0", "353ms", "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $request->all());
        }
    }

    // Post Independent Comment
    public function commentIndependent(Request $request)
    {
        $request->validate([
            'comment' => 'required',
            'advId' => 'required|integer',
            'senderRoleId' => 'nullable|integer'
        ]);

        try {
            $workflowTrack = new WorkflowTrack();
            $adv = AdvActiveSelfadvertisement::find($request->advId);                // Advertisment Details
            $mModuleId = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
            $metaReqs = array();
            DB::beginTransaction();
            // Save On Workflow Track For Level Independent
            $metaReqs = [
                'workflowId' => $adv->workflow_id,
                'moduleId' => $mModuleId,
                'refTableDotId' => "adv_active_selfadvertisments.id",
                'refTableIdValue' => $adv->id,
                'message' => $request->comment
            ];
            // For Citizen Independent Comment
            if (!$request->senderRoleId) {
                $metaReqs = array_merge($metaReqs, ['citizenId' => $adv->user_id]);
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
     * | Get Applied Applications by Logged In Citizen
     */
    public function getCitizenApplications(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $selfAdvets = new AdvActiveSelfadvertisement();
            $applications = $selfAdvets->getCitizenApplications($citizenId);
            return responseMsgs(
                true,
                "Applied Applications",
                remove_null($applications->toArray()),
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

    public function getLicence(Request $req){
        $validator = Validator::make($req->all(), [
                'user_id' => 'required|integer'
            ]);
            if ($validator->fails()) {
                return responseMsgs(false, $validator->errors(), "", "040105", "1.0", "", "POST", $req->deviceId ?? "");
            }
            try {
                $tradeLicence = new TradeLicence();
                $licenceList = $tradeLicence->select('id','license_no')->where('user_id', $req->user_id)
                    ->get();
                    return responseMsgs(
                        true,
                        "Licences",
                        remove_null($licenceList->toArray()),
                        "040106",
                        "1.0",
                        "",
                        "POST",
                        $req->deviceId ?? ""
                    );
            } catch (Exception $e) {
                return responseMsgs(false, $e->getMessage(), "", "040105", "1.0", "", "POST", $req->deviceId ?? "");
            }
    }

    public function getLicenceByHoldingNo(Request $req){
        $validator = Validator::make($req->all(), [
            'holding_no' => 'required|string'
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors(), "", "040105", "1.0", "", "POST", $req->deviceId ?? "");
        }
        try {
            $tradeLicence = new TradeLicence();
            $licenceList = $tradeLicence->select('id','license_no')->where('holding_no', $req->holding_no)
                ->get();
                return responseMsgs(
                    true,
                    "Licences",
                    remove_null($licenceList->toArray()),
                    "040106",
                    "1.0",
                    "",
                    "POST",
                    $req->deviceId ?? ""
                );
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040105", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function uploadDocuments(Request $req){
        $selfAdvets = new AdvActiveSelfadvertisement();
        $data = array();
        $fullDetailsData = array();
        if ($req->id) {
            $data = $selfAdvets->details($req->id);
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

    public function specialInbox(Request $req){
        return ["message" => "success"];
    }
}
