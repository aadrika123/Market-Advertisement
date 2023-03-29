<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agency\RenewalHordingRequest;
use App\Http\Requests\Agency\RenewalRequest;
use App\Http\Requests\Agency\StoreRequest;
use App\Http\Requests\Agency\StoreLicenceRequest;
use App\Models\Advertisements\AdvActiveAgency;
use App\Models\Advertisements\AdvAgency;
use App\Models\Advertisements\AdvRejectedAgency;
use App\Models\Advertisements\AdvRejectedAgencyLicense;
use App\Models\Advertisements\AdvActiveAgencyLicense;
use App\Models\Advertisements\AdvActiveHoarding;
use App\Models\Advertisements\AdvAgencyLicense;
use App\Models\Advertisements\AdvCheckDtl;
use App\Models\Advertisements\AdvChequeDtl;
use App\Models\Advertisements\AdvHoarding;
use App\Models\Advertisements\AdvTypologyMstr;
use App\Models\Advertisements\WfActiveDocument;
use App\Models\Workflows\WfRoleusermap;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

use App\Traits\AdvDetailsTraits;
use App\Models\Workflows\WfWardUser;
use App\Models\Workflows\WfWorkflowrolemap;
use App\Repositories\SelfAdvets\iSelfAdvetRepo;
use App\Models\Workflows\WorkflowTrack;
use App\Traits\WorkflowTrait;

use Illuminate\Support\Facades\Validator;


use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

/**
 * | Created On-02-01-20222 
 * | Created By-Anshu Kumar
 * | Agency Operations
 */
class AgencyController extends Controller
{

    use AdvDetailsTraits;

    use WorkflowTrait;

    protected $_modelObj;

    protected $Repository;

    protected $_workflowIds;
    protected $_agencyRegPrice;
    protected $_agencyRenewPrice;
    protected $_moduleId;
    protected $_docCode;
    protected $_tempParamId;
    protected $_paramId;
    protected $_baseUrl;
    public function __construct(iSelfAdvetRepo $agency_repo)
    {
        $this->_modelObj = new AdvActiveAgency();
        $this->_workflowIds = Config::get('workflow-constants.AGENCY_WORKFLOWS');
        $this->_agencyRegPrice = Config::get('workflow-constants.AGENCY_REG_PRICE');
        $this->_agencyRenewPrice = Config::get('workflow-constants.AGENCY_RENEW_PRICE');
        $this->_moduleId = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
        $this->_docCode = Config::get('workflow-constants.AGENCY_DOC_CODE');
        $this->_tempParamId = Config::get('workflow-constants.TEMP_AGY_ID');
        $this->_paramId = Config::get('workflow-constants.AGY_ID');
        $this->_baseUrl = Config::get('constants.BASE_URL');
        $this->Repository = $agency_repo;
    }

    /**
     * | Agency Details After Login
     * | @param Request $req
     */

