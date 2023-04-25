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
            'application_no' => $req->application_no,
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
            'user_id' => $req->userId,
            'typology' => $req->typology
        ];
        return $metaReqs;
    }


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
            'user_id' => $req->userId,
            'typology' => $req->typology
        ];
        return $metaReqs;
    }

    /**
     * | Store function to apply(1)
     * | @param request 
     */
    public function addNew($req)
    {
        $metaReqs = $this->metaReqs($req);
        $bearerToken = $req->bearerToken();
        $workflowId = Config::get('workflow-constants.MOVABLE_VEHICLE');
        $ulbWorkflows = $this->getUlbWorkflowId($bearerToken, $req->ulbId, $workflowId);        // Workflow Trait Function
        $ipAddress = getClientIpAddress();
        // $mApplicationNo = ['application_no' => 'VEHICLE-' . random_int(100000, 999999)];                  // Generate Application No
        $ulbWorkflowReqs = [                                                                           // Workflow Meta Requests
            'workflow_id' => $ulbWorkflows['id'],
            'initiator_role' => $ulbWorkflows['initiator_role_id'],
            'current_roles' => $ulbWorkflows['initiator_role_id'],
            'last_role_id' => $ulbWorkflows['initiator_role_id'],
            'finisher_role' => $ulbWorkflows['finisher_role_id'],
        ];
        $metaReqs = array_merge(
            [
                'ulb_id' => $req->ulbId,
                'citizen_id' => $req->citizenId,
                'application_date' => $this->_applicationDate,
                'ip_address' => $ipAddress,
                'application_type' => "New Apply"
            ],
            $this->metaReqs($req),
            // $mApplicationNo,
            $ulbWorkflowReqs
        );
        // return $metaReqs;
        $tempId = AdvActiveVehicle::create($metaReqs)->id;
        $mDocuments = $req->documents;
        $this->uploadDocument($tempId, $mDocuments);

        return $req->application_no;
    }


    /**
     * | Store function for Renew(1)
     * | @param request 
     */
    public function renewalApplication($req)
    {
        $metaReqs = $this->metaReqs($req);
        $bearerToken = $req->bearerToken();
        $workflowId = Config::get('workflow-constants.MOVABLE_VEHICLE');
        $ulbWorkflows = $this->getUlbWorkflowId($bearerToken, $req->ulbId, $workflowId);        // Workflow Trait Function
        $ipAddress = getClientIpAddress();
        $mRenew = ['renew_no' => 'VEHICLE/REN-' . random_int(100000, 999999)];                  // Generate Application No
        $details = AdvVehicle::find($req->applicationId);                              // Find Previous Application No
        $licenseNo = ['license_no' => $details->license_no];
        $ulbWorkflowReqs = [                                                                           // Workflow Meta Requests
            'workflow_id' => $ulbWorkflows['id'],
            'initiator_role' => $ulbWorkflows['initiator_role_id'],
            'current_roles' => $ulbWorkflows['initiator_role_id'],
            'last_role_id' => $ulbWorkflows['initiator_role_id'],
            'finisher_role' => $ulbWorkflows['finisher_role_id'],
        ];
        $metaReqs = array_merge(
            [
                'ulb_id' => $req->ulbId,
                'citizen_id' => $req->citizenId,
                'application_date' => $this->_applicationDate,
                'ip_address' => $ipAddress,
                'application_type' => "Renew"
            ],
            $this->metaRenewReqs($req),
            $mRenew,
            $licenseNo,
            $ulbWorkflowReqs
        );
        // return $metaReqs;
        $tempId = AdvActiveVehicle::create($metaReqs)->id;
        $mDocuments = $req->documents;
        $this->uploadDocument($tempId, $mDocuments);

        return $mRenew['renew_no'];
    }


    /**
     * | Get Application Outbox List by Role Ids
     */
    public function listOutbox($roleIds)
    {
        $outbox = DB::table('adv_active_vehicles')
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'application_type',
            )
            ->orderByDesc('id')
            ->whereNotIn('current_roles', $roleIds)
            ->get();
        return $outbox;
    }

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



    public function listInbox($roleIds)
    {
        $inbox = DB::table('adv_active_vehicles')
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'created_at as applied_date',
                'doc_upload_status',
                'application_type',
            )
            ->orderByDesc('id')
            ->whereIn('current_roles', $roleIds)
            ->get();
        return $inbox;
    }


    public function getDetailsById($id, $type)
    {
        $details = array();
        if ($type == 'Active' || $type == NULL) {
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
        } elseif ($type == 'Reject') {
            $details = DB::table('adv_rejected_vehicles')
                ->select(
                    'adv_rejected_vehicles.*',
                    'u.ulb_name',
                    // 'p.string_parameter as m_license_year',
                    'w.ward_name as ward_no',
                    'pw.ward_name as permanent_ward_no',
                    'ew.ward_name as entity_ward_no',
                    'dp.string_parameter as m_display_type',
                    // 'il.string_parameter as m_installation_location',
                    'r.role_name as m_current_role'
                )
                ->where('adv_rejected_vehicles.id', $id)
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_rejected_vehicles.ulb_id')
                // ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'adv_rejected_vehicles.license_year')
                ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_rejected_vehicles.ward_id')
                ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_rejected_vehicles.permanent_ward_id')
                ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'adv_rejected_vehicles.ward_id')
                ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', 'adv_rejected_vehicles.display_type')
                // ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', 'adv_rejected_vehicles.installation_location')
                ->leftJoin('wf_roles as r', 'r.id', '=', 'adv_rejected_vehicles.current_roles')
                ->first();
        } elseif ($type == "Approve") {
            $details = DB::table('adv_vehicles')
                ->select(
                    'adv_vehicles.*',
                    'u.ulb_name',
                    // 'p.string_parameter as m_license_year',
                    'w.ward_name as ward_no',
                    'pw.ward_name as permanent_ward_no',
                    'ew.ward_name as entity_ward_no',
                    'dp.string_parameter as m_display_type',
                    // 'il.string_parameter as m_installation_location',
                    'r.role_name as m_current_role'
                )
                ->where('adv_vehicles.id', $id)
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_vehicles.ulb_id')
                // ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'adv_vehicles.license_year')
                ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_vehicles.ward_id')
                ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_vehicles.permanent_ward_id')
                ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'adv_vehicles.ward_id')
                ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', 'adv_vehicles.display_type')
                // ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', 'adv_vehicles.installation_location')
                ->leftJoin('wf_roles as r', 'r.id', '=', 'adv_vehicles.current_roles')
                ->first();
        }

        $details = json_decode(json_encode($details), true);            // Convert Std Class to Array

        //     $documents = DB::table('adv_active_selfadvetdocuments')
        //     ->select(
        //         'adv_active_selfadvetdocuments.*',
        //         'd.document_name as doc_type',
        //         DB::raw("CONCAT(adv_active_selfadvetdocuments.relative_path,'/',adv_active_selfadvetdocuments.doc_name) as doc_path")
        //     )
        //     ->leftJoin('ref_adv_document_mstrs as d', 'd.id', '=', 'adv_active_selfadvetdocuments.document_id')
        //     ->where(array('adv_active_selfadvetdocuments.temp_id'=> $id,'adv_active_selfadvetdocuments.workflow_id'=>$workflowId))
        //     ->get();
        // $details['documents'] = remove_null($documents->toArray());
        return $details;
    }

    /**
     * | Get Citizen Applied applications
     * | @param citizenId
     */
    public function listAppliedApplications($citizenId)
    {
        return AdvActiveVehicle::where('citizen_id', $citizenId)
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'father',
                'residence_address',
                'entity_name',
                'vehicle_no',
                'vehicle_name',
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


    public function entryZone($req)
    {
        $AdvActiveVehicle = AdvActiveVehicle::find($req->applicationId);        // Application ID
        $AdvActiveVehicle->zone = $req->zone;
        return $AdvActiveVehicle->save();
    }

    public function getVehicleNo($appId)
    {
        return AdvActiveVehicle::select('*')
            ->where('id', $appId)
            ->first();
    }



    public function getVehicleList($ulbId)
    {
        return AdvActiveVehicle::select('*')
            ->where('adv_active_vehicles.ulb_id', $ulbId);
    }

    /**
     * | Reupload Documents
     */
    public function reuploadDocument($req)
    {
        $docUpload = new DocumentUpload;
        $docDetails = WfActiveDocument::find($req->id);
        $relativePath = Config::get('constants.VEHICLE_ADVET.RELATIVE_PATH');

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
        $mWfActiveDocument = new WfActiveDocument();
        $mWfActiveDocument->postDocuments($a);
        $docDetails->current_status = '0';
        $docDetails->save();
        return $docDetails['active_id'];
    }

    /**
     * | Get Pending applications
     * | @param citizenId
     */
    public function allPendingList()
    {
        return AdvActiveVehicle::all();
    }

    /**
     * | Pending List For Report
     */
    public function pendingListForReport()
    {
        return AdvActiveVehicle::select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'ulb_id', DB::raw("'Active' as application_status"));
    }
}
