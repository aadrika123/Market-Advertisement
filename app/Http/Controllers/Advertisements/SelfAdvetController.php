<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelfAdvets\StoreRequest;
use App\Models\Advertisements\AdvActiveSelfadvertisement;
use App\Models\Advertisements\AdvSelfadvertisement;
use App\Models\Advertisements\AdvRejectedSelfadvertisement;
use App\Models\TradeLicence;
use App\Traits\WorkflowTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Traits\AdvDetailsTraits;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Workflows\WorkflowTrack;
use Illuminate\Support\Facades\Config;
use App\Models\Workflows\WfWardUser;
use App\Repositories\SelfAdvets\iSelfAdvetRepo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

// use App\Repository\WorkflowMaster\Concrete\WorkflowMap;


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

    protected $Repository;

    protected $_workflowIds;
    public function __construct(iSelfAdvetRepo $self_repo)
    {
        $this->_modelObj = new AdvActiveSelfadvertisement();
        $this->_workflowIds = Config::get('workflow-constants.ADVERTISEMENT_WORKFLOWS');
        $this->Repository = $self_repo;
    }
    /**
     * | Apply for Self Advertisements 
     * | @param StoreRequest 
     */
    public function store(StoreRequest $req)
    {
        try {
            $startTime = microtime(true);
            $selfAdvets = new AdvActiveSelfadvertisement();
            // $citizenId = ['citizenId' => authUser()->id];
            if (authUser()->user_type == 'JSK') {
                $userId = ['userId' => authUser()->id];
                $req->request->add($userId);
            } else {
                $citizenId = ['citizenId' => authUser()->id];
                $req->request->add($citizenId);
            }
            // $req->request->add($citizenId);
            DB::beginTransaction();
            $applicationNo = $selfAdvets->store($req);       //<--------------- Model function to store 
            DB::commit();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(
                true,
                "Successfully Submitted the application !!",
                [
                    'status' => true,
                    'ApplicationNo' => $applicationNo
                ],
                "050101",
                "1.0",
                "$executionTime Sec",
                'POST',
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "050101",
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
            $startTime = microtime(true);
            $selfAdvets = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $inboxList = $selfAdvets->inbox($roleIds);            
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(
                true,
                "Inbox Applications",
                remove_null($inboxList->toArray()),
                "050103",
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
                "050103",
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
            $startTime = microtime(true);
            $selfAdvets = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $outboxList = $selfAdvets->outbox($roleIds);         
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(
                true,
                "Outbox Lists",
                remove_null($outboxList->toArray()),
                "050104",
                "1.0",
                "$executionTime Sec",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "050104",
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
            $startTime = microtime(true);
            $selfAdvets = new AdvActiveSelfadvertisement();
            // $forwardBackward = new WorkflowMap;
            // $data = array();
            $fullDetailsData = array();
            $workflowId = $this->_workflowIds;
            if ($req->applicationId) {
                $data = $selfAdvets->details($req->applicationId, $workflowId);
            }

            // Basic Details
            $basicDetails = $this->generateBasicDetails($data); // Trait function to get Basic Details
            $basicElement = [
                'headerTitle' => "Basic Details",
                "data" => $basicDetails
            ];

            $cardDetails = $this->generateCardDetails($data);
            $cardElement = [
                'headerTitle' => "About Advertisement",
                'data' => $cardDetails
            ];
            $fullDetailsData['fullDetailsData']['dataArray'] = new Collection([$basicElement]);
            $fullDetailsData['fullDetailsData']['cardArray'] = new Collection($cardElement);

            // return ($data);

            $metaReqs['customFor'] = 'Slef Advertisement';
            $metaReqs['wfRoleId'] = $data['current_role_id'];
            $metaReqs['workflowId'] = $data['workflow_id'];

            $req->request->add($metaReqs);
            $forwardBackward = $this->getRoleDetails($req);
            $fullDetailsData['roleDetails'] = collect($forwardBackward)['original']['data'];

            $fullDetailsData = remove_null($fullDetailsData);

            $fullDetailsData['application_no'] = $data['application_no'];
            $fullDetailsData['apply_date'] = $data['application_date'];
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
            $startTime = microtime(true);
            // Advertisment Application Update Current Role Updation
            DB::beginTransaction();
            $adv = AdvActiveSelfadvertisement::find($request->applicationId);
            $adv->current_role_id = $request->receiverRoleId;
            $adv->save();

            $metaReqs['moduleId'] = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
            $metaReqs['workflowId'] = $adv->workflow_id;
            $metaReqs['refTableDotId'] = "adv_active_selfadvertisments.id";
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
     * | Escalate
     */
    public function escalate(Request $request)
    {
        $request->validate([
            "escalateStatus" => "required|int",
            "applicationId" => "required|int",
        ]);
        try {
            $startTime = microtime(true);
            $userId = auth()->user()->id;
            $applicationId = $request->applicationId;
            $data = AdvActiveSelfadvertisement::find($applicationId);
            $data->is_escalate = $request->escalateStatus;
            $data->escalate_by = $userId;
            $data->save();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, $request->escalateStatus == 1 ? 'Advertisment is Escalated' : "Advertisment is removed from Escalated", '', "050107", "1.0", "$executionTime Sec", "POST", $request->deviceId);
        } catch (Exception $e) {
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
            $startTime = microtime(true);
            $workflowTrack = new WorkflowTrack();
            $adv = AdvActiveSelfadvertisement::find($request->applicationId);                // Advertisment Details
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
            
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "You Have Commented Successfully!!", ['Comment' => $request->comment], "050110", "1.0", " $executionTime Sec", "POST", "");
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
            $startTime = microtime(true);
            $citizenId = authUser()->id;
            $selfAdvets = new AdvActiveSelfadvertisement();
            $applications = $selfAdvets->getCitizenApplications($citizenId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(
                true,
                "Applied Applications",
                $data1,
                "050106",
                "1.0",
                "$executionTime Sec",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "050106",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        }
    }



    /**
     * | Get License By User ID
     */
    public function getLicense(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'user_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors(), "", "050111", "1.0", "", "POST", $req->deviceId ?? "");
        }
        try {
            $startTime = microtime(true);
            $tradeLicence = new TradeLicence();
            // $licenceList = $tradeLicence->select('id','license_no')->where('user_id', $req->user_id)
            //     ->get();
            $licenseList = $tradeLicence->getLicenceByUserId($req->user_id);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(
                true,
                "Licenses",
                remove_null($licenseList->toArray()),
                "050111",
                "1.0",
                "$executionTime Sec",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050111", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    /**
     * | Get License By Holding No
     */
    public function getLicenseByHoldingNo(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'holding_no' => 'required|string'
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors(), "", "040105", "1.0", "", "POST", $req->deviceId ?? "");
        }
        try {
            $startTime = microtime(true);
            $tradeLicense = new TradeLicence();
            // $licenceList = $tradeLicence->select('id', 'license_no')->where('holding_no', $req->holding_no)
            //     ->get();
            $licenseList = $tradeLicense->getLicenceByHoldingNo($req->holding_no);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(
                true,
                "Licenses",
                remove_null($licenseList->toArray()),
                "050111",
                "1.0",
                "$executionTime Sec",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050111", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    /**
     * | Get Uploaded Document by application ID
     */
    public function uploadDocumentsView(Request $req)
    {
        $selfAdvets = new AdvActiveSelfadvertisement();
        $data = array();
        $fullDetailsData = array();
        if ($req->applicationId) {
            $data = $selfAdvets->details($req->applicationId, $this->_workflowIds);
        }

        // $fullDetailsData['application_no'] = $data['application_no'];
        // $fullDetailsData['apply_date'] = $data['application_date'];
        $fullDetailsData = $data['documents'];


        $data1['data'] = $fullDetailsData;
        return $data1;
    }


    /**
     * | Workflow Upload Document by application ID
     */
    public function workflowUploadDocument(Request $req)
    {
        try {
            $validate = validator::make(
                $req->all(),
                [
                    'applicationId' => 'required|integer',
                    'document' => 'required|mimes:png,jpeg,pdf,jpg',
                    'docMstrId' => 'required|integer',
                    'docRefName' => 'required|string'
                ]
            );
            if ($validate->fails()) {
                return response()->json(["error" => 'validation_error', "message" => $validate->errors()], 422);
            }
            $selfAdvets = new AdvActiveSelfadvertisement();
            $selfAdvets->workflowUploadDocument($req);

            return responseMsgs(true, "Document Uploaded Successfully", '', "010106", "1.0", "353ms", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $req->all());
        }
    }

    /**
     * | Workflow View Uploaded Document by application ID
     */
    public function workflowViewDocuments(Request $req)
    {
        $startTime = microtime(true);
        $selfAdvets = new AdvActiveSelfadvertisement();
        $data = array();
        $fullDetailsData = array();
        if ($req->applicationId) {
            $data = $selfAdvets->details($req->applicationId, $this->_workflowIds);
        }
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        return responseMsgs(true, "Data Fetched", remove_null($data['documents']), "050115", "1.0", "$executionTime Sec", "POST", "");
    }

    public function specialInbox(Request $req)
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

            // print_r($wardId);

            $advData = $this->Repository->specialInbox($this->_workflowIds)                      // Repository function to get Advertiesment Details
                ->where('is_escalate', 1)
                ->where('adv_active_selfadvertisements.ulb_id', $ulbId)
                // ->whereIn('ward_mstr_id', $wardId)
                ->get();
                $endTime = microtime(true);
                $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Data Fetched", remove_null($advData), "050108", "1.0", "$executionTime Sec", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    public function getDetailsByLicenseNo(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'license_no' => 'required'
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors(), "", "040105", "1.0", "", "POST", $req->deviceId ?? "");
        }
        $mtradeLicense = new TradeLicence();
        $data = array();
        if ($req->license_no) {
            $data = $mtradeLicense->getDetailsByLicenceNo($req->license_no);
        }
        if (!empty($data)) {
            // $licenseDetails = $this->generateLicenseDetails($data);
            $licenseElement = [
                'status' => true,
                'headerTitle' => "License Details",
                'data' => $data
            ];
        } else {
            $licenseElement = [
                'status' => false,
                'headerTitle' => "License Details",
                'data' => "Invalid License No"
            ];
        }
        return $licenseElement;
    }



    /**
     * |-------------------------------------Final Approval and Rejection of the Application ------------------------------------------------|
     * | Rating-
     * | Status- Open
     */
    public function finalApprovalRejection(Request $req)
    {
        $req->validate([
            'roleId' => 'required',
            'applicationId' => 'required|integer',
            'status' => 'required|integer',
            // 'payment_amount' => 'required',

        ]);
        try {
            $startTime = microtime(true);
            // Check if the Current User is Finisher or Not         
            $mAdvActiveSelfadvertisement = AdvActiveSelfadvertisement::find($req->applicationId);
            $getFinisherQuery = $this->getFinisherId($mAdvActiveSelfadvertisement->workflow_id);                                 // Get Finisher using Trait
            $refGetFinisher = collect(DB::select($getFinisherQuery))->first();
            if ($refGetFinisher->role_id != $req->roleId) {
                return responseMsgs(false, " Access Forbidden", "");
            }

            DB::beginTransaction();
            // Approval
            if ($req->status == 1) {

                $payment_amount = ['payment_amount' => 1000];
                $req->request->add($payment_amount);
                // Selfadvertisement Application replication

                $approvedSelfadvertisement = $mAdvActiveSelfadvertisement->replicate();
                $approvedSelfadvertisement->setTable('adv_selfadvertisements');
                $temp_id = $approvedSelfadvertisement->temp_id = $mAdvActiveSelfadvertisement->id;
                $approvedSelfadvertisement->payment_amount = $req->payment_amount;
                $approvedSelfadvertisement->approve_date = Carbon::now();
                $approvedSelfadvertisement->save();

                // Save in self Advertisement Renewal
                $approvedSelfadvertisement = $mAdvActiveSelfadvertisement->replicate();
                $approvedSelfadvertisement->approve_date = Carbon::now();
                $approvedSelfadvertisement->setTable('adv_selfadvet_renewals');
                $approvedSelfadvertisement->selfadvet_id = $temp_id;
                $approvedSelfadvertisement->save();


                $mAdvActiveSelfadvertisement->delete();

                // Update in adv_selfadvertisements (last_renewal_id)

                DB::table('adv_selfadvertisements')
                    ->where('temp_id', $temp_id)
                    ->update(['last_renewal_id' => $approvedSelfadvertisement->id]);

                $msg = "Application Successfully Approved !!";
            }
            // Rejection
            if ($req->status == 0) {

                $payment_amount = ['payment_amount' => 0];
                $req->request->add($payment_amount);
                // Selfadvertisement Application replication
                $rejectedSelfadvertisement = $mAdvActiveSelfadvertisement->replicate();
                $rejectedSelfadvertisement->setTable('adv_rejected_selfadvertisements');
                $rejectedSelfadvertisement->temp_id = $mAdvActiveSelfadvertisement->id;
                $rejectedSelfadvertisement->rejected_date = Carbon::now();
                $rejectedSelfadvertisement->save();
                $mAdvActiveSelfadvertisement->delete();
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
     * | Approve Application List for Citzen
     * | @param Request $req
     */
    public function approvedList(Request $req)
    {
        try {
            $startTime = microtime(true);
            $citizenId = authUser()->id;
            $mAdvSelfadvertisements = new AdvSelfadvertisement();
            $applications = $mAdvSelfadvertisements->approvedList($citizenId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(
                true,
                "Approved Application List",
                $data1,
                "050118",
                "1.0",
                "$executionTime Sec",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "050118",
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
            $startTime = microtime(true);
            $citizenId = authUser()->id;
            $mAdvRejectedSelfadvertisement = new AdvRejectedSelfadvertisement();
            $applications = $mAdvRejectedSelfadvertisement->rejectedList($citizenId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(
                true,
                "Rejected Application List",
                $data1,
                "050119",
                "1.0",
                " $executionTime Sec",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "050119",
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
            $startTime = microtime(true);
            $userId = authUser()->id;
            $selfAdvets = new AdvActiveSelfadvertisement();
            $applications = $selfAdvets->getJSKApplications($userId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(
                true,
                "Applied Applications",
                $data1,
                "050120",
                "1.0",
                "$executionTime Sec",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "050120",
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
            $startTime = microtime(true);
            $userId = authUser()->id;
            $mAdvSelfadvertisements = new AdvSelfadvertisement();
            $applications = $mAdvSelfadvertisements->jskApprovedList($userId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(
                true,
                "Approved Application List",
                $data1,
                "050121",
                "1.0",
                "$executionTime Sec",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "050121",
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
            $startTime = microtime(true);
            $userId = authUser()->id;
            $mAdvRejectedSelfadvertisement = new AdvRejectedSelfadvertisement();
            $applications = $mAdvRejectedSelfadvertisement->jskRejectedList($userId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;            
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(
                true,
                "Rejected Application List",
                $data1,
                "050122",
                "1.0",
                "$executionTime Sec",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "050122",
                "1.0",
                "",
                'POST',
                $req->deviceId ?? ""
            );
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
            $mAdvSelfadvertisement = AdvSelfadvertisement::find($req->id);
            $reqData = [
                "id" => $mAdvSelfadvertisement->id,
                'amount' => $mAdvSelfadvertisement->payment_amount,
                'workflowId' => $mAdvSelfadvertisement->workflow_id,
                'ulbId' => $mAdvSelfadvertisement->ulb_id,
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

            $data->name = $mAdvSelfadvertisement->applicant;
            $data->email = $mAdvSelfadvertisement->email;
            $data->contact = $mAdvSelfadvertisement->mobile_no;
            // $data->apply_date = $mAdvSelfadvertisement->application_date;
            // $data->entity_name = $mAdvSelfadvertisement->entity_name;
            $data->type = "Self Advertisement";
            // return $data;
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(
                true,
                "Payment OrderId Generated Successfully !!!",
                $data,
                "050123",
                "1.0",
                "$executionTime Sec",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "050123",
                "1.0",
                "",
                'POST',
                $req->deviceId ?? ""
            );
        }
    }


    /**
     * Summary of application Details For Payment
     * @param Request $req
     * @return void
     */
    public function applicationDetailsForPayment(Request $req){
        $req->validate([
            'applicationId' => 'required|integer',
        ]);
        try {
            $startTime = microtime(true);
            $selfAdvets = new AdvSelfadvertisement();
            $workflowId = $this->_workflowIds;
            if ($req->applicationId) {
                $data = $selfAdvets->detailsForPayments($req->applicationId, $workflowId);
            }    
            if (!$data)
                 throw new Exception("Application Not Found");

            $data['type']="Self Advertisement";
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, 'Data Fetched',  $data, "050124", "1.0", "$executionTime Sec", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }
}
