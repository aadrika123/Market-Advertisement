<?php

namespace App\Models\Markets;

use App\MicroServices\DocumentUpload;
use App\Models\Advertisements\WfActiveDocument;
use App\Traits\WorkflowTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MarActiveDharamshala extends Model
{
    use HasFactory;
    
    use WorkflowTrait;

    protected $guarded = [];
    protected $_applicationDate;

    public function __construct()
    {
        $this->_applicationDate = Carbon::now()->format('Y-m-d');
    }
    public function metaReqs($req)
    {
        return [
            'applicant' => $req->applicantName,
            'license_year' => $req->licenseYear,
            'father' => $req->fatherName,
            'residential_address' => $req->residentialAddress,
            'residential_ward_id' => $req->residentialWardId,
            'permanent_address' => $req->permanentAddress,
            'permanent_ward_id' => $req->permanentWardId,
            'email' => $req->email,
            'mobile' => $req->mobile,
            'entity_name' => $req->entityName,
            'entity_address' => $req->entityAddress,
            'entity_ward_id' => $req->entityWardId,
            'holding_no' => $req->holdingNo,
            'trade_license_no' => $req->tradeLicenseNo,
            'longitude' => $req->longitude,
            'latitude' => $req->latitude,
            'organization_type' => $req->organizationType,
            'land_deed_type'=>$req->landDeedType,
            'no_of_beds'=>$req->noOfBeds,
            'no_of_rooms'=>$req->noOfRooms,


            
            'water_supply_type'=>$req->waterSupplyType,
            'electricity_type'=>$req->electricityType,
            'security_type'=>$req->securityType,
            'cctv_camera'=>$req->cctvCamera,
            'fire_extinguisher'=>$req->fireExtinguisher,
            'entry_gate'=>$req->entryGate,
            'exit_gate'=>$req->exitGate,
            'two_wheelers_parking'=>$req->twoWheelersParking,
            'four_wheelers_parking'=>$req->fourWheelersParking,
            'aadhar_card'=>$req->aadharCard,
            'pan_card'=>$req->panCard,
            'floor_area'=>$req->floorArea,
            'rule'=>$req->rule,
        ];
    }
     // Store Application Foe Dharamshala(1)
     public function addNew($req)
     {
         $bearerToken = $req->bearerToken();
         $workflowId = Config::get('workflow-constants.DHARAMSHALA');                            // 350
         $ulbWorkflows = $this->getUlbWorkflowId($bearerToken, $req->ulbId, $workflowId);                 // Workflow Trait Function
         $ipAddress = getClientIpAddress();
         $mApplicationNo = ['application_no' => 'DHARAMSHALA-' . random_int(100000, 999999)];                  // Generate Application No
         $ulbWorkflowReqs = [                                                                             // Workflow Meta Requests
             'workflow_id' => $ulbWorkflows['id'],
             'initiator_role_id' => $ulbWorkflows['initiator_role_id'],
             'current_role_id' => $ulbWorkflows['initiator_role_id'],
             'last_role_id' => $ulbWorkflows['initiator_role_id'],
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
        $tempId = MarActiveDharamshala::create($metaReqs)->id;
         $this->uploadDocument($tempId, $mDocuments);
 
         return $mApplicationNo['application_no'];
     }

      /**
     * upload Document By Citizen At the time of Registration
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadDocument($tempId, $documents)
    {
        $docUpload = new DocumentUpload;
        $mWfActiveDocument = new WfActiveDocument();
        $mMarActiveDharamshala = new MarActiveDharamshala();
        $relativePath = Config::get('constants.DHARAMSHALA.RELATIVE_PATH');

        collect($documents)->map(function ($doc) use ($tempId,$docUpload, $mWfActiveDocument,$mMarActiveDharamshala,$relativePath) {
            $metaReqs = array();
            $getApplicationDtls = $mMarActiveDharamshala->getApplicationDtls($tempId);
            $refImageName = $doc['docCode'];
            $refImageName = $getApplicationDtls->id . '-' . $refImageName;
            $documentImg = $doc['image'];
            $imageName = $docUpload->upload($refImageName, $documentImg, $relativePath);
            $metaReqs['moduleId'] = Config::get('workflow-constants.MARKET_MODULE_ID');
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

    
    public function getApplicationDtls($appId){
        
        return MarActiveDharamshala::select('*')
            ->where('id', $appId)
            ->first();
    }

        /**
     * | Get Application Inbox List by Role Ids
     * | @param roleIds $roleIds
     */
    public function listInbox($roleIds)
    {
        $inbox = DB::table('mar_active_dharamshalas')
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
    public function listOutbox($roleIds)
    {
        $outbox = DB::table('mar_active_dharamshalas')
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

    

    /**
     * | Get Application Details by id
     * | @param SelfAdvertisements id
     */
    public function getDetailsById($id,$type=NULL)
    {
        $details = array();
        if ($type == 'Active' || $type == NULL) {
            $details = DB::table('mar_active_dharamshalas')
                ->select(
                    'mar_active_dharamshalas.*',
                    'u.ulb_name',
                    'p.string_parameter as m_license_year',
                    'w.string_parameter as ward_no',
                    'pw.string_parameter as permanent_ward_no',
                    'ew.string_parameter as entity_ward_no',
                    'ot.string_parameter as organizationtype'
                )
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'mar_active_dharamshalas.ulb_id')
                ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'mar_active_dharamshalas.license_year')
                ->leftJoin('ref_adv_paramstrings as w', 'w.id', '=', 'mar_active_dharamshalas.residential_ward_id')
                ->leftJoin('ref_adv_paramstrings as pw', 'pw.id', '=', 'mar_active_dharamshalas.permanent_ward_id')
                ->leftJoin('ref_adv_paramstrings as ew', 'ew.id', '=', 'mar_active_dharamshalas.entity_ward_id')
                ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', 'mar_active_dharamshalas.organization_type')
                ->where('mar_active_dharamshalas.id', $id)
                ->first();
        } elseif ($type == 'Reject') {
            $details = DB::table('mar_rejected_dharamshalas')
                ->select(
                    'mar_rejected_dharamshalas.*',
                    'u.ulb_name',
                    'p.string_parameter as m_license_year',
                    'w.string_parameter as ward_no',
                    'pw.string_parameter as permanent_ward_no',
                    'ew.string_parameter as entity_ward_no',
                    'ot.string_parameter as organizationtype'
                )
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'mar_rejected_dharamshalas.ulb_id')
                ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'mar_rejected_dharamshalas.license_year')
                ->leftJoin('ref_adv_paramstrings as w', 'w.id', '=', 'mar_rejected_dharamshalas.residential_ward_id')
                ->leftJoin('ref_adv_paramstrings as pw', 'pw.id', '=', 'mar_rejected_dharamshalas.permanent_ward_id')
                ->leftJoin('ref_adv_paramstrings as ew', 'ew.id', '=', 'mar_rejected_dharamshalas.entity_ward_id')
                ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', 'mar_rejected_dharamshalas.organization_type')
                ->where('mar_rejected_dharamshalas.id', $id)
                ->first();
        }elseif ($type == 'Approve'){
            $details = DB::table('mar_dharamshalas')
                ->select(
                    'mar_dharamshalas.*',
                    'u.ulb_name',
                    'p.string_parameter as m_license_year',
                    'w.string_parameter as ward_no',
                    'pw.string_parameter as permanent_ward_no',
                    'ew.string_parameter as entity_ward_no',
                    'ot.string_parameter as organizationtype'
                )
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'mar_dharamshalas.ulb_id')
                ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'mar_dharamshalas.license_year')
                ->leftJoin('ref_adv_paramstrings as w', 'w.id', '=', 'mar_dharamshalas.residential_ward_id')
                ->leftJoin('ref_adv_paramstrings as pw', 'pw.id', '=', 'mar_dharamshalas.permanent_ward_id')
                ->leftJoin('ref_adv_paramstrings as ew', 'ew.id', '=', 'mar_dharamshalas.entity_ward_id')
                ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', 'mar_dharamshalas.organization_type')
                ->where('mar_dharamshalas.id', $id)
                ->first();
        }
        return json_decode(json_encode($details), true);            // Convert Std Class to Array
    }

        /**
     * | Get Citizen Applied applications
     * | @param citizenId
     */
    public function listAppliedApplications($citizenId)
    {
        return MarActiveDharamshala::where('citizen_id', $citizenId)
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
            ->get();
    }

    public function getDharamshalaDetails($appId)
    {
        return MarActiveDharamshala::select('*')
            ->where('id', $appId)
            ->first();
    }

    public function getDharamshalaList($ulbId)
    {
        return MarActiveDharamshala::select('*')
            ->where('mar_active_dharamshalas.ulb_id', $ulbId);
    }

        

    /**
     * | Reupload Documents
     */
    public function reuploadDocument($req){
        $docUpload = new DocumentUpload;
        $docDetails=WfActiveDocument::find($req->id);
        $relativePath = Config::get('constants.DHARAMSHALA.RELATIVE_PATH');

        $refImageName = $docDetails['doc_code'];
        $refImageName = $docDetails['active_id'] . '-' . $refImageName;
        $documentImg = $req->image;
        $imageName = $docUpload->upload($refImageName, $documentImg, $relativePath);

        $metaReqs['moduleId'] = Config::get('workflow-constants.MARKET_MODULE_ID');
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
