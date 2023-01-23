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
            'ulb_id' => $req->ulbId
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

    
    public function store($req)
    {
        $bearerToken = $req->bearerToken();
        $workflowId = Config::get('workflow-constants.PRIVATE_LANDS');
        $ulbWorkflows = $this->getUlbWorkflowId($bearerToken, $req->ulbId, $workflowId);        // Workflow Trait Function
        $ipAddress = getClientIpAddress();
        $mApplicationNo = ['application_no' => 'LAND-' . random_int(100000, 999999)];                  // Generate Application No
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
    public function uploadDocument_old($req, $metaReqs)
    {
        $mDocUpload = new DocumentUpload();
        $mRelativePath = Config::get('constants.LAND_ADVET.RELATIVE_PATH');
        $mDocSuffix = $this->_applicationDate . '-' . $req->citizenId;

        // Document Upload

        // Aadhar Document
        if ($req->aadharDoc) {
            $mRefDocName = Config::get('constants.AADHAR_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->aadharDoc, $mRelativePath);          // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['aadhar_path' => $docName]);
        }
        // Trade License Path
        if ($req->tradeDoc) {
            $mRefDocName = Config::get('constants.TRADE_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->tradeDoc, $mRelativePath);          // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['trade_license_path' => $docName]);
        }
        // Gps Document
        if ($req->gpsDoc) {
            $mRefDocName = Config::get('constants.GPS_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->gpsDoc, $mRelativePath);          // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['gps_path' => $docName]);
        }
        // Holding Document
        if ($req->holdingDoc) {
            $mRefDocName = Config::get('constants.HOLDING_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->holdingDoc, $mRelativePath);          // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['holding_path' => $docName]);
        }
        // GST Document
        if ($req->gstDoc) {
            $mRefDocName = Config::get('constants.GST_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->gstDoc, $mRelativePath);           // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['gst_path' => $docName]);
        }
        // Brand Display Path
        if ($req->brandDisplayDoc) {
            $mRefDocName = Config::get('constants.BRAND_DISPLAY_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->brandDisplayDoc, $mRelativePath);           // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['brand_display_path' => $docName]);
        }

        return $metaReqs;
    }


    
    /**
     * | Document Upload (1.1)
     * | @param tempId Temprory Id
     * | @param documents Uploading Documents
     * */
    public function uploadDocument($tempId, $documents)
    {
        $mAdvDocument = new AdvActiveSelfadvetdocument();
        $mDocService = new DocumentUpload;
        $mRelativePath = Config::get('constants.LAND_ADVET.RELATIVE_PATH');

        collect($documents)->map(function ($document) use ($mAdvDocument, $tempId, $mDocService, $mRelativePath) {
            $mDocumentId = $document['id'];
            $mDocRelativeName = $document['relativeName'];
            $mImage = $document['image'];
            $mDocName = $mDocService->upload($mDocRelativeName, $mImage, $mRelativePath);

            $docUploadReqs = [
                'tempId' => $tempId,
                'docTypeCode' => 'Test-Code',
                'documentId' => $mDocumentId,
                'relativePath' => $mRelativePath,
                'docName' => $mDocName
            ];
            $docUploadReqs = new Request($docUploadReqs);

            $mAdvDocument->store($docUploadReqs);
        });
    }

        /**
     * | Get Application Details by id
     * | @param SelfAdvertisements id
     */
    public function details($id)
    {
        $details = array();
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

        $details = json_decode(json_encode($details), true);            // Convert Std Class to Array
        $documents = DB::table('adv_active_selfadvetdocuments')
            ->select(
                'adv_active_selfadvetdocuments.*',
                'd.document_name',
                DB::raw("CONCAT(adv_active_selfadvetdocuments.relative_path,'/',adv_active_selfadvetdocuments.doc_name) as document_path")
            )
            ->leftJoin('ref_adv_document_mstrs as d', 'd.id', '=', 'adv_active_selfadvetdocuments.document_id')
            ->where('temp_id', $id)
            ->get();
        $details['documents'] = remove_null($documents->toArray());
        return $details;
    }

    /**
     * | Get Application Inbox List by Role Ids
     * | @param roleIds $roleIds
     */
    public function inbox($roleIds)
    {
        $inbox = DB::table('adv_active_privatelands')
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
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

    public function getCitizenApplications($citizenId)
    {
        return AdvActivePrivateland::where('citizen_id', $citizenId)
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
            )
            ->orderByDesc('id')
            ->get();
    }

}
