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
use App\Traits\Workflow\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Exception;

class MarriageRegistrationController extends Controller
{

    use Workflow;

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
    public function filterDocument($documentList, $refSafs, $ownerId = null)
    {
        $mWfActiveDocument = new WfActiveDocument();
        $safId = $refSafs->id;
        $workflowId = $refSafs->workflow_id;
        $moduleId = 10;
        $uploadedDocs = $mWfActiveDocument->getDocByRefIds($safId, $workflowId, $moduleId);
        $explodeDocs = collect(explode('#', $documentList));

        $filteredDocs = $explodeDocs->map(function ($explodeDoc) use ($uploadedDocs, $ownerId, $refSafs) {
            $document = explode(',', $explodeDoc);
            $key = array_shift($document);
            $label = array_shift($document);
            $documents = collect();

            collect($document)->map(function ($item) use ($uploadedDocs, $documents, $ownerId, $refSafs) {
                $uploadedDoc = $uploadedDocs->where('doc_code', $item)
                    ->where('owner_dtl_id', $ownerId)
                    ->first();

                if ($uploadedDoc) {
                    $response = [
                        "uploadedDocId" => $uploadedDoc->id ?? "",
                        "documentCode" => $item,
                        "ownerId" => $uploadedDoc->owner_dtl_id ?? "",
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
        return $documentList = $mRefReqDocs->getDocsByDocCode($moduleId, "MARRIAGE_REQUIRED_DOC")->requirements;
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
            "ownerId" => "nullable|numeric"
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
            $metaReqs['ownerDtlId']   = $marriageRegitrationDtl->prop_owner_id;

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

            $objection = $this->inboxList($workflowIds)
                ->where('marriage_active_registrations.ulb_id', $ulbId)
                ->whereIn('marriage_active_registrations.current_role', $roleId)
                ->orderByDesc('marriage_active_registrations.id')
                ->paginate($perPage);

            return responseMsgs(true, "", remove_null($objection), '010805', '01', responseTime(), 'Post', '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }
}
