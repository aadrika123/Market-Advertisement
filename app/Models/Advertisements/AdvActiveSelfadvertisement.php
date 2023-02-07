<?php

namespace App\Models\Advertisements;

use App\MicroServices\DocumentUpload;
use App\Traits\WorkflowTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class AdvActiveSelfadvertisement extends Model
{
    use WorkflowTrait;
    protected $guarded = [];
    protected $_applicationDate;

    public function __construct()
    {
        $this->_applicationDate = Carbon::now()->format('Y-m-d');
    }

    // helper meta reqs
    public function metaReqs($req)
    {
        return [
            'applicant' => $req->applicantName,
            'license_year' => $req->licenseYear,
            'father' => $req->fatherName,
            'email' => $req->email,
            'residence_address' => $req->residenceAddress,
            'ward_id' => $req->wardId,
            'permanent_address' => $req->permanentAddress,
            'permanent_ward_id' => $req->permanentWardId,
            'entity_name' => $req->entityName,
            'entity_address' => $req->entityAddress,
            'entity_ward_id' => $req->entityWardId,
            'mobile_no' => $req->mobileNo,
            'aadhar_no' => $req->aadharNo,
            'trade_license_no' => $req->tradeLicenseNo,
            'holding_no' => $req->holdingNo,
            'gst_no' => $req->gstNo,
            'longitude' => $req->longitude,
            'latitude' => $req->latitude,
            'display_area' => $req->displayArea,
            'display_type' => $req->displayType,
            'installation_location' => $req->installationLocation,
            'brand_display_name' => $req->brandDisplayName,
            'user_id'=>$req->userId
        ];
    }

    // Store Self Advertisements(1)
    public function store($req)
    {
        $bearerToken = $req->bearerToken();
        $workflowId = Config::get('workflow-constants.SELF_ADVERTISENTS');
        $ulbWorkflows = $this->getUlbWorkflowId($bearerToken, $req->ulbId, $workflowId);        // Workflow Trait Function
        $ipAddress = getClientIpAddress();
        $mApplicationNo = ['application_no' => 'SELF-' . random_int(100000, 999999)];                  // Generate Application No
        $ulbWorkflowReqs = [                                                                           // Workflow Meta Requests
            'workflow_id' => $ulbWorkflows['id'],
            'initiator_role_id' => $ulbWorkflows['initiator_role_id'],
            'current_role_id' => $ulbWorkflows['initiator_role_id'],
            'finisher_role_id' => $ulbWorkflows['finisher_role_id'],
        ];
        $mDocuments = $req->documents;

        $metaReqs = array_merge(
            [
                'ulb_id' => $req->ulbId,
                'citizen_id' => $req->citizenId,
                'application_date' => $this->_applicationDate,
                'ip_address' => $ipAddress
            ],
            $this->metaReqs($req),
            $mApplicationNo,
            $ulbWorkflowReqs
        );                                                                                          // Add Relative Path as Request and Client Ip Address etc.
        $tempId = AdvActiveSelfadvertisement::create($metaReqs)->id;
        $this->uploadDocument($tempId, $mDocuments);

        return $mApplicationNo['application_no'];
    }


       /**
     * upload Document By Admin
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadDocument($tempId, $documents)
    {
        collect($documents)->map(function ($doc) use ($tempId) {
            $metaReqs = array();
            $docUpload = new DocumentUpload;
            $mWfActiveDocument = new WfActiveDocument();
            $mAdvActiveSelfadvertisement = new AdvActiveSelfadvertisement();
            $relativePath = Config::get('constants.SELF_ADVET_RELATIVE_PATH');
            $getApplicationDtls = $mAdvActiveSelfadvertisement->getSelfAdvertNo($tempId);
            $refImageName = $doc['docCode'];
            $refImageName = $getApplicationDtls->id . '-' . $refImageName;
            $documentImg = $doc['image'];
            $imageName = $docUpload->upload($refImageName, $documentImg, $relativePath);

            $metaReqs['moduleId'] = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
            $metaReqs['activeId'] = $getApplicationDtls->id;
            $metaReqs['workflowId'] = $getApplicationDtls->workflow_id;
            $metaReqs['ulbId'] = $getApplicationDtls->ulb_id;
            $metaReqs['relativePath'] = $relativePath;
            $metaReqs['document'] = $imageName;
            $metaReqs['docCode'] = $doc['docCode'];
            $metaReqs['ownerDtlId'] = $doc['ownerDtlId'];
            $a = new Request($metaReqs);
            $mWfActiveDocument->postDocuments($a);
        });
    }

    /**
     * | Document Upload (1.1)
     * | @param applicationId Application Id
     * | @param document Uploading Document
      * */
    // public function workflowUploadDocument($req)
    // {
    //     // return $req;
    //     $mAdvDocument = new AdvActiveSelfadvetdocument();
    //     $mDocService = new DocumentUpload;
    //     $mRelativePath = Config::get('constants.SELF_ADVET.RELATIVE_PATH');
    //     $workflowId = Config::get('workflow-constants.ADVERTISEMENT_WORKFLOWS');

    //     $mDocName = $mDocService->upload($req->docRefName, $req->document, $mRelativePath);
    //     $docUploadReqs = [
    //         'tempId' => $req->applicationId,
    //         'docTypeCode' => 'Test-Code',
    //         'documentId' => $req->docMstrId,
    //         'relativePath' => $mRelativePath,
    //         'docName' => $mDocName,
    //         'workflowId'=>$workflowId
    //     ];
    //     $docUploadReqs = new Request($docUploadReqs);
    //     $mAdvDocument->store($docUploadReqs);
    // }

    /**
     * | Get Citizen Applied applications
     * | @param citizenId
     */
    public function getCitizenApplications($citizenId)
    {
        return AdvActiveSelfadvertisement::where('citizen_id', $citizenId)
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
                'old_application_no',
                'payment_status'
            )
            ->orderByDesc('id')
            ->get();
    }

    /**
     * | Get Application Details by id
     * | @param SelfAdvertisements id
     */
    public function details($id,$type)
    {
        $details = array();
        if ($type == 'Active') {
            $details = DB::table('adv_active_selfadvertisements')
                ->select(
                    'adv_active_selfadvertisements.*',
                    'u.ulb_name',
                    'p.string_parameter as m_license_year',
                    'w.ward_name as ward_no',
                    'pw.ward_name as permanent_ward_no',
                    'ew.ward_name as entity_ward_no',
                    'dp.string_parameter as m_display_type',
                    'il.string_parameter as m_installation_location',
                    'r.role_name as m_current_role'
                )
                ->where('adv_active_selfadvertisements.id', $id)
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_active_selfadvertisements.ulb_id')
                ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'adv_active_selfadvertisements.license_year')
                ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_active_selfadvertisements.ward_id')
                ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_active_selfadvertisements.permanent_ward_id')
                ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'adv_active_selfadvertisements.entity_ward_id')
                ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', 'adv_active_selfadvertisements.display_type')
                ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', 'adv_active_selfadvertisements.installation_location')
                ->leftJoin('wf_roles as r', 'r.id', '=', 'adv_active_selfadvertisements.current_role_id')
                ->first();
        } elseif ($type == 'Reject') {
            $details = DB::table('adv_rejected_selfadvertisements')
            ->select(
                'adv_rejected_selfadvertisements.*',
                'u.ulb_name',
                'p.string_parameter as m_license_year',
                'w.ward_name as ward_no',
                'pw.ward_name as permanent_ward_no',
                'ew.ward_name as entity_ward_no',
                'dp.string_parameter as m_display_type',
                'il.string_parameter as m_installation_location',
                'r.role_name as m_current_role'
            )
            ->where('adv_rejected_selfadvertisements.id', $id)
            ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_rejected_selfadvertisements.ulb_id')
            ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'adv_rejected_selfadvertisements.license_year')
            ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_rejected_selfadvertisements.ward_id')
            ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_rejected_selfadvertisements.permanent_ward_id')
            ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'adv_rejected_selfadvertisements.entity_ward_id')
            ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', 'adv_rejected_selfadvertisements.display_type')
            ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', 'adv_rejected_selfadvertisements.installation_location')
            ->leftJoin('wf_roles as r', 'r.id', '=', 'adv_rejected_selfadvertisements.current_role_id')
            ->first();
        }elseif ($type == 'Approve'){
            $details = DB::table('adv_selfadvertisements')
            ->select(
                'adv_selfadvertisements.*',
                'u.ulb_name',
                'p.string_parameter as m_license_year',
                'w.ward_name as ward_no',
                'pw.ward_name as permanent_ward_no',
                'ew.ward_name as entity_ward_no',
                'dp.string_parameter as m_display_type',
                'il.string_parameter as m_installation_location',
                'r.role_name as m_current_role'
            )
            ->where('adv_selfadvertisements.id', $id)
            ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_selfadvertisements.ulb_id')
            ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'adv_selfadvertisements.license_year')
            ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_selfadvertisements.ward_id')
            ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_selfadvertisements.permanent_ward_id')
            ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'adv_selfadvertisements.entity_ward_id')
            ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', 'adv_selfadvertisements.display_type')
            ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', 'adv_selfadvertisements.installation_location')
            ->leftJoin('wf_roles as r', 'r.id', '=', 'adv_selfadvertisements.current_role_id')
            ->first();
        }
        return json_decode(json_encode($details), true);            // Convert Std Class to Array
    }

    /**
     * | Get Application Inbox List by Role Ids
     * | @param roleIds $roleIds
     */
    public function inbox($roleIds)
    {
        $inbox = DB::table('adv_active_selfadvertisements')
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
                'old_application_no',
                'payment_status'
            )
            ->orderByDesc('id')
            ->whereIn('current_role_id', $roleIds)
            ->get();
        return $inbox;
    }

    /**
     * | Get Application Outbox List by Role Ids
     */
    public function outbox($roleIds)
    {
        $outbox = DB::table('adv_active_selfadvertisements')
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
                'old_application_no',
                'payment_status'
            )
            ->orderByDesc('id')
            ->whereNotIn('current_role_id', $roleIds)
            ->get();
        return $outbox;
    }


      /**
     * | Get Jsk Applied applications
     * | @param userId
     */
    public function getJSKApplications($userId)
    {
        return AdvActiveSelfadvertisement::where('user_id', $userId)
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
                'old_application_no',
                'payment_status'
            )
            ->orderByDesc('id')
            ->get();
    }

    public function getSelfAdvertNo($appId)
    {
        return AdvActiveSelfadvertisement::select('*')
            ->where('id', $appId)
            ->first();
    }

}
