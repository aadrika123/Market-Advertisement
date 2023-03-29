<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;

use App\Http\Requests\Agency\RenewalHordingRequest;
use App\Http\Requests\Agency\StoreLicenceRequest;
use App\Models\Advertisements\AdvActiveHoarding;
use App\Models\Advertisements\AdvChequeDtl;
use App\Models\Advertisements\AdvHoarding;
use App\Models\Advertisements\AdvRejectedHoarding;
use App\Models\Advertisements\AdvTypologyMstr;
use App\Models\Advertisements\WfActiveDocument;
use App\Models\Workflows\WfRoleusermap;
use App\Models\Workflows\WfWardUser;
use App\Models\Workflows\WfWorkflowrolemap;
use App\Models\Workflows\WorkflowTrack;
use App\Repositories\SelfAdvets\iSelfAdvetRepo;
use App\Traits\AdvDetailsTraits;
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

class HoardingController extends Controller
{
    use AdvDetailsTraits;

    use WorkflowTrait;

    protected $_modelObj;

    protected $Repository;

    protected $_workflowIds;
    protected $_moduleId;
    protected $_docCode;
    protected $_tempParamId;
    protected $_paramId;
    protected $_baseUrl;
    public function __construct(iSelfAdvetRepo $agency_repo)
    {
        $this->_modelObj = new AdvActivehoarding();
        $this->_workflowIds = Config::get('workflow-constants.AGENCY_HORDING_WORKFLOWS');
        $this->_moduleId = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
        $this->_docCode = Config::get('workflow-constants.AGENCY_HORDING_DOC_CODE');
        $this->_tempParamId = Config::get('workflow-constants.TEMP_HOR_ID');
        $this->_paramId = Config::get('workflow-constants.HOR_ID');
        $this->_baseUrl = Config::get('constants.BASE_URL');
        $this->Repository = $agency_repo;
    }