    public function getagencyDetails(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $mAdvAgency = new AdvAgency();
            $agencydetails = $mAdvAgency->getagencyDetails($req->applicationId);
            if (!$agencydetails) {
                throw new Exception('You Have No Any Agency !!!');
            }
            remove_null($agencydetails);
            $data1['data'] = $agencydetails;
            return responseMsgs(true, "Agency Details", $data1, "040106", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040106", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }




    /**
     * | Store 
     * | @param StoreRequest Request
     */
    public function addNew(StoreRequest $req)
    {
        try {
            $agency = new AdvActiveAgency();
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
            $applicationNo = $agency->addNew($req);       //<--------------- Model function to store 
            DB::commit();
            return responseMsgs(true, "Successfully Submitted the application !!", ['status' => true, 'ApplicationNo' => $applicationNo], "040501", "1.0", "", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(true, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Inbox List
     * | @param Request $req
     */
    public function listInbox(Request $req)
    {
        try {
            $mAdvActiveAgency = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $inboxList = $mAdvActiveAgency->listInbox($roleIds);
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
            $mAdvActiveAgency = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $outboxList = $mAdvActiveAgency->listOutbox($roleIds);
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
            $mAdvActiveAgency = new AdvActiveAgency();
            // $data = array();
            $fullDetailsData = array();
            if (isset($req->type)) {
                $type = $req->type;
            } else {
                $type = NULL;
            }
            if ($req->applicationId) {
                $data = $mAdvActiveAgency->getDetailsById($req->applicationId, $type);
            } else {
                throw new Exception("Not Pass Application Id");
            }

            // Basic Details
            $basicDetails = $this->generateAgencyBasicDetails($data); // Trait function to get Basic Details
            $basicElement = [
                'headerTitle' => "Basic Agency Details",
                "data" => $basicDetails
            ];

            $cardDetails = $this->generateAgencyCardDetails($data);
            $cardElement = [
                'headerTitle' => "About Agency",
                'data' => $cardDetails
            ];

            $fullDetailsData['fullDetailsData']['dataArray'] = new Collection([$basicElement]);
            $fullDetailsData['fullDetailsData']['cardArray'] = new Collection($cardElement);


            $metaReqs['customFor'] = 'AGENCY';
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
            if (isset($data['payment_amount'])) {
                $fullDetailsData['payment_amount'] = $data['payment_amount'];
            }
            $fullDetailsData['directors'] = $data['directors'];
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
            $mAdvActiveAgency = new AdvActiveAgency();
            $applications = $mAdvActiveAgency->listAppliedApplications($citizenId);
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
            $data = AdvActiveAgency::find($applicationId);
            $data->is_escalate = $request->escalateStatus;
            $data->escalate_by = $userId;
            $data->save();
            return responseMsgs(true, $request->escalateStatus == 1 ? 'Agency is Escalated' : "Agency is removed from Escalated", '', "010106", "1.0", "353ms", "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $request->all());
        }
    }

    /**
     * | Special Inbox
     */
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
            $advData = $this->Repository->specialAgencyInbox($this->_workflowIds)                      // Repository function to get Advertiesment Details
                ->where('is_escalate', 1)
                ->where('adv_active_agencies.ulb_id', $ulbId)
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
            $adv = AdvActiveAgency::find($request->applicationId);
            $adv->last_role_id = $request->current_role_id;
            $adv->current_role_id = $request->receiverRoleId;
            $adv->save();

            $metaReqs['moduleId'] = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
            $metaReqs['workflowId'] = $adv->workflow_id;
            $metaReqs['refTableDotId'] = "adv_active_agencies.id";
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
            $userId = authUser()->id;
            $userType = authUser()->user_type;
            $workflowTrack = new WorkflowTrack();
            $mWfRoleUsermap = new WfRoleusermap();
            $mAdvActiveAgency = AdvActiveAgency::find($request->applicationId);                // Agency Details
            $mModuleId = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
            $metaReqs = array();
            DB::beginTransaction();
            // Save On Workflow Track For Level Independent
            $metaReqs = [
                'workflowId' => $mAdvActiveAgency->workflow_id,
                'moduleId' => $mModuleId,
                'refTableDotId' => "adv_active_agencies.id",
                'refTableIdValue' => $mAdvActiveAgency->id,
                'message' => $request->comment
            ];
            // For Citizen Independent Comment
            if ($userType != 'Citizen') {
                $roleReqs = new Request([
                    'workflowId' => $mAdvActiveAgency->workflow_id,
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


    public function viewAgencyDocuments(Request $req)
    {
        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        if ($req->applicationId && $req->type) {
            // if ($req->type == 'Active') {
            //     $appId = $req->applicationId;
            // } elseif ($req->type == 'Reject') {
            //     $appId = AdvRejectedAgency::find($req->applicationId)->temp_id;
            // } elseif ($req->type == 'Approve') {
            //     $appId = AdvAgency::find($req->applicationId)->temp_id;
            // }
            $data = $mWfActiveDocument->uploadDocumentsViewById($req->applicationId, $this->_workflowIds);
        } else {
            throw new Exception("Required Application Id And Application Type ");
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

        return responseMsgs(true, "Data Fetched", remove_null($data), "050115", "1.0", "$executionTime Sec", "POST", "");
    }

    /**
     * |-------------------------------------Final Approval and Rejection of the Application ------------------------------------------------|
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

            ]);

            // Check if the Current User is Finisher or Not         
            $mAdvActiveAgency = AdvActiveAgency::find($req->applicationId);
            $getFinisherQuery = $this->getFinisherId($mAdvActiveAgency->workflow_id);                                 // Get Finisher using Trait
            $refGetFinisher = collect(DB::select($getFinisherQuery))->first();
            if ($refGetFinisher->role_id != $req->roleId) {
                return responseMsgs(false, " Access Forbidden", "");
            }

            DB::beginTransaction();
            // Approval
            if ($req->status == 1) {
                $payment_amount = ['payment_amount' =>  $this->_agencyRegPrice];                            // Agency Reg Price
                if ($mAdvActiveAgency->renewal == 1) {
                    $payment_amount = ['payment_amount' => $this->_agencyRenewPrice];                        // Agency Renew Price
                }
                $req->request->add($payment_amount);
                    
                // License NO Generate
                $reqData = [
                    "paramId" => $this->_paramId,
                    'ulbId' => $mAdvActiveAgency->ulb_id
                ];
                $refResponse = Http::withToken($req->bearerToken())
                    ->post($this->_baseUrl . 'api/id-generator', $reqData);
                $idGenerateData = json_decode($refResponse);
                // approved Vehicle Application replication
                $mAdvActiveAgency = AdvActiveAgency::find($req->applicationId);
                // $mAdvActiveAgency = AdvActiveAgency::find($req->applicationId);
                if ($mAdvActiveAgency->renew_no == NULL) {
                    $approvedAgency = $mAdvActiveAgency->replicate();
                    $approvedAgency->setTable('adv_agencies');
                    $temp_id = $approvedAgency->id = $mAdvActiveAgency->id;
                    $approvedAgency->license_no =  $idGenerateData->data;
                    $approvedAgency->payment_amount = $req->payment_amount;
                    $approvedAgency->approve_date = Carbon::now();
                    $approvedAgency->save();

                    // Save in Agency Advertisement Renewal
                    $approvedAgency = $mAdvActiveAgency->replicate();
                    $approvedAgency->approve_date = Carbon::now();
                    $approvedAgency->license_no =  $idGenerateData->data;
                    $approvedAgency->setTable('adv_agency_renewals');
                    $approvedAgency->agencyadvet_id = $temp_id;
                    $approvedAgency->save();

                    $mAdvActiveAgency->delete();
                    // Update in adv_agencies (last_renewal_id)
                    DB::table('adv_agencies')
                        ->where('id', $temp_id)
                        ->update(['last_renewal_id' => $approvedAgency->id]);

                    $msg = "Application Successfully Approved !!";
                } else {
                    //  Renewal Case
                    // Agency Advert Application replication
                    $application_no = $mAdvActiveAgency->application_no;
                    AdvAgency::where('application_no', $application_no)->delete();

                    $approvedAgency = $mAdvActiveAgency->replicate();
                    $approvedAgency->setTable('adv_agencies');
                    $temp_id = $approvedAgency->id = $mAdvActiveAgency->id;
                    $approvedAgency->payment_amount = $req->payment_amount;
                    $approvedAgency->payment_status = $req->payment_status;
                    $approvedAgency->approve_date = Carbon::now();
                    $approvedAgency->save();

                    // Save in Agency Advertisement Renewal
                    $approvedAgency = $mAdvActiveAgency->replicate();
                    $approvedAgency->approve_date = Carbon::now();
                    $approvedAgency->setTable('adv_agency_renewals');
                    $approvedAgency->id = $temp_id;
                    $approvedAgency->save();

                    $mAdvActiveAgency->delete();
                    // Update in adv_agencies (last_renewal_id)
                    DB::table('adv_agencies')
                        ->where('id', $temp_id)
                        ->update(['last_renewal_id' => $approvedAgency->id]);
                    $msg = "Application Successfully Renewal !!";
                }
            }
            // Rejection
            if ($req->status == 0) {
                $payment_amount = ['payment_amount' => 0];
                $req->request->add($payment_amount);

                // Agency advertisement Application replication
                $rejectedAgency = $mAdvActiveAgency->replicate();
                $rejectedAgency->setTable('adv_rejected_agencies');
                $rejectedAgency->id = $mAdvActiveAgency->id;
                $rejectedAgency->rejected_date = Carbon::now();
                $rejectedAgency->save();
                $mAdvActiveAgency->delete();
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
            $mAdvAgency = new AdvAgency();
            $applications = $mAdvAgency->listApproved($citizenId, $userType);
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
            $mAdvRejectedAgency = new AdvRejectedAgency();
            $applications = $mAdvRejectedAgency->listRejected($citizenId);
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
     * | Get Applied Applications by Logged In JSK
     */
    public function getJSKApplications(Request $req)
    {
        try {
            $userId = authUser()->id;
            $mAdvActiveAgency = new AdvActiveAgency();
            $applications = $mAdvActiveAgency->getJSKApplications($userId);
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
            $mAdvAgency = new AdvAgency();
            $applications = $mAdvAgency->listjskApprovedApplication($userId);
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
            $mAdvRejectedAgency = new AdvRejectedAgency();
            $applications = $mAdvRejectedAgency->listJskRejectedApplication($userId);
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
            $mAdvAgency = AdvAgency::find($req->id);
            $reqData = [
                "id" => $mAdvAgency->id,
                'amount' => $mAdvAgency->payment_amount,
                'workflowId' => $mAdvAgency->workflow_id,
                'ulbId' => $mAdvAgency->ulb_id,
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

            $data->name = $mAdvAgency->applicant;
            $data->email = $mAdvAgency->email;
            $data->contact = $mAdvAgency->mobile_no;
            $data->type = "Agency";
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
            $mAdvAgency = new AdvAgency();
            if ($req->applicationId) {
                $data = $mAdvAgency->getApplicationDetailsForPayment($req->applicationId);
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
     * | Get Payment Details
     */
    public function getPaymentDetails(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'paymentId' => 'required|string'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $mAdvAgency = new AdvAgency();
            $paymentDetails = $mAdvAgency->getPaymentDetails($req->paymentId);
            if (empty($paymentDetails)) {
                throw new Exception("Payment Details Not Found By Given Paymenst Id !!!");
            } else {
                return responseMsgs(true, 'Data Fetched',  $paymentDetails, "050124", "1.0", "2 Sec", "POST", $req->deviceId);
            }
        } catch (Exception $e) {
            responseMsgs(false, $e->getMessage(), "");
        }
    }

    /**
     * | Renewal Agency
     */
    public function renewalAgency(RenewalRequest $req)
    {
        try {
            $agency = new AdvActiveAgency();
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
            $applicationNo = $agency->renewalAgency($req);       //<--------------- Model function to store 
            DB::commit();
            return responseMsgs(true, "Successfully Submitted Application For Renewals !!", ['status' => true, 'ApplicationNo' => $applicationNo], "040501", "1.0", "", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function agencyPaymentByCash(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|string',
            'status' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $mAdvAgency = new AdvAgency();
            DB::beginTransaction();
            $d = $mAdvAgency->paymentByCash($req);
            DB::commit();
            if ($req->status == '1' && $d['status'] == 1) {
                return responseMsgs(true, "Payment Successfully !!", ['status' => true, 'TransactionNo' => $d['paymentId'],'workflowId'=>$this->_workflowIds], "040501", "1.0", "", 'POST', $req->deviceId ?? "");
            } else {
                return responseMsgs(false, "Payment Rejected !!", '', "040501", "1.0", "", 'POST', $req->deviceId ?? "");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function entryChequeDd(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|string',               //  id of Application
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
            return responseMsgs(true, "Check Entry Successfully !!", ['status' => true, 'TransactionNo' => $transNo,'workflowId'=>$this->_workflowIds], "040501", "1.0", "", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function clearOrBounceCheque(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'paymentId' => 'required|string',
            'status' => 'required|integer',
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
     * | Approved Agency List
     */
    // public function listApprovedAgency()
    // {
    //     $mAdvAgency = new AdvAgency();
    //     $agencies = $mAdvAgency->listApprovedAgency();
    //     if (!empty($agencies)) {
    //         return responseMsgs(true, "Agency List", $agencies, "040501", "1.0", "", 'POST',  "");
    //     } else {
    //         return responseMsgs(false, "No Any Agency Found !!!", '', "040501", "1.0", "", 'POST', "");
    //     }
    // }




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
            $mAdvActiveAgency = new AdvActiveAgency();
            $mWfRoleusermap = new WfRoleusermap();
            $wfDocId = $req->id;
            $userId = authUser()->id;
            $applicationId = $req->applicationId;

            $wfLevel = Config::get('constants.SELF-LABEL');
            // Derivative Assigments
            $appDetails = $mAdvActiveAgency->getAgencyNo($applicationId);

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


            $ifFullDocVerified1 = $this->ifFullDocVerified($applicationId);       // (Current Object Derivative Function 4.1)

            if ($ifFullDocVerified1 == 1)
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
            return responseMsgs(true, $req->docStatus . " Successfully", "", "010204", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "010204", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Check if the Document is Fully Verified or Not (4.1)
     */
    public function ifFullDocVerified($applicationId)
    {
        $mAdvActiveVehicle = new AdvActiveAgency();
        $mWfActiveDocument = new WfActiveDocument();
        $mAdvActiveVehicle = $mAdvActiveVehicle->getAgencyNo($applicationId);                      // Get Application Details
        $refReq = [
            'activeId' => $applicationId,
            'workflowId' => $mAdvActiveVehicle->workflow_id,
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
     *  send back to citizen
     */
    public function backToCitizen(Request $req)
    {
        $req->validate([
            'applicationId' => "required"
        ]);
        try {
            $redis = Redis::connection();
            $mAdvActiveAgency = AdvActiveAgency::find($req->applicationId);

            $workflowId = $mAdvActiveAgency->workflow_id;
            $backId = json_decode(Redis::get('workflow_initiator_' . $workflowId));
            if (!$backId) {
                $backId = WfWorkflowrolemap::where('workflow_id', $workflowId)
                    ->where('is_initiator', true)
                    ->first();
                $redis->set('workflow_initiator_' . $workflowId, json_encode($backId));
            }

            $mAdvActiveAgency->current_role_id = $backId->wf_role_id;
            $mAdvActiveAgency->parked = 1;
            $mAdvActiveAgency->save();


            $metaReqs['moduleId'] = $this->_moduleId;
            $metaReqs['workflowId'] = $mAdvActiveAgency->workflow_id;
            $metaReqs['refTableDotId'] = "adv_active_agencies.id";
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

            $mAdvActiveAgency = new AdvActiveAgency();
            $btcList = $mAdvActiveAgency->getAgencyList($ulbId)
                ->whereIn('adv_active_agencies.current_role_id', $roleId)
                // ->whereIn('a.ward_mstr_id', $occupiedWards)
                ->where('parked', true)
                ->orderByDesc('adv_active_agencies.id')
                ->get();

            return responseMsgs(true, "BTC Inbox List", remove_null($btcList), 010717, 1.0, "271ms", "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", 010717, 1.0, "271ms", "POST", "", "");
        }
    }

    public function checkFullUpload($applicationId)
    {
        $docCode = $this->_docCode;
        $mWfActiveDocument = new WfActiveDocument();
        $moduleId = $this->_moduleId;
        $totalRequireDocs = $mWfActiveDocument->totalNoOfDocs($docCode);
        $appDetails = AdvActiveAgency::find($applicationId);
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
            $mAdvActiveAgency = new AdvActiveAgency();
            DB::beginTransaction();
            $appId = $mAdvActiveAgency->reuploadDocument($req);
            $this->checkFullUpload($appId);
            DB::commit();
            return responseMsgs(true, "Document Uploaded Successfully", "", 010717, 1.0, "271ms", "POST", "", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, "Document Not Uploaded", "", 010717, 1.0, "271ms", "POST", "", "");
        }
    }

    public function searchByNameorMobile(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'filterBy' => 'required|in:mobileNo,entityName',
            'parameter' => $req->filterBy == 'mobileNo' ? 'required|digits:10' : 'required|string',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $mAdvAgency = new AdvAgency();
            $listApplications = $mAdvAgency->searchByNameorMobile($req);
            if (!$listApplications)
                throw new Exception("Application Not Found !!!");

            return responseMsgs(true, "Application Fetched Successfully", $listApplications, 010717, 1.0, "271ms", "POST", "", "");
        } catch (Exception $e) {
            return responseMsgs(false, "Application Not Fetched", $e->getMessage(), 010717, 1.0, "271ms", "POST", "", "");
        }
    }

    /**
     * |==============================================================
     * |====================  Bikash Kumar ===========================
     * |==================== Hording Apply ===========================
     * |====================== 30-01-2023   ==========================
     * |==============================================================
     */

    /**
     * | Get Typology List
     */
    // public function listTypology(Request $req)
    // {
    //     try {
    //         $mAdvTypologyMstr = new AdvTypologyMstr();
    //         $typologyList = $mAdvTypologyMstr->listTypology1();
    //         $typologyList = $typologyList->groupBy('type');
    //         foreach ($typologyList as $key => $data) {
    //             $type = [
    //                 'Type' => "Type " . $key,
    //                 'data' => $typologyList[$key]
    //             ];
    //             $fData[] = $type;
    //         }
    //         $fullData['typology'] = $fData;

    //         return responseMsgs(true, "Typology Data Fetch Successfully!!", remove_null($fullData), "040103", "1.0", "", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
    //     }
    // }


    /**
     * | Get Typology List
     */
    //public function getHordingCategory(Request $req)
    // {
    //     try {
    //         $mAdvTypologyMstr = new AdvTypologyMstr();
    //         $typologyList = $mAdvTypologyMstr->getHordingCategory();

    //         return responseMsgs(true, "Typology Data Fetch Successfully!!", remove_null($typologyList), "040103", "1.0", "", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
    //     }
    // }

    /**
     * | Save Application For Licence
     */
    // public function addNewLicense(StoreLicenceRequest $req)
    // {
    //     try {
    //         $mAdvActiveAgencyLicense = new AdvActiveAgencyLicense();
    //         if (authUser()->user_type == 'JSK') {
    //             $userId = ['userId' => authUser()->id];
    //             $req->request->add($userId);
    //         } else {
    //             $citizenId = ['citizenId' => authUser()->id];
    //             $req->request->add($citizenId);
    //         }
    //         DB::beginTransaction();
    //         $LicenseNo = $mAdvActiveAgencyLicense->addNewLicense($req);       //<--------------- Model function to store 
    //         DB::commit();
    //         return responseMsgs(true, "Successfully Submitted the application !!", ['status' => true, 'ApplicationNo' => $LicenseNo], "040501", "1.0", "", 'POST', $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return responseMsgs(true, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
    //     }
    // }

    // public function getHordingDetailsForRenew(Request $req){
    //     $validator = Validator::make($req->all(), [
    //         'applicationId' => 'required|digits_between:1,9223372036854775807'
    //     ]);
    //     if ($validator->fails()) {
    //         return ['status' => false, 'message' => $validator->errors()];
    //     }
    //     try {
    //         $mAdvAgencyLicense = new AdvAgencyLicense();
    //         $details = $mAdvAgencyLicense->applicationDetailsForRenew($req->applicationId);
    //         if (!$details)
    //             throw new Exception("Application Not Found !!!");

    //         return responseMsgs(true, "Application Fetched !!!", remove_null($details), "050103", "1.0", "200 ms", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040301", "1.0", "", "POST", $req->deviceId ?? "");
    //     }
    // }

     /**
     * | Save Application For Licence
     */
    // public function renewalHording(RenewalHordingRequest $req)
    // { 
    //     try {
    //         $mAdvActiveAgencyLicense = new AdvActiveAgencyLicense();
    //         if (authUser()->user_type == 'JSK') {
    //             $userId = ['userId' => authUser()->id];
    //             $req->request->add($userId);
    //         } else {
    //             $citizenId = ['citizenId' => authUser()->id];
    //             $req->request->add($citizenId);
    //         }
    //         DB::beginTransaction();
    //         $RenewNo = $mAdvActiveAgencyLicense->renewalHording($req);       //<--------------- Model function to store 
    //         DB::commit();
    //         return responseMsgs(true, "Successfully Renewal the application !!", ['status' => true, 'ApplicationNo' => $RenewNo], "040501", "1.0", "", 'POST', $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return responseMsgs(true, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
    //     }
    // }

    /**
     * | License Inbox List
     * | @param Request $req
     */
    // public function listLicenseInbox(Request $req)
    // {
    //     try {
    //         $mAdvActiveAgencyLicense = new AdvActiveAgencyLicense();
    //         $bearerToken = $req->bearerToken();
    //         $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
    //         $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
    //             return $workflowRole['wf_role_id'];
    //         });
    //         $inboxList = $mAdvActiveAgencyLicense->listLicenseInbox($roleIds);
    //         return responseMsgs(true, "Inbox Applications", remove_null($inboxList->toArray()), "040103", "1.0", "", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
    //     }
    // }

    /**
     * | License Outbox List
     */
    // public function listLicenseOutbox(Request $req)
    // {
    //     try {
    //         $mAdvActiveAgencyLicense = new AdvActiveAgencyLicense();
    //         $bearerToken = $req->bearerToken();
    //         $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
    //         $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
    //             return $workflowRole['wf_role_id'];
    //         });
    //         $outboxList = $mAdvActiveAgencyLicense->listLicenseOutbox($roleIds);
    //         return responseMsgs(true, "Outbox Lists", remove_null($outboxList->toArray()), "040104", "1.0", "", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040104", "1.0", "", 'POST', $req->deviceId ?? "");
    //     }
    // }


    /**
     * | License Application Details
     */

    // public function getLicenseDetailsById(Request $req)
    // {
    //     try {
    //         $mAdvActiveAgencyLicense = new AdvActiveAgencyLicense();
    //         // $data = array();
    //         $fullDetailsData = array();
    //         if (isset($req->type)) {
    //             $type = $req->type;
    //         } else {
    //             $type = NULL;
    //         }
    //         if ($req->applicationId) {
    //             $data = $mAdvActiveAgencyLicense->getLicenseDetailsById($req->applicationId, $type);
    //         } else {
    //             throw new Exception("Not Pass Application Id");
    //         }

    //         if (!$data) {
    //             throw new Exception("Not Application Details Found");
    //         }
    //         // Basic Details
    //         $basicDetails = $this->generatehordingLicenseDetails($data); // Trait function to get Basic Details
    //         $basicElement = [
    //             'headerTitle' => "Basic License Details",
    //             "data" => $basicDetails
    //         ];

    //         $cardDetails = $this->generateLiceasneCardDetails($data);
    //         $cardElement = [
    //             'headerTitle' => "License Details",
    //             'data' => $cardDetails
    //         ];

    //         $fullDetailsData['fullDetailsData']['dataArray'] = new Collection([$basicElement]);
    //         $fullDetailsData['fullDetailsData']['cardArray'] = new Collection($cardElement);

    //         $metaReqs['customFor'] = 'Agency Hording License';
    //         $metaReqs['wfRoleId'] = $data['current_role_id'];
    //         $metaReqs['workflowId'] = $data['workflow_id'];
    //         $metaReqs['lastRoleId'] = $data['last_role_id'];
    //         // return $metaReqs;
    //         $req->request->add($metaReqs);

    //         $forwardBackward = $this->getRoleDetails($req);
    //         // return $forwardBackward;
    //         $fullDetailsData['roleDetails'] = collect($forwardBackward)['original']['data'];
    //         // return $fullDetailsData['roleDetails'];

    //         $fullDetailsData = remove_null($fullDetailsData);

    //         $fullDetailsData['application_no'] = $data['application_no'];
    //         $fullDetailsData['apply_date'] = $data['application_date'];
    //         $fullDetailsData['doc_verify_status'] = $data['doc_verify_status'];
    //         $fullDetailsData['timelineData'] = collect($req);
    //         return responseMsgs(true, 'Data Fetched', $fullDetailsData, "010104", "1.0", "303ms", "POST", $req->deviceId);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "");
    //     }
    // }

    /**
     * | Get Applied Applications by Logged In Citizen
     */
    // public function listLicenseAppliedApplications(Request $req)
    // {
    //     try {
    //         $citizenId = authUser()->id;
    //         $mAdvActiveAgencyLicense = new AdvActiveAgencyLicense();
    //         $applications = $mAdvActiveAgencyLicense->listLicenseAppliedApplications($citizenId);
    //         $totalApplication = $applications->count();
    //         remove_null($applications);
    //         $data1['data'] = $applications;
    //         $data1['arrayCount'] =  $totalApplication;
    //         return responseMsgs(true, "Applied Applications", $data1, "040106", "1.0", "", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040106", "1.0", "", "POST", $req->deviceId ?? "");
    //     }
    // }

    /**
     * | License Escalate
     */
    // public function escalateLicenseApplication(Request $request)
    // {
    //     $request->validate([
    //         "escalateStatus" => "required|int",
    //         "applicationId" => "required|int",
    //     ]);
    //     try {
    //         $userId = auth()->user()->id;
    //         $applicationId = $request->applicationId;
    //         $data = AdvActiveAgencyLicense::find($applicationId);
    //         $data->is_escalate = $request->escalateStatus;
    //         $data->escalate_by = $userId;
    //         $data->save();
    //         return responseMsgs(true, $request->escalateStatus == 1 ? 'Agency Hording is Escalated' : "Agency Hording is removed from Escalated", '', "010106", "1.0", "353ms", "POST", $request->deviceId);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), $request->all());
    //     }
    // }

    /**
     * | Special Inbox
     */
    // public function listLicenseEscalated(Request $req)
    // {
    //     try {
    //         $mWfWardUser = new WfWardUser();
    //         $userId = authUser()->id;
    //         $ulbId = authUser()->ulb_id;

    //         $occupiedWard = $mWfWardUser->getWardsByUserId($userId);                        // Get All Occupied Ward By user id using trait
    //         $wardId = $occupiedWard->map(function ($item, $key) {                           // Filter All ward_id in an array using laravel collections
    //             return $item->ward_id;
    //         });

    //         // print_r($wardId);

    //         $advData = $this->Repository->specialAgencyLicenseInbox($this->_hordingWorkflowIds)                      // Repository function to get Advertiesment Details
    //             ->where('is_escalate', 1)
    //             ->where('adv_active_agency_licenses.ulb_id', $ulbId)
    //             // ->whereIn('ward_mstr_id', $wardId)
    //             ->get();
    //         return responseMsgs(true, "Data Fetched", remove_null($advData), "010107", "1.0", "251ms", "POST", "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "");
    //     }
    // }

    /**
     * | License Forward or Backward Application
     */
    // public function forwardLicenseNextLevel(Request $request)
    // {
    //     $request->validate([
    //         'applicationId' => 'required|integer',
    //         'senderRoleId' => 'required|integer',
    //         'receiverRoleId' => 'required|integer',
    //         'comment' => 'required',
    //     ]);

    //     try {
    //         // Hording License Application Update Current Role Updation
    //         DB::beginTransaction();
    //         $adv = AdvActiveAgencyLicense::find($request->applicationId);
    //         $adv->last_role_id = $request->current_role_id;
    //         $adv->current_role_id = $request->receiverRoleId;
    //         $adv->save();

    //         $metaReqs['moduleId'] = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
    //         $metaReqs['workflowId'] = $adv->workflow_id;
    //         $metaReqs['refTableDotId'] = "adv_active_agency_licenses.id";
    //         $metaReqs['refTableIdValue'] = $request->applicationId;
    //         $request->request->add($metaReqs);

    //         $track = new WorkflowTrack();
    //         $track->saveTrack($request);
    //         DB::commit();
    //         return responseMsgs(true, "Successfully Forwarded The Application!!", "", "010109", "1.0", "286ms", "POST", $request->deviceId);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return responseMsgs(false, $e->getMessage(), $request->all());
    //     }
    // }

    // License Post Independent Comment
    // public function commentLicenseApplication(Request $request)
    // {
    //     $request->validate([
    //         'comment' => 'required',
    //         'applicationId' => 'required|integer',
    //         'senderRoleId' => 'nullable|integer'
    //     ]);

    //     try {
    //         $userId = authUser()->id;
    //         $userType = authUser()->user_type;
    //         $workflowTrack = new WorkflowTrack();
    //         $mWfRoleUsermap = new WfRoleusermap();
    //         $mAdvActiveAgencyLicense = AdvActiveAgencyLicense::find($request->applicationId);                // Agency License Details
    //         $mModuleId = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
    //         $metaReqs = array();
    //         DB::beginTransaction();
    //         // Save On Workflow Track For Level Independent
    //         $metaReqs = [
    //             'workflowId' => $mAdvActiveAgencyLicense->workflow_id,
    //             'moduleId' => $mModuleId,
    //             'refTableDotId' => "adv_active_agency_licenses.id",
    //             'refTableIdValue' => $mAdvActiveAgencyLicense->id,
    //             'message' => $request->comment
    //         ];
    //         // For Citizen Independent Comment
    //         if ($userType != 'Citizen') {
    //             $roleReqs = new Request([
    //                 'workflowId' => $mAdvActiveAgencyLicense->workflow_id,
    //                 'userId' => $userId,
    //             ]);
    //             $wfRoleId = $mWfRoleUsermap->getRoleByUserWfId($roleReqs);
    //             $metaReqs = array_merge($metaReqs, ['senderRoleId' => $wfRoleId->wf_role_id]);
    //             $metaReqs = array_merge($metaReqs, ['user_id' => $userId]);
    //         }
    //         $request->request->add($metaReqs);
    //         $workflowTrack->saveTrack($request);

    //         DB::commit();
    //         return responseMsgs(true, "You Have Commented Successfully!!", ['Comment' => $request->comment], "010108", "1.0", "", "POST", "");
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return responseMsgs(false, $e->getMessage(), "");
    //     }
    // }

    // public function viewLicenseDocuments(Request $req)
    // {
    //     $mWfActiveDocument = new WfActiveDocument();
    //     $data = array();
    //     if ($req->applicationId && $req->type) {
    //         // if ($req->type == 'Active') {
    //         //     $appId = $req->applicationId;
    //         // } elseif ($req->type == 'Reject') {
    //         //     // $appId = AdvRejectedAgencyLicense::find($req->applicationId)->temp_id;
    //         //     $appId = $req->applicationId;
    //         // } elseif ($req->type == 'Approve') {
    //         //     // $appId = AdvActiveAgencyLicense::find($req->applicationId)->temp_id;
    //         //     $appId = $req->applicationId;
    //         // }
    //         $data = $mWfActiveDocument->uploadDocumentsViewById($req->applicationId,  $this->_hordingWorkflowIds);
    //     } else {
    //         throw new Exception("Required Application Id And Application Type ");
    //     }
    //     $data1['data'] = $data;
    //     return $data1;
    // }

    /**
     * | Get Uploaded Active Document by application ID
     */
    // public function viewActiveLicenseDocument(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'applicationId' => 'required|digits_between:1,9223372036854775807'
    //     ]);
    //     if ($validator->fails()) {
    //         return ['status' => false, 'message' => $validator->errors()];
    //     }
    //     $mWfActiveDocument = new WfActiveDocument();
    //     $data = array();
    //     $data = $mWfActiveDocument->uploadedActiveDocumentsViewById($req->applicationId, $this->_hordingWorkflowIds);
    //     $data1['data'] = $data;
    //     return $data1;
    // }

    /**
     * | Workflow View Uploaded Document by application ID
     */
    // public function viewLicenseDocumentsOnWorkflow(Request $req)
    // {
    //     $startTime = microtime(true);
    //     $mWfActiveDocument = new WfActiveDocument();
    //     $data = array();
    //     if ($req->applicationId) {
    //         $data = $mWfActiveDocument->uploadDocumentsViewById($req->applicationId, $this->_hordingWorkflowIds);
    //     }
    //     $endTime = microtime(true);
    //     $executionTime = $endTime - $startTime;

    //     return responseMsgs(true, "Data Fetched", remove_null($data), "050115", "1.0", "$executionTime Sec", "POST", "");
    // }

    /**
     * | Final Approval and Rejection of the Application
     * | Rating-
     * | Status- Open
     */
    // public function approvalOrRejectionLicense(Request $req)
    // {
    //     try {
    //         $req->validate([
    //             'roleId' => 'required',
    //             'applicationId' => 'required|integer',
    //             'status' => 'required|integer',
    //             // 'payment_amount' => 'required',

    //         ]);

    //         // Check if the Current User is Finisher or Not         
    //         $mAdvActiveAgencyLicense = AdvActiveAgencyLicense::find($req->applicationId);
    //         $getFinisherQuery = $this->getFinisherId($mAdvActiveAgencyLicense->workflow_id);                                 // Get Finisher using Trait
    //         $refGetFinisher = collect(DB::select($getFinisherQuery))->first();
    //         if ($refGetFinisher->role_id != $req->roleId) {
    //             return responseMsgs(false, " Access Forbidden", "");
    //         }

    //         DB::beginTransaction();
    //         // Approval
    //         if ($req->status == 1) {
    //             $amount = $this->getHordingPrice($mAdvActiveAgencyLicense->typology, $mAdvActiveAgencyLicense->zone_id);
    //             $payment_amount = ['payment_amount' => $amount];
    //             $req->request->add($payment_amount);

    //             if ($mAdvActiveAgencyLicense->renew_no == NULL) {
    //                 // approved Hording Application replication
    //                 $approvedAgencyLicense = $mAdvActiveAgencyLicense->replicate();
    //                 $approvedAgencyLicense->setTable('adv_agency_licenses');
    //                 $temp_id = $approvedAgencyLicense->id = $mAdvActiveAgencyLicense->id;
    //                 $approvedAgencyLicense->payment_amount = $req->payment_amount;
    //                 $approvedAgencyLicense->approve_date = Carbon::now();
    //                 $approvedAgencyLicense->save();

    //                 // Save in Hording Renewal
    //                 $approvedAgencyLicense = $mAdvActiveAgencyLicense->replicate();
    //                 $approvedAgencyLicense->approve_date = Carbon::now();
    //                 $approvedAgencyLicense->setTable('adv_agency_license_renewals');
    //                 $approvedAgencyLicense->id = $temp_id;
    //                 $approvedAgencyLicense->save();

    //                 $mAdvActiveAgencyLicense->delete();

    //                 // Update in adv_agency_licenses (last_renewal_id)

    //                 DB::table('adv_agency_licenses')
    //                     ->where('id', $temp_id)
    //                     ->update(['last_renewal_id' => $approvedAgencyLicense->id]);

    //                 $msg = "Application Successfully Approved !!";
    //             } else {
    //                  //  Renewal Application Case

    //                  // Hording Application replication
    //                  $license_no=$mAdvActiveAgencyLicense->license_no;
    //                  AdvAgencyLicense::where('license_no', $license_no)->delete();
 
    //                   $approvedAgencyLicense = $mAdvActiveAgencyLicense->replicate();
    //                   $approvedAgencyLicense->setTable('adv_agency_licenses');
    //                   $temp_id = $approvedAgencyLicense->id = $mAdvActiveAgencyLicense->id;
    //                   $approvedAgencyLicense->payment_amount = $req->payment_amount;
    //                   $approvedAgencyLicense->payment_status = $req->payment_status;
    //                   $approvedAgencyLicense->approve_date = Carbon::now();
    //                   $approvedAgencyLicense->save();
  
    //                   // Save in Hording Advertisement Renewal
    //                   $approvedAgencyLicense = $mAdvActiveAgencyLicense->replicate();
    //                   $approvedAgencyLicense->approve_date = Carbon::now();
    //                   $approvedAgencyLicense->setTable('adv_agency_license_renewals');
    //                   $approvedAgencyLicense->id = $temp_id;
    //                   $approvedAgencyLicense->save();
  
    //                   $mAdvActiveAgencyLicense->delete();
  
    //                   // Update in adv_agency_licenses (last_renewal_id)
    //                   DB::table('adv_agency_licenses')
    //                       ->where('id', $temp_id)
    //                       ->update(['last_renewal_id' => $approvedAgencyLicense->id]);
    //                   $msg = "Application Successfully Renewal !!";
    //             }
    //         }
    //         // Rejection
    //         if ($req->status == 0) {

    //             $payment_amount = ['payment_amount' => 0];
    //             $req->request->add($payment_amount);

    //             // Agency advertisement Application replication
    //             $rejectedAgencyLicense = $mAdvActiveAgencyLicense->replicate();
    //             $rejectedAgencyLicense->setTable('adv_rejected_agency_licenses');
    //             $rejectedAgencyLicense->id = $mAdvActiveAgencyLicense->id;
    //             $rejectedAgencyLicense->rejected_date = Carbon::now();
    //             $rejectedAgencyLicense->save();
    //             $mAdvActiveAgencyLicense->delete();
    //             $msg = "Application Successfully Rejected !!";
    //         }
    //         DB::commit();
    //         return responseMsgs(true, $msg, "", '011111', 01, '391ms', 'Post', $req->deviceId);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return responseMsgs(false, $e->getMessage(), "");
    //     }
    // }

    /**
     * | Get Hording price
     */
    // public function getHordingPrice($typology_id, $zone = 'A')
    // {
    //     return DB::table('adv_typology_mstrs')
    //         ->select(DB::raw("case when $zone = 1 then rate_zone_a
    //                           when $zone = 2 then rate_zone_b
    //                           when $zone = 3 then rate_zone_c
    //                     else 0 end as rate"))
    //         ->where('id', $typology_id)
    //         ->first()->rate;
    // }

    /**
     * | Approve License Application List for Citzen
     * | @param Request $req
     */
    // public function listApprovedLicense(Request $req)
    // {
    //     try {
    //         $citizenId = authUser()->id;
    //         $userId = authUser()->user_type;
    //         $mAdvAgencyLicense = new AdvAgencyLicense();
    //         $applications = $mAdvAgencyLicense->listApprovedLicense($citizenId, $userId);
    //         $totalApplication = $applications->count();
    //         remove_null($applications);
    //         $data1['data'] = $applications;
    //         $data1['arrayCount'] =  $totalApplication;
    //         if ($data1['arrayCount'] == 0) {
    //             $data1 = null;
    //         }
    //         return responseMsgs(true, "Approved Application List", $data1, "040103", "1.0", "", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
    //     }
    // }

    /**
     * | Unpaid License Application List for Citzen
     * | @param Request $req
     */
    // public function listUnpaidLicenses(Request $req)
    // {
    //     try {
    //         $citizenId = authUser()->id;
    //         $userId = authUser()->user_type;
    //         $mAdvAgencyLicense = new AdvAgencyLicense();
    //         $applications = $mAdvAgencyLicense->listUnpaidLicenses($citizenId, $userId);
    //         $totalApplication = $applications->count();
    //         remove_null($applications);
    //         $data1['data'] = $applications;
    //         $data1['arrayCount'] =  $totalApplication;
    //         if ($data1['arrayCount'] == 0) {
    //             $data1 = null;
    //         }
    //         return responseMsgs(true, "Unpaid Application List", $data1, "040103", "1.0", "", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
    //     }
    // }


    /**
     * | Reject License Application List for Citizen
     * | @param Request $req
     */
    // public function listRejectedLicense(Request $req)
    // {
    //     try {
    //         $citizenId = authUser()->id;
    //         $mAdvRejectedAgency = new AdvRejectedAgencyLicense();
    //         $applications = $mAdvRejectedAgency->listRejectedLicense($citizenId);
    //         $totalApplication = $applications->count();
    //         remove_null($applications);
    //         $data1['data'] = $applications;
    //         $data1['arrayCount'] =  $totalApplication;
    //         if ($data1['arrayCount'] == 0) {
    //             $data1 = null;
    //         }
    //         return responseMsgs(true, "Rejected Application List", $data1, "040103", "1.0", "", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
    //     }
    // }





    /**
     * | Get Applied License Applications by Logged In JSK
     */
    // public function getJskLicenseApplications(Request $req)
    // {
    //     try {
    //         $userId = authUser()->id;
    //         $mAdvActiveAgencyLicense = new AdvActiveAgencyLicense();
    //         $applications = $mAdvActiveAgencyLicense->getJskLicenseApplications($userId);
    //         $totalApplication = $applications->count();
    //         remove_null($applications);
    //         $data1['data'] = $applications;
    //         $data1['arrayCount'] =  $totalApplication;
    //         if ($data1['arrayCount'] == 0) {
    //             $data1 = null;
    //         }
    //         return responseMsgs(true, "Applied Applications", $data1, "040106", "1.0", "", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040106", "1.0", "", "POST", $req->deviceId ?? "");
    //     }
    // }


    /**
     * | Approve License Application List for JSK
     * | @param Request $req
     */
    // public function listJskApprovedLicenseApplication(Request $req)
    // {
    //     try {
    //         $userId = authUser()->id;
    //         $mAdvAgencyLicense = new AdvAgencyLicense();
    //         $applications = $mAdvAgencyLicense->listJskApprovedLicenseApplication($userId);
    //         $totalApplication = $applications->count();
    //         remove_null($applications);
    //         $data1['data'] = $applications;
    //         $data1['arrayCount'] =  $totalApplication;
    //         if ($data1['arrayCount'] == 0) {
    //             $data1 = null;
    //         }

    //         return responseMsgs(true, "Approved Application List", $data1, "040103", "1.0", "", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
    //     }
    // }

    /**
     * | Reject License Application List for JSK
     * | @param Request $req
     */
    // public function listJskRejectedLicenseApplication(Request $req)
    // {
    //     try {
    //         $userId = authUser()->id;
    //         $mAdvRejectedAgencyLicense = new AdvRejectedAgencyLicense();
    //         $applications = $mAdvRejectedAgencyLicense->listJskRejectedLicenseApplication($userId);
    //         $totalApplication = $applications->count();
    //         remove_null($applications);
    //         $data1['data'] = $applications;
    //         $data1['arrayCount'] =  $totalApplication;
    //         if ($data1['arrayCount'] == 0) {
    //             $data1 = null;
    //         }

    //         return responseMsgs(true, "Rejected Application List", $data1, "040103", "1.0", "", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
    //     }
    // }

    /**
     * | Generate Payment Order ID
     * | @param Request $req
     */
    // public function generateLicensePaymentOrderId(Request $req)
    // {
    //     $req->validate([
    //         'id' => 'required|integer',
    //     ]);
    //     try {
    //         $startTime = microtime(true);
    //         $mAdvAgencyLicense = AdvAgencyLicense::find($req->id);
    //         $reqData = [
    //             "id" => $mAdvAgencyLicense->id,
    //             'amount' => $mAdvAgencyLicense->payment_amount,
    //             'workflowId' => $mAdvAgencyLicense->workflow_id,
    //             'ulbId' => $mAdvAgencyLicense->ulb_id,
    //             'departmentId' => Config::get('workflow-constants.ADVERTISMENT_MODULE_ID'),
    //         ];
    //         $paymentUrl = Config::get('constants.PAYMENT_URL');
    //         $refResponse = Http::withHeaders([
    //             "api-key" => "eff41ef6-d430-4887-aa55-9fcf46c72c99"
    //         ])
    //             ->withToken($req->bearerToken())
    //             ->post($paymentUrl . 'api/payment/generate-orderid', $reqData);

    //         $data = json_decode($refResponse);

    //         if (!$data)
    //             throw new Exception("Payment Order Id Not Generate");

    //         $data->name = $mAdvAgencyLicense->applicant;
    //         $data->email = $mAdvAgencyLicense->email;
    //         $data->contact = $mAdvAgencyLicense->mobile_no;
    //         $data->type = "Hording";
    //         // return $data;
    //         $endTime = microtime(true);
    //         $executionTime = $endTime - $startTime;

    //         return responseMsgs(true, "Payment OrderId Generated Successfully !!!", $data, "050123", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "050123", "1.0", "", 'POST', $req->deviceId ?? "");
    //     }
    // }

    /**
     * License (Hording) application Details For Payment
     * @param Request $req
     * @return void
     */
    // public function getLicenseApplicationDetailsForPayment(Request $req)
    // {
    //     $req->validate([
    //         'applicationId' => 'required|integer',
    //     ]);
    //     try {
    //         $startTime = microtime(true);
    //         $mAdvAgencyLicense = new AdvAgencyLicense();
    //         if ($req->applicationId) {
    //             $data = $mAdvAgencyLicense->getLicenseApplicationDetailsForPayment($req->applicationId);
    //         }

    //         if (!$data)
    //             throw new Exception("Application Not Found");

    //         $data['type'] = "Hording";
    //         $endTime = microtime(true);
    //         $executionTime = $endTime - $startTime;
    //         return responseMsgs(true, 'Data Fetched',  $data, "050124", "1.0", "$executionTime Sec", "POST", $req->deviceId);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "");
    //     }
    // }

    /**
     * Check isAgency or Not
     * @return void
     */
    public function isAgency(Request $req)
    {
        try {
            $userType = authUser()->user_type;
            if ($userType == "Citizen") {
                $startTime = microtime(true);
                $citizenId = authUser()->id;
                $mAdvAgency = new AdvAgency();
                $isAgency = $mAdvAgency->checkAgency($citizenId);
                $endTime = microtime(true);
                $executionTime = $endTime - $startTime;
                if (empty($isAgency)) {
                    throw new Exception("You Have Not Agency !!");
                } else {
                    return responseMsgs(true, "Data Fetched !!!", $isAgency, "050123", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
                }
            } else {
                throw new Exception("You Are Not Citizen");
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050123", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

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

    // public function paymentByCash(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'applicationId' => 'required|string',
    //         'status' => 'required|integer'
    //     ]);
    //     if ($validator->fails()) {
    //         return ['status' => false, 'message' => $validator->errors()];
    //     }
    //     try {
    //         $mAdvAgencyLicense = new AdvAgencyLicense();
    //         DB::beginTransaction();
    //         $status = $mAdvAgencyLicense->paymentByCash($req);
    //         DB::commit();
    //         if ($req->status == '1' && $status == 1) {
    //             return responseMsgs(true, "Payment Successfully !!", '', "040501", "1.0", "", 'POST', $req->deviceId ?? "");
    //         } else {
    //             return responseMsgs(false, "Payment Rejected !!", '', "040501", "1.0", "", 'POST', $req->deviceId ?? "");
    //         }
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return responseMsgs(false, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
    //     }
    // }

    // public function entryChequeDdLicense(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'applicationId' => 'required|string',               //  temp_id of Application
    //         'bankName' => 'required|string',
    //         'branchName' => 'required|string',
    //         'chequeNo' => 'required|integer',
    //     ]);
    //     if ($validator->fails()) {
    //         return ['status' => false, 'message' => $validator->errors()];
    //     }
    //     try {
    //         $mAdvCheckDtl = new AdvChequeDtl();
    //         $workflowId = ['workflowId' => $this->_hordingWorkflowIds];
    //         $req->request->add($workflowId);
    //         $transNo = $mAdvCheckDtl->entryChequeDd($req);
    //         return responseMsgs(true, "Check Entry Successfully !!", ['status' => true, 'TransactionNo' => $transNo], "040501", "1.0", "", 'POST', $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
    //     }
    // }

    // public function clearOrBounceChequeLicense(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'paymentId' => 'required|string',
    //         'status' => 'required|string',
    //         'remarks' => $req->status == 1 ? 'nullable|string' : 'required|string',
    //         'bounceAmount' => $req->status == 1 ? 'nullable|numeric' : 'required|numeric',
    //     ]);
    //     if ($validator->fails()) {
    //         return ['status' => false, 'message' => $validator->errors()];
    //     }
    //     try {
    //         $mAdvCheckDtl = new AdvChequeDtl();
    //         DB::beginTransaction();
    //         $status = $mAdvCheckDtl->clearOrBounceCheque($req);
    //         DB::commit();
    //         if ($req->status == '1' && $status == 1) {
    //             return responseMsgs(true, "Payment Successfully !!", '', "040501", "1.0", "", 'POST', $req->deviceId ?? "");
    //         } else {
    //             return responseMsgs(false, "Payment Rejected !!", '', "040501", "1.0", "", 'POST', $req->deviceId ?? "");
    //         }
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return responseMsgs(false, $e->getMessage(), "", "040501", "1.0", "", "POST", $req->deviceId ?? "");
    //     }
    // }

    /**
     * | Verify Single Application Approve or reject
     * |
     */
    // public function verifyOrRejectLicenseDoc(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'id' => 'required|digits_between:1,9223372036854775807',
    //         'applicationId' => 'required|digits_between:1,9223372036854775807',
    //         'docRemarks' =>  $req->docStatus == "Rejected" ? 'required|regex:/^[a-zA-Z1-9][a-zA-Z1-9\. \s]+$/' : "nullable",
    //         'docStatus' => 'required|in:Verified,Rejected'
    //     ]);
    //     if ($validator->fails()) {
    //         return ['status' => false, 'message' => $validator->errors()];
    //     }
    //     try {
    //         $mWfDocument = new WfActiveDocument();
    //         $mAdvActiveHoarding = new AdvActiveHoarding();
    //         $mWfRoleusermap = new WfRoleusermap();
    //         $wfDocId = $req->id;
    //         $userId = authUser()->id;
    //         $applicationId = $req->applicationId;

    //         $wfLevel = Config::get('constants.SELF-LABEL');
    //         // Derivative Assigments
    //         $appDetails = $mAdvActiveHoarding->getHoardingNo($applicationId);

    //         if (!$appDetails || collect($appDetails)->isEmpty())
    //             throw new Exception("Application Details Not Found");

    //         $appReq = new Request([
    //             'userId' => $userId,
    //             'workflowId' => $appDetails->workflow_id
    //         ]);
    //         $senderRoleDtls = $mWfRoleusermap->getRoleByUserWfId($appReq);
    //         if (!$senderRoleDtls || collect($senderRoleDtls)->isEmpty())
    //             throw new Exception("Role Not Available");

    //         $senderRoleId = $senderRoleDtls->wf_role_id;

    //         if ($senderRoleId != $wfLevel['DA'])                                // Authorization for Dealing Assistant Only
    //             throw new Exception("You are not Authorized");


    //         $ifFullDocVerified = $this->ifFullLicenseDocVerified($applicationId);       // (Current Object Derivative Function 4.1)

    //         if ($ifFullDocVerified == 1)
    //             throw new Exception("Document Fully Verified");

    //         DB::beginTransaction();
    //         if ($req->docStatus == "Verified") {
    //             $status = 1;
    //         }
    //         if ($req->docStatus == "Rejected") {
    //             $status = 2;
    //             // For Rejection Doc Upload Status and Verify Status will disabled
    //             $appDetails->doc_upload_status = 0;
    //             $appDetails->doc_verify_status = 0;
    //             $appDetails->save();
    //         }



    //         $reqs = [
    //             'remarks' => $req->docRemarks,
    //             'verify_status' => $status,
    //             'action_taken_by' => $userId
    //         ];
    //         $mWfDocument->docVerifyReject($wfDocId, $reqs);
    //         $ifFullDocVerifiedV1 = $this->ifFullLicenseDocVerified($applicationId);

    //         if ($ifFullDocVerifiedV1 == 1) {                                     // If The Document Fully Verified Update Verify Status
    //             $appDetails->doc_verify_status = 1;
    //             $appDetails->save();
    //         }

    //         DB::commit();
    //         return responseMsgs(true, $req->docStatus . " Successfully", "", "010204", "1.0", "", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return responseMsgs(false, $e->getMessage(), "", "010204", "1.0", "", "POST", $req->deviceId ?? "");
    //     }
    // }

    /**
     * | Check if the Document is Fully Verified or Not (4.1)
     */
    // public function ifFullLicenseDocVerified($applicationId)
    // {
    //     $mAdvActiveAgencyLicense = new AdvActiveAgencyLicense();
    //     $mWfActiveDocument = new WfActiveDocument();
    //     $mAdvActiveAgencyLicense = $mAdvActiveAgencyLicense->getHoardingNo($applicationId);                      // Get Application Details
    //     $refReq = [
    //         'activeId' => $applicationId,
    //         'workflowId' => $mAdvActiveAgencyLicense->workflow_id,
    //         'moduleId' =>  $this->_moduleId
    //     ];
    //     $req = new Request($refReq);
    //     $refDocList = $mWfActiveDocument->getDocsByActiveId($req);
    //     // Vehicle Advertiesement List Documents
    //     $ifAdvDocUnverified = $refDocList->contains('verify_status', 0);
    //     if ($ifAdvDocUnverified == 1)
    //         return 0;
    //     else
    //         return 1;
    // }




    /**
     *  | Send Application back to citizen
     */
    // public function backToCitizenLicense(Request $req)
    // {
    //     $req->validate([
    //         'applicationId' => "required"
    //     ]);
    //     try {
    //         $redis = Redis::connection();
    //         $mAdvActiveAgencyLicense = AdvActiveAgencyLicense::find($req->applicationId);

    //         $workflowId = $mAdvActiveAgencyLicense->workflow_id;
    //         $backId = json_decode(Redis::get('workflow_initiator_' . $workflowId));
    //         if (!$backId) {
    //             $backId = WfWorkflowrolemap::where('workflow_id', $workflowId)
    //                 ->where('is_initiator', true)
    //                 ->first();
    //             $redis->set('workflow_initiator_' . $workflowId, json_encode($backId));
    //         }

    //         $mAdvActiveAgencyLicense->current_role_id = $backId->wf_role_id;
    //         $mAdvActiveAgencyLicense->parked = 1;
    //         $mAdvActiveAgencyLicense->save();


    //         $metaReqs['moduleId'] = $this->_moduleId;
    //         $metaReqs['workflowId'] = $mAdvActiveAgencyLicense->workflow_id;
    //         $metaReqs['refTableDotId'] = "adv_active_agency_licenses.id";
    //         $metaReqs['refTableIdValue'] = $req->applicationId;
    //         $metaReqs['verificationStatus'] = $req->verificationStatus;
    //         $metaReqs['senderRoleId'] = $req->currentRoleId;
    //         $req->request->add($metaReqs);

    //         $req->request->add($metaReqs);
    //         $track = new WorkflowTrack();
    //         $track->saveTrack($req);

    //         return responseMsgs(true, "Successfully Done", "", "", '010710', '01', '358ms', 'Post', '');
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "");
    //     }
    // }

    /**
     * | Back To Citizen Inbox
     */
    // public function listLicenseBtcInbox()
    // {
    //     try {
    //         $auth = auth()->user();
    //         $userId = $auth->id;
    //         $ulbId = $auth->ulb_id;
    //         $wardId = $this->getWardByUserId($userId);

    //         $occupiedWards = collect($wardId)->map(function ($ward) {                               // Get Occupied Ward of the User
    //             return $ward->ward_id;
    //         });

    //         $roles = $this->getRoleIdByUserId($userId);

    //         $roleId = collect($roles)->map(function ($role) {                                       // get Roles of the user
    //             return $role->wf_role_id;
    //         });

    //         $mAdvActiveAgencyLicense = new AdvActiveAgencyLicense();
    //         $btcList = $mAdvActiveAgencyLicense->getAgencyLicenseList($ulbId)
    //             ->whereIn('adv_active_agency_licenses.current_role_id', $roleId)
    //             // ->whereIn('a.ward_mstr_id', $occupiedWards)
    //             ->where('parked', true)
    //             ->orderByDesc('adv_active_agency_licenses.id')
    //             ->get();

    //         return responseMsgs(true, "BTC Inbox List", remove_null($btcList), 010717, 1.0, "271ms", "POST", "", "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", 010717, 1.0, "271ms", "POST", "", "");
    //     }
    // }

    // public function checkFullLicenseUpload($applicationId)
    // {
    //     $docCode = $this->_hordingDocCode;
    //     $mWfActiveDocument = new WfActiveDocument();
    //     $moduleId = $this->_moduleId;
    //     $totalRequireDocs = $mWfActiveDocument->totalNoOfDocs($docCode);
    //     $appDetails = AdvActiveAgencyLicense::find($applicationId);
    //     $totalUploadedDocs = $mWfActiveDocument->totalUploadedDocs($applicationId, $appDetails->workflow_id, $moduleId);
    //     if ($totalRequireDocs == $totalUploadedDocs) {
    //         $appDetails->doc_upload_status = '1';
    //         // $appDetails->doc_verify_status = '1';
    //         $appDetails->parked = NULL;
    //         $appDetails->save();
    //     } else {
    //         $appDetails->doc_upload_status = '0';
    //         $appDetails->doc_verify_status = '0';
    //         $appDetails->save();
    //     }
    // }

    // public function reuploadLicenseDocument(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'id' => 'required|digits_between:1,9223372036854775807',
    //         'image' => 'required|mimes:png,jpeg,pdf,jpg'
    //     ]);
    //     if ($validator->fails()) {
    //         return ['status' => false, 'message' => $validator->errors()];
    //     }
    //     try {
    //         $mAdvActiveAgencyLicense = new AdvActiveAgencyLicense();
    //         DB::beginTransaction();
    //         $appId = $mAdvActiveAgencyLicense->reuploadDocument($req);
    //         $this->checkFullLicenseUpload($appId);
    //         DB::commit();
    //         return responseMsgs(true, "Document Uploaded Successfully", "", 010717, 1.0, "271ms", "POST", "", "");
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return responseMsgs(false, "Document Not Uploaded", "", 010717, 1.0, "271ms", "POST", "", "");
    //     }
    // }

    /**
     * | Approve License Application List for Citzen
     * | @param Request $req
     */
    // public function getRenewActiveApplications(Request $req)
    // {
    //     try {
    //         $citizenId = authUser()->id;
    //         $userId = authUser()->user_type;
    //         $mAdvAgencyLicense = new AdvAgencyLicense();
    //         $applications = $mAdvAgencyLicense->getRenewActiveApplications($citizenId, $userId);
    //         $totalApplication = count($applications);
    //         $data1['data'] = $applications;
    //         $data1['arrayCount'] =  $totalApplication;
    //         if ($data1['arrayCount'] == 0) {
    //             $data1 = null;
    //         }
    //         return responseMsgs(true, "Approved Application List", $data1, "040103", "1.0", "", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
    //     }
    // }

    // public function listExpiredHording(Request $req){
    //     try {
    //         $citizenId = authUser()->id;
    //         $userId = authUser()->user_type;
    //         $mAdvAgencyLicense = new AdvAgencyLicense();
    //         $applications = $mAdvAgencyLicense->listExpiredHording($citizenId, $userId);
    //         $totalApplication = count($applications);
    //         $data1['data'] = $applications;
    //         $data1['arrayCount'] =  $totalApplication;
    //         if ($data1['arrayCount'] == 0) {
    //             $data1 = null;
    //         }
    //         return responseMsgs(true, "Approved Application List", $data1, "040103", "1.0", "", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
    //     }
    // }

    /**
     * | Archived Application By Id 
     */
    // public function archivedHording(Request $req){
    //     $validator = Validator::make($req->all(), [
    //         'applicationId' => 'required|digits_between:1,9223372036854775807'
    //     ]);
    //     if ($validator->fails()) {
    //         return ['status' => false, 'message' => $validator->errors()];
    //     }
    //      try {
    //         $mAdvAgencyLicense = AdvAgencyLicense::find($req->applicationId);
    //         $mAdvAgencyLicense->is_archived=1;
    //         $mAdvAgencyLicense->save();
    //         return responseMsgs(true, "Archived Application Successfully", "", "040103", "1.0", "", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
    //     }
    // }

       /**
     * | Hording Archived List for Citizen
     * | @param Request $req
     */
    // public function listHordingArchived(Request $req)
    // {
    //     try {
    //         $citizenId = authUser()->id;
    //         $userId = authUser()->user_type;
    //         $mAdvAgencyLicense = new AdvAgencyLicense();
    //         $applications = $mAdvAgencyLicense->listHordingArchived($citizenId, $userId);
    //         $totalApplication = $applications->count();
    //         remove_null($applications);
    //         $data1['data'] = $applications;
    //         $data1['arrayCount'] =  $totalApplication;
    //         if ($data1['arrayCount'] == 0) {
    //             $data1 = null;
    //         }
    //         return responseMsgs(true, "Archived Application List", $data1, "040103", "1.0", "", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
    //     }
    // }

    
    /**
     * | Blacklist Application By Id 
     */
    // public function blacklistHording(Request $req){
    //     $validator = Validator::make($req->all(), [
    //         'applicationId' => 'required|digits_between:1,9223372036854775807'
    //     ]);
    //     if ($validator->fails()) {
    //         return ['status' => false, 'message' => $validator->errors()];
    //     }
    //      try {
    //         $mAdvAgencyLicense = AdvAgencyLicense::find($req->applicationId);
    //         $mAdvAgencyLicense->is_blacklist=1;
    //         $mAdvAgencyLicense->save();
    //         return responseMsgs(true, "Blacklist Application Successfully", "", "040103", "1.0", "", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
    //     }
    // }

       /**
     * | Hording Archived List for Citizen
     * | @param Request $req
     */
    // public function listHordingBlacklist(Request $req)
    // {
    //     try {
    //         $citizenId = authUser()->id;
    //         $userId = authUser()->user_type;
    //         $mAdvAgencyLicense = new AdvAgencyLicense();
    //         $applications = $mAdvAgencyLicense->listHordingArchived($citizenId, $userId);
    //         $totalApplication = $applications->count();
    //         remove_null($applications);
    //         $data1['data'] = $applications;
    //         $data1['arrayCount'] =  $totalApplication;
    //         if ($data1['arrayCount'] == 0) {
    //             $data1 = null;
    //         }
    //         return responseMsgs(true, "Blacklist Application List", $data1, "040103", "1.0", "", "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "040103", "1.0", "", 'POST', $req->deviceId ?? "");
    //     }
    // }
}