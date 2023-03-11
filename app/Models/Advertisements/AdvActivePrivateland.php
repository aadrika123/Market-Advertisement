<?php

namespace App\Models\Advertisements;

use App\MicroServices\DocumentUpload;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use App\Traits\WorkflowTrait;
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
     * | Store function to apply(1)
     * | @param request 
     */
    // public function store($req)
    // {
    //     $metaReqs = $this->metaReqs($req);
    //     $mRelativePath = Config::get('constants.LAND_ADVET.RELATIVE_PATH');
    //     $mDocRelPathReq = ['doc_relative_path' => $mRelativePath];
    //     $mClientIpAddress = ['ip_address' => getClientIpAddress()];
    //     $applicationNo = ['application_no' => "LAND-" . random_int(100000, 999999)];
    //     $metaReqs = array_merge($metaReqs, $applicationNo, $mDocRelPathReq, $mClientIpAddress);     // Final Merged Meta Requests
    //     $metaReqs = $this->uploadDocument($req, $metaReqs);             // Current Objection function to Upload Document
    //     return AdvActivePrivateland::create($metaReqs)->application_no;
    // }


    public function addNew($req)
    {
        $bearerToken = $req->bearerToken();
        $workflowId = Config::get('workflow-constants.PRIVATE_LANDS');
        $ulbWorkflows = $this->getUlbWorkflowId($bearerToken, $req->ulbId, $workflowId);        // Workflow Trait Function
        $ipAddress = getClientIpAddress();
        $mApplicationNo = ['application_no' => 'LAND-' . random_int(100000, 999999)];                  // Generate Application No
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
                'ip_address' => $ipAddress
            ],
            $this->metaReqs($req),
            $mApplicationNo,
            $ulbWorkflowReqs
        );
        // return $metaReqs;                                                                                      // Add Relative Path as Request and Client Ip Address etc.
        $tempId = AdvActivePrivateland::create($metaReqs)->id;
        $this->uploadDocument($tempId, $mDocuments);

        return $mApplicationNo['application_no'];
    }


    /**
     * | Document Upload(1.1)
     * | @param Client User Requested Data
     * | @param metaReqs More Added Filtered Data
     */
    // public function uploadDocument_old($req, $metaReqs)
    // {
    //     $mDocUpload = new DocumentUpload();
    //     $mRelativePath = Config::get('constants.LAND_ADVET.RELATIVE_PATH');
    //     $mDocSuffix = $this->_applicationDate . '-' . $req->citizenId;

    //     // Document Upload

    //     // Aadhar Document
    //     if ($req->aadharDoc) {
    //         $mRefDocName = Config::get('constants.AADHAR_RELATIVE_NAME') . '-' . $mDocSuffix;
    //         $docName = $mDocUpload->upload($mRefDocName, $req->aadharDoc, $mRelativePath);          // Micro Service for Uploading Document
    //         $metaReqs = array_merge($metaReqs, ['aadhar_path' => $docName]);
    //     }
    //     // Trade License Path
    //     if ($req->tradeDoc) {
    //         $mRefDocName = Config::get('constants.TRADE_RELATIVE_NAME') . '-' . $mDocSuffix;
    //         $docName = $mDocUpload->upload($mRefDocName, $req->tradeDoc, $mRelativePath);          // Micro Service for Uploading Document
    //         $metaReqs = array_merge($metaReqs, ['trade_license_path' => $docName]);
    //     }
    //     // Gps Document
    //     if ($req->gpsDoc) {
    //         $mRefDocName = Config::get('constants.GPS_RELATIVE_NAME') . '-' . $mDocSuffix;
    //         $docName = $mDocUpload->upload($mRefDocName, $req->gpsDoc, $mRelativePath);          // Micro Service for Uploading Document
    //         $metaReqs = array_merge($metaReqs, ['gps_path' => $docName]);
    //     }
    //     // Holding Document
    //     if ($req->holdingDoc) {
    //         $mRefDocName = Config::get('constants.HOLDING_RELATIVE_NAME') . '-' . $mDocSuffix;
    //         $docName = $mDocUpload->upload($mRefDocName, $req->holdingDoc, $mRelativePath);          // Micro Service for Uploading Document
    //         $metaReqs = array_merge($metaReqs, ['holding_path' => $docName]);
    //     }
    //     // GST Document
    //     if ($req->gstDoc) {
    //         $mRefDocName = Config::get('constants.GST_RELATIVE_NAME') . '-' . $mDocSuffix;
    //         $docName = $mDocUpload->upload($mRefDocName, $req->gstDoc, $mRelativePath);           // Micro Service for Uploading Document
    //         $metaReqs = array_merge($metaReqs, ['gst_path' => $docName]);
    //     }
    //     // Brand Display Path
    //     if ($req->brandDisplayDoc) {
    //         $mRefDocName = Config::get('constants.BRAND_DISPLAY_RELATIVE_NAME') . '-' . $mDocSuffix;
    //         $docName = $mDocUpload->upload($mRefDocName, $req->brandDisplayDoc, $mRelativePath);           // Micro Service for Uploading Document
    //         $metaReqs = array_merge($metaReqs, ['brand_display_path' => $docName]);
    //     }

    //     return $metaReqs;
    // }



    /**
     * | Document Upload (1.1)
     * | @param tempId Temprory Id
     * | @param documents Uploading Documents
     * */
    // public function uploadDocument_old_2($tempId, $documents)
    // {
    //     $mAdvDocument = new AdvActiveSelfadvetdocument();
    //     $mDocService = new DocumentUpload;
    //     $mRelativePath = Config::get('constants.LAND_ADVET.RELATIVE_PATH');
    //     $workflowId = Config::get('workflow-constants.PRIVATE_LANDS_WORKFLOWS');

    //     collect($documents)->map(function ($document) use ($mAdvDocument, $tempId, $mDocService, $mRelativePath, $workflowId) {
    //         $mDocumentId = $document['id'];
    //         $mDocRelativeName = $document['relativeName'];
    //         $mImage = $document['image'];
    //         $mDocName = $mDocService->upload($mDocRelativeName, $mImage, $mRelativePath);

    //         $docUploadReqs = [
    //             'tempId' => $tempId,
    //             'docTypeCode' => 'Test-Code',
    //             'documentId' => $mDocumentId,
    //             'relativePath' => $mRelativePath,
    //             'docName' => $mDocName,
    //             'workflowId'=> $workflowId
    //         ];
    //         $docUploadReqs = new Request($docUploadReqs);

    //         $mAdvDocument->store($docUploadReqs);
    //     });
    // }


    /** 
     * upload Document
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadDocument($tempId, $documents)
    {
        collect($documents)->map(function ($doc) use ($tempId) {
            $metaReqs = array();
            $docUpload = new DocumentUpload;
            $mWfActiveDocument = new WfActiveDocument();
            $mAdvActivePrivateland = new AdvActivePrivateland();
            $relativePath = Config::get('constants.LAND_ADVET.RELATIVE_PATH');
            $getApplicationDtls = $mAdvActivePrivateland->getPrivatelandDetails($tempId);
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
        if ($type == "Active" || $type==NULL) {
            $details = DB::table('adv_active_privatelands')
                ->select(
                    'adv_active_privatelands.*',
                    'u.ulb_name',
                    // 'p.string_parameter as m_license_year',
                    // 'w.ward_name as ward_no',
                    // 'pw.ward_name as permanent_ward_no',
                    // 'ew.ward_name as entity_ward_no',
                    // 'dp.string_parameter as m_display_type',
                    // 'il.string_parameter as m_installation_location',
                    // 'r.role_name as m_current_role'
                )
                ->where('adv_active_privatelands.id', $id)
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_active_privatelands.ulb_id')
                // ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'adv_active_privatelands.license_year')
                // ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_active_privatelands.ward_id')
                // ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_active_privatelands.permanent_ward_id')
                // ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'adv_active_privatelands.entity_ward_id')
                // ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', 'adv_active_privatelands.display_type')
                // ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', 'adv_active_privatelands.installation_location')
                // ->leftJoin('wf_roles as r', 'r.id', '=', 'adv_active_privatelands.current_role_id')
                ->first();
        } elseif ($type == "Reject") {
            $details = DB::table('adv_rejected_privatelands')
                ->select(
                    'adv_rejected_privatelands.*',
                    'u.ulb_name',
                    // 'p.string_parameter as m_license_year',
                    // 'w.ward_name as ward_no',
                    // 'pw.ward_name as permanent_ward_no',
                    // 'ew.ward_name as entity_ward_no',
                    // 'dp.string_parameter as m_display_type',
                    // 'il.string_parameter as m_installation_location',
                    // 'r.role_name as m_current_role'
                )
                ->where('adv_rejected_privatelands.id', $id)
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_rejected_privatelands.ulb_id')
                // ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'adv_rejected_privatelands.license_year')
                // ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_rejected_privatelands.ward_id')
                // ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_rejected_privatelands.permanent_ward_id')
                // ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'adv_rejected_privatelands.entity_ward_id')
                // ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', 'adv_rejected_privatelands.display_type')
                // ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', 'adv_rejected_privatelands.installation_location')
                // ->leftJoin('wf_roles as r', 'r.id', '=', 'adv_rejected_privatelands.current_role_id')
                ->first();
        } elseif ($type == 'Approve') {
            $details = DB::table('adv_active_privatelands')
                ->select(
                    'adv_active_privatelands.*',
                    'u.ulb_name',
                    // 'p.string_parameter as m_license_year',
                    // 'w.ward_name as ward_no',
                    // 'pw.ward_name as permanent_ward_no',
                    // 'ew.ward_name as entity_ward_no',
                    // 'dp.string_parameter as m_display_type',
                    // 'il.string_parameter as m_installation_location',
                    // 'r.role_name as m_current_role'
                )
                ->where('adv_active_privatelands.id', $id)
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_active_privatelands.ulb_id')
                // ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'adv_active_privatelands.license_year')
                // ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_active_privatelands.ward_id')
                // ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_active_privatelands.permanent_ward_id')
                // ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'adv_active_privatelands.entity_ward_id')
                // ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', 'adv_active_privatelands.display_type')
                // ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', 'adv_active_privatelands.installation_location')
                // ->leftJoin('wf_roles as r', 'r.id', '=', 'adv_active_privatelands.current_role_id')
                ->first();
        } elseif ($type == "Reject") {
            $details = DB::table('adv_privatelands')
                ->select(
                    'adv_privatelands.*',
                    'u.ulb_name',
                    // 'p.string_parameter as m_license_year',
                    // 'w.ward_name as ward_no',
                    // 'pw.ward_name as permanent_ward_no',
                    // 'ew.ward_name as entity_ward_no',
                    // 'dp.string_parameter as m_display_type',
                    // 'il.string_parameter as m_installation_location',
                    // 'r.role_name as m_current_role'
                )
                ->where('adv_privatelands.id', $id)
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_privatelands.ulb_id')
                // ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'adv_privatelands.license_year')
                // ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_privatelands.ward_id')
                // ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_privatelands.permanent_ward_id')
                // ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'adv_privatelands.entity_ward_id')
                // ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', 'adv_privatelands.display_type')
                // ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', 'adv_privatelands.installation_location')
                // ->leftJoin('wf_roles as r', 'r.id', '=', 'adv_privatelands.current_role_id')
                ->first();
        }

        return json_decode(json_encode($details), true);            // Convert Std Class to Array
    }

    /**
     * | Get Application Inbox List by Role Ids
     * | @param roleIds $roleIds
     */
    public function listInbox($roleIds)
    {
        $inbox = DB::table('adv_active_privatelands')
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
                'doc_upload_status',
            )
            ->orderByDesc('id')
            ->whereIn('current_role_id', $roleIds)
            ->get();
        return $inbox;
    }

    /**
     * | Get Application Outbox List by Role Ids
     */
    public function listOutbox($roleIds)
    {
        $outbox = DB::table('adv_active_privatelands')
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
            )
            ->orderByDesc('id')
            ->whereNotIn('current_role_id', $roleIds)
            ->get();
        return $outbox;
    }

    public function listAppliedApplications($citizenId)
    {
        return AdvActivePrivateland::where('citizen_id', $citizenId)
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
                'doc_upload_status'
            )
            ->orderByDesc('id')
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

    
    public function entryZone($req){
        $AdvActivePrivateland = AdvActivePrivateland::find($req->applicationId);        // Application ID
        $AdvActivePrivateland->zone = $req->zone;
        return $AdvActivePrivateland->save();
    }

    public function getPrivateLandNo($appId)
    {
        return AdvActivePrivateland::select('*')
            ->where('id', $appId)
            ->first();
    }

    public function getPrivateLandList($ulbId)
    {
        return AdvActivePrivateland::select('*')
            ->where('adv_active_privatelands.ulb_id', $ulbId);
    }

    
    /**
     * | Reupload Documents
     */
    public function reuploadDocument($req){
        $docUpload = new DocumentUpload;
        $docDetails=WfActiveDocument::find($req->id);
        $relativePath = Config::get('constants.LAND_ADVET.RELATIVE_PATH');

        $refImageName = $docDetails['doc_code'];
        $refImageName = $docDetails['active_id'] . '-' . $refImageName;
        $documentImg = $req->image;
        $imageName = $docUpload->upload($refImageName, $documentImg, $relativePath);

        $metaReqs['moduleId'] = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
        $metaReqs['activeId'] = $docDetails['active_id'];
        $metaReqs['workflowId'] = $docDetails['workflow_id'];
        $metaReqs['ulbId'] = $docDetails['ulb_id'];
        $metaReqs['relativePath'] = $relativePath;
        $metaReqs['document'] = $imageName;
        $metaReqs['docCode'] = $docDetails['doc_code'];
        $metaReqs['ownerDtlId'] = $docDetails['ownerDtlId'];
        $a = new Request($metaReqs);
        $mWfActiveDocument=new WfActiveDocument();
        $mWfActiveDocument->postDocuments($a);
        $docDetails->current_status='0';
        $docDetails->save();
        return $docDetails['active_id'];
    }


}
