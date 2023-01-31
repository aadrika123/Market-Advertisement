<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agency\StoreRequest;
use App\Http\Requests\Agency\StoreLicenceRequest;
use App\Models\Advertisements\AdvActiveAgency;
use App\Models\Advertisements\AdvAgencyLicense;
use App\Models\Advertisements\AdvTypologyMstr;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

use App\Traits\AdvDetailsTraits;
use App\Models\Workflows\WfWardUser;
use App\Repositories\SelfAdvets\iSelfAdvetRepo;
use App\Models\Workflows\WorkflowTrack;
use App\Traits\WorkflowTrait;

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
    public function __construct(iSelfAdvetRepo $agency_repo)
    {
        $this->_modelObj = new AdvActiveAgency();
        $this->_workflowIds = Config::get('workflow-constants.AGENCY_WORKFLOWS');
        $this->Repository = $agency_repo;
    }
    /**
     * | Store 
     * | @param StoreRequest Request
     */
    public function store(StoreRequest $req)
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
            DB::beginTransaction();
            $applicationNo = $agency->store($req);       //<--------------- Model function to store 
            DB::commit();
            return responseMsgs(
                true,
                "Successfully Submitted the application !!",
                [
                    'status' => true,
                    'ApplicationNo' => $applicationNo
                ],
                "040501",
                "1.0",
                "",
                'POST',
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(
                true,
                $e->getMessage(),
                "",
                "040501",
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
            $mAdvActiveAgency = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $inboxList = $mAdvActiveAgency->inbox($roleIds);
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
            $mAdvActiveAgency = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $outboxList = $mAdvActiveAgency->outbox($roleIds);
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
            $mAdvActiveAgency = new AdvActiveAgency();
            // $data = array();
            $fullDetailsData = array();
            if ($req->applicationId) {
                $data = $mAdvActiveAgency->details($req->applicationId);
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


            $metaReqs['customFor'] = 'Agency Advertisement';
            $metaReqs['wfRoleId'] = $data['current_role_id'];
            $metaReqs['workflowId'] = $data['workflow_id'];
            // return $metaReqs;
            $req->request->add($metaReqs);

            $forwardBackward = $this->getRoleDetails($req);
            // return $forwardBackward;
            $fullDetailsData['roleDetails'] = collect($forwardBackward)['original']['data'];
            // return $fullDetailsData['roleDetails'];

            $fullDetailsData = remove_null($fullDetailsData);

            $fullDetailsData['application_no'] = $data['application_no'];
            $fullDetailsData['apply_date'] = $data['application_date'];
            $fullDetailsData['directors'] = $data['directors'];

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
            $mAdvActiveAgency = new AdvActiveAgency();
            $applications = $mAdvActiveAgency->getCitizenApplications($citizenId);
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
            $adv = AdvActiveAgency::find($request->applicationId);
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
    public function commentIndependent(Request $request)
    {
        $request->validate([
            'comment' => 'required',
            'applicationId' => 'required|integer',
            'senderRoleId' => 'nullable|integer'
        ]);

        try {
            $workflowTrack = new WorkflowTrack();
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
            if (!$request->senderRoleId) {
                $metaReqs = array_merge($metaReqs, ['citizenId' => $mAdvActiveAgency->user_id]);
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

    public function uploadDocumentsView(Request $req)
    {
        $mAdvActiveAgency = new AdvActiveAgency();
        $data = array();
        $fullDetailsData = array();
        if ($req->applicationId) {
            $data = $mAdvActiveAgency->viewUploadedDocuments($req->applicationId,$this->_workflowIds);
        }
        // return $data;

        // $fullDetailsData['application_no'] = $data['application_no'];
        // $fullDetailsData['apply_date'] = $data['application_date'];
        $fullDetailsData['documents'] = $data['documents'];


        $data1['data'] = $fullDetailsData;
        return $data1;
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
    public function getTypologyList(Request $req)
    {
        try {
            $mAdvTypologyMstr = new AdvTypologyMstr();
            $typologyList = $mAdvTypologyMstr->getTypologyList();
            $typologyList = $typologyList->groupBy('type');
            // $data1 = array();
            // foreach($typologyList as $key =>$data){
            //     // $d = array();
            //     // $d[$key] = $data;
            //     // $data1['Type '.$key] = $d;

            //     $data1[$key]['Type'] = $key;
            //     $data1[$key]['data'] = $data;
            // }
            // return $typologyList;
            // $data2['typology'] = collect([$data1]);
            // $data3['dataArray'] = $data2;


            // $typeADetails = $this->generateType($data1);
            foreach ($typologyList as $key => $data) {
                $type = [
                    'Type' => "Type ".$key,
                    'data' => $typologyList[$key]
                ];
                $fData[] = $type;
            }
            $fullData['typology'] = $fData;

            return responseMsgs(
                true,
                "Typology Data Fetch Successfully!!",
                remove_null($fullData),
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
     * | Save Application For Licence
     */
    public function saveForLicence(StoreLicenceRequest $req)
    {
        try {
            $mAdvAgencyLicense = new AdvAgencyLicense();
            if (authUser()->user_type == 'JSK') {
                $userId = ['userId' => authUser()->id];
                $req->request->add($userId);
            } else {
                $citizenId = ['citizenId' => authUser()->id];
                $req->request->add($citizenId);
            }
            DB::beginTransaction();
            $LicenseNo = $mAdvAgencyLicense->licenceStore($req);       //<--------------- Model function to store 
            DB::commit();
            return responseMsgs(
                true,
                "Successfully Submitted the application !!",
                [
                    'status' => true,
                    'LicenceNo' => $LicenseNo
                ],
                "040501",
                "1.0",
                "",
                'POST',
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(
                true,
                $e->getMessage(),
                "",
                "040501",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        }
    }


    
    /**
     * | License Inbox List
     * | @param Request $req
     */
    public function licenseInbox(Request $req)
    {
        try {
            $mAdvAgencyLicense = new AdvAgencyLicense();
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $inboxList = $mAdvAgencyLicense->inbox($roleIds);
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
     * | License Outbox List
     */
    public function licenseOutbox(Request $req)
    {
        try {
            $mAdvAgencyLicense = new AdvAgencyLicense;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $outboxList = $mAdvAgencyLicense->outbox($roleIds);
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
     * | License Application Details
     */

    public function licenseDetails(Request $req)
    {
        try {
            $mAdvAgencyLicense = new AdvAgencyLicense();
            // $data = array();
            $fullDetailsData = array();
            if ($req->applicationId) {
                $data = $mAdvAgencyLicense->details($req->applicationId);
            }

            // return $data;

            // Basic Details
            $basicDetails = $this->generatehordingLicenseDetails($data); // Trait function to get Basic Details
            $basicElement = [
                'headerTitle' => "Basic License Details",
                "data" => $basicDetails
            ];

            $cardDetails = $this->generateLiceasneCardDetails($data);
            $cardElement = [
                'headerTitle' => "License Details",
                'data' => $cardDetails
            ];

            $fullDetailsData['fullDetailsData']['dataArray'] = new Collection([$basicElement]);
            $fullDetailsData['fullDetailsData']['cardArray'] = new Collection($cardElement);


            $metaReqs['customFor'] = 'Agency Hording License';
            $metaReqs['wfRoleId'] = $data['current_role_id'];
            $metaReqs['workflowId'] = $data['workflow_id'];
            // return $metaReqs;
            $req->request->add($metaReqs);

            $forwardBackward = $this->getRoleDetails($req);
            // return $forwardBackward;
            $fullDetailsData['roleDetails'] = collect($forwardBackward)['original']['data'];
            // return $fullDetailsData['roleDetails'];

            $fullDetailsData = remove_null($fullDetailsData);

            $fullDetailsData['application_no'] = $data['application_no'];
            $fullDetailsData['apply_date'] = $data['application_date'];

            return responseMsgs(true, 'Data Fetched', $fullDetailsData, "010104", "1.0", "303ms", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }


    
    /**
     * | Get Applied Applications by Logged In Citizen
     */
    public function licenseGetCitizenApplications(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $mAdvAgencyLicense = new AdvAgencyLicense();
            $applications = $mAdvAgencyLicense->getCitizenApplications($citizenId);
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
     * | License Escalate
     */
    public function licenseEscalate(Request $request)
    {
        $request->validate([
            "escalateStatus" => "required|int",
            "applicationId" => "required|int",
        ]);
        try {
            $userId = auth()->user()->id;
            $applicationId = $request->applicationId;
            $data = AdvAgencyLicense::find($applicationId);
            $data->is_escalate = $request->escalateStatus;
            $data->escalate_by = $userId;
            $data->save();
            return responseMsgs(true, $request->escalateStatus == 1 ? 'Agency Hording is Escalated' : "Agency Hording is removed from Escalated", '', "010106", "1.0", "353ms", "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $request->all());
        }
    }

    /**
     * | Special Inbox
     */
    public function licenseSpecialInbox(Request $req)
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
                ->where('adv_agency_licenses.ulb_id', $ulbId)
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
    public function licensePostNextLevel(Request $request)
    {
        $request->validate([
            'applicationId' => 'required|integer',
            'senderRoleId' => 'required|integer',
            'receiverRoleId' => 'required|integer',
            'comment' => 'required',
        ]);

        try {
            // Hording License Application Update Current Role Updation
            DB::beginTransaction();
            $adv = AdvAgencyLicense::find($request->applicationId);
            $adv->current_role_id = $request->receiverRoleId;
            $adv->save();

            $metaReqs['moduleId'] = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
            $metaReqs['workflowId'] = $adv->workflow_id;
            $metaReqs['refTableDotId'] = "adv_agency_licenses.id";
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
    public function licenseCommentIndependent(Request $request)
    {
        $request->validate([
            'comment' => 'required',
            'applicationId' => 'required|integer',
            'senderRoleId' => 'nullable|integer'
        ]);

        try {
            $workflowTrack = new WorkflowTrack();
            $mAdvAgencyLicense = AdvAgencyLicense::find($request->applicationId);                // Agency License Details
            $mModuleId = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
            $metaReqs = array();
            DB::beginTransaction();
            // Save On Workflow Track For Level Independent
            $metaReqs = [
                'workflowId' => $mAdvAgencyLicense->workflow_id,
                'moduleId' => $mModuleId,
                'refTableDotId' => "adv_agency_licenses.id",
                'refTableIdValue' => $mAdvAgencyLicense->id,
                'message' => $request->comment
            ];
            // For Citizen Independent Comment
            if (!$request->senderRoleId) {
                $metaReqs = array_merge($metaReqs, ['citizenId' => $mAdvAgencyLicense->user_id]);
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
     * | Hording Uploaded Document View
     */
    public function licenseUploadDocumentsView(Request $req)
    {
        $mAdvAgencyLicense = new AdvAgencyLicense();
        
        $data = array();
        $fullDetailsData = array();
        $workflowId = Config::get('workflow-constants.AGENCY_HORDING_WORKFLOWS');
        if ($req->applicationId) {
            $data = $mAdvAgencyLicense->viewUploadedDocuments($req->applicationId,$workflowId);
        }
        // return $data;

        // $fullDetailsData['application_no'] = $data['application_no'];
        // $fullDetailsData['apply_date'] = $data['application_date'];
        $fullDetailsData['documents'] = $data['documents'];


        $data1['data'] = $fullDetailsData;
        return $data1;
    }


}
