<?php

namespace App\Models\Advertisements;

use App\MicroServices\DocumentUpload;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use App\Traits\WorkflowTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class AdvActivePrivateland extends Model
{
    use HasFactory;

    use WorkflowTrait;
    protected $guarded = [];
    protected $_applicationDate;

    public function __construct()
    {
        $this->_applicationDate = Carbon::now()->format('Y-m-d');
    }

    /**
     * | Meta Data Uses to Store data in DB
     */
    public function metaReqs($req)
    {
        $metaReqs = [
            'applicant' => $req->applicant,
            'application_no' => $req->application_no,
            'father' => $req->father,
            'email' => $req->email,
            'residence_address' => $req->residenceAddress,
            'ward_id' => $req->wardId,
            'permanent_address' => $req->permanentAddress,
            'permanent_ward_id' => $req->permanentWardId,
            'mobile_no' => $req->mobileNo,
            'aadhar_no' => $req->aadharNo,
            'license_from' => $req->licenseFrom,
            'license_to' => $req->licenseTo,
            'holding_no' => $req->holdingNo,
            'trade_license_no' => $req->tradeLicenseNo,
            'gst_no' => $req->gstNo,
            'entity_name' => $req->entityName,
            'entity_address' => $req->entityAddress,
            'entity_ward_id' => $req->entityWardId,
            'brand_display_name' => $req->brandDisplayName,
            'brand_display_address' => $req->brandDisplayAddress,
            'holding_brand_display_address' => $req->brandDisplayHoldingNo,
            'display_area' => $req->displayArea,
            'display_type' => $req->displayType,
            'no_of_hoardings' => $req->noOfHoardings,
            'longitude' => $req->longitude,
            'latitude' => $req->latitude,
            'installation_location' => $req->installationLocation,
            'citizen_id' => $req->citizenId,
            'ulb_id' => $req->ulbId,
            'user_id' => $req->userId,
            'typology' => $req->typology
        ];
        return $metaReqs;
    }


    /**
     * | Meta Data Uses to Renewal Application
     */
    public function metaRenewReqs($req)
    {
        $metaReqs = [
            'applicant' => $req->applicant,
            'application_no' => $req->application_no,
            'father' => $req->father,
            'email' => $req->email,
            'residence_address' => $req->residenceAddress,
            'ward_id' => $req->wardId,
            'permanent_address' => $req->permanentAddress,
            'permanent_ward_id' => $req->permanentWardId,
            'mobile_no' => $req->mobileNo,
            'aadhar_no' => $req->aadharNo,
            'license_from' => $req->licenseFrom,
            'license_to' => $req->licenseTo,
            'holding_no' => $req->holdingNo,
            'trade_license_no' => $req->tradeLicenseNo,
            'gst_no' => $req->gstNo,
            'entity_name' => $req->entityName,
            'entity_address' => $req->entityAddress,
            'entity_ward_id' => $req->entityWardId,
            'brand_display_name' => $req->brandDisplayName,
            'brand_display_address' => $req->brandDisplayAddress,
            'display_area' => $req->displayArea,
            'display_type' => $req->displayType,
            'no_of_hoardings' => $req->noOfHoardings,
            'longitude' => $req->longitude,
            'latitude' => $req->latitude,
            'installation_location' => $req->installationLocation,
            'citizen_id' => $req->citizenId,
            'ulb_id' => $req->ulbId,
            'user_id' => $req->userId,
            'typology' => $req->typology
        ];
        return $metaReqs;
    }

    /**
     * | Apply for private Land
     */
    public function addNew($req)
    {
        $bearerToken = $req->token;
        $ulbWorkflows = $this->getUlbWorkflowId($bearerToken, $req->ulbId, $req->WfMasterId);                 // Workflow Trait Function
        // $ulbWorkflows = $ulbWorkflows['data'];
        $ulbWorkflowReqs = [                                                                           // Workflow Meta Requests
            // 'workflow_id' => $ulbWorkflows['id'],
            'workflow_id' => $ulbWorkflows['wf_master_id'],
            'initiator_role_id' => $ulbWorkflows['initiator_role_id'],
            'last_role_id' => $ulbWorkflows['initiator_role_id'],
            'current_role_id' => $ulbWorkflows['initiator_role_id'],
            'finisher_role_id' => $ulbWorkflows['finisher_role_id'],
        ];
        $mDocuments = $req->documents;

        $metaReqs = array_merge(
            [
                'ulb_id' => $req->ulbId,
                'citizen_id' => $req->citizenId,
                'application_date' => $this->_applicationDate,
                'ip_address' => $req->ipAddress,
                'application_type' => "New Apply",
                'user_id'      => $req->userId
            ],
            $this->metaReqs($req),
            // $mApplicationNo,
            $ulbWorkflowReqs
        );
        $tempId = AdvActivePrivateland::create($metaReqs)->id;
        $this->uploadDocument($tempId, $mDocuments, $req->auth);

        return $req->application_no;
    }

    /**
     * | Renewal For private land
     */
    public function renewalApplication($req)
    {
        $bearerToken = $req->bearerToken();
        // $workflowId = Config::get('workflow-constants.PRIVATE_LANDS');
        // $ulbWorkflows = $this->getUlbWorkflowId($bearerToken, $req->ulbId, $req->WfMasterId);        // Workflow Trait Function
        // $ipAddress = getClientIpAddress();
        $ulbWorkflows = $this->getUlbWorkflowId($bearerToken, $req->ulbId, $req->WfMasterId);                 // Workflow Trait Function
        $ulbWorkflows = $ulbWorkflows['data'];
        $mRenewalNo = ['renew_no' => 'LAND/REN-' . random_int(100000, 999999)];                  // Generate Application No
        $details = AdvPrivateland::find($req->applicationId);                              // Find Previous Application No
        $licenseNo = ['license_no' => $details->license_no];
        $ulbWorkflowReqs = [                                                                           // Workflow Meta Requests
            'workflow_id' => $ulbWorkflows['id'],
            'initiator_role_id' => $ulbWorkflows['initiator_role_id'],
            'last_role_id' => $ulbWorkflows['initiator_role_id'],
            'current_role_id' => $ulbWorkflows['initiator_role_id'],
            'finisher_role_id' => $ulbWorkflows['finisher_role_id'],
        ];
        $mDocuments = $req->documents;

        $metaReqs = array_merge(
            [
                'ulb_id' => $req->ulbId,
                'citizen_id' => $req->citizenId,
                'application_date' => $this->_applicationDate,
                'ip_address' => $req->ipAddress,
                'application_type' => "Renew"
            ],
            $this->metaRenewReqs($req),
            $mRenewalNo,
            $licenseNo,
            $ulbWorkflowReqs
        );
        // return $metaReqs;                                                                                      // Add Relative Path as Request and Client Ip Address etc.
        $tempId = AdvActivePrivateland::create($metaReqs)->id;
        $this->uploadDocument($tempId, $mDocuments, $req->auth);

        return $mRenewalNo['renew_no'];
    }


    /** 
     * upload Document
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadDocument($tempId, $documents, $auth)
    {
        collect($documents)->map(function ($doc) use ($tempId, $auth) {
            $metaReqs = array();
            $docUpload = new DocumentUpload;
            $mWfActiveDocument = new WfActiveDocument();
            $mAdvActivePrivateland = new AdvActivePrivateland();
            $relativePath = Config::get('constants.LAND_ADVET.RELATIVE_PATH');
            $getApplicationDtls = $mAdvActivePrivateland->getPrivatelandDetails($tempId);
            $refImageName = $doc['docCode'];
            $refImageName = $getApplicationDtls->id . '-' . $refImageName;
            $documentImg = $doc['image'];
            $newRequest = new Request([
                'document' => $documentImg
            ]);
            //$imageName = $docUpload->upload($refImageName, $documentImg, $relativePath);
            $imageName = $docUpload->upload($newRequest);

            $metaReqs['moduleId'] = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
            $metaReqs['activeId'] = $getApplicationDtls->id;
            $metaReqs['workflowId'] = $getApplicationDtls->workflow_id;
            $metaReqs['ulbId'] = $getApplicationDtls->ulb_id;
            $metaReqs['relativePath'] = $relativePath;
            $metaReqs['document'] = $imageName;
            $metaReqs['docCode'] = $doc['docCode'];
            $metaReqs['ownerDtlId'] = $doc['ownerDtlId'];
            $metaReqs['uniqueId'] = $imageName['data']['uniqueId'];
            $metaReqs['referenceNo'] = $imageName['data']['ReferenceNo'];
            $a = new Request($metaReqs);
            // $mWfActiveDocument->postDocuments($a, $auth);
            $metaReqs =  $mWfActiveDocument->metaReqs($metaReqs);
            // $mWfActiveDocument->create($metaReqs);
            foreach ($metaReqs as $key => $val) {
                $mWfActiveDocument->$key = $val;
            }
            $mWfActiveDocument->save();
        });
    }

    /**
     * | Get private land application details by Id 
     */
    public function getPrivatelandDetails($appId)
    {
        return AdvActivePrivateland::select('*')
            ->where('id', $appId)
            ->first();
    }

    /**
     * | Get Application Details by id
     * | @param Advertisements id
     */
    public function getDetailsById($id, $type)
    {
        $details = array();
        if ($type == "Active" || $type == NULL) {
            $details = DB::table('adv_active_privatelands')
                ->select(
                    'adv_active_privatelands.*',
                    'u.ulb_name',
                    'w.ward_name as resident_ward_no',
                    'pw.ward_name as permanent_ward_no',
                    'ew.ward_name as entity_ward_no',
                    't.descriptions as typology',
                    'dp.string_parameter as dispayType',
                    'il.string_parameter as installationLocation',
                )
                ->where('adv_active_privatelands.id', $id)
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_active_privatelands.ulb_id')
                ->leftJoin('ulb_ward_masters as w', 'w.id', '=', DB::raw('adv_active_privatelands.ward_id::int'))
                ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', DB::raw('adv_active_privatelands.permanent_ward_id::int'))
                ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', DB::raw('adv_active_privatelands.entity_ward_id::int'))
                ->leftJoin('adv_typology_mstrs as t', 't.id', '=', 'adv_active_privatelands.typology')
                ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', DB::raw('adv_active_privatelands.display_type::int'))
                ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', DB::raw('adv_active_privatelands.installation_location::int'))
                ->first();
        } elseif ($type == "Reject") {
            $details = DB::table('adv_rejected_privatelands')
                ->select(
                    'adv_rejected_privatelands.*',
                    'u.ulb_name',
                    'w.ward_name as resident_ward_no',
                    'pw.ward_name as permanent_ward_no',
                    'ew.ward_name as entity_ward_no',
                    't.descriptions as typology',
                    'dp.string_parameter as dispayType',
                    'il.string_parameter as installationLocation',
                )
                ->where('adv_rejected_privatelands.id', $id)
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_rejected_privatelands.ulb_id')
                ->leftJoin('ulb_ward_masters as w', 'w.id', '=', DB::raw('adv_rejected_privatelands.ward_id::int'))
                ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', DB::raw('adv_rejected_privatelands.permanent_ward_id::int'))
                ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', DB::raw('adv_rejected_privatelands.entity_ward_id::int'))
                ->leftJoin('adv_typology_mstrs as t', 't.id', '=', 'adv_rejected_privatelands.typology')
                ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', DB::raw('adv_rejected_privatelands.display_type::int'))
                ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', DB::raw('adv_rejected_privatelands.installation_location::int'))
                ->first();
        } elseif ($type == 'Approve') {
            $details = DB::table('adv_privatelands')
                ->select(
                    'adv_privatelands.*',
                    'u.ulb_name',
                    'w.ward_name as resident_ward_no',
                    'pw.ward_name as permanent_ward_no',
                    'ew.ward_name as entity_ward_no',
                    't.descriptions as typology',
                    'dp.string_parameter as dispayType',
                    'il.string_parameter as installationLocation',
                )
                ->where('adv_privatelands.id', $id)
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_privatelands.ulb_id')
                ->leftJoin('ulb_ward_masters as w', 'w.id', '=', DB::raw('adv_privatelands.ward_id::int'))
                ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', DB::raw('adv_privatelands.permanent_ward_id::int'))
                ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', DB::raw('adv_privatelands.entity_ward_id::int'))
                ->leftJoin('adv_typology_mstrs as t', 't.id', '=', 'adv_privatelands.typology')
                ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', DB::raw('adv_privatelands.display_type::int'))
                ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', DB::raw('adv_privatelands.installation_location::int'))
                ->first();
        }

        return json_decode(json_encode($details), true);            // Convert Std Class to Array
    }

    /**
     * | Get Application Inbox List by Role Ids
     * | @param roleIds $roleIds
     */
    public function listInbox($roleIds, $ulbId)
    {
        $inbox = DB::table('adv_active_privatelands')
            ->select(
                'id',
                'application_no',
                DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"),
                'applicant',
                'entity_name',
                'entity_address',
                'doc_upload_status',
                'application_type',
                DB::raw("CASE WHEN citizen_id IS NULL THEN 'JSK' ELSE 'Citizen' END as applied_by")
            )
            ->orderByDesc('id')
            ->where('parked', NULL)
            ->where('ulb_id', $ulbId)
            ->whereIn('current_role_id', $roleIds);
        // ->get();
        return $inbox;
    }

    /**
     * | Get Application Outbox List by Role Ids
     */
    public function listOutbox($roleIds, $ulbId)
    {
        $outbox = DB::table('adv_active_privatelands')
            ->select(
                'id',
                'application_no',
                DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"),
                'applicant',
                'entity_name',
                'entity_address',
                'application_type',
                DB::raw("CASE WHEN citizen_id IS NULL THEN 'JSK' ELSE 'Citizen' END as applied_by")
            )
            ->orderByDesc('id')
            ->where('parked', NULL)
            ->where('ulb_id', $ulbId)
            ->whereNotIn('current_role_id', $roleIds);
        // ->get();
        return $outbox;
    }

    /**
     * | Get Applied application list
     */
    public function listAppliedApplications($citizenId)
    {
        return AdvActivePrivateland::where('citizen_id', $citizenId)
            ->select(
                'adv_active_privatelands.id',
                'adv_active_privatelands.application_no',
                'adv_active_privatelands.application_date',
                'adv_active_privatelands.application_type',
                'adv_active_privatelands.applicant',
                'adv_active_privatelands.entity_name',
                'adv_active_privatelands.entity_address',
                'adv_active_privatelands.parked',
                'adv_active_privatelands.doc_upload_status',
                DB::raw("TO_CHAR(adv_active_privatelands.application_date, 'DD-MM-YYYY') as application_date"),
                'wr.role_name',
                'um.ulb_name',
            )
            ->join('wf_roles as wr', 'wr.id', '=', 'adv_active_privatelands.current_role_id')
            ->join('ulb_masters as um', 'um.id', '=', 'adv_active_privatelands.ulb_id')
            ->orderByDesc('adv_active_privatelands.id')
            ->get();
    }


    /**
     * | Get Jsk Applied applications
     * | @param userId
     */
    public function getJSKApplications($userId)
    {
        return AdvActivePrivateland::where('user_id', $userId)
            ->select(
                'id',
                'application_no',
                'application_date'
            )
            ->orderByDesc('id')
            ->get();
    }

    /**
     * | Zone Entry for Zone
     */
    public function entryZone($req)
    {
        $AdvActivePrivateland = AdvActivePrivateland::find($req->applicationId);        // Application ID
        if ($AdvActivePrivateland->zone == NULL) {
            $AdvActivePrivateland->zone = $req->zone;
            return $AdvActivePrivateland->save();
        } else {
            return 0;
        }
    }

    /**
     * | Get application details for private land by id
     */
    public function getPrivateLandNo($appId)
    {
        return AdvActivePrivateland::select('*')
            ->where('id', $appId)
            ->first();
    }

    /**
     * | Get List of private land ULB wise
     */
    public function getPrivateLandList($ulbId)
    {
        return AdvActivePrivateland::select('*')
            ->where('adv_active_privatelands.ulb_id', $ulbId);
    }


    /**
     * | Reupload Documents
     */
    public function reuploadDocument($req ,$Image, $docId)
    {
        try{
        $docUpload = new DocumentUpload;
        $docDetails = WfActiveDocument::find($req->id);
        $relativePath = Config::get('constants.LAND_ADVET.RELATIVE_PATH');

        $data = [];
        $mWfActiveDocument = new WfActiveDocument();
        $user = collect(authUser($req));
        $file = $Image;
        $req->merge([
            'document' => $file
        ]);
        $imageName = $docUpload->upload($req);
        $metaReqs = [
            'moduleId' => Config::get('workflow-constants.ADVERTISMENT_MODULE_ID') ?? 5,
            'unique_id' => $imageName['data']['uniqueId'] ?? null,
            'reference_no' => $imageName['data']['ReferenceNo'] ?? null,
        ];
         // Save document metadata in wfActiveDocuments
         $activeId = $mWfActiveDocument->updateDocuments(new Request($metaReqs), $user, $docId);
         return $activeId;
 
         // return $data;
     } catch (Exception $e) {
         return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
     }
    }


    /**
     * | Get Pending applications
     * | @param citizenId
     */
    public function allPendingList()
    {
        return AdvActivePrivateland::all();
    }

    /**
     * | Pending List For Report
     */
    public function pendingListForReport()
    {
        return AdvActivePrivateland::select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', 'ulb_id', 'display_type','entity_name', DB::raw("'Active' as application_status"));
    }

    public function listAppliedApplicationsjsk($ulbId)
    {
        return AdvActivePrivateland::select(
            'adv_active_privatelands.id',
            'adv_active_privatelands.application_no',
            'adv_active_privatelands.application_date',
            'adv_active_privatelands.application_type',
            'adv_active_privatelands.applicant',
            'adv_active_privatelands.entity_name',
            'adv_active_privatelands.entity_address',
            'adv_active_privatelands.parked',
            'adv_active_privatelands.doc_upload_status',
            'adv_active_privatelands.mobile_no',
            'adv_active_privatelands.payment_status',
            DB::raw("TO_CHAR(adv_active_privatelands.application_date, 'DD-MM-YYYY') as application_date"),
            'wr.role_name',
            'um.ulb_name',
            DB::raw("CASE WHEN user_id IS NOT NULL THEN 'jsk' ELSE 'citizen' END AS user_type"),
            'adv_active_privatelands.current_role_id',
            'wr.role_name as pending_at'
        )
            ->join('wf_roles as wr', 'wr.id', '=', 'adv_active_privatelands.current_role_id')
            ->join('ulb_masters as um', 'um.id', '=', 'adv_active_privatelands.ulb_id')
            ->where('adv_active_privatelands.ulb_id', $ulbId)
            ->orderByDesc('adv_active_privatelands.id');
        //->get();
    }

    public function getLodgeListJsk($ulbId)
    {
        return AdvActivePrivateland::select(
            'adv_active_privatelands.id',
            'application_no',
            'ward_id',
            DB::raw("TO_CHAR(adv_active_privatelands.btc_date, 'DD-MM-YYYY') as btc_date"),
            'remarks',
            DB::raw("TO_CHAR(adv_active_privatelands.application_date, 'DD-MM-YYYY') as application_date"),
            'adv_active_privatelands.application_type',
            'adv_active_privatelands.applicant',
            'adv_active_privatelands.applicant as owner_name',
            'adv_active_privatelands.entity_name',
            'adv_active_privatelands.mobile_no as mobile_no',
            //DB::raw("CASE WHEN user_id IS NOT NULL THEN 'jsk' ELSE 'citizen' END AS applied_by"),
            'users.name as applied_by',
            'wr.role_name as btc_by',
        )
            ->join('wf_roles as wr', 'wr.id', '=', 'adv_active_privatelands.current_role_id')
            ->join('users', 'users.id', '=', 'adv_active_privatelands.user_id')
            ->where('adv_active_privatelands.ulb_id', $ulbId);
    }

    public function getDetailsByIdjsk($applicationId)
    {
        return AdvActivePrivateland::select(
            'adv_active_privatelands.id',
            'adv_active_privatelands.application_no',
            'adv_active_privatelands.applicant',
            'adv_active_privatelands.application_date',
            'adv_active_privatelands.entity_address',
            'adv_active_privatelands.entity_name',
            'adv_active_privatelands.mobile_no as mobile_no',
            'adv_active_privatelands.citizen_id',
            'adv_active_privatelands.ulb_id',
           'adv_active_privatelands.user_id',
            'adv_active_privatelands.workflow_id',
            'adv_active_privatelands.application_type',
            'um.ulb_name as ulb_name',
            'entity_ward_id as ward_no',
            'current_role_id',
            'holding_no',
            'father',
            'adv_active_privatelands.email',
            'adv_active_privatelands.aadhar_no',
            'permanent_ward_id as permanent_ward_no',
            'permanent_address',
            'doc_upload_status',
            'doc_verify_status'
        )
            ->leftjoin('ulb_masters as um', 'um.id', '=', 'adv_active_privatelands.ulb_id')
            ->where('adv_active_privatelands.id', $applicationId)
            ->orderByDesc('adv_active_privatelands.id');
        //->get();
    }
}
