<?php

namespace App\Models\Advertisements;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use App\MicroServices\DocumentUpload;
use Illuminate\Support\Facades\DB;
use App\Traits\WorkflowTrait;
use Illuminate\Http\Request;


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
            'user_id' => $req->userId,
            'ulb_id' => $req->ulbId
        ];
        return $metaReqs;
    }



    
    /**
     * | Renewal Data Uses to Store data in DB
     */
    public function renewalReqs($req)
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
            'user_id' => $req->userId,
            'ulb_id' => $req->ulbId,
            'application_no' => $req->applicationNo,
        ];
        return $metaReqs;
    }

    /**
     * | Store function to apply(1)
     * | @param request 
     */
    public function addNew($req)
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
            'last_role_id' => $ulbWorkflows['initiator_role_id'],
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

        $mDocuments = $req->documents;
        $this->uploadDocument($agencyId, $mDocuments);

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


    public function uploadDocument($tempId, $documents)
    {
        collect($documents)->map(function ($doc) use ($tempId) {
            $metaReqs = array();
            $docUpload = new DocumentUpload;
            $mWfActiveDocument = new WfActiveDocument();
            $mAdvActiveAgency = new AdvActiveAgency();
            $relativePath = Config::get('constants.AGENCY_ADVET.RELATIVE_PATH');
            $getApplicationDtls = $mAdvActiveAgency->getAgencyDetails($tempId);
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

    public function getAgencyDetails($appId)
    {
        return AdvActiveAgency::select('*')
            ->where('id', $appId)
            ->first();
    }


    /**
     * | Get Application Details by id
     * | @param Agencies id
     */
    public function getDetailsById($id,$type)
    {
        $details = array();
        if ($type == "Active" || $type==NULL) {
            $details = DB::table('adv_active_agencies')
                ->select(
                    'adv_active_agencies.*',
                    'u.ulb_name',
                    'et.string_parameter as entityType',
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
                ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', 'adv_active_agencies.entity_type')
                    // ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'adv_active_agencies.license_year')
                    // ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_active_agencies.ward_id')
                    // ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_active_agencies.permanent_ward_id')
                    // ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'adv_active_agencies.entity_ward_id')
                    // ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', 'adv_active_agencies.display_type')
                    // ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', 'adv_active_agencies.installation_location')
                    // ->leftJoin('wf_roles as r', 'r.id', '=', 'adv_active_agencies.current_role_id')
                ->first();
        }elseif($type=="Reject"){
            $details = DB::table('adv_rejected_agencies')
            ->select(
                'adv_rejected_agencies.*',
                'u.ulb_name',
                'et.string_parameter as entityType',
            // 'p.string_parameter as m_license_year',
            // 'w.ward_name as ward_no',
            // 'pw.ward_name as permanent_ward_no',
            // 'ew.ward_name as entity_ward_no',
            // 'dp.string_parameter as m_display_type',
            // 'il.string_parameter as m_installation_location',
            // 'r.role_name as m_current_role'
            )
            ->where('adv_rejected_agencies.id', $id)
            ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_rejected_agencies.ulb_id')
            ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', 'adv_rejected_agencies.entity_type')
                // ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'adv_rejected_agencies.license_year')
                // ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_rejected_agencies.ward_id')
                // ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_rejected_agencies.permanent_ward_id')
                // ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'adv_rejected_agencies.entity_ward_id')
                // ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', 'adv_rejected_agencies.display_type')
                // ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', 'adv_rejected_agencies.installation_location')
                // ->leftJoin('wf_roles as r', 'r.id', '=', 'adv_rejected_agencies.current_role_id')
            ->first();
        }elseif($type=="Approve"){
            $details = DB::table('adv_agencies')
            ->select(
                'adv_agencies.*',
                'u.ulb_name',
                'et.string_parameter as entityType',
            // 'p.string_parameter as m_license_year',
            // 'w.ward_name as ward_no',
            // 'pw.ward_name as permanent_ward_no',
            // 'ew.ward_name as entity_ward_no',
            // 'dp.string_parameter as m_display_type',
            // 'il.string_parameter as m_installation_location',
            // 'r.role_name as m_current_role'
            )
            ->where('adv_agencies.id', $id)
            ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_agencies.ulb_id')
            ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', 'adv_agencies.entity_type')
                // ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'adv_agencies.license_year')
                // ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_agencies.ward_id')
                // ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_agencies.permanent_ward_id')
                // ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'adv_agencies.entity_ward_id')
                // ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', 'adv_agencies.display_type')
                // ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', 'adv_agencies.installation_location')
                // ->leftJoin('wf_roles as r', 'r.id', '=', 'adv_agencies.current_role_id')
            ->first();
        }

       $details=json_decode(json_encode($details), true);            // Convert Std Class to Array
    //    return $details['temp_id'];
        $directors = DB::table('adv_active_agencydirectors')
            ->select(
                'adv_active_agencydirectors.*',
                DB::raw("CONCAT(adv_active_agencydirectors.relative_path,'/',adv_active_agencydirectors.doc_name) as document_path")
            );
            if($type=='Active'){
                $directors = $directors->where('agency_id', $id);
            }
            elseif($type=='Reject'){
                $directors = $directors->where('agency_id', $details['temp_id']);
            }
            elseif($type=='Approve'){
                $directors = $directors->where('agency_id', $details['temp_id']);
            }
            $directors=$directors->get();
        $details['directors'] = remove_null($directors->toArray());
        return $details;
    }


     /**
     * | Get Application Inbox List by Role Ids
     * | @param roleIds $roleIds
     */
    public function listInbox($roleIds)
    {
        $inbox = DB::table('adv_active_agencies')
            ->select(
                'id',
                'application_no',
                'application_date',
                'entity_name',
                'address'
            )
            ->orderByDesc('id')
            ->whereIn('current_role_id', $roleIds)
            ->get();
        return $inbox;
    }

    
    /**
     * | Get Citizen Applied applications
     * | @param citizenId
     */
    public function listAppliedApplications($citizenId)
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

    
    /**
     * | Get Application Outbox List by Role Ids
     */
    public function listOutbox($roleIds)
    {
        $outbox = DB::table('adv_active_agencies')
            ->select(
                'id',
                'application_no',
                'application_date',
                'entity_name',
                'address',
            )
            ->orderByDesc('id')
            ->whereNotIn('current_role_id', $roleIds)
            ->get();
        return $outbox;
    }

    public function viewUploadedDocuments($id,$workflowId){
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
     * | Get Jsk Applied applications
     * | @param userId
     */
    public function getJSKApplications($userId)
    {
        return AdvActiveAgency::where('user_id', $userId)
            ->select(
                'id',
                'application_no',
                'application_date'
            )
            ->orderByDesc('id')
            ->get();
    }





     /**
     * | Agency Renewals
     * | @param request 
     */
    public function renewalAgency($req)
    {
        $directors = $req->directors;
        $bearerToken = $req->bearerToken();
        $metaReqs = $this->renewalReqs($req);

        $workflowId = Config::get('workflow-constants.AGENCY');
        $ulbWorkflows = $this->getUlbWorkflowId($bearerToken, $req->ulbId, $workflowId);        // Workflow Trait Function
        $ipAddress = getClientIpAddress();
        // $mApplicationNo = ['application_no' => 'AGENCY-' . random_int(100000, 999999)];                  // Generate Application No
        $ulbWorkflowReqs = [                                                                           // Workflow Meta Requests
            'workflow_id' => $ulbWorkflows['id'],
            'initiator_role_id' => $ulbWorkflows['initiator_role_id'],
            'last_role_id' => $ulbWorkflows['initiator_role_id'],
            'current_role_id' => $ulbWorkflows['initiator_role_id'],
            'finisher_role_id' => $ulbWorkflows['finisher_role_id'],
        ];

        $metaReqs = array_merge(
            [
                'ulb_id' => $req->ulbId,
                'citizen_id' => $req->citizenId,
                'application_date' => $this->_applicationDate,
                'ip_address' => $ipAddress,
                'renewal' => 1
            ],
            $this->renewalReqs($req),
            // $mApplicationNo,
            $ulbWorkflowReqs
        ); 

        $agencyDirector = new AdvActiveAgencydirector();
        $agencyId = AdvActiveAgency::create($metaReqs)->id;

        $mDocuments = $req->documents;
        $this->uploadDocument($agencyId, $mDocuments);

        // Store Director Details
        $mDocService = new DocumentUpload;
        $mRelativePath = Config::get('constants.AGENCY_ADVET.RELATIVE_PATH');
        collect($directors)->map(function ($director) use ($agencyId, $agencyDirector, $mDocService, $mRelativePath) {
            // $mDocRelativeName = "AADHAR";
            // $mImage = $director['aadhar'];
            // $mDocName = $mDocService->upload($mDocRelativeName, $mImage, $mRelativePath);
            $agencyDirector->store($director, $agencyId);       // Model function to store
        });

        // return $mApplicationNo['application_no'];
        return $req->applicationNo;
    }

    public function getAgencyNo($appId)
    {
        return AdvActiveAgency::select('*')
            ->where('id', $appId)
            ->first();
    }


    
    public function getAgencyList($ulbId)
    {
        return AdvActiveAgency::select('*')
            ->where('adv_active_agencies.ulb_id', $ulbId);
    }

     /**
     * | Reupload Documents
     */
    public function reuploadDocument($req){
        $docUpload = new DocumentUpload;
        $docDetails=WfActiveDocument::find($req->id);
        $relativePath = Config::get('constants.AGENCY_ADVET.RELATIVE_PATH');

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