    /**
     * | Get Typology List
     */
    public function listTypology(Request $req)
    {
        try {
            $mAdvTypologyMstr = new AdvTypologyMstr();
            $typologyList = $mAdvTypologyMstr->listTypology1();
            $typologyList = $typologyList->groupBy('type');
            foreach ($typologyList as $key => $data) {
                $type = [
                    'Type' => "Type " . $key,
                    'data' => $typologyList[$key]
                ];
                $fData[] = $type;
            }
            $fullData['typology'] = $fData;

            return responseMsgs(true, "Typology Data Fetch Successfully!!", remove_null($fullData), "040103", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * | Get Typology List
     */
    public function getHordingCategory(Request $req)
    {
        try {
            $mAdvTypologyMstr = new AdvTypologyMstr();
            $typologyList = $mAdvTypologyMstr->getHordingCategory();

            return responseMsgs(true, "Typology Data Fetch Successfully!!", remove_null($typologyList), "040103", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Save Application For Licence
     */
    public function addNewLicense(StoreLicenceRequest $req)
    {
        try {
            $mAdvActiveHoarding = new AdvActiveHoarding();
            if (authUser()->user_type == 'JSK') {
                $userId = ['userId' => authUser()->id];
                $req->request->add($userId);
            } else {
                $citizenId = ['citizenId' => authUser()->id];
                $req->request->add($citizenId);
            }

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
            $LicenseNo = $mAdvActiveHoarding->addNew($req);       //<--------------- Model function to store 
            DB::commit();
            return responseMsgs(true, "Successfully Submitted the application !!", ['status' => true, 'ApplicationNo' => $LicenseNo], "040501", "1.0", "", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(true, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function getHordingDetailsForRenew(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|digits_between:1,9223372036854775807'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $mAdvHoarding = new AdvHoarding();
            $details = $mAdvHoarding->applicationDetailsForRenew($req->applicationId);
            if (!$details)
                throw new Exception("Application Not Found !!!");

            return responseMsgs(true, "Application Fetched !!!", remove_null($details), "050103", "1.0", "200 ms", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040301", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Save Application For Licence
     */
    public function renewalHording(RenewalHordingRequest $req)
    {
        try {
            $mAdvActiveHoarding = new AdvActiveHoarding();
            if (authUser()->user_type == 'JSK') {
                $userId = ['userId' => authUser()->id];
                $req->request->add($userId);
            } else {
                $citizenId = ['citizenId' => authUser()->id];
                $req->request->add($citizenId);
            }
            DB::beginTransaction();
            $RenewNo = $mAdvActiveHoarding->renewalHording($req);       //<--------------- Model function to store 
            DB::commit();
            return responseMsgs(true, "Successfully Renewal the application !!", ['status' => true, 'ApplicationNo' => $RenewNo], "040501", "1.0", "", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(true, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | License Inbox List
     * | @param Request $req
     */
    public function listLicenseInbox(Request $req)
    {
        try {
            $mAdvActiveHoarding = new AdvActiveHoarding();
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $inboxList = $mAdvActiveHoarding->listInbox($roleIds);
            return responseMsgs(true, "Inbox Applications", remove_null($inboxList->toArray()), "040103", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | License Outbox List
     */
    public function listLicenseOutbox(Request $req)
    {
        try {
            $mAdvActiveHoarding = new AdvActiveHoarding();
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $outboxList = $mAdvActiveHoarding->listOutbox($roleIds);
            return responseMsgs(true, "Outbox Lists", remove_null($outboxList->toArray()), "040104", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040104", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * | License Application Details
     */

    public function getLicenseDetailsById(Request $req)
    {
        try {
            $mAdvActiveHoarding = new AdvActiveHoarding();
            // $data = array();
            $fullDetailsData = array();
            if (isset($req->type)) {
                $type = $req->type;
            } else {
                $type = NULL;
            }
            if ($req->applicationId) {
                $data = $mAdvActiveHoarding->getDetailsById($req->applicationId, $type);
            } else {
                throw new Exception("Application Id Not Passed");
            }

            if (!$data) {
                throw new Exception("Application Details Not Found");
            }
            // Basic Details
            $basicDetails = $this->generatehordingDetails($data); // Trait function to get Basic Details
            $basicElement = [
                'headerTitle' => "Basic License Details",
                "data" => $basicDetails
            ];

            $cardDetails = $this->generateHoardingCardDetails($data);
            $cardElement = [
                'headerTitle' => "License Details",
                'data' => $cardDetails
            ];

            $fullDetailsData['fullDetailsData']['dataArray'] = new Collection([$basicElement]);
            $fullDetailsData['fullDetailsData']['cardArray'] = new Collection($cardElement);

            $metaReqs['customFor'] = 'HOARDING';
            $metaReqs['wfRoleId'] = $data['current_role_id'];
            $metaReqs['workflowId'] = $data['workflow_id'];
            $metaReqs['lastRoleId'] = $data['last_role_id'];
            // return $metaReqs;
            $req->request->add($metaReqs);

            $forwardBackward = $this->getRoleDetails($req);
            // return $forwardBackward;
            $fullDetailsData['roleDetails'] = collect($forwardBackward)['original']['data'];
            // return $fullDetailsData['roleDetails'];

            $fullDetailsData = remove_null($fullDetailsData);

            $fullDetailsData['application_no'] = $data['application_no'];
            $fullDetailsData['apply_date'] = $data['application_date'];
            $fullDetailsData['doc_verify_status'] = $data['doc_verify_status'];
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
    public function listLicenseAppliedApplications(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $mAdvActiveHoarding = new AdvActiveHoarding();
            $applications = $mAdvActiveHoarding->listAppliedApplications($citizenId);
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
     * | License Escalate
     */
    public function escalateLicenseApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "escalateStatus" => "required|int",
            "applicationId" => "required|int",
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $userId = auth()->user()->id;
            $applicationId = $request->applicationId;
            $data = AdvActiveHoarding::find($applicationId);
            $data->is_escalate = $request->escalateStatus;
            $data->escalate_by = $userId;
            $data->save();
            return responseMsgs(true, $request->escalateStatus == 1 ? 'Hording is Escalated' : "Hording is removed from Escalated", '', "010106", "1.0", "353ms", "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $request->all());
        }
    }

    /**
     * | Special Inbox
     */
    public function listLicenseEscalated(Request $req)
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

            $advData = $this->Repository->specialAgencyLicenseInbox($this->_workflowIds)                      // Repository function to get Advertiesment Details
                ->where('is_escalate', 1)
                ->where('adv_active_agency_licenses.ulb_id', $ulbId)
                // ->whereIn('ward_mstr_id', $wardId)
                ->get();
            return responseMsgs(true, "Data Fetched", remove_null($advData), "010107", "1.0", "251ms", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    /**
     * | License Forward or Backward Application
     */
    public function forwardLicenseNextLevel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'applicationId' => 'required|integer',
            'senderRoleId' => 'required|integer',
            'receiverRoleId' => 'required|integer',
            'comment' => 'required',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Hording  Application Update Current Role Updation
            DB::beginTransaction();
            $adv = AdvActiveHoarding::find($request->applicationId);
            $adv->last_role_id = $request->senderRoleId;
            $adv->current_role_id = $request->receiverRoleId;
            $adv->save();

            $metaReqs['moduleId'] = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
            $metaReqs['workflowId'] = $adv->workflow_id;
            $metaReqs['refTableDotId'] = "adv_active_hoardings.id";
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

    // License Post Independent Comment
    public function commentLicenseApplication(Request $request)
    {
        $request->validate([
            'comment' => 'required',
            'applicationId' => 'required|integer',
            'senderRoleId' => 'nullable|integer'
        ]);

        try {
            $userId = authUser()->id;
            $userType = authUser()->user_type;
            $workflowTrack = new WorkflowTrack();
            $mWfRoleUsermap = new WfRoleusermap();
            $mAdvActiveHoarding = AdvActiveHoarding::find($request->applicationId);                // Agency License Details
            $mModuleId = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
            $metaReqs = array();
            DB::beginTransaction();
            // Save On Workflow Track For Level Independent
            $metaReqs = [
                'workflowId' => $mAdvActiveHoarding->workflow_id,
                'moduleId' => $mModuleId,
                'refTableDotId' => "adv_active_hoardings.id",
                'refTableIdValue' => $mAdvActiveHoarding->id,
                'message' => $request->comment
            ];
            // For Citizen Independent Comment
            if ($userType != 'Citizen') {
                $roleReqs = new Request([
                    'workflowId' => $mAdvActiveHoarding->workflow_id,
                    'userId' => $userId,
                ]);
                $wfRoleId = $mWfRoleUsermap->getRoleByUserWfId($roleReqs);
                $metaReqs = array_merge($metaReqs, ['senderRoleId' => $wfRoleId->wf_role_id]);
                $metaReqs = array_merge($metaReqs, ['user_id' => $userId]);
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

    public function viewLicenseDocuments(Request $req)
    {
        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        if ($req->applicationId) {
            $data = $mWfActiveDocument->uploadDocumentsViewById($req->applicationId,  $this->_workflowIds);
        } else {
            throw new Exception("Required Application Id And Application Type ");
        }
        $data1['data'] = $data;
        return $data1;
    }

    /**
     * | Get Uploaded Active Document by application ID
     */
    public function viewActiveLicenseDocument(Request $req)
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
    public function viewLicenseDocumentsOnWorkflow(Request $req)
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
     * | Final Approval and Rejection of the Application
     * | Rating-
     * | Status- Open
     */
    public function approvalOrRejectionLicense(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'roleId' => 'required',
                'applicationId' => 'required|integer',
                'status' => 'required|integer',
                // 'payment_amount' => 'required',
            ]);
            if ($validator->fails()) {
                return ['status' => false, 'message' => $validator->errors()];
            }

            // Check if the Current User is Finisher or Not         
            $mAdvActiveHoarding = AdvActiveHoarding::find($req->applicationId);
            $getFinisherQuery = $this->getFinisherId($mAdvActiveHoarding->workflow_id);                                 // Get Finisher using Trait
            $refGetFinisher = collect(DB::select($getFinisherQuery))->first();
            if ($refGetFinisher->role_id != $req->roleId) {
                return responseMsgs(false, " Access Forbidden", "");
            }

            DB::beginTransaction();
            // Approval
            if ($req->status == 1) {
                $amount = $this->getHordingPrice($mAdvActiveHoarding->typology, $mAdvActiveHoarding->zone_id);
                $payment_amount = ['payment_amount' => $amount];
                $req->request->add($payment_amount);
                     
                // License NO Generate
                $reqData = [
                    "paramId" => $this->_paramId,
                    'ulbId' => $mAdvActiveHoarding->ulb_id
                ];
                $refResponse = Http::withToken($req->bearerToken())
                    ->post($this->_baseUrl . 'api/id-generator', $reqData);
                $idGenerateData = json_decode($refResponse);

                if ($mAdvActiveHoarding->renew_no == NULL) {
                    // approved Hording Application replication
                    $approvedHoarding = $mAdvActiveHoarding->replicate();
                    $approvedHoarding->setTable('adv_hoardings');
                    $temp_id = $approvedHoarding->id = $mAdvActiveHoarding->id;
                    $approvedHoarding->license_no = $idGenerateData->data;
                    $approvedHoarding->payment_amount = $req->payment_amount;
                    $approvedHoarding->approve_date = Carbon::now();
                    $approvedHoarding->save();

                    // Save in Hording Renewal
                    $approvedHoarding = $mAdvActiveHoarding->replicate();
                    $approvedHoarding->approve_date = Carbon::now();
                    $approvedHoarding->license_no = $idGenerateData->data;
                    $approvedHoarding->setTable('adv_hoarding_renewals');
                    $approvedHoarding->id = $temp_id;
                    $approvedHoarding->save();

                    $mAdvActiveHoarding->delete();

                    // Update in adv_hoardings (last_renewal_id)

                    DB::table('adv_hoardings')
                        ->where('id', $temp_id)
                        ->update(['last_renewal_id' => $approvedHoarding->id]);

                    $msg = "Application Successfully Approved !!";
                } else {
                    //  Renewal Application Case

                    // Hording Application replication
                    $license_no = $mAdvActiveHoarding->license_no;
                    AdvHoarding::where('license_no', $license_no)->delete();

                    $approvedHoarding = $mAdvActiveHoarding->replicate();
                    $approvedHoarding->setTable('adv_hoardings');
                    $temp_id = $approvedHoarding->id = $mAdvActiveHoarding->id;
                    $approvedHoarding->payment_amount = $req->payment_amount;
                    $approvedHoarding->payment_status = $req->payment_status;
                    $approvedHoarding->approve_date = Carbon::now();
                    $approvedHoarding->save();

                    // Save in Hording Advertisement Renewal
                    $approvedHoarding = $approvedHoarding->replicate();
                    $approvedHoarding->approve_date = Carbon::now();
                    $approvedHoarding->setTable('adv_hoarding_renewals');
                    $approvedHoarding->id = $temp_id;
                    $approvedHoarding->save();

                    $approvedHoarding->delete();

                    // Update in adv_hoardings (last_renewal_id)
                    DB::table('adv_hoardings')
                        ->where('id', $temp_id)
                        ->update(['last_renewal_id' => $approvedHoarding->id]);
                    $msg = "Application Successfully Renewal !!";
                }
            }
            // Rejection
            if ($req->status == 0) {

                $payment_amount = ['payment_amount' => 0];
                $req->request->add($payment_amount);

                // Agency advertisement Application replication
                $rejectedAgencyLicense = $mAdvActiveHoarding->replicate();
                $rejectedAgencyLicense->setTable('adv_rejected_hoardings');
                $rejectedAgencyLicense->id = $mAdvActiveHoarding->id;
                $rejectedAgencyLicense->rejected_date = Carbon::now();
                $rejectedAgencyLicense->save();
                $mAdvActiveHoarding->delete();
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
     * | Get Hording price
     */
    public function getHordingPrice($typology_id, $zone = 'A')
    {
        return DB::table('adv_typology_mstrs')
            ->select(DB::raw("case when $zone = 1 then rate_zone_a
                              when $zone = 2 then rate_zone_b
                              when $zone = 3 then rate_zone_c
                        else 0 end as rate"))
            ->where('id', $typology_id)
            ->first()->rate;
    }

    /**
     * | Approve License Application List for Citzen
     * | @param Request $req
     */
    public function listApprovedLicense(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $userId = authUser()->user_type;
            $mAdvHoarding = new AdvHoarding();
            $applications = $mAdvHoarding->listApproved($citizenId, $userId);
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
     * | Unpaid License Application List for Citzen
     * | @param Request $req
     */
    public function listUnpaidLicenses(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $userId = authUser()->user_type;
            $mAdvHoarding = new AdvHoarding();
            $applications = $mAdvHoarding->listUnpaid($citizenId, $userId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            if ($data1['arrayCount'] == 0) {
                $data1 = null;
            }
            return responseMsgs(true, "Unpaid Application List", $data1, "040103", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * | Reject License Application List for Citizen
     * | @param Request $req
     */
    public function listRejectedLicense(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $mAdvRejectedHoarding = new AdvRejectedHoarding();
            $applications = $mAdvRejectedHoarding->listRejected($citizenId);
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
     * | Get Applied License Applications by Logged In JSK
     */
    public function getJskLicenseApplications(Request $req)
    {
        try {
            $userId = authUser()->id;
            $mmAdvRejectedHoarding = new AdvActiveHoarding();
            $applications = $mmAdvRejectedHoarding->getJskLicenseApplications($userId);
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
     * | Approve License Application List for JSK
     * | @param Request $req
     */
    public function listJskApprovedLicenseApplication(Request $req)
    {
        try {
            $userId = authUser()->id;
            $mAdvHoarding = new AdvHoarding();
            $applications = $mAdvHoarding->listJskApprovedLicenseApplication($userId);
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
     * | Reject License Application List for JSK
     * | @param Request $req
     */
    public function listJskRejectedLicenseApplication(Request $req)
    {
        try {
            $userId = authUser()->id;
            $mAdvRejectedHoarding = new AdvRejectedHoarding();
            $applications = $mAdvRejectedHoarding->listJskRejectedLicenseApplication($userId);
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
    public function generateLicensePaymentOrderId(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $startTime = microtime(true);
            $mAdvHoarding = AdvHoarding::find($req->id);
            $reqData = [
                "id" => $mAdvHoarding->id,
                'amount' => $mAdvHoarding->payment_amount,
                'workflowId' => $mAdvHoarding->workflow_id,
                'ulbId' => $mAdvHoarding->ulb_id,
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

            $data->name = $mAdvHoarding->applicant;
            $data->email = $mAdvHoarding->email;
            $data->contact = $mAdvHoarding->mobile_no;
            $data->type = "Hording";
            // return $data;
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            return responseMsgs(true, "Payment OrderId Generated Successfully !!!", $data, "050123", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050123", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * License (Hording) application Details For Payment
     * @param Request $req
     * @return void
     */
    public function getLicenseApplicationDetailsForPayment(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $startTime = microtime(true);
            $mAdvHoarding = new AdvHoarding();
            if ($req->applicationId) {
                $data = $mAdvHoarding->getApplicationDetailsForPayment($req->applicationId);
            }

            if (!$data)
                throw new Exception("Application Not Found");

            $data['type'] = "Hording";
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, 'Data Fetched',  $data, "050124", "1.0", "$executionTime Sec", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    /**
     * Check isAgency or Not      
     * | It is a case of agency=========================================================
     * @return void
     */
    // public function isAgency(Request $req)
    // {
    //     try {
    //         $userType = authUser()->user_type;
    //         if ($userType == "Citizen") {
    //             $startTime = microtime(true);
    //             $citizenId = authUser()->id;
    //             $mAdvAgency = new AdvAgency();
    //             $isAgency = $mAdvAgency->checkAgency($citizenId);
    //             $endTime = microtime(true);
    //             $executionTime = $endTime - $startTime;
    //             if (empty($isAgency)) {
    //                 throw new Exception("You Have Not Agency !!");
    //             } else {
    //                 return responseMsgs(true, "Data Fetched !!!", $isAgency, "050123", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
    //             }
    //         } else {
    //             throw new Exception("You Are Not Citizen");
    //         }
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "050123", "1.0", "", 'POST', $req->deviceId ?? "");
    //     }
    // }

    public function getAgencyDashboard(Request $req)
    {
        try {
            $userType = authUser()->user_type;
            if ($userType == "Citizen") {
                $startTime = microtime(true);
                $citizenId = authUser()->id;
                $mAdvHoarding = new AdvHoarding();
                $agencyDashboard = $mAdvHoarding->agencyDashboard($citizenId);
                $endTime = microtime(true);
                $executionTime = $endTime - $startTime;
                if (empty($agencyDashboard)) {
                    throw new Exception("You Have Not Agency !!");
                } else {
                    return responseMsgs(true, "Data Fetched !!!", $agencyDashboard, "050123", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
                }
            } else {
                throw new Exception("You Are Not Citizen");
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050123", "1.0", "", 'POST', $req->deviceId ?? "");
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
            $mAdvHoarding = new AdvHoarding();
            DB::beginTransaction();
            $status = $mAdvHoarding->paymentByCash($req);
            DB::commit();
            if ($req->status == '1' && $status == 1) {
                return responseMsgs(true, "Payment Successfully !!", '', "040501", "1.0", "", 'POST', $req->deviceId ?? "");
            } else {
                return responseMsgs(false, "Payment Rejected !!", '', "040501", "1.0", "", 'POST', $req->deviceId ?? "");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function entryChequeDdLicense(Request $req)
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

    public function clearOrBounceChequeLicense(Request $req)
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
                return responseMsgs(true, "Payment Successfully !!", '', "040501", "1.0", "", 'POST', $req->deviceId ?? "");
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
     * |
     */
    public function verifyOrRejectLicenseDoc(Request $req)
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
            $mAdvActiveHoarding = new AdvActiveHoarding();
            $mWfRoleusermap = new WfRoleusermap();
            $wfDocId = $req->id;
            $userId = authUser()->id;
            $applicationId = $req->applicationId;

            $wfLevel = Config::get('constants.SELF-LABEL');
            // Derivative Assigments
            $appDetails = $mAdvActiveHoarding->getHoardingNo($applicationId);

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


            $ifFullDocVerified = $this->ifFullLicenseDocVerified($applicationId);       // (Current Object Derivative Function 4.1)

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
            $ifFullDocVerifiedV1 = $this->ifFullLicenseDocVerified($applicationId);

            if ($ifFullDocVerifiedV1 == 1) {                                     // If The Document Fully Verified Update Verify Status
                $appDetails->doc_verify_status = 1;
                $appDetails->save();
            }

            DB::commit();
            return responseMsgs(true, $req->docStatus . " Successfully", "", "010204", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "010204", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Check if the Document is Fully Verified or Not (4.1)
     */
    public function ifFullLicenseDocVerified($applicationId)
    {
        $mAdvActiveHoarding = new AdvActiveHoarding();
        $mWfActiveDocument = new WfActiveDocument();
        $mAdvActiveHoarding = $mAdvActiveHoarding->getHoardingNo($applicationId);                      // Get Application Details
        $refReq = [
            'activeId' => $applicationId,
            'workflowId' => $mAdvActiveHoarding->workflow_id,
            'moduleId' =>  $this->_moduleId
        ];
        $req = new Request($refReq);
        $refDocList = $mWfActiveDocument->getDocsByActiveId($req);
        // Vehicle Advertiesement List Documents
        $ifAdvDocUnverified = $refDocList->contains('verify_status', 0);
        if ($ifAdvDocUnverified == 1)
            return 0;
        else
            return 1;
    }




    /**
     *  | Send Application back to citizen
     */
    public function backToCitizenLicense(Request $req)
    {
        $req->validate([
            'applicationId' => "required"
        ]);
        try {
            $redis = Redis::connection();
            $mAdvActiveHoarding = AdvActiveHoarding::find($req->applicationId);

            $workflowId = $mAdvActiveHoarding->workflow_id;
            $backId = json_decode(Redis::get('workflow_initiator_' . $workflowId));
            if (!$backId) {
                $backId = WfWorkflowrolemap::where('workflow_id', $workflowId)
                    ->where('is_initiator', true)
                    ->first();
                $redis->set('workflow_initiator_' . $workflowId, json_encode($backId));
            }

            $mAdvActiveHoarding->current_role_id = $backId->wf_role_id;
            $mAdvActiveHoarding->parked = 1;
            $mAdvActiveHoarding->save();


            $metaReqs['moduleId'] = $this->_moduleId;
            $metaReqs['workflowId'] = $mAdvActiveHoarding->workflow_id;
            $metaReqs['refTableDotId'] = "adv_active_agency_licenses.id";
            $metaReqs['refTableIdValue'] = $req->applicationId;
            $metaReqs['verificationStatus'] = $req->verificationStatus;
            $metaReqs['senderRoleId'] = $req->currentRoleId;
            $req->request->add($metaReqs);

            $req->request->add($metaReqs);
            $track = new WorkflowTrack();
            $track->saveTrack($req);

            return responseMsgs(true, "Successfully Done", "", "", '010710', '01', '358ms', 'Post', '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    /**
     * | Back To Citizen Inbox
     */
    public function listLicenseBtcInbox()
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

            $mAdvActiveHoarding = new AdvActiveHoarding();
            $btcList = $mAdvActiveHoarding->getHoardingList($ulbId)
                ->whereIn('adv_active_hoardings.current_role_id', $roleId)
                // ->whereIn('a.ward_mstr_id', $occupiedWards)
                ->where('parked', true)
                ->orderByDesc('adv_active_hoardings.id')
                ->get();

            return responseMsgs(true, "BTC Inbox List", remove_null($btcList), 010717, 1.0, "271ms", "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", 010717, 1.0, "271ms", "POST", "", "");
        }
    }

    public function checkFullLicenseUpload($applicationId)
    {
        $docCode = $this->_docCode;
        $mWfActiveDocument = new WfActiveDocument();
        $moduleId = $this->_moduleId;
        $totalRequireDocs = $mWfActiveDocument->totalNoOfDocs($docCode);
        $appDetails = AdvActiveHoarding::find($applicationId);
        $totalUploadedDocs = $mWfActiveDocument->totalUploadedDocs($applicationId, $appDetails->workflow_id, $moduleId);
        if ($totalRequireDocs == $totalUploadedDocs) {
            $appDetails->doc_upload_status = '1';
            // $appDetails->doc_verify_status = '1';
            $appDetails->parked = NULL;
            $appDetails->save();
        } else {
            $appDetails->doc_upload_status = '0';
            $appDetails->doc_verify_status = '0';
            $appDetails->save();
        }
    }

    public function reuploadLicenseDocument(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|digits_between:1,9223372036854775807',
            'image' => 'required|mimes:png,jpeg,pdf,jpg'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $mAdvActivehoarding = new AdvActivehoarding();
            DB::beginTransaction();
            $appId = $mAdvActivehoarding->reuploadDocument($req);
            $this->checkFullLicenseUpload($appId);
            DB::commit();
            return responseMsgs(true, "Document Uploaded Successfully", "", 010717, 1.0, "271ms", "POST", "", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, "Document Not Uploaded", "", 010717, 1.0, "271ms", "POST", "", "");
        }
    }

    /**
     * | Approve License Application List for Citzen
     * | @param Request $req
     */
    public function getRenewActiveApplications(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $userId = authUser()->user_type;
            $AdvHoarding = new AdvHoarding();
            $applications = $AdvHoarding->getRenewActiveApplications($citizenId, $userId);
            $totalApplication = count($applications);
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

    public function listExpiredHording(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $userId = authUser()->user_type;
            $mAdvHoarding = new AdvHoarding();
            $applications = $mAdvHoarding->listExpiredHording($citizenId, $userId);
            $totalApplication = count($applications);
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
     * | Archived Application By Id 
     */
    public function archivedHording(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|digits_between:1,9223372036854775807'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $mAdvHoarding = AdvHoarding::find($req->applicationId);
            $mAdvHoarding->is_archived = 1;
            $mAdvHoarding->save();
            return responseMsgs(true, "Archived Application Successfully", "", "040103", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Hording Archived List for Citizen
     * | @param Request $req
     */
    public function listHordingArchived(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $userId = authUser()->user_type;
            $mAdvHoarding = new AdvHoarding();
            $applications = $mAdvHoarding->listHordingArchived($citizenId, $userId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            if ($data1['arrayCount'] == 0) {
                $data1 = null;
            }
            return responseMsgs(true, "Archived Application List", $data1, "040103", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * | Blacklist Application By Id 
     */
    public function blacklistHording(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|digits_between:1,9223372036854775807'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $mmAdvHoarding = AdvHoarding::find($req->applicationId);
            $mmAdvHoarding->is_blacklist = 1;
            $mmAdvHoarding->save();
            return responseMsgs(true, "Blacklist Application Successfully", "", "040103", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Hording Archived List for Citizen
     * | @param Request $req
     */
    public function listHordingBlacklist(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $userId = authUser()->user_type;
            $mAdvHoarding = new AdvHoarding();
            $applications = $mAdvHoarding->listHordingArchived($citizenId, $userId);
            $totalApplication = $applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            if ($data1['arrayCount'] == 0) {
                $data1 = null;
            }
            return responseMsgs(true, "Blacklist Application List", $data1, "040103", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }
}
