<?php

namespace App\Models\Markets;

use App\MicroServices\DocumentUpload;
use App\Models\Advertisements\WfActiveDocument;
use App\Traits\WorkflowTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarActiveHostel extends Model
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
            'hostel_type' => $req->hostelType,
            'holding_no' => $req->holdingNo,
            'trade_license_no' => $req->tradeLicenseNo,
            'longitude' => $req->longitude,
            'latitude' => $req->latitude,
            'organization_type' => $req->organizationType,
            'land_deed_type'=>$req->landDeedType,
            'mess_type'=>$req->messType,
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
            'rule'=>$req->rule,
            // 'is_school_college_univ'=>$req->isSchoolCollegeUniv,
            // 'school_college_univ_name'=>$req->schoolCollegeUnivName,
            'is_approve_by_govt'=>$req->isApproveByGovt,
            // 'govt_type'=>$req->govtType,
        ];
    }
     // Store Application Foe Hostel(1)
     public function addNew($req)
     {
         $bearerToken = $req->bearerToken();
            $workflowId = Config::get('workflow-constants.HOSTEL');                            // 350
         $ulbWorkflows = $this->getUlbWorkflowId($bearerToken, $req->ulbId, $workflowId);                 // Workflow Trait Function
         $ipAddress = getClientIpAddress();
         $mApplicationNo = ['application_no' => 'HOSTEL-' . random_int(100000, 999999)];                  // Generate Application No
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
                 'ip_address' => $ipAddress,
                 'application_type' => "New Apply"
             ],
             $this->metaReqs($req),
             $mApplicationNo,
             $ulbWorkflowReqs
         );                                                                                          // Add Relative Path as Request and Client Ip Address etc.
        $tempId = MarActiveHostel::create($metaReqs)->id;
         $this->uploadDocument($tempId, $mDocuments);
 
         return $mApplicationNo['application_no'];
     }


      // Renew Application For Hostel(1)
      public function renewApplication($req)
      {
          $bearerToken = $req->bearerToken();
          $workflowId = Config::get('workflow-constants.HOSTEL');                            // 350
          $ulbWorkflows = $this->getUlbWorkflowId($bearerToken, $req->ulbId, $workflowId);                 // Workflow Trait Function
          $ipAddress = getClientIpAddress();
          $mRenewNo = ['renew_no' => 'HOSTEL/REN-' . random_int(100000, 999999)];                  // Generate Application No
          $details=MarHostel::find($req->applicationId);                              // Find Previous Application No
          $mApplicationNo=['application_no'=>$details->application_no];
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
                  'ip_address' => $ipAddress,
                  'application_type'=>"Renew"
              ],
              $this->metaReqs($req),
              $mApplicationNo,
              $mRenewNo,
              $ulbWorkflowReqs
          );                                                                                          // Add Relative Path as Request and Client Ip Address etc.
         $tempId = MarActiveHostel::create($metaReqs)->id;
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
        $mMarActiveHostel = new MarActiveHostel();
        $relativePath = Config::get('constants.HOSTEL.RELATIVE_PATH');

        collect($documents)->map(function ($doc) use ($tempId,$docUpload, $mWfActiveDocument,$mMarActiveHostel,$relativePath) {
            $metaReqs = array();
            $getApplicationDtls = $mMarActiveHostel->getApplicationDtls($tempId);
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
        
        return MarActiveHostel::select('*')
            ->where('id', $appId)
            ->first();
    }

        /**
     * | Get Application Inbox List by Role Ids
     * | @param roleIds $roleIds
     */
    public function listInbox($roleIds)
    {
        $inbox = DB::table('mar_active_hostels')
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
                'application_type',
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
        $outbox = DB::table('mar_active_hostels')
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
                'application_type',
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
            $details = DB::table('mar_active_hostels')
                ->select(
                    'mar_active_hostels.*',
                    'u.ulb_name',
                    'p.string_parameter as m_license_year',
                    'w.string_parameter as ward_no',
                    'pw.string_parameter as permanent_ward_no',
                    'ew.string_parameter as entity_ward_no',
                    'ht.string_parameter as hosteltype',
                    'mt.string_parameter as messtype',
                    'ot.string_parameter as organizationtype'
                )
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'mar_active_hostels.ulb_id')
                ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'mar_active_hostels.license_year')
                ->leftJoin('ref_adv_paramstrings as ht', 'ht.id', '=', 'mar_active_hostels.hostel_type')
                ->leftJoin('ref_adv_paramstrings as mt', 'mt.id', '=', 'mar_active_hostels.mess_type')
                ->leftJoin('ref_adv_paramstrings as w', 'w.id', '=', 'mar_active_hostels.residential_ward_id')
                ->leftJoin('ref_adv_paramstrings as pw', 'pw.id', '=', 'mar_active_hostels.permanent_ward_id')
                ->leftJoin('ref_adv_paramstrings as ew', 'ew.id', '=', 'mar_active_hostels.entity_ward_id')
                ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', 'mar_active_hostels.organization_type')
                ->where('mar_active_hostels.id', $id)
                ->first();
        } elseif ($type == 'Reject') {
            $details = DB::table('mar_rejected_hostels')
                ->select(
                    'mar_rejected_hostels.*',
                    'u.ulb_name',
                    'p.string_parameter as m_license_year',
                    'w.string_parameter as ward_no',
                    'pw.string_parameter as permanent_ward_no',
                    'ew.string_parameter as entity_ward_no',
                    'ht.string_parameter as hosteltype',
                    'mt.string_parameter as messtype',
                    'ot.string_parameter as organizationtype'
                )
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'mar_rejected_hostels.ulb_id')
                ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'mar_rejected_hostels.license_year')
                ->leftJoin('ref_adv_paramstrings as ht', 'ht.id', '=', 'mar_rejected_hostels.hostel_type')
                ->leftJoin('ref_adv_paramstrings as mt', 'mt.id', '=', 'mar_rejected_hostels.mess_type')
                ->leftJoin('ref_adv_paramstrings as w', 'w.id', '=', 'mar_rejected_hostels.residential_ward_id')
                ->leftJoin('ref_adv_paramstrings as pw', 'pw.id', '=', 'mar_rejected_hostels.permanent_ward_id')
                ->leftJoin('ref_adv_paramstrings as ew', 'ew.id', '=', 'mar_rejected_hostels.entity_ward_id')
                ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', 'mar_rejected_hostels.organization_type')
                ->where('mar_rejected_hostels.id', $id)
                ->first();
        }elseif ($type == 'Approve'){
            $details = DB::table('mar_hostels')
                ->select(
                    'mar_hostels.*',
                    'u.ulb_name',
                    'p.string_parameter as m_license_year',
                    'w.string_parameter as ward_no',
                    'pw.string_parameter as permanent_ward_no',
                    'ew.string_parameter as entity_ward_no',
                    'ht.string_parameter as hosteltype',
                    'mt.string_parameter as messtype',
                    'ot.string_parameter as organizationtype'
                )
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'mar_hostels.ulb_id')
                ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'mar_hostels.license_year')
                ->leftJoin('ref_adv_paramstrings as ht', 'ht.id', '=', 'mar_hostels.hostel_type')
                ->leftJoin('ref_adv_paramstrings as mt', 'mt.id', '=', 'mar_hostels.mess_type')
                ->leftJoin('ref_adv_paramstrings as w', 'w.id', '=', 'mar_hostels.residential_ward_id')
                ->leftJoin('ref_adv_paramstrings as pw', 'pw.id', '=', 'mar_hostels.permanent_ward_id')
                ->leftJoin('ref_adv_paramstrings as ew', 'ew.id', '=', 'mar_hostels.entity_ward_id')
                ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', 'mar_hostels.organization_type')
                ->where('mar_hostels.id', $id)
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
        return MarActiveHostel::where('citizen_id', $citizenId)
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
                'doc_upload_status',
                'doc_verify_status',
                'application_type',
            )
            ->orderByDesc('id')
            ->get();
    }

        
    public function getHostelDetails($appId)
    {
        return MarActiveHostel::select('*')
            ->where('id', $appId)
            ->first();
    }

    
    public function getHostelList($ulbId)
    {
        return MarActiveHostel::select('*')
            ->where('mar_active_hostels.ulb_id', $ulbId);
    }

    /**
     * | Reupload Documents
     */
    public function reuploadDocument($req){
        $docUpload = new DocumentUpload;
        $docDetails=WfActiveDocument::find($req->id);
        $relativePath = Config::get('constants.HOSTEL.RELATIVE_PATH');

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
