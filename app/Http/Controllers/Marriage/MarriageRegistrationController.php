<?php

namespace App\Http\Controllers\Marriage;

use App\Http\Controllers\Controller;
use App\MicroServices\DocumentUpload;
use App\MicroServices\IdGenerator\PrefixIdGenerator;
use App\Models\Advertisements\RefRequiredDocument;
use App\Models\Advertisements\WfActiveDocument;
use App\Models\Marriage\MarriageActiveRegistration;
use App\Models\Workflows\WfRoleusermap;
use App\Models\Workflows\WfWorkflow;
use App\Models\Workflows\WfWorkflowrolemap;
use App\Models\Workflows\WorkflowTrack;
use App\Traits\Marriage\MarriageTrait;
use App\Traits\Workflow\Workflow;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use ReflectionFunctionAbstract;

class MarriageRegistrationController extends Controller
{

    use Workflow;
    use MarriageTrait;

    private $_workflowMasterId;
    private $_marriageParamId;
    private $_marriageModuleId;
    private $_userType;
    private $_marriageWfRoles;
    private $_docReqCatagory;
    private $_dbKey;
    private $_fee;
    private $_applicationType;
    private $_applyMode;
    private $_tranType;
    private $_registrarRoleId;
    # Class constructer 
    public function __construct()
    {
        $this->_workflowMasterId    = Config::get("marriage.WORKFLOW_MASTER_ID");
        $this->_marriageParamId     = Config::get("marriage.PARAM_ID");
        $this->_marriageModuleId    = Config::get('marriage.MODULE_ID');
        $this->_userType            = Config::get("marriage.REF_USER_TYPE");
        $this->_registrarRoleId     = Config::get("marriage.REGISTRAR_ROLE_ID");
        $this->_docReqCatagory      = Config::get("marriage.DOC_REQ_CATAGORY");
        $this->_dbKey               = Config::get("marriage.DB_KEYS");
        $this->_fee                 = Config::get("marriage.FEE_CHARGES");
        $this->_applicationType     = Config::get("marriage.APPLICATION_TYPE");
        $this->_applyMode           = Config::get("marriage.APPLY_MODE");
        $this->_tranType            = Config::get("marriage.TRANSACTION_TYPE");
    }
    /**
     * | Apply for marriage registration
     */
    public function apply(Request $req)
    {
        try {
            $mWfWorkflow = new WfWorkflow();
            $mMarriageActiveRegistration = new MarriageActiveRegistration();
            $mWfRoleusermaps = new WfRoleusermap();
            $user                       = authUser();
            $ulbId                      = $user->ulb_id ?? $req->ulbId;
            $userType                   = $user->user_type;
            $workflowMasterId           = $this->_workflowMasterId;
            $marriageParamId            = $this->_marriageParamId;
            $feeId                      = $this->_fee;
            $registrarRoleId            = $this->_registrarRoleId;

            # Get initiator and finisher for the workflow 
            $ulbWorkflowId = $mWfWorkflow->getulbWorkflowId($workflowMasterId, $ulbId);
            if (!$ulbWorkflowId) {
                throw new Exception("Respective Ulb is not maped to 'marriage Registration' Workflow!");
            }
            $registrationCharges = 100;
            $refInitiatorRoleId  = $this->getInitiatorId($ulbWorkflowId->id);
            $refFinisherRoleId   = $this->getFinisherId($ulbWorkflowId->id);
            $mreqs = [
                "roleId" => $registrarRoleId,
                "ulbId"  => $ulbId
            ];
            $registrarId         = $mWfRoleusermaps->getUserId($mreqs);
            $finisherRoleId      = collect(DB::select($refFinisherRoleId))->first();
            $initiatorRoleId     = collect(DB::select($refInitiatorRoleId))->first();
            if ($userType == 'Citizen') {
                $initiatorRoleId = collect($initiatorRoleId)['forward_role_id'];         // Send to DA in Case of Citizen
                $userId = null;
                $citizenId = $user->id;
            }

            $idGeneration = new PrefixIdGenerator($marriageParamId, $ulbId);
            $marriageApplicationNo = $idGeneration->generate();
            $refData = [
                "finisherRoleId"    => collect($finisherRoleId)['role_id'],
                "initiatorRoleId"   => ($initiatorRoleId),
                // "initiatorRoleId"   => collect($initiatorRoleId)['role_id'],
                "workflowId"        => $ulbWorkflowId->id,
                "applicationNo"     => $marriageApplicationNo,
                "userId"            => $userId,
                "citizenId"         => $citizenId,
                "registrarId"       => $registrarId->user_id,
            ];
            $req->merge($refData);

            # Save active details 
            $applicationDetails = $mMarriageActiveRegistration->saveRegistration($req, $user);

            $returnData = [
                "id" => $applicationDetails['id'],
                "applicationNo" => $applicationDetails['applicationNo'],
            ];
            return responseMsgs(true, "Marriage Registration Application Submitted!", $returnData, "", "01", ".ms", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }

    /**
     * | Get Doc List
     */
    public function getDocList(Request $req)
    {
        $req->validate([
            'applicationId' => 'required|numeric'
        ]);
        try {
            $mMarriageActiveRegistration = new MarriageActiveRegistration();
            $applicationId               = $req->applicationId;

            $refMarriageApplication = $mMarriageActiveRegistration->getApplicationById($applicationId);                      // Get Marriage Details
            if (is_null($refMarriageApplication)) {
                throw new Exception("Application Not Found for respective ($applicationId) id!");
            }

            $filterDocs = $this->getMarriageDocLists($refMarriageApplication);
            if (!empty($filterDocs))
                $totalDocLists['listDocs'] = $this->filterDocument($filterDocs, $refMarriageApplication);                                     // function(1.2)
            else
                $totalDocLists['listDocs'] = [];
            // $totalDocLists = collect($document);
            // $totalDocLists['docUploadStatus']   = $refMarriageApplication->doc_upload_status;
            // $totalDocLists['docVerifyStatus']   = $refMarriageApplication->doc_verify_status;
            // $totalDocLists['ApplicationNo']     = $refMarriageApplication->application_no;
            return responseMsgs(true, "", remove_null($totalDocLists), "010203", "", "", 'POST', "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010203", "1.0", "", 'POST', "");
        }
    }

    /**
     *  | Filtering
     */
    public function filterDocument($documentList, $refSafs, $witnessId = null)
    {
        $mWfActiveDocument = new WfActiveDocument();
        $safId = $refSafs->id;
        $workflowId = $refSafs->workflow_id;
        $moduleId = 10;
        $uploadedDocs = $mWfActiveDocument->getDocByRefIds($safId, $workflowId, $moduleId);
        $explodeDocs = collect(explode('#', $documentList));

        $filteredDocs = $explodeDocs->map(function ($explodeDoc) use ($uploadedDocs, $witnessId, $refSafs) {
            $document = explode(',', $explodeDoc);
            $key = array_shift($document);
            $label = array_shift($document);
            $documents = collect();

            collect($document)->map(function ($item) use ($uploadedDocs, $documents, $witnessId, $refSafs) {
                $uploadedDoc = $uploadedDocs->where('doc_code', $item)
                    ->where('Witness_dtl_id', $witnessId)
                    ->first();

                if ($uploadedDoc) {
                    $response = [
                        "uploadedDocId" => $uploadedDoc->id ?? "",
                        "documentCode" => $item,
                        "WitnessId" => $uploadedDoc->Witness_dtl_id ?? "",
                        "docPath" => $uploadedDoc->doc_path ?? "",
                        "verifyStatus" => $refSafs->payment_status == 1 ? ($uploadedDoc->verify_status ?? "") : 0,
                        "remarks" => $uploadedDoc->remarks ?? "",
                    ];
                    $documents->push($response);
                }
            });
            $reqDoc['docType'] = $key;
            $reqDoc['docName'] = substr($label, 1, -1);

            // Check back to citizen status
            $uploadedDocument = $documents->sortByDesc('uploadedDocId')->first();                           // Get Last Uploaded Document

            if (collect($uploadedDocument)->isNotEmpty() && $uploadedDocument['verifyStatus'] == 2) {
                $reqDoc['btcStatus'] = true;
            } else
                $reqDoc['btcStatus'] = false;
            $reqDoc['uploadedDoc'] = $documents->sortByDesc('uploadedDocId')->first();                      // Get Last Uploaded Document

            $reqDoc['masters'] = collect($document)->map(function ($doc) use ($uploadedDocs, $refSafs) {
                $uploadedDoc = $uploadedDocs->where('doc_code', $doc)->first();
                $strLower = strtolower($doc);
                $strReplace = str_replace('_', ' ', $strLower);
                $arr = [
                    "documentCode" => $doc,
                    "docVal" => ucwords($strReplace),
                    "uploadedDoc" => $uploadedDoc->doc_path ?? "",
                    "uploadedDocId" => $uploadedDoc->id ?? "",
                    "verifyStatus'" => $refSafs->payment_status == 1 ? ($uploadedDoc->verify_status ?? "") : 0,
                    "remarks" => $uploadedDoc->remarks ?? "",
                ];
                return $arr;
            });
            return $reqDoc;
        });
        return $filteredDocs;
    }

    /**
     * | Get Doc List
     */
    public function getMarriageDocLists($refApplication)
    {
        $mRefReqDocs = new RefRequiredDocument();
        $moduleId = 10;
        $documentList = $mRefReqDocs->getDocsByDocCode($moduleId, "MARRIAGE_REQUIRED_DOC")->requirements;

        if ($refApplication->is_bpl == true) {
            $documentList .= $mRefReqDocs->getDocsByDocCode($moduleId, "BPL_CATEGORY")->requirements;
        }

        //GROOM PASSPORT
        if ($refApplication->groom_nationality == 'NRI') {
            $documentList .= $mRefReqDocs->getDocsByDocCode($moduleId, "GROOM_PASSPORT")->requirements;
        }
        //BRIDE PASSPORT
        if ($refApplication->bride_nationality == 'NRI') {
            $documentList .= $mRefReqDocs->getDocsByDocCode($moduleId, "BRIDE_PASSPORT")->requirements;
        }
        return $documentList;
    }

    /**
     * | Doc Upload
     */
    public function uploadDocument(Request $req)
    {
        $req->validate([
            "applicationId" => "required|numeric",
            "document" => "required|mimes:pdf,jpeg,png,jpg",
            "docCode" => "required",
            "WitnessId" => "nullable|numeric"
        ]);
        $extention = $req->document->getClientOriginalExtension();
        $req->validate([
            'document' => $extention == 'pdf' ? 'max:10240' : 'max:1024',
        ]);

        try {
            $metaReqs = array();
            $docUpload = new DocumentUpload;
            $mWfActiveDocument = new WfActiveDocument();
            $mMarriageActiveRegistration = new MarriageActiveRegistration();
            $relativePath = Config::get('marriage.RELATIVE_PATH');
            $marriageRegitrationDtl = $mMarriageActiveRegistration->getApplicationById($req->applicationId);
            $refImageName = $req->docCode;
            $refImageName = $marriageRegitrationDtl->id . '-' . $refImageName;
            $document = $req->document;
            $imageName = $docUpload->upload($refImageName, $document, $relativePath);

            $metaReqs['moduleId']   = 10;
            $metaReqs['activeId']   = $marriageRegitrationDtl->id;
            $metaReqs['workflowId'] = $marriageRegitrationDtl->workflow_id;
            $metaReqs['ulbId']      = $marriageRegitrationDtl->ulb_id;
            $metaReqs['relativePath'] = $relativePath;
            $metaReqs['document']     = $imageName;
            $metaReqs['docCode']      = $req->docCode;
            $metaReqs['WitnessDtlId']   = $marriageRegitrationDtl->prop_Witness_id;

            $metaReqs = new Request($metaReqs);
            $mWfActiveDocument->postDocuments($metaReqs);

            // $docUploadStatus = $this->checkFullDocUpload($req->applicationId);
            // if ($docUploadStatus == 1) {                                        // Doc Upload Status Update
            //     $marriageRegitrationDtl->doc_upload_status = 1;
            //     if ($marriageRegitrationDtl->parked == true)                                // Case of Back to Citizen
            //         $marriageRegitrationDtl->parked = false;

            //     $marriageRegitrationDtl->save();
            // }
            return responseMsgs(true, "Document Uploadation Successful", "", "010201", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Registrar Inbox
     */
    public function inbox(Request $req)
    {
        try {
            $userId = authUser()->id;
            $ulbId = authUser()->ulb_id;
            $mWfWorkflowRoleMaps = new WfWorkflowrolemap();
            $perPage = $req->perPage ?? 10;

            $roleId = $this->getRoleIdByUserId($userId)->pluck('wf_role_id');
            $workflowIds = $mWfWorkflowRoleMaps->getWfByRoleId($roleId)->pluck('workflow_id');

            $list = MarriageActiveRegistration::whereIn('workflow_id', $workflowIds)
                ->where('marriage_active_registrations.ulb_id', $ulbId)
                // ->whereIn('marriage_active_registrations.current_role', $roleId)
                ->orderByDesc('marriage_active_registrations.id')
                ->get();

            return responseMsgs(true, "", remove_null($list), '010805', '01', responseTime(), 'Post', '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    /**
     * | Get details by id
     */
    public function details(Request $req)
    {
        $req->validate([
            'applicationId' => 'required'
        ]);

        try {
            $details = array();
            $mMarriageActiveRegistration = new MarriageActiveRegistration();
            // $mWorkflowTracks = new WorkflowTrack();
            // $mCustomDetails = new CustomDetail();
            // $mForwardBackward = new WorkflowMap();
            $details = $mMarriageActiveRegistration->getApplicationById($req->applicationId);
            if (!$details)
                throw new Exception("Application Not Found");
            $witnessDetails = array();

            for ($i = 0; $i < 3; $i++) {
                $index = $i + 1;
                $name = "witness$index" . "_name";
                $mobile = "witness$index" . "_mobile_no";
                $address = "witness$index" . "_residential_address";
                $witnessDetails[$i]['withnessName'] = $details->$name;
                $witnessDetails[$i]['withnessMobile'] = $details->$mobile;
                $witnessDetails[$i]['withnessAddress'] = $details->$address;
            }
            if (!$details)
                throw new Exception("Application Not Found for this id");

            // Data Array
            $marriageDetails = $this->generateMarriageDetails($details);         // (Marriage Details) Trait function to get Marriage Details
            $marriageElement = [
                'headerTitle' => "Marriage Details",
                "data" => $marriageDetails
            ];

            $brideDetails = $this->generateBrideDetails($details);   // (Property Details) Trait function to get Property Details
            $brideElement = [
                'headerTitle' => "Bride Details",
                'data' => $brideDetails
            ];

            $groomDetails = $this->generateGroomDetails($details);   // (Property Details) Trait function to get Property Details
            $groomElement = [
                'headerTitle' => "Groom Details",
                'data' => $groomDetails
            ];

            $groomElement = [
                'headerTitle' => "Groom Details",
                'data' => $groomDetails
            ];

            $fullDetailsData['application_no'] = $details->application_no;
            $fullDetailsData['apply_date'] = $details->created_at->format('d-m-Y');
            $fullDetailsData['fullDetailsData']['dataArray'] = new Collection([$marriageElement, $brideElement, $groomElement]);

            $witnessDetails = $this->generateWitnessDetails($witnessDetails);   // (Property Details) Trait function to get Property Details

            // Table Array
            $witnessElement = [
                'headerTitle' => 'Witness Details',
                'tableHead' => ["#", "Witness Name", "Witness Mobile No", "Address"],
                'tableData' => $witnessDetails
            ];

            $fullDetailsData['fullDetailsData']['tableArray'] = new Collection([$witnessElement]);
            // Card Details
            $cardElement = $this->generateCardDtls($details);
            $fullDetailsData['fullDetailsData']['cardArray'] = $cardElement;

            // $levelComment = $mWorkflowTracks->getTracksByRefId($mRefTable, $req->applicationId);
            // $fullDetailsData['levelComment'] = $levelComment;

            // $citizenComment = $mWorkflowTracks->getCitizenTracks($mRefTable, $req->applicationId, $details->user_id);
            // $fullDetailsData['citizenComment'] = $citizenComment;

            $metaReqs['customFor'] = 'MARRIAGE';
            $metaReqs['wfRoleId'] = $details->current_role;
            $metaReqs['workflowId'] = $details->workflow_id;
            $metaReqs['lastRoleId'] = $details->last_role_id;
            $req->request->add($metaReqs);

            // $forwardBackward = $mForwardBackward->getRoleDetails($req);
            // $fullDetailsData['roleDetails'] = collect($forwardBackward)['original']['data'];

            $fullDetailsData['timelineData'] = collect($req);

            // $custom = $mCustomDetails->getCustomDetails($req);
            // $fullDetailsData['departmentalPost'] = collect($custom)['original']['data'];

            return responseMsgs(true, "Concession Details", remove_null($fullDetailsData), '010705', '01', '', 'POST', '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", '010705', '01', '', 'POST', '');
        }
    }

    /**
     * | Static Details
     */
    public function staticDetails(Request $req)
    {
        $req->validate([
            "applicationId" => "required|numeric"
        ]);

        try {
            $registrationDtl = MarriageActiveRegistration::find($req->applicationId);
            if (!$registrationDtl)
                throw new Exception('No Data Found');
            if (isset($registrationDtl->appointment_date))
                $registrationDtl->appointment_status = true;
            else
                $registrationDtl->appointment_status = false;

            return responseMsgs(true, "", remove_null($registrationDtl), "010203", "", responseTime(), 'POST', "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010203", "1.0", responseTime(), 'POST', "");
        }
    }

    /**
     * | listApplications
     */
    public function listApplications(Request $req)
    {
        try {
            $registrationDtl = MarriageActiveRegistration::where('citizen_id', authUser()->id)->get();
            if (!$registrationDtl)
                throw new Exception('No Data Found');

            return responseMsgs(true, "", remove_null($registrationDtl), "010203", "", responseTime(), 'POST', "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010203", "1.0", responseTime(), 'POST', "");
        }
    }

    // Post Next Level Application
    // public function postNextLevel(Request $req)
    // {
    //     $wfLevels = Config::get('PropertyConstaint.CONCESSION-LABEL');
    //     $req->validate([
    //         'applicationId' => 'required|integer',
    //         'action' => 'required|In:forward,backward',
    //     ]);
    //     try {
    //         $userId = authUser()->id;
    //         $track = new WorkflowTrack();
    //         $mWfWorkflows = new WfWorkflow();
    //         $mWfRoleMaps = new WfWorkflowrolemap();
    //         $marriageeDtl = MarriageActiveRegistration::find($req->applicationId);
    //         $senderRoleId = $concession->current_role;
    //         $ulbWorkflowId = $concession->workflow_id;
    //         $req->validate([
    //             'comment' => $senderRoleId == $wfLevels['BO'] ? 'nullable' : 'required',
    //         ]);

    //         $ulbWorkflowMaps = $mWfWorkflows->getWfDetails($ulbWorkflowId);
    //         $roleMapsReqs = new Request([
    //             'workflowId' => $ulbWorkflowMaps->id,
    //             'roleId' => $senderRoleId
    //         ]);
    //         $forwardBackwardIds = $mWfRoleMaps->getWfBackForwardIds($roleMapsReqs);

    //         DB::beginTransaction();
    //         if ($req->action == 'forward') {
    //             $this->checkPostCondition($senderRoleId, $wfLevels, $concession);          // Check Post Next level condition
    //             $concession->current_role = $forwardBackwardIds->forward_role_id;
    //             $concession->last_role_id =  $forwardBackwardIds->forward_role_id;         // Update Last Role Id
    //             $metaReqs['verificationStatus'] = 1;
    //             $metaReqs['receiverRoleId'] = $forwardBackwardIds->forward_role_id;
    //         }

    //         if ($req->action == 'backward') {
    //             $concession->current_role = $forwardBackwardIds->backward_role_id;
    //             $metaReqs['verificationStatus'] = 0;
    //             $metaReqs['receiverRoleId'] = $forwardBackwardIds->backward_role_id;
    //         }
    //         $concession->save();

    //         $metaReqs['moduleId'] = Config::get('module-constants.PROPERTY_MODULE_ID');
    //         $metaReqs['workflowId'] = $concession->workflow_id;
    //         $metaReqs['refTableDotId'] = 'prop_active_concessions.id';
    //         $metaReqs['refTableIdValue'] = $req->applicationId;
    //         $metaReqs['senderRoleId'] = $senderRoleId;
    //         $metaReqs['user_id'] = $userId;

    //         $req->request->add($metaReqs);
    //         $track->saveTrack($req);

    //         // Updation of Received Date
    //         $preWorkflowReq = [
    //             'workflowId' => $concession->workflow_id,
    //             'refTableDotId' => 'prop_active_concessions.id',
    //             'refTableIdValue' => $req->applicationId,
    //             'receiverRoleId' => $senderRoleId
    //         ];
    //         $previousWorkflowTrack = $track->getWfTrackByRefId($preWorkflowReq);
    //         $previousWorkflowTrack->update([
    //             'forward_date' => $this->_todayDate->format('Y-m-d'),
    //             'forward_time' => $this->_todayDate->format('H:i:s')
    //         ]);

    //         DB::commit();
    //         return responseMsgs(true, "Successfully Forwarded The Application!!", "", "", '010708', '01', '', 'Post', '');
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return responseMsgs(false, $e->getMessage(), "");
    //     }
    // }

    /**
     * | Concession Application Approval or Rejected 
     * | @param req
     * | Status-closed
     * | Query Costing-376 ms
     * | Rating-2
     * | Status-Closed
     */
    // public function approvalRejection(Request $req)
    // {
    //     try {
    //         $req->validate([
    //             "applicationId" => "required",
    //             "status" => "required"
    //         ]);
    //         // Check if the Current User is Finisher or Not
    //         $mWfRoleUsermap = new WfRoleusermap();
    //         $mMarriageActiveRegistration = new MarriageActiveRegistration();
    //         $track = new WorkflowTrack();

    //         $details = $mMarriageActiveRegistration->getApplicationById($req->applicationId);

    //         $userId = authUser()->id;
    //         $getFinisherQuery = $this->getFinisherId($req->workflowId);                                 // Get Finisher using Trait
    //         $refGetFinisher = collect(DB::select($getFinisherQuery))->first();

    //         $workflowId = $details->workflow_id;
    //         $senderRoleId = $details->current_role;
    //         $getRoleReq = new Request([                                                 // make request to get role id of the user
    //             'userId' => $userId,
    //             'workflowId' => $workflowId
    //         ]);
    //         $readRoleDtls = $mWfRoleUsermap->getRoleByUserWfId($getRoleReq);
    //         $roleId = $readRoleDtls->wf_role_id;

    //         if ($refGetFinisher->role_id != $roleId) {
    //             return responseMsgs(false, "Forbidden Access", "");
    //         }
    //         DB::beginTransaction();

    //         // Approval
    //         if ($req->status == 1) {
    //             // Concession Application replication

    //             $approvedConcession = $details->replicate();
    //             $approvedConcession->setTable('prop_concessions');
    //             $approvedConcession->id = $details->id;
    //             $approvedConcession->save();
    //             $details->delete();

    //             $approvedOwners = $propOwners->replicate();
    //             $approvedOwners->setTable('log_prop_owners');
    //             $approvedOwners->id = $propOwners->id;
    //             $approvedOwners->save();

    //             $this->updateOwner($propOwners, $details);

    //             $msg =  "Application Successfully Approved !!";
    //             $metaReqs['verificationStatus'] = 1;
    //         }
    //         // Rejection
    //         if ($req->status == 0) {
    //             // Concession Application replication
    //             $details = Propdetails::query()
    //                 ->where('id', $req->applicationId)
    //                 ->first();

    //             $approvedConcession = $details->replicate();
    //             $approvedConcession->setTable('prop_rejected_concessions');
    //             $approvedConcession->id = $details->id;
    //             $approvedConcession->save();
    //             $details->delete();
    //             $msg =  "Application Successfully Rejected !!";
    //             $metaReqs['verificationStatus'] = 0;
    //         }

    //         $metaReqs['moduleId'] = Config::get('module-constants.PROPERTY_MODULE_ID');
    //         $metaReqs['workflowId'] = $details->workflow_id;
    //         $metaReqs['refTableDotId'] = 'prop_active_concessions.id';
    //         $metaReqs['refTableIdValue'] = $req->applicationId;
    //         $metaReqs['senderRoleId'] = $senderRoleId;
    //         $metaReqs['user_id'] = $userId;
    //         $metaReqs['trackDate'] = $this->_todayDate->format('Y-m-d H:i:s');
    //         $req->request->add($metaReqs);
    //         $track->saveTrack($req);

    //         // Updation of Received Date
    //         // $preWorkflowReq = [
    //         //     'workflowId' => $details->workflow_id,
    //         //     'refTableDotId' => 'prop_active_concessions.id',
    //         //     'refTableIdValue' => $req->applicationId,
    //         //     'receiverRoleId' => $senderRoleId
    //         // ];
    //         // $previousWorkflowTrack = $track->getWfTrackByRefId($preWorkflowReq);
    //         // $previousWorkflowTrack->update([
    //         //     'forward_date' => $this->_todayDate->format('Y-m-d'),
    //         //     'forward_time' => $this->_todayDate->format('H:i:s')
    //         // ]);

    //         DB::commit();
    //         return responseMsgs(true, $msg, "", "", '010709', '01', '376ms', 'Post', '');
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return responseMsgs(false, $e->getMessage(), "");
    //     }
    // }

    public function appointmentDate(Request $req)
    {
        $req->validate([
            "applicationId" => "required|numeric"
        ]);

        try {
            $registrationDtl = MarriageActiveRegistration::find($req->applicationId);
            if (!$registrationDtl)
                throw new Exception('No Data Found');
            $registrationDtl->appointment_date = Carbon::now()->addMonth(1);
            $registrationDtl->save();

            return responseMsgs(true, "", remove_null($registrationDtl), "010203", "", responseTime(), 'POST', "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010203", "1.0", responseTime(), 'POST', "");
        }
    }

    /**
     *  get uploaded documents
     */
    public function getUploadedDocuments(Request $req)
    {
        $req->validate([
            'applicationId' => 'required|numeric'
        ]);
        try {
            $mWfActiveDocument = new WfActiveDocument();
            $mMarriageActiveRegistration = new MarriageActiveRegistration();
            $moduleId = 10;

            $marriageDetails = $mMarriageActiveRegistration->getApplicationById($req->applicationId);
            if (!$marriageDetails)
                throw new Exception("Application Not Found for this application Id");

            $workflowId = $marriageDetails->workflow_id;
            $documents = $mWfActiveDocument->getDocsByAppId($req->applicationId, $workflowId, $moduleId);
            return responseMsgs(true, "Uploaded Documents", remove_null($documents), "010102", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010202", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }
}
