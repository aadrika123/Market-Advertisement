<?php

namespace App\Models\Advertisements;

use App\MicroServices\DocumentUpload;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\WorkflowTrait;

class AdvActiveVehicle extends Model
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
            'mobile_no' => $req->mobile,
            'aadhar_no' => $req->aadharNo,
            'license_from' => $req->licenseFrom,
            'license_to' => $req->licenseTo,
            'entity_name' => $req->entityName,
            'trade_license_no' => $req->tradeLicenseNo,
            'gst_no' => $req->gstNo,
            'vehicle_name' => $req->vehicleName,
            'vehicle_no' => $req->vehicleNo,
            'vehicle_type' => $req->vehicleType,
            'brand_display' => $req->brandDisplayed,
            'front_area' => $req->frontArea,
            'rear_area' => $req->rearArea,
            'side_area' => $req->sideArea,
            'top_area' => $req->topArea,
            'display_type' => $req->displayType,
            'citizen_id' => $req->citizenId,
            'ulb_id' => $req->ulbId,
            'user_id' => $req->userId
        ];
        return $metaReqs;
    }

    /**
     * | Store function to apply(1)
     * | @param request 
     */
    public function store($req)
    {
        $metaReqs = $this->metaReqs($req);
        $bearerToken = $req->bearerToken();
        $workflowId = Config::get('workflow-constants.MOVABLE_VEHICLE');
        $ulbWorkflows = $this->getUlbWorkflowId($bearerToken, $req->ulbId, $workflowId);        // Workflow Trait Function
        $ipAddress = getClientIpAddress();
        $mApplicationNo = ['application_no' => 'VEHICLE-' . random_int(100000, 999999)];                  // Generate Application No
        $ulbWorkflowReqs = [                                                                           // Workflow Meta Requests
            'workflow_id' => $ulbWorkflows['id'],
            'initiator_role' => $ulbWorkflows['initiator_role_id'],
            'current_roles' => $ulbWorkflows['initiator_role_id'],
            'finisher_role' => $ulbWorkflows['finisher_role_id'],
        ];
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
        // return $metaReqs;
        $tempId = AdvActiveVehicle::create($metaReqs)->id;
        $mDocuments = $req->documents;
        $this->uploadDocument($tempId, $mDocuments);

        return $mApplicationNo['application_no'];
    }


   /**
     * | Get Application Outbox List by Role Ids
     */
    public function outbox($roleIds)
    {
        $outbox = DB::table('adv_active_vehicles')
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
            )
            ->orderByDesc('id')
            ->whereNotIn('current_roles', $roleIds)
            ->get();
        return $outbox;
    }

    


        /**
     * | Document Upload (1.1)
     * | @param tempId Temprory Id
     * | @param documents Uploading Documents
     * */
    // public function uploadDocument($tempId, $documents)
    // {
    //     $mAdvDocument = new AdvActiveSelfadvetdocument();
    //     $mDocService = new DocumentUpload;
    //     $mRelativePath = Config::get('constants.VEHICLE_ADVET.RELATIVE_PATH');
    //     $workflowId = Config::get('workflow-constants.MOVABLE_VEHICLE_WORKFLOWS');

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
    //             'workflowId' => $workflowId
    //         ];
    //         $docUploadReqs = new Request($docUploadReqs);

    //         $mAdvDocument->store($docUploadReqs);
    //     });
    // }

    
    public function uploadDocument($tempId, $documents)
    {
        collect($documents)->map(function ($doc) use ($tempId) {
            $metaReqs = array();
            $docUpload = new DocumentUpload;
            $mWfActiveDocument = new WfActiveDocument();
            $mAdvActiveVehicle = new AdvActiveVehicle();
            $relativePath = Config::get('constants.VEHICLE_ADVET.RELATIVE_PATH');
            $getApplicationDtls = $mAdvActiveVehicle->getApplicationDetails($tempId);
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

    public function getApplicationDetails($appId)
    {
        return AdvActiveVehicle::select('*')
            ->where('id', $appId)
            ->first();
    }



    public function inbox($roleIds)
    {
        $inbox = DB::table('adv_active_vehicles')
            ->select(
                'id',
                'application_no',
                'applicant',
                'entity_name',
                'created_at as applied_date'
            )
            ->orderByDesc('id')
            ->whereIn('current_roles', $roleIds)
            ->get();
        return $inbox;
    }


    public function details($id,$workflowId){
        $details = array();
        $details = DB::table('adv_active_vehicles')
            ->select(
                'adv_active_vehicles.*',
                'u.ulb_name',
                // 'p.string_parameter as m_license_year',
                'w.ward_name as ward_no',
                'pw.ward_name as permanent_ward_no',
                'ew.ward_name as entity_ward_no',
                'dp.string_parameter as m_display_type',
                // 'il.string_parameter as m_installation_location',
                'r.role_name as m_current_role'
            )
            ->where('adv_active_vehicles.id', $id)
            ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_active_vehicles.ulb_id')
            // ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'adv_active_vehicles.license_year')
            ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_active_vehicles.ward_id')
            ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_active_vehicles.permanent_ward_id')
            ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'adv_active_vehicles.ward_id')
            ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', 'adv_active_vehicles.display_type')
            // ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', 'adv_active_vehicles.installation_location')
            ->leftJoin('wf_roles as r', 'r.id', '=', 'adv_active_vehicles.current_roles')
            ->first();

        $details = json_decode(json_encode($details), true);            // Convert Std Class to Array
        // $documents = DB::table('adv_active_selfadvetdocuments')
        //     ->select(
        //         'adv_active_selfadvetdocuments.*',
        //         'd.document_name',
        //         DB::raw("CONCAT(adv_active_selfadvetdocuments.relative_path,'/',adv_active_selfadvetdocuments.doc_name) as document_path")
        //     )
        //     ->leftJoin('ref_adv_document_mstrs as d', 'd.id', '=', 'adv_active_selfadvetdocuments.document_id')
        //     ->where(array('adv_active_selfadvetdocuments.temp_id'=> $id,'adv_active_selfadvetdocuments.workflow_id'=>$workflowId))
        //     ->get();
        // $details['documents'] = remove_null($documents->toArray());
        // return $details;
        $documents = DB::table('adv_active_selfadvetdocuments')
        ->select(
            'adv_active_selfadvetdocuments.*',
            'd.document_name as doc_type',
            DB::raw("CONCAT(adv_active_selfadvetdocuments.relative_path,'/',adv_active_selfadvetdocuments.doc_name) as doc_path")
        )
        ->leftJoin('ref_adv_document_mstrs as d', 'd.id', '=', 'adv_active_selfadvetdocuments.document_id')
        ->where(array('adv_active_selfadvetdocuments.temp_id'=> $id,'adv_active_selfadvetdocuments.workflow_id'=>$workflowId))
        ->get();
    $details['documents'] = remove_null($documents->toArray());
    return $details;
    }

     /**
     * | Get Citizen Applied applications
     * | @param citizenId
     */
    public function getCitizenApplications($citizenId)
    {
        return AdvActiveVehicle::where('citizen_id', $citizenId)
            ->select(
                'id',
                'application_no',
                'applicant',
                'father',
                'residence_address',
                'entity_name',
                'vehicle_no',
                'vehicle_name',
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

    
}
