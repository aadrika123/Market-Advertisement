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

class MarActiveBanquteHall extends Model
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
            'hall_type' => $req->hallType,
            'holding_no' => $req->holdingNo,
            'trade_license_no' => $req->tradeLicenseNo,
            'longitude' => $req->longitude,
            'latitude' => $req->latitude,
            'organization_type' => $req->organizationType,
            'floor_area' => $req->floorArea,
            'land_deed_type'=>$req->landDeedType,


            
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
        ];
    }

    // Store Application Banqute-Marrige Hall(1)
    public function store($req)
    {
        $bearerToken = $req->bearerToken();
        $workflowId = Config::get('workflow-constants.BANQUTE_MARRIGE_HALL');                            // 350
        $ulbWorkflows = $this->getUlbWorkflowId($bearerToken, $req->ulbId, $workflowId);                 // Workflow Trait Function
        $ipAddress = getClientIpAddress();
        $mApplicationNo = ['application_no' => 'BMHALL-' . random_int(100000, 999999)];                  // Generate Application No
        $ulbWorkflowReqs = [                                                                             // Workflow Meta Requests
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
        $tempId = MarActiveBanquteHall::create($metaReqs)->id;
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
        $mMarActiveBanquteHall = new MarActiveBanquteHall();
        $relativePath = Config::get('constants.BANQUTE_MARRIGE_HALL.RELATIVE_PATH');

        collect($documents)->map(function ($doc) use ($tempId,$docUpload, $mWfActiveDocument,$mMarActiveBanquteHall,$relativePath) {
            $metaReqs = array();
            $getApplicationDtls = $mMarActiveBanquteHall->getApplicationDtls($tempId);
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
        
        return MarActiveBanquteHall::select('*')
            ->where('id', $appId)
            ->first();
    }

        /**
     * | Get Application Inbox List by Role Ids
     * | @param roleIds $roleIds
     */
    public function inbox($roleIds)
    {
        $inbox = DB::table('mar_active_banqute_halls')
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
        $outbox = DB::table('mar_active_banqute_halls')
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



}
