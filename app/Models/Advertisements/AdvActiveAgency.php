<?php

namespace App\Models\Advertisements;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use App\MicroServices\DocumentUpload;
use Illuminate\Support\Facades\DB;
use App\Traits\WorkflowTrait;


class AdvActiveAgency extends Model
{
    use HasFactory;
    
    use WorkflowTrait;
    protected $guarded = [];
    protected $_applicationDate;

    // Initializing construction
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
            'application_date' => $this->_applicationDate,
            'entity_type' => $req->entityType,
            'entity_name' => $req->entityName,
            'address' => $req->address,
            'mobile_no' => $req->mobileNo,
            'telephone' => $req->officeTelephone,
            'fax' => $req->fax,
            'email' => $req->email,
            'pan_no' => $req->panNo,
            'gst_no' => $req->gstNo,
            'blacklisted' => $req->blacklisted,
            'pending_court_case' => $req->pendingCourtCase,
            'pending_amount' => $req->pendingAmount,
            'citizen_id' => $req->citizenId,
            'ulb_id' => $req->ulbId
        ];
        return $metaReqs;
    }

    /**
     * | Store function to apply(1)
     * | @param request 
     */
    public function store($req)
    {
        $directors = $req->directors;
        $bearerToken = $req->bearerToken();
        $metaReqs = $this->metaReqs($req);

        $workflowId = Config::get('workflow-constants.AGENCY');
        $ulbWorkflows = $this->getUlbWorkflowId($bearerToken, $req->ulbId, $workflowId);        // Workflow Trait Function
        $ipAddress = getClientIpAddress();
        $mApplicationNo = ['application_no' => 'AGENCY-' . random_int(100000, 999999)];                  // Generate Application No
        $ulbWorkflowReqs = [                                                                           // Workflow Meta Requests
            'workflow_id' => $ulbWorkflows['id'],
            'initiator_role_id' => $ulbWorkflows['initiator_role_id'],
            'current_role_id' => $ulbWorkflows['initiator_role_id'],
            'finisher_role_id' => $ulbWorkflows['finisher_role_id'],
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

        $agencyDirector = new AdvActiveAgencydirector();
        $agencyId = AdvActiveAgency::create($metaReqs)->id;

        // Store Director Details
        $mDocService = new DocumentUpload;
        $mRelativePath = Config::get('constants.AGENCY_ADVET.RELATIVE_PATH');
        collect($directors)->map(function ($director) use ($agencyId, $agencyDirector, $mDocService, $mRelativePath) {
            // $mDocRelativeName = "AADHAR";
            // $mImage = $director['aadhar'];
            // $mDocName = $mDocService->upload($mDocRelativeName, $mImage, $mRelativePath);
            $agencyDirector->store($director, $agencyId);       // Model function to store
        });

        return $mApplicationNo['application_no'];
    }


    /**
     * | Get Application Details by id
     * | @param Agencies id
     */
    public function details($id)
    {
        $details = array();
        $details = DB::table('adv_active_agencies')
            ->select(
                'adv_active_agencies.*',
                'u.ulb_name',
                // 'p.string_parameter as m_license_year',
                // 'w.ward_name as ward_no',
                // 'pw.ward_name as permanent_ward_no',
                // 'ew.ward_name as entity_ward_no',
                // 'dp.string_parameter as m_display_type',
                // 'il.string_parameter as m_installation_location',
                // 'r.role_name as m_current_role'
            )
            ->where('adv_active_agencies.id', $id)
            ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_active_agencies.ulb_id')
            // ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'adv_active_agencies.license_year')
            // ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_active_agencies.ward_id')
            // ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_active_agencies.permanent_ward_id')
            // ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'adv_active_agencies.entity_ward_id')
            // ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', 'adv_active_agencies.display_type')
            // ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', 'adv_active_agencies.installation_location')
            // ->leftJoin('wf_roles as r', 'r.id', '=', 'adv_active_agencies.current_role_id')
            ->first();

        $details = json_decode(json_encode($details), true);            // Convert Std Class to Array
        $directors = DB::table('adv_active_agencydirectors')
            ->select(
                'adv_active_agencydirectors.*',
                DB::raw("CONCAT(adv_active_agencydirectors.relative_path,'/',adv_active_agencydirectors.doc_name) as document_path")
            )
            ->where('agency_id', $id)
            ->get();
        $details['directors'] = remove_null($directors->toArray());
        return $details;
    }

    
    /**
     * | Get Citizen Applied applications
     * | @param citizenId
     */
    public function getCitizenApplications($citizenId)
    {
        return AdvActiveAgency::where('citizen_id', $citizenId)
            ->select(
                'id',
                'application_no',
                'application_date',
                'entity_name',
                'address',
            )
            ->orderByDesc('id')
            ->get();
    }
}
