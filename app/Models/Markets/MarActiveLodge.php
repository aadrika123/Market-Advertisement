<?php

namespace App\Models\Markets;

use App\MicroServices\DocumentUpload;
use App\Models\Advertisements\WfActiveDocument;
use App\Traits\WorkflowTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MarActiveLodge extends Model
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
            'lodge_type' => $req->lodgeType,
            'holding_no' => $req->holdingNo,
            'trade_license_no' => $req->tradeLicenseNo,
            'longitude' => $req->longitude,
            'latitude' => $req->latitude,
            'organization_type' => $req->organizationType,
            'land_deed_type'=>$req->landDeedType,
            'mess_type'=>$req->messType,
            'no_of_beds'=>$req->noOfBeds,
            'no_of_rooms'=>$req->noOfRooms,


            
            'water_supply_ype'=>$req->waterSupplyType,
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
        ];
    }
     // Store Application Foe Lodge(1)
     public function addNew($req)
     {
         $bearerToken = $req->bearerToken();
         $workflowId = Config::get('workflow-constants.LODGE');                            // 350
         $ulbWorkflows = $this->getUlbWorkflowId($bearerToken, $req->ulbId, $workflowId);                 // Workflow Trait Function
         $ipAddress = getClientIpAddress();
         $mApplicationNo = ['application_no' => 'LODGE-' . random_int(100000, 999999)];                  // Generate Application No
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
        $tempId = MarActiveLodge::create($metaReqs)->id;
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
        $mMarActiveLodge = new MarActiveLodge();
        $relativePath = Config::get('constants.LODGE.RELATIVE_PATH');

        collect($documents)->map(function ($doc) use ($tempId,$docUpload, $mWfActiveDocument,$mMarActiveLodge,$relativePath) {
            $metaReqs = array();
            $getApplicationDtls = $mMarActiveLodge->getApplicationDtls($tempId);
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
        
        return MarActiveLodge::select('*')
            ->where('id', $appId)
            ->first();
    }

            /**
     * | Get Application Inbox List by Role Ids
     * | @param roleIds $roleIds
     */
    public function listInbox($roleIds)
    {
        $inbox = DB::table('mar_active_lodges')
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
        $outbox = DB::table('mar_active_lodges')
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
     * | @param Application id
     */
    public function getDetailsById($id,$type=NULL)
    {
        $details = array();
        if ($type == 'Active' || $type == NULL) {
            $details = DB::table('mar_active_lodges')
                ->select(
                    'mar_active_lodges.*',
                    'u.ulb_name',
                    'p.string_parameter as m_license_year',
                    'w.string_parameter as ward_no',
                    'pw.string_parameter as permanent_ward_no',
                    'ew.string_parameter as entity_ward_no',
                    'lt.string_parameter as lodgetype',
                    'mt.string_parameter as messtype',
                    'ot.string_parameter as organizationtype'
                )
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'mar_active_lodges.ulb_id')
                ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'mar_active_lodges.license_year')
                ->leftJoin('ref_adv_paramstrings as lt', 'lt.id', '=', 'mar_active_lodges.lodge_type')
                ->leftJoin('ref_adv_paramstrings as mt', 'mt.id', '=', 'mar_active_lodges.mess_type')
                ->leftJoin('ref_adv_paramstrings as w', 'w.id', '=', 'mar_active_lodges.residential_ward_id')
                ->leftJoin('ref_adv_paramstrings as pw', 'pw.id', '=', 'mar_active_lodges.permanent_ward_id')
                ->leftJoin('ref_adv_paramstrings as ew', 'ew.id', '=', 'mar_active_lodges.entity_ward_id')
                ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', 'mar_active_lodges.organization_type')
                ->where('mar_active_lodges.id', $id)
                ->first();
        } elseif ($type == 'Reject') {
            $details = DB::table('mar_rejected_lodges')
                ->select(
                    'mar_rejected_lodges.*',
                    'u.ulb_name',
                    'p.string_parameter as m_license_year',
                    'w.string_parameter as ward_no',
                    'pw.string_parameter as permanent_ward_no',
                    'ew.string_parameter as entity_ward_no',
                    'lt.string_parameter as lodgetype',
                    'mt.string_parameter as messtype',
                    'ot.string_parameter as organizationtype'
                )
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'mar_rejected_lodges.ulb_id')
                ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'mar_rejected_lodges.license_year')
                ->leftJoin('ref_adv_paramstrings as lt', 'ht.id', '=', 'mar_rejected_lodges.lodge_type')
                ->leftJoin('ref_adv_paramstrings as mt', 'mt.id', '=', 'mar_rejected_lodges.mess_type')
                ->leftJoin('ref_adv_paramstrings as w', 'w.id', '=', 'mar_rejected_lodges.residential_ward_id')
                ->leftJoin('ref_adv_paramstrings as pw', 'pw.id', '=', 'mar_rejected_lodges.permanent_ward_id')
                ->leftJoin('ref_adv_paramstrings as ew', 'ew.id', '=', 'mar_rejected_lodges.entity_ward_id')
                ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', 'mar_rejected_lodges.organization_type')
                ->where('mar_rejected_lodges.id', $id)
                ->first();
        }elseif ($type == 'Approve'){
            $details = DB::table('mar_lodges')
                ->select(
                    'mar_lodges.*',
                    'u.ulb_name',
                    'p.string_parameter as m_license_year',
                    'w.string_parameter as ward_no',
                    'pw.string_parameter as permanent_ward_no',
                    'ew.string_parameter as entity_ward_no',
                    'lt.string_parameter as lodgetype',
                    'mt.string_parameter as messtype',
                    'ot.string_parameter as organizationtype'
                )
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'mar_lodges.ulb_id')
                ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'mar_lodges.license_year')
                ->leftJoin('ref_adv_paramstrings as ht', 'lt.id', '=', 'mar_lodges.lodge_type')
                ->leftJoin('ref_adv_paramstrings as mt', 'mt.id', '=', 'mar_lodges.mess_type')
                ->leftJoin('ref_adv_paramstrings as w', 'w.id', '=', 'mar_lodges.residential_ward_id')
                ->leftJoin('ref_adv_paramstrings as pw', 'pw.id', '=', 'mar_lodges.permanent_ward_id')
                ->leftJoin('ref_adv_paramstrings as ew', 'ew.id', '=', 'mar_lodges.entity_ward_id')
                ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', 'mar_lodges.organization_type')
                ->where('mar_lodges.id', $id)
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
        return MarActiveLodge::where('citizen_id', $citizenId)
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