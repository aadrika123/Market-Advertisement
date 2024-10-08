<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pet\PetEditReq;
use App\Http\Requests\Pet\PetEditRequests;
use App\Http\Requests\Pet\PetRegistrationReq;
use App\MicroServices\DocumentUpload;
use App\MicroServices\IdGenerator\PrefixIdGenerator;
use App\Models\ActiveCitizenUndercare;
use App\Models\Advertisements\RefRequiredDocument;
use App\Models\Advertisements\WfActiveDocument;
use App\Models\ApiMaster;
use App\Models\Pet\MPetFee;
use App\Models\Pet\MPetOccurrenceType;
use App\Models\Pet\PetActiveApplicant;
use App\Models\Pet\PetActiveDetail;
use App\Models\Pet\PetActiveRegistration;
use App\Models\Pet\PetApproveApplicant;
use App\Models\Pet\PetApprovedRegistration;
use App\Models\Pet\PetAudit;
use App\Models\Pet\PetRegistrationCharge;
use App\Models\Pet\PetRejectedApplicant;
use App\Models\Pet\PetRejectedRegistration;
use App\Models\Pet\PetRenewalApplicant;
use App\Models\Pet\PetRenewalDetail;
use App\Models\Pet\PetRenewalRegistration;
use App\Models\Pet\PetTran;
use App\Models\Property\PropActiveSaf;
use App\Models\Property\PropActiveSafsFloor;
use App\Models\Property\PropActiveSafsOwner;
use App\Models\Property\PropFloor;
use App\Models\Property\PropOwner;
use App\Models\Property\PropProperty;
use App\Models\Property\PropSaf;
use App\Models\Workflows\CustomDetail;
use App\Models\Workflows\UlbWardMaster;
use App\Models\Workflows\WfRoleusermap;
use App\Models\Workflows\WfWorkflowrolemap;
use App\Models\Workflows\WfWorkflow;
use App\Models\Workflows\WorkflowMap;
use App\Models\Workflows\WorkflowTrack;
use App\Traits\Workflow\Workflow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use PhpParser\Node\Expr\Empty_;

/**
 * | Created On-02-01-20222 
 * | Created By- Sam Kerketta
 * | Pet Registration Operations
 */

class PetRegistrationController extends Controller
{
    use Workflow;
    private $_masterDetails;
    private $_propertyType;
    private $_occupancyType;
    private $_workflowMasterId;
    private $_petParamId;
    private $_petModuleId;
    private $_userType;
    private $_petWfRoles;
    private $_docReqCatagory;
    private $_dbKey;
    private $_fee;
    private $_applicationType;
    private $_applyMode;
    private $_tranType;
    private $_tableName;
    protected $_DB_NAME;
    protected $_DB;
    protected $_DB_NAME2;
    protected $_DB2;
    # Class constructer 
    public function __construct()
    {
        $this->_masterDetails       = Config::get("pet.MASTER_DATA");
        $this->_propertyType        = Config::get("pet.PROP_TYPE");
        $this->_occupancyType       = Config::get("pet.PROP_OCCUPANCY_TYPE");
        $this->_workflowMasterId    = Config::get("pet.WORKFLOW_MASTER_ID");
        $this->_petParamId          = Config::get("pet.PARAM_ID");
        $this->_petModuleId         = Config::get('pet.PET_MODULE_ID');
        $this->_userType            = Config::get("pet.REF_USER_TYPE");
        $this->_petWfRoles          = Config::get("pet.ROLE_LABEL");
        $this->_docReqCatagory      = Config::get("pet.DOC_REQ_CATAGORY");
        $this->_dbKey               = Config::get("pet.DB_KEYS");
        $this->_fee                 = Config::get("pet.FEE_CHARGES");
        $this->_applicationType     = Config::get("pet.APPLICATION_TYPE");
        $this->_applyMode           = Config::get("pet.APPLY_MODE");
        $this->_tranType            = Config::get("pet.TRANSACTION_TYPE");
        $this->_tableName           = Config::get("pet.TABLE_NAME");
        # Database connectivity
        $this->_DB_NAME    = "pgsql_masters";
        $this->_DB         = DB::connection($this->_DB_NAME);
        $this->_DB_NAME2   = "pgsql_property";
        $this->_DB2        = DB::connection($this->_DB_NAME2);
    }


    /**
     * | Database transaction connection
        uncomment the database connection after property launch
     */
    public function begin()
    {
        $db1 = DB::connection()->getDatabaseName();
        $db2 = $this->_DB->getDatabaseName();
        // $db3 = $this->_DB2->getDatabaseName();
        DB::beginTransaction();
        if ($db1 != $db2)
            $this->_DB->beginTransaction();
        // if ($db1 != $db3 && $db2 != $db3)
        //     $this->_DB2->beginTransaction();
    }
    /**
     * | Database transaction connection
     */
    public function rollback()
    {
        $db1 = DB::connection()->getDatabaseName();
        $db2 = $this->_DB->getDatabaseName();
        // $db3 = $this->_DB2->getDatabaseName();
        DB::rollBack();
        if ($db1 != $db2)
            $this->_DB->rollBack();
        // if ($db1 != $db3 && $db2 != $db3)
        //     $this->_DB2->rollBack();
    }
    /**
     * | Database transaction connection
     */
    public function commit()
    {
        $db1 = DB::connection()->getDatabaseName();
        $db2 = $this->_DB->getDatabaseName();
        // $db3 = $this->_DB2->getDatabaseName();
        DB::commit();
        if ($db1 != $db2)
            $this->_DB->commit();
        // if ($db1 != $db3 && $db2 != $db3)
        //     $this->_DB2->commit();
    }

    #-----------------------------------------------------------------------------------------------------------------------------------#



    /**
     * | Get all master data
     * | Collect the master data related pet module
        | Serial No : 0
        | Working
     */
    public function getAllMasters(Request $req)
    {
        try {
            $mMPetOccurrenceType = new MPetOccurrenceType();
            $occurenceType = $mMPetOccurrenceType->listOccurenceType()
                ->select('id', 'occurrence_types')
                ->get();
            $is_occurence_exist = collect($occurenceType)->first();
            if (is_null($is_occurence_exist)) {
                throw new Exception("Master data not found");
            }
            $refMasterDetails       = $this->_masterDetails;
            $registrationThrough    = $this->formatTheArray($refMasterDetails['REGISTRATION_THROUGH'], "registration_through");
            $ownertype              = $this->formatTheArray($refMasterDetails['OWNER_TYPE_MST'], "owner_type");
            $petGender              = $this->formatTheArray($refMasterDetails['PET_GENDER'], "pet_gender");

            $returnData = [
                "occurenceType"         => $occurenceType,
                "registrationThrough"   => $registrationThrough,
                "ownertype"             => $ownertype,
                "petGender"             => $petGender
            ];
            $message = "list for Pet Module's master data!";
            return responseMsgs(true, $message, $returnData, "", "01", ".ms", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }


    /**
     * | Use for data structure for master data from config 
        | Serial No : 0
        | Working 
     */
    public function formatTheArray($array, $refname)
    {
        $returnData = array();
        foreach ($array as $key => $value) {
            $array = [
                "id" => $value,
                "$refname" => $key
            ];
            array_push($returnData, $array);
        }
        return $returnData;
    }


    /**
     * | Apply for the pet Registration
     * | Save form data 
     * | @param req
        | Serial No : 0
        | Need Modifications in saving charges
        | Working 
     */
    public function applyPetRegistration(PetRegistrationReq $req)
    {
        try {
            $mPetActiveDetail           = new PetActiveDetail();
            $mPetActiveRegistration     = new PetActiveRegistration();
            $mPetActiveApplicant        = new PetActiveApplicant();
            $mWfWorkflow                = new WfWorkflow();
            $mMPetFee                   = new MPetFee();
            $mWorkflowTrack             = new WorkflowTrack();
            $mPetRegistrationCharge     = new PetRegistrationCharge();
            $user                       = authUser($req);
            $ulbId                      = $req->ulbId ?? $user->ulb_id;
            $workflowMasterId           = $this->_workflowMasterId;
            $petParamId                 = $this->_petParamId;
            $feeId                      = $this->_fee;
            $confApplicationType        = $this->_applicationType;
            $confApplyThrough           = $this->_masterDetails['REGISTRATION_THROUGH'];

            # Get iniciater and finisher for the workflow 
            $ulbWorkflowId = $mWfWorkflow->getulbWorkflowId($workflowMasterId, $ulbId);
            if (!$ulbWorkflowId) {
                throw new Exception("Respective Ulb is not maped to 'Pet Registration' Workflow!");
            }
            $registrationCharges = $mMPetFee->getFeeById($feeId['REGISTRATION_RENEWAL']);
            if (!$registrationCharges) {
                throw new Exception("Currently charges are not available!");
            }
            # Save data in track
            if ($user->user_type == $this->_userType['1']) {
                $citzenId = $user->id;
            } else {
                $userId = $user->id;
            }

            # Get the Initiator and Finisher details 
            $refInitiatorRoleId = $this->getInitiatorId($ulbWorkflowId->id);
            $refFinisherRoleId  = $this->getFinisherId($ulbWorkflowId->id);
            $finisherRoleId     = collect(DB::select($refFinisherRoleId))->first()->role_id;
            $initiatorRoleId    = collect(DB::select($refInitiatorRoleId))->first()->role_id;

            # check the proprty detials 
            $refValidatedDetails = $this->checkParamForRegister($req);

            # Data Base interaction 
            $this->begin();
            $idGeneration = new PrefixIdGenerator($petParamId['REGISTRATION'], $ulbId);                                     // Generate the application no 
            $petApplicationNo = $idGeneration->generate();
            $refData = [
                "finisherRoleId"    => $finisherRoleId,
                "initiatorRoleId"   => $initiatorRoleId,
                "workflowId"        => $ulbWorkflowId->id,
                "applicationNo"     => $petApplicationNo,
                "ulbId"             => $ulbId,
            ];
            if ($req->applyThrough == $confApplyThrough['Holding']) {
                //uncomment this after property database is updated on live
                // $refData["holdingNo"] = collect($refValidatedDetails['propDetails'])['holding_no'] ?? null;
                $refData["holdingNo"] = $req->propertyNo;
            }
            if ($req->applyThrough == $confApplyThrough['Saf']) {
                //uncomment this after property database is updated on live
                // $refData["safNo"] = collect($refValidatedDetails['propDetails'])['saf_no'] ?? null;
                $refData["safNo"] = $req->propertyNo;
            }
            $req->merge($refData);

            # Renewal and the New Registration
            if ($req->isRenewal == 0 || !isset($req->isRenewal)) {
                if (isset($req->registrationId)) {
                    throw new Exception("Registration No is Not Req for new Pet Registraton!");
                }
                $refData = [
                    "applicationType"   => "New_Apply",
                    "applicationTypeId" => $confApplicationType['NEW_APPLY']
                ];
                $req->merge($refData);
            }
            if ($req->isRenewal == 1) {
                $refData = [
                    "applicationType"   => "Renewal",
                    "registrationId"    => $req->registrationId,
                    "applicationTypeId" => $confApplicationType['RENEWAL']
                ];
                $req->merge($refData);
            }
            # Save active details 
            $applicationDetails = $mPetActiveRegistration->saveRegistration($req, $user);
            $mPetActiveApplicant->saveApplicants($req, $applicationDetails['id']);
            $mPetActiveDetail->savePetDetails($req, $applicationDetails['id']);

            # Save registration charges
            $metaRequest = new Request([
                "applicationId"     => $applicationDetails['id'],
                "applicationType"   => $req->applicationType,
                "amount"            => $registrationCharges->amount,
                "registrationFee"   => $registrationCharges->amount,
                "applicationTypeId" => $req->applicationTypeId
            ]);
            $mPetRegistrationCharge->saveRegisterCharges($metaRequest);

            # Save the data in workflow track
            $metaReqs = new Request(
                [
                    'citizenId'         => $citzenId ?? null,
                    'moduleId'          => $this->_petModuleId,
                    'workflowId'        => $ulbWorkflowId->id,
                    'refTableDotId'     => $this->_tableName['2'] . '.id',                             // Static                              // Static
                    'refTableIdValue'   => $applicationDetails['id'],
                    'user_id'           => $userId ?? null,
                    'ulb_id'            => $ulbId,
                    'senderRoleId'      => null,
                    'receiverRoleId'    => $initiatorRoleId,
                    'auth'              => $req->auth
                ]
            );
            $mWorkflowTrack->saveTrack($metaReqs);

            $this->commit();
            # Data structure for return
            $returnData = [
                "id"            => $applicationDetails['id'],
                "applicationNo" => $applicationDetails['applicationNo'],
            ];

            #_Whatsaap Message
            // if (strlen($req->mobileNo) == 10) {

            //         $whatsapp2 = (Whatsapp_Send(
            //             $req->mobileNo,
            //             "all_module_succesfull_generation ",
            //             [
            //                 "content_type" => "text",
            //                 [
            //                     $req->applicantName ?? "Pet Owner",
            //                     "Pet Registration",
            //                     "Application No.",
            //                     $petApplicationNo,
            //                     "1800123231"
            //                 ]
            //             ]
            //         ));
            // }

            return responseMsgs(true, "Pet Registration application submitted!", $returnData, "", "01", ".ms", "POST", $req->deviceId);
        } catch (Exception $e) {
            $this->rollback();
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }

    /**
     * | Check params before pet registration 
     * | Check property and saf details 
     * | @param req
        | for Http Request
        | $transfer = ["holdingNo" => $req->propertyNo];
                $refApi = $mApiMaster->getApiEndPoint($confApiId['get_prop_detils'])->first();
                Http::withHeaders([
                    "Content-Type" => "$confHttpHeaders"
                ])
                    ->withToken('19013|TpteWXV08A7wYKVNwLrVpdaOO1oC1tyJDxE7LVwN')                       // Static
                    ->post("$refApi->end_point", $transfer);
        | Serial No : 0
        | Working
     */
    public function checkParamForRegister($req)
    {
        $mPropProperty          = new PropProperty();
        $mPropFloor             = new PropFloor();
        $mPropActiveSaf         = new PropActiveSaf();
        $mPropActiveSafsFloor   = new PropActiveSafsFloor();

        $confApplyThrough   = $this->_masterDetails['REGISTRATION_THROUGH'];
        $confPropertyType   = $this->_propertyType;
        $ownertype          = $this->_masterDetails['OWNER_TYPE_MST'];

        // commented because of property database is not present on live server
        // switch ($req->applyThrough) {
        //     case ($req->applyThrough == $confApplyThrough['Holding']):
        //         $refPropDetails = $mPropProperty->getPropDtls()
        //             ->where('prop_properties.holding_no', $req->propertyNo)
        //             ->first();
        //         if (is_null($refPropDetails)) {
        //             throw new Exception("Property according to $req->propertyNo not found!");
        //         }
        //         if ($refPropDetails->prop_type_mstr_id != $confPropertyType['VACANT_LAND']) {
        //             $floorsDetails = $mPropFloor->getPropFloors($refPropDetails->id)->get();
        //             $isTenant = $this->getPropOccupancyType($floorsDetails);
        //             if ($req->ownerCategory == $ownertype['Tenant'] && $isTenant == false) {
        //                 throw new Exception("Respective property dont have tenant!");
        //             }
        //         }
        //         if ($refPropDetails->prop_type_mstr_id == $confPropertyType['VACANT_LAND']) {
        //             throw new Exception("Pet cannot be applied in VACANT LAND!");
        //         }
        //         $returnDetails = [
        //             "tenant"        => $isTenant,
        //             "propDetails"   => $refPropDetails,
        //         ];
        //         break;

        //     case ($req->applyThrough == $confApplyThrough['Saf']):
        //         $refSafDetails = $mPropActiveSaf->getSafDtlBySaf()->where('prop_active_safs.saf_no', $req->propertyNo)
        //             ->first();
        //         if (is_null($refSafDetails)) {
        //             throw new Exception("Property according to $req->propertyNo not found!");
        //         }
        //         if ($refSafDetails->prop_type_mstr_id != $confPropertyType['VACANT_LAND']) {
        //             $floorsDetails = $mPropActiveSafsFloor->getSafFloors($refSafDetails->id)->get();
        //             $isTenant = $this->getPropOccupancyType($floorsDetails);
        //             if ($req->ownerCategory == $ownertype['Tenant'] && $isTenant == false) {
        //                 throw new Exception("Respective property dont have tenant!");
        //             }
        //         }
        //         if ($refSafDetails->prop_type_mstr_id == $confPropertyType['VACANT_LAND']) {
        //             throw new Exception("Pet cannot be applied in VACANT LAND!");
        //         }
        //         $returnDetails = [
        //             "tenant"        => $isTenant,
        //             "propDetails"   => $refSafDetails,
        //         ];
        //         break;
        // }
        // return $returnDetails;
    }

    /**
     * | Get occupancy type accordingly for saf and holding
        | Serial No : 0
        | Working
     */
    public function getPropOccupancyType($floorDetails)
    {
        $confOccupancyType  = $this->_occupancyType;
        $refOccupancyType   = collect($confOccupancyType)->flip();
        $isTenanted = collect($floorDetails)
            ->where('occupancy_type_mstr_id', $refOccupancyType['TENANTED'])
            ->first();

        if ($isTenanted) {
            return true;                               // Static
        }
        return false;                                   // Static
    }


    /**
     * |---------------------------- Get Document Lists To Upload ----------------------------|
     * | Doc Upload for the Workflow
        | Serial No : 0
        | Working
     */
    public function getDocToUpload(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'applicationId' => 'required|numeric'
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $mPetActiveRegistration     = new PetActiveRegistration();
            $petApplicationId           = $req->applicationId;

            $refPetApplication = $mPetActiveRegistration->getPetApplicationById($petApplicationId)->first();                      // Get Pet Details
            if (is_null($refPetApplication)) {
                throw new Exception("Application not found");
            }
            // check if the respective is working on the front end
            // $this->checkAutheriseUser($req);
            $documentList = $this->getPetDocLists($refPetApplication);
            $petTypeDocs['listDocs'] = collect($documentList)->map(function ($value) use ($refPetApplication) {
                return $this->filterDocument($value, $refPetApplication)->first();
            });
            $totalDocLists = collect($petTypeDocs);
            $totalDocLists['docUploadStatus']   = $refPetApplication->doc_upload_status;
            $totalDocLists['docVerifyStatus']   = $refPetApplication->doc_verify_status;
            $totalDocLists['ApplicationNo']     = $refPetApplication->application_no;
            $totalDocLists['paymentStatus']     = $refPetApplication->payment_status;
            return responseMsgs(true, "", remove_null($totalDocLists), "010203", "", "", 'POST', "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010203", "1.0", "", 'POST', "");
        }
    }


    /**
     * |---------------------------- Filter The Document For Viewing ----------------------------|
     * | @param documentList
     * | @param refWaterApplication
     * | @param ownerId
     * | @var mWfActiveDocument
     * | @var applicationId
     * | @var workflowId
     * | @var moduleId
     * | @var uploadedDocs
     * | Calling Function 
        | Serial No : 
        | Working
     */
    public function filterDocument($documentList, $refPetApplication, $ownerId = null)
    {
        $mWfActiveDocument  = new WfActiveDocument();
        $docUpload          = new DocumentUpload;
        $applicationId      = $refPetApplication->ref_application_id;
        $workflowId         = $refPetApplication->workflow_id;
        $moduleId           = $this->_petModuleId;
        $confDocReqCatagory = $this->_docReqCatagory;
        $uploadedDocs       = $mWfActiveDocument->getDocByRefIds($applicationId, $workflowId, $moduleId);
        $uploadedDocs       = $docUpload->getDocUrl($uploadedDocs);           #_Calling BLL for Document Path from DMS

        $explodeDocs = collect(explode('#', $documentList->requirements));
        $filteredDocs = $explodeDocs->map(function ($explodeDoc) use ($uploadedDocs, $ownerId, $confDocReqCatagory) {

            # var defining
            $document   = explode(',', $explodeDoc);
            $key        = array_shift($document);
            $label      = array_shift($document);
            $documents  = collect();

            collect($document)->map(function ($item) use ($uploadedDocs, $documents, $ownerId) {
                $uploadedDoc = $uploadedDocs->where('doc_code', $item)
                    ->where('owner_dtl_id', $ownerId)
                    ->first();
                if ($uploadedDoc) {
                    $path = $uploadedDoc['doc_path']; //$this->readDocumentPath($uploadedDoc->doc_path);
                    $fullDocPath = !empty(trim($uploadedDoc['doc_path'])) ? $path : null;
                    $response = [
                        "uploadedDocId" => $uploadedDoc['id'] ?? "",
                        "documentCode"  => $item,
                        "ownerId"       => $uploadedDoc['owner_dtl_id'] ?? "",
                        "docPath"       => $fullDocPath ?? "",
                        "verifyStatus"  => $uploadedDoc['verify_status'] ?? "",
                        "remarks"       => $uploadedDoc['remarks'] ?? "",
                    ];
                    $documents->push($response);
                }
            });
            $reqDoc['docType']      = $key;
            $reqDoc['uploadedDoc']  = $documents->last();
            $reqDoc['docName']      = substr($label, 1, -1);
            switch ($key) {
                case ($confDocReqCatagory['1']):
                    $reqDoc['isMandatory'] = 1;                                                 // Static
                    break;
                case ($confDocReqCatagory['2']):
                    $reqDoc['isMandatory'] = 1;                                                 // Static
                    break;
                case ($confDocReqCatagory['3']):
                    $reqDoc['isMandatory'] = 0;                                                 // Static
                    break;
            }

            $reqDoc['masters'] = collect($document)->map(function ($doc) use ($uploadedDocs) {
                $uploadedDoc = $uploadedDocs->where('doc_code', $doc)->first();
                $strLower = strtolower($doc);
                $strReplace = str_replace('_', ' ', $strLower);
                if (isset($uploadedDoc)) {
                    $path =  $uploadedDoc['doc_path']; //$this->readDocumentPath($uploadedDoc->doc_path);
                    $fullDocPath = !empty(trim($uploadedDoc['doc_path'])) ? $path : null;
                }
                $arr = [
                    "documentCode"  => $doc,
                    "docVal"        => ucwords($strReplace),
                    "uploadedDoc"   => $fullDocPath ?? "",
                    "uploadedDocId" => $uploadedDoc['id'] ?? "",
                    "verifyStatus'" => $uploadedDoc['verify_status'] ?? "",
                    "remarks"       => $uploadedDoc['remarks'] ?? "",
                ];
                return $arr;
            });
            return $reqDoc;
        });
        return $filteredDocs;
    }

    /**
     * | List of the doc to upload 
     * | Calling function
        | Serial No :  
        | Working
     */
    public function getPetDocLists($application)
    {
        $mRefRequiredDocument   = new RefRequiredDocument();
        $confPetModuleId        = $this->_petModuleId;
        $confOwnerType          = $this->_masterDetails['OWNER_TYPE_MST'];

        $type = ["PET_VACCINATION", "LEPTOSPIROSIS_VACCINATION", "PET_PHOTO"];      // "ADDRESS PROOF"
        if ($application->owner_type == $confOwnerType['Tenant'])                   // Holding No, SAF No // Static
        {
            $type = array_merge($type, ["TENANTED", "NOC"]);;
        }
        return $mRefRequiredDocument->getCollectiveDocByCode($confPetModuleId, $type);
    }

    /**
     * | Read the server url 
        | Common function
        | Serial No : 
        | Working
        | Check the port for live project
     */
    public function readDocumentPath($path)
    {
        $docUrl = Config::get("marriage.DOC_URL");
        $path   = ($docUrl . "/" . $path);
        return $path;
    }


    /**
     * | Upload Application Documents 
     * | @param req
        | Serial No :
        | Working 
        | Look on the concept of deactivation of the rejected documents 
        | Put the static "verify status" 2 in config  
     */
    public function uploadPetDoc(Request $req)
    {
        $extension = $req->document->getClientOriginalExtension();
        $validated = Validator::make(
            $req->all(),
            [
                "applicationId" => "required|numeric",
                "document"      => "required|mimes:pdf,jpeg,png,jpg" . (strtolower($extension) == 'pdf' ? '|max:2048' : '|max:2048'),
                "docCode"       => "required",
                "docCategory"   => "required", // Make sure to verify if this rule is appropriate for your application
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $user                       = authUser($req);
            $metaReqs                   = array();
            $applicationId              = $req->applicationId;
            $document                   = $req->document;
            $refDocUpload               = new DocumentUpload;
            $mWfActiveDocument          = new WfActiveDocument();
            $mPetActiveRegistration     = new PetActiveRegistration();
            $relativePath               = Config::get('pet.PET_RELATIVE_PATH');
            $refmoduleId                = $this->_petModuleId;
            $confUserType               = $this->_userType;

            $getPetDetails  = $mPetActiveRegistration->getPetApplicationById($applicationId)->firstOrFail();
            $refImageName   = $req->docCode;
            $refImageName   = $getPetDetails->ref_application_id . '-' . str_replace(' ', '_', $refImageName);
            //$imageName      = $refDocUpload->upload($refImageName, $document, $relativePath['REGISTRATION']);
            $docDetail      = $refDocUpload->upload($req);
            $metaReqs = [
                'moduleId'      => $refmoduleId,
                'activeId'      => $getPetDetails->ref_application_id,
                'workflowId'    => $getPetDetails->workflow_id,
                'ulbId'         => $getPetDetails->ulb_id,
                'relativePath'  => $relativePath['REGISTRATION'],
                //'document'      => $docDetail[],
                'docCode'       => $req->docCode,
                'ownerDtlId'    => $req->ownerId ?? null,
                'docCategory'   => $req->docCategory,
                'auth'          => $req->auth
            ];


            $metaReqs['unique_id'] = $docDetail['data']['uniqueId'];
            $metaReqs['reference_no'] = $docDetail['data']['ReferenceNo'];
            // $metaReqs['document'] = $docDetail['data']['document'];


            if ($user->user_type == $confUserType['1']) {
                $isCitizen = true;
                $this->checkParamForDocUpload($isCitizen, $getPetDetails, $user);
            } else {
                $isCitizen = false;
                $this->checkParamForDocUpload($isCitizen, $getPetDetails, $user);
            }

            $this->begin();
            $ifDocExist = $mWfActiveDocument->isDocCategoryExists($getPetDetails->ref_application_id, $getPetDetails->workflow_id, $refmoduleId, $req->docCategory, $req->ownerId);   // Checking if the document is already existing or not
            $metaReqs = new Request($metaReqs);
            if (collect($ifDocExist)->isEmpty()) {
                $mWfActiveDocument->postPetDocuments($metaReqs);
            }
            if ($ifDocExist) {
                $mWfActiveDocument->editDocuments($ifDocExist, $metaReqs);
            }

            #check full doc upload
            $refCheckDocument = $this->checkFullDocUpload($req);
            # Update the Doc Upload Satus in Application Table
            if ($refCheckDocument->contains(false) && $getPetDetails->doc_upload_status == true) {
                $mPetActiveRegistration->updateUploadStatus($applicationId, false);
            }
            if ($refCheckDocument->unique()->count() === 1 && $refCheckDocument->unique()->first() === true) {
                $mPetActiveRegistration->updateUploadStatus($req->applicationId, true);
            }
            $this->commit();
            return responseMsgs(true, "Document Uploadation Successful", "", "", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            $this->rollback();
            return responseMsgs(false, $e->getMessage(), "", "", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    /**
     * | Check if the params for document upload
     * | @param isCitizen
     * | @param applicantDetals
     * | @param user
        | Serial No :
        | Working 
     */
    public function checkParamForDocUpload($isCitizen, $applicantDetals, $user)
    {
        $mWfRoleusermap = new WfRoleusermap();
        $refWorkFlowMaster = Config::get('workflow-constants.WATER_MASTER_ID');
        switch ($isCitizen) {
            case (true): # For citizen 
                if (!is_null($applicantDetals->current_role) && $applicantDetals->parked == true) {
                    return true;
                }
                if (!is_null($applicantDetals->current_role)) {
                    throw new Exception("You aren't allowed to upload document!");
                }
                break;
            case (false): # For user
                $refReq = new Request([
                    "userId"        => $user->id,
                    "workflowId"    => $applicantDetals->workflow_id,
                ]);
                $role =  $mWfRoleusermap->getRoleByUserWfId($refReq);
                // $role = $this->getUserRoll($userId, $ulbId, $refWorkFlowMaster);
                if (!$role) {
                    throw new Exception("You dont have any role");
                }
                if ($role->can_upload_document != true) {
                    throw new Exception("You dont have permission to upload Document!");
                }
                break;
        }
    }



    /**
     * | Caheck the Document if Fully Upload or not
     * | @param req
        | Working
        | Serial No :
     */
    public function checkFullDocUpload($req)
    {
        # Check the Document upload Status
        $confDocReqCatagory = $this->_docReqCatagory;
        $documentList = $this->getDocToUpload($req);
        $refDoc = collect($documentList)['original']['data']['listDocs'];
        $checkDocument = collect($refDoc)->map(function ($value)
        use ($confDocReqCatagory) {
            if ($value['docType'] == $confDocReqCatagory['1'] || $value['docType'] == $confDocReqCatagory['2']) {
                $doc = collect($value['uploadedDoc'])->first();
                if (is_null($doc)) {
                    return false;
                }
                return true;
            }
            return true;
        });
        return $checkDocument;
    }


    /**
     * | Get the upoaded docunment
        | Serial No : 
        | Working
     */
    public function getUploadDocuments(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'applicationId' => 'required|numeric'
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $mWfActiveDocument      = new WfActiveDocument();
            $mPetActiveRegistration = new PetActiveRegistration();
            $refDocUpload               = new DocumentUpload;
            $moduleId               = $this->_petModuleId;
            $applicationId          = $req->applicationId;

            $petDetails = $mPetActiveRegistration->getPetApplicationById($applicationId)->first();
            if (!$petDetails)
                throw new Exception("Application not found for this ($applicationId) application Id!");

            $workflowId = $petDetails->workflow_id;
            $documents  = $mWfActiveDocument->getWaterDocsByAppNo($applicationId, $workflowId, $moduleId)
                ->where('d.status', '!=', 0)
                ->get();
            // $returnData = collect($documents)->map(function ($value) {
            //     $path =  $this->getDocUrl($value->refDocUpload);
            //     $value->doc_path = !empty(trim($value->refDocUpload)) ? $path : null;
            //     return $value;
            // });
            $data = $refDocUpload->getDocUrl($documents);
            return responseMsgs(true, "Uploaded Documents", remove_null($data), "010102", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010202", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Get Application list for the respective user 
     * | List the application filled by the user 
        | Serial No :
        | Working
     */
    public function getApplicationList(Request $req)
    {
        try {
            $user                   = authUser($req);
            $confUserType           = $this->_userType;
            $confDbKey              = $this->_dbKey;
            $mPetActiveRegistration = new PetActiveRegistration();
            $mPetTran               = new PetTran();

            if ($user->user_type != $confUserType['1']) {                                       // If not a citizen
                throw new Exception("You are not an autherised Citizen!");
            }
            # Collect querry Exceptions 
            try {
                $refAppDetails = $mPetActiveRegistration->getAllApplicationDetails($user->id, $confDbKey['1'])
                    ->select(
                        DB::raw("REPLACE(pet_active_registrations.application_type, '_', ' ') AS ref_application_type"),
                        DB::raw("TO_CHAR(pet_active_registrations.application_apply_date, 'DD-MM-YYYY') as ref_application_apply_date"),
                        "pet_active_registrations.*",
                        "pet_active_applicants.applicant_name",
                        "wf_roles.role_name"
                    )
                    ->orderByDesc('pet_active_registrations.id')
                    ->get();
            } catch (QueryException $q) {
                return responseMsgs(false, "An error occurred during the query!", $q->getMessage(), "", "01", ".ms", "POST", $req->deviceId);
            }

            # Get transaction no for the respective application
            $returnData = collect($refAppDetails)->map(function ($value)
            use ($mPetTran) {
                if ($value->payment_status != 0) {
                    $tranNo = $mPetTran->getTranDetails($value->id, $value->application_type_id)->first();
                    $value->transactionNo = $tranNo->tran_no;
                }
                return $value;
            });
            return responseMsgs(true, "list of active registration!", remove_null($returnData), "", "01", ".ms", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }

    //BEGIN///////////////////////////////////////////////////////////////////////////////
    /**
     * | Get Application details for workflow view 
     * | @param request
     * | @var ownerDetails
     * | @var applicantDetails
     * | @var applicationDetails
     * | @var returnDetails
     * | @return returnDetails : list of individual applications
        | Serial No : 08
        | Workinig 
     */
    public function getApplicationsDetails(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'applicationId' => 'required|numeric'
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            # object assigning              
            $mPetActiveRegistration = new PetActiveRegistration();
            $mPetActiveApplicant    = new PetActiveApplicant();
            $mWorkflowMap           = new WorkflowMap();
            $mWorkflowTracks        = new WorkflowTrack();
            $mCustomDetails         = new CustomDetail();
            $applicationId          = $request->applicationId;
            $aplictionList          = array();

            # application details
            $applicationDetails = $mPetActiveRegistration->getPetApplicationById($applicationId)->first();
            if (!$applicationDetails) {
                throw new Exception("Application data according to $request->applicationId not found");
            }
            # owner Details
            $applyDate = Carbon::createFromFormat('Y-m-d', $applicationDetails->application_apply_date)->format('d-m-Y');
            $aplictionList['application_no'] = $applicationDetails->application_no;
            $aplictionList['apply_date']     = $applyDate;

            # DataArray
            $basicDetails       = $this->getBasicDetails($applicationDetails);
            $propertyDetails    = $this->getpropertyDetails($applicationDetails);
            $petDetails         = $this->getrefPetDetails($applicationDetails);

            $firstView = [
                'headerTitle'   => 'Basic Details',
                'data'          => $basicDetails
            ];
            $secondView = [
                'headerTitle'   => 'Applicant Property Details',
                'data'          => $propertyDetails
            ];
            $thirdView = [
                'headerTitle'   => 'Pet Details',
                'data'          => $petDetails
            ];
            $fullDetailsData['fullDetailsData']['dataArray'] = new collection([$firstView, $secondView, $thirdView]);

            # CardArray
            $cardDetails = $this->getCardDetails($applicationDetails);
            $cardData = [
                'headerTitle' => 'Pet Registration',
                'data' => $cardDetails
            ];
            $fullDetailsData['fullDetailsData']['cardArray'] = new Collection($cardData);

            # TableArray
            $ownerDetail = $mPetActiveApplicant->getApplicationDetails($applicationId)->get();
            $ownerList = $this->getOwnerDetails($ownerDetail);
            $ownerView = [
                'headerTitle' => 'Owner Details',
                'tableHead' => ["#", "Owner Name", "Mobile No", "Email", "Pan"],
                'tableData' => $ownerList
            ];
            $fullDetailsData['fullDetailsData']['tableArray'] = new Collection([$ownerView]);

            # Level comment
            $mtableId = $applicationDetails->ref_application_id;
            $mRefTable = "pet_active_registrations.id";                         // Static
            $levelComment['levelComment'] = $mWorkflowTracks->getTracksByRefId($mRefTable, $mtableId);

            #citizen comment
            $refCitizenId = $applicationDetails->citizen_id;
            $citizenComment['citizenComment'] = $mWorkflowTracks->getCitizenTracks($mRefTable, $mtableId, $refCitizenId);

            # Role Details
            $metaReqs = [
                'customFor'     => 'Pet',
                'wfRoleId'      => $applicationDetails->current_role_id,
                'workflowId'    => $applicationDetails->workflow_id,
                'lastRoleId'    => $applicationDetails->last_role_id
            ];
            $request->request->add($metaReqs);
            $roleDetails['roleDetails'] = $mWorkflowMap->getRoleDetails($request);

            # Timeline Data
            $timelineData['timelineData'] = collect($request);

            # Departmental Post
            $custom = $mCustomDetails->getCustomDetails($request);
            $departmentPost['departmentalPost'] = $custom;

            # Payments Details
            // return array_merge($aplictionList, $fullDetailsData,$levelComment,$citizenComment,$roleDetails,$timelineData,$departmentPost);
            $returnValues = array_merge($aplictionList, $fullDetailsData, $levelComment, $citizenComment, $roleDetails, $timelineData, $departmentPost);
            return responseMsgs(true, "listed Data!", $returnValues, "", "02", ".ms", "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "02", ".ms", "POST", $request->deviceId);
        }
    }


    /**
     * |------------------ Basic Details ------------------|
     * | @param applicationDetails
     * | @var collectionApplications
        | Serial No : 08.01
        | Workinig 
     */
    public function getBasicDetails($applicationDetails)
    {
        if ($applicationDetails->apply_through == 1) {
            $applyThrough = "Holding";
        } else {
            $applyThrough = "Saf";
        }
        $applyDate = Carbon::createFromFormat('Y-m-d', $applicationDetails->application_apply_date)->format('d-m-Y');
        return new Collection([
            ['displayString' => 'Ward No',              'key' => 'WardNo',                  'value' => $applicationDetails->ward_name],
            ['displayString' => 'Type of Application',   'key' => 'TypeOfConnection',       'value' => $applicationDetails->ref_application_type],
            ['displayString' => 'Registration Through', 'key' => 'RegistrationThrough',     'value' => $applyThrough],
            ['displayString' => 'Apply From',           'key' => 'ApplyFrom',               'value' => $applicationDetails->apply_mode],
            ['displayString' => 'Apply Date',           'key' => 'ApplyDate',               'value' => $applyDate]
        ]);
    }

    /**
     * |------------------ Property Details ------------------|
     * | @param applicationDetails
     * | @var propertyDetails
     * | @var collectionApplications
        | Serial No : 08.02
        | Workinig 
     */
    public function getpropertyDetails($applicationDetails)
    {
        $propertyDetails = array();
        if (!is_null($applicationDetails->holding_no)) {
            array_push($propertyDetails, ['displayString' => 'Holding No',    'key' => 'AppliedBy',  'value' => $applicationDetails->holding_no]);
        }
        if (!is_null($applicationDetails->saf_no)) {
            array_push($propertyDetails, ['displayString' => 'Saf No',        'key' => 'AppliedBy',   'value' => $applicationDetails->saf_no]);
        }
        if ($applicationDetails->owner_type == 1) {
            $ownerType = "Owner";
        } else {
            $ownerType = "Tenant";
        }
        array_push($propertyDetails, ['displayString' => 'Ward No',       'key' => 'WardNo',      'value' => $applicationDetails->ward_name]);
        array_push($propertyDetails, ['displayString' => 'Address',       'key' => 'Address',     'value' => $applicationDetails->address]);
        array_push($propertyDetails, ['displayString' => 'Owner Type',    'key' => 'OwnerType',   'value' => $ownerType]);

        return $propertyDetails;
    }

    /**
     * |------------------ Owner details ------------------|
     * | @param ownerDetails
        | Serial No : 08.04
        | Workinig 
     */
    public function getrefPetDetails($applicationDetails)
    {
        if ($applicationDetails->sex == 1) {
            $sex = "Male";
        } else {
            $sex = "Female";
        }
        $dob = Carbon::createFromFormat('Y-m-d', $applicationDetails->dob)->format('d-m-Y');
        $rabiesVac = Carbon::createFromFormat('Y-m-d', $applicationDetails->rabies_vac_date)->format('d-m-Y');
        $lepVac = Carbon::createFromFormat('Y-m-d', $applicationDetails->leptospirosis_vac_date)->format('d-m-Y');

        if ($applicationDetails->pet_type == 1) {
            $petType = "Dog";
        } else {
            $petType = "Cat";
        }
        return new Collection([
            ['displayString' => 'Pet Name',                         'key' => 'PetName',                         'value' => $applicationDetails->pet_name],
            ['displayString' => 'Pet Type',                         'key' => 'PetType',                         'value' => $petType],
            ['displayString' => 'Sex',                              'key' => 'Sex',                             'value' => $sex],
            ['displayString' => 'Breed',                            'key' => 'Breed',                           'value' => $applicationDetails->breed],
            ['displayString' => 'Veterinary Doctor Name',           'key' => 'VeterinaryDoctorName',            'value' => $applicationDetails->vet_doctor_name],
            ['displayString' => 'Doctor Registration No',           'key' => 'DoctorRegistrationNo',            'value' => $applicationDetails->doctor_registration_no],
            ['displayString' => 'Pet DOB',                          'key' => 'PetDob',                          'value' => $dob],
            ['displayString' => 'Rabies Vaccination Date',          'key' => 'RabiesVaccinationDate',           'value' => $rabiesVac],
            ['displayString' => 'Leptospirosis Vaccination Date',   'key' => 'LeptospirosisVaccinationDate',    'value' => $lepVac],
        ]);
    }

    /**
     * |------------------ Owner details ------------------|
     * | @param ownerDetails
        | Serial No : 08.04
        | Workinig 
     */
    public function getOwnerDetails($ownerDetails)
    {
        return collect($ownerDetails)->map(function ($value, $key) {
            return [
                $key + 1,
                $value['applicant_name'],
                $value['mobile_no'],
                $value['email'],
                $value['pan_no']
            ];
        });
    }

    /**
     * |------------------ Get Card Details ------------------|
     * | @param applicationDetails
     * | @param ownerDetails
     * | @var ownerDetail
     * | @var collectionApplications
        | Serial No : 08.05
        | Workinig 
     */
    public function getCardDetails($applicationDetails)
    {
        if ($applicationDetails->pet_type == 1) {
            $petType = "Dog";
        } else {
            $petType = "Cat";
        }
        if ($applicationDetails->apply_through == 1) {
            $applyThrough = "Holding";
        } else {
            $applyThrough = "Saf";
        }
        $applyDate = Carbon::createFromFormat('Y-m-d', $applicationDetails->application_apply_date)->format('d-m-Y');
        return new Collection([
            ['displayString' => 'Ward No.',             'key' => 'WardNo.',             'value' => $applicationDetails->ward_name],
            ['displayString' => 'Application No.',      'key' => 'ApplicationNo.',      'value' => $applicationDetails->application_no],
            ['displayString' => 'Owner Name',           'key' => 'OwnerName',           'value' => $applicationDetails->applicant_name],
            ['displayString' => 'Pet Type',             'key' => 'PetType',             'value' => $petType],
            ['displayString' => 'Connection Type',      'key' => 'ConnectionType',      'value' => $applicationDetails->ref_application_type],
            ['displayString' => 'Connection Through',   'key' => 'ConnectionThrough',   'value' => $applyThrough],
            ['displayString' => 'Apply-Date',           'key' => 'ApplyDate',           'value' => $applyDate],
        ]);
    }


    ///////////////////////////////////////////////////////////////////////////////END

    /**
     * | Get application details by application id
     * | collective data with registration charges
        | Serial No :
        | Working
     */
    public function getApplicationDetails(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'applicationId' => 'required|numeric'
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $applicationId          = $req->applicationId;
            $mPetActiveRegistration = new PetActiveRegistration();
            $mPetRegistrationCharge = new PetRegistrationCharge();
            $mPetTran               = new PetTran();

            $applicationDetails = $mPetActiveRegistration->getPetApplicationById($applicationId)->first();
            if (is_null($applicationDetails)) {
                throw new Exception("Application not found");
            }
            $chargeDetails = $mPetRegistrationCharge->getChargesbyId($applicationDetails->ref_application_id)
                ->select(
                    'id AS chargeId',
                    'amount',
                    'registration_fee',
                    'paid_status',
                    'charge_category',
                    'charge_category_name'
                )
                ->first();
            if (is_null($chargeDetails)) {
                throw new Exception("Charges for respective application not found");
            }
            if ($chargeDetails->paid_status != 0) {
                # Get Transaction details 
                $tranDetails = $mPetTran->getTranByApplicationId($applicationId)->first();
                if (!$tranDetails) {
                    throw new Exception("Transaction details not found there is some error in data !");
                }
                $applicationDetails['transactionDetails'] = $tranDetails;
            }
            $chargeDetails['roundAmount'] = round($chargeDetails['amount']);
            $applicationDetails['charges'] = $chargeDetails;
            return responseMsgs(true, "Listed application detail", remove_null($applicationDetails), "", "01", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Delete the Application before payment 
        | Serial No : 
        | CAUTION
        | Working
        | ❗❗❗ Cross Check / incase of delete deactivate its status ❗❗❗
     */
    public function deletePetApplication(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'applicationId' => 'required|numeric'
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $user                       = authUser($req);
            $applicationId              = $req->applicationId;
            $confPetModuleId            = $this->_petModuleId;
            $mPetActiveRegistration     = new PetActiveRegistration();
            $mWfActiveDocument          = new WfActiveDocument();
            $mPetRegistrationCharge     = new PetRegistrationCharge();

            $applicantDetals = $mPetActiveRegistration->getPetApplicationById($applicationId)->first();
            if (!$applicantDetals) {
                throw new Exception("Application details not found!");
            }
            $this->checkParamsForDelete($applicantDetals, $user);

            $this->begin();
            # Deletion process start
            $mPetActiveRegistration->deleteApplication($applicationId);
            $mWfActiveDocument->deleteDocuments($applicationId, $applicantDetals->workflow_id, $confPetModuleId);
            $mPetRegistrationCharge->deleteCharges($applicationId);
            $this->commit();
            return responseMsgs(true, "Application Successfully Deleted", "", "", "1.0", "", "POST", $req->deviceId);
        } catch (Exception $e) {
            $this->rollback();
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }

    /**
     * | Check the parameter for deleting Application 
     * | @param applicationDetails
     * | @param user
        | Serial No :
        | Working
     */
    public function checkParamsForDelete($applicationDetails, $user)
    {
        $applyMode              = $this->_applyMode;
        $trantype               = $this->_tranType;
        $applyMode              = collect($applyMode)->flip();
        $mPetTran               = new PetTran();
        $mPetRegistrationCharge = new PetRegistrationCharge();
        $applicationId          = $applicationDetails->ref_application_id;

        if (is_null($applicationDetails)) {
            throw new Exception("Relted Data or Owner not found!");
        }
        if ($applicationDetails->payment_status != 0) {
            throw new Exception("Your paymnet is done application Cannot be Deleted!");
        }
        if (!is_null($applicationDetails->current_role)) {
            throw new Exception("application is under process can't be deleted!");
        }
        # for jsk and citizen
        if ($applicationDetails->apply_mode == $applyMode['1']) {
            if ($applicationDetails->citizen_id != $user->id) {
                throw new Exception("You'r not the user of this form!");
            }
        } else {
            if ($applicationDetails->user_id != $user->id) {
                throw new Exception("You'r not the user of this form!");
            }
        }

        if ($applicationDetails->renewal == 0) {
            $tranTypeId = $trantype['New_Apply'];
        }
        if ($applicationDetails->renewal == 1) {
            $tranTypeId = $trantype['Renewal'];
        }
        $transactionDetails = $mPetTran->getTranDetails($applicationId, $tranTypeId)->first();
        if (!is_null($transactionDetails)) {
            throw new Exception("invalid operation Transaction details exist for application!");
        }
        $chargePayment = $mPetRegistrationCharge->getChargesbyId($applicationId)->where('paid_status', 1)->first();
        if (!is_null($chargePayment)) {
            throw new Exception("Payment for Respective charges exist!");
        }
    }

    /**
     * | Serch the holding and the saf details
     * | Serch the property details for filling the water Application Form
     * | @param request
     * | 01
        | Serial No : 
        | Working
     */
    public function getSafHoldingDetails(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'connectionThrough' => 'required|int|in:1,2',
                'id'                => 'required',
                'ulbId'             => 'nullable'
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $user                   = authUser($request);
            $ulbId                  = $request->ulbId ?? $user->ulb_id;
            $mPropProperty          = new PropProperty();
            $mPropOwner             = new PropOwner();
            $mPropFloor             = new PropFloor();
            $mPropActiveSafOwners   = new PropActiveSafsOwner();
            $mPropActiveSafsFloor   = new PropActiveSafsFloor();
            $mPropActiveSaf         = new PropActiveSaf();
            $key                    = $request->connectionThrough;
            $refTenanted            = Config::get('property.OCCUPANCY_TYPE.TENANTED');
            if (!$ulbId)
                throw new Exception("Please select ulb");

            switch ($key) {
                    # For Property
                case (1):
                    $application = collect($mPropProperty->getPropByHolding($request->id, $ulbId));
                    $checkExist = collect($application)->first();
                    if (!$checkExist) {
                        throw new Exception("Data According to Holding Not Found!");
                    }
                    if (is_null($application['new_ward_mstr_id']) && is_null($application['new_ward_no'])) {
                        $owners['wardDetails'] = [
                            "wardId" => $application['ward_mstr_id'],
                            "wardNo" => $application['old_ward_no']
                        ];
                    } else {
                        $owners['wardDetails'] = [
                            "wardId" => $application['new_ward_mstr_id'],
                            "wardNo" => $application['new_ward_no']
                        ];
                    }
                    # collecting all data of owner and occupency
                    $occupancyOwnerType = collect($mPropFloor->getOccupancyType($application['id'], $refTenanted));
                    $owners['owners'] = collect($mPropOwner->getOwnerByPropId($application['id']));

                    # merge all data for return 
                    $details = $application->merge($owners)->merge($occupancyOwnerType);
                    return responseMsgs(true, "related Details!", $details, "", "", "", "POST", "");
                    break;

                    # For Saf
                case (2):
                    $application = collect($mPropActiveSaf->getSafDtlBySafUlbNo($request->id, $ulbId));
                    $checkExist = collect($application)->first();
                    if (!$checkExist) {
                        throw new Exception("Data According to SAF Not Found!");
                    }
                    if (is_null($application['new_ward_mstr_id']) && is_null($application['new_ward_no'])) {
                        $owners['wardDetails'] = [
                            "wardId" => $application['ward_mstr_id'],
                            "wardNo" => $application['old_ward_no']
                        ];
                    } else {
                        $owners['wardDetails'] = [
                            "wardId" => $application['new_ward_mstr_id'],
                            "wardNo" => $application['new_ward_no']
                        ];
                    }
                    # collecting all data 
                    $occupancyOwnerType = collect($mPropActiveSafsFloor->getOccupancyType($application['id'], $refTenanted));
                    $owners['owners']   = collect($mPropActiveSafOwners->getOwnerDtlsBySafId($application['id']));

                    # merge all data for return 
                    $details = $application->merge($owners)->merge($occupancyOwnerType);
                    return responseMsgs(true, "related Details!", $details, "", "", "", "POST", "");
                    break;
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $request->deviceId);
        }
    }


    /**
     * | Logged in citizen Holding & Saf
        | Serial No :
        | Working
     */
    public function citizenHoldingSaf(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'type'  => 'required|In:holding,saf,ptn',
                'ulbId' => 'required|numeric'
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {

            $citizenId                  = authUser($req)->id;
            $ulbId                      = $req->ulbId;
            $type                       = $req->type;
            $mPropActiveSafs            = new PropActiveSaf();
            $mPropProperty              = new PropProperty();
            $mActiveCitizenUndercare    = new ActiveCitizenUndercare();
            $caretakerProperty          = $mActiveCitizenUndercare->getTaggedPropsByCitizenId($citizenId);

            if ($type == 'saf') {
                $data = $mPropActiveSafs->getCitizenSafs($citizenId, $ulbId);
                $msg = 'Citizen Safs';
            }

            if ($type == 'holding') {
                $data = $mPropProperty->getCitizenHoldings($citizenId, $ulbId);
                if ($caretakerProperty->isNotEmpty()) {
                    $propertyId = collect($caretakerProperty)->pluck('property_id');
                    $data2 = $mPropProperty->getNewholding($propertyId);
                    $data = $data->merge($data2);
                }
                $data = collect($data)->map(function ($value) {
                    if (!is_null($value['new_holding_no']) || !is_null($value['holding_no'])) {
                        return $value;
                    }
                })->filter()->values();
                $msg = 'Citizen Holdings';
            }
            if ($data->isEmpty())
                throw new Exception('No Data Found');

            return responseMsgs(true, $msg, remove_null($data), '010801', '01', '623ms', 'Post', '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }


    /**
     * | Show approved appliction for citizen side
        | Serial No :
        | Working
     */
    public function getApproveRegistration(Request $req)
    {
        try {
            $user                       = authUser($req);
            $confUserType               = $this->_userType;
            $mPetApprovedRegistration   = new PetApprovedRegistration();

            if ($user->user_type != $confUserType['1']) {                                       // If not a citizen
                throw new Exception("You are not an autherised Citizen!");
            }
            # Collect querry Exceptions 
            try {
                $refApproveDetails = $mPetApprovedRegistration->getAllApprovdApplicationDetails()
                    ->select(
                        DB::raw("REPLACE(pet_approved_registrations.application_type, '_', ' ') AS ref_application_type"),
                        DB::raw("TO_CHAR(pet_approved_registrations.application_apply_date, 'DD-MM-YYYY') as ref_application_apply_date"),
                        "pet_approved_registrations.*",
                        "pet_approve_applicants.applicant_name",
                        "wf_roles.role_name",
                        "pet_approved_registrations.status as registrationSatus",
                        DB::raw("CASE 
                                    WHEN pet_approved_registrations.status = 1 THEN 'Approved'
                                    WHEN pet_approved_registrations.status = 2 THEN 'Under Renewal Process'
                                END as current_status"),
                        "pet_renewal_registrations.id as renewal_id",
                        DB::raw("CASE 
                                    WHEN 'pet_renewal_registrations.id as renewal_id' IS NULL THEN 'true'
                                        else 'false'
                                END as preview_button"),
                    )
                    ->leftJoin('pet_renewal_registrations', 'pet_renewal_registrations.registration_id', 'pet_approved_registrations.registration_id')
                    ->where('pet_approved_registrations.status', 1)
                    ->where('pet_approved_registrations.citizen_id', $user->id)
                    ->orderByDesc('pet_approved_registrations.id')
                    ->get();
            } catch (QueryException $qurry) {
                return responseMsgs(false, "An error occurred during the query!", $qurry->getMessage(), "", "01", ".ms", "POST", $req->deviceId);
            }
            return responseMsgs(true, "list of active registration!", remove_null($refApproveDetails), "", "01", ".ms", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }


    /**
     * | Edit the application pet details
        | Serial No :
        | Working
        | CAUTION
     */
    public function editPetDetails(PetEditReq $req)
    {
        try {
            $applicationId          = $req->id;
            $confTableName          = $this->_tableName;
            $mPetActiveDetail       = new PetActiveDetail();
            $mPetActiveRegistration = new PetActiveRegistration();
            $mPetAudit              = new PetAudit();
            $refRelatedDetails      = $this->checkParamForPetUdate($req);
            $applicationDetails     = $refRelatedDetails['applicationDetails'];

            $this->begin();
            # operate with the data from above calling function 
            $petDetails     = $mPetActiveDetail->getPetDetailsByApplicationId($applicationId)->first();
            $oldPetDetails  = json_encode($petDetails);
            $oldApplication = json_encode($applicationDetails);

            $mPetAudit->saveAuditData($oldPetDetails, $confTableName['1']);
            $mPetAudit->saveAuditData($oldApplication, $confTableName['2']);
            $mPetActiveDetail->updatePetDetails($req, $petDetails);
            $updateReq = [
                "occurrence_type_id" => $req->petFrom ?? $applicationDetails->occurrence_type_id
            ];
            $mPetActiveRegistration->saveApplicationStatus($applicationDetails->ref_application_id, $updateReq);
            $this->commit();
            return responseMsgs(true, "Pet Details Updated!", [], "", "01", ".ms", "POST", $req->deviceId);
        } catch (Exception $e) {
            $this->rollback();
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }

    /**
     * | Check Param for update the pet Application details 
        | Serial No : 
        | Working
     */
    public function checkParamForPetUdate($req)
    {
        $user                   = authUser($req);
        $applicationId          = $req->id;
        $confRoles              = $this->_petWfRoles;
        $mPetActiveRegistration = new PetActiveRegistration();
        $mWfRoleusermap         = new WfRoleusermap();
        $mPetTran               = new PetTran();

        # Collecting application detials
        $applicationdetails = $mPetActiveRegistration->getPetApplicationById($applicationId)->first();
        if (!$applicationdetails) {
            throw new Exception("Application details not found");
        }
        if ($applicationdetails->renewal == 1) {
            throw new Exception("Application cannot be edited in case of renewal");
        }

        # Validation diff btw citizen and user
        switch ($applicationdetails) {
            case (is_null($applicationdetails->citizen_id) && !is_null($applicationdetails->user_id)):
                $getRoleReq = new Request([                                                 // make request to get role id of the user
                    'userId'        => $user->id,
                    'workflowId'    => $applicationdetails->workflow_id
                ]);
                $readRoleDtls = $mWfRoleusermap->getRoleByUserWfId($getRoleReq);
                if (!$readRoleDtls) {
                    throw new Exception("User Don't have any role");
                }

                # Check for jsk
                $roleId = $readRoleDtls->wf_role_id;
                if ($roleId != $applicationdetails->current_role_id) {
                    throw new Exception("You are not Permitted to edit the application");
                }
                // if ($roleId != $confRoles['JSK']) {
                //     throw new Exception("You are not Permitted to edit the application");
                // }
                // if ($user->id != $applicationdetails->user_id) {
                //     throw new Exception("You are not the right user who applied");
                // }
                if ($applicationdetails->payment_status == 1 && $roleId == $confRoles['JSK']) {
                    throw new Exception("Payment is done application cannot be updated");
                }
                break;

            case (is_null($applicationdetails->user_id)):
                if ($user->id != $applicationdetails->citizen_id) {
                    throw new Exception("You are not the right user who applied");
                }
                if ($applicationdetails->payment_status == 1) {
                    throw new Exception("Payment is done application cannot be updated");
                }
                break;
        }

        // # Checking the transaction details 
        // $transactionDetails = $mPetTran->getTranByApplicationId($applicationId)->first();
        // if ($transactionDetails) {
        //     throw new Exception("Transaction data exist application cannot be updated");
        // }
        return [
            "applicationDetails" => $applicationdetails,
        ];
    }

    /**
     * | Apply the renewal for pet 
     * | registered pet renewal process
        | Serial No :
        | Under Con 
        | Check
     */
    public function applyPetRenewal(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'registrationId'    => 'required|int',
                'dateOfLepVaccine'  => 'required',
                'dateOfRabiesVac'   => 'required',
                'doctorName'        => 'required',
                'doctorRegNo'       => 'required'
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $renewal = 1;                                                                           // Static
            $mPetApprovedRegistration = new PetApprovedRegistration();

            # Check the Registered Application existence
            $refApprovedDetails = $mPetApprovedRegistration->getApplictionByRegId($request->registrationId)->first();
            $today = date('Y-m-d');
            if (!$refApprovedDetails) {
                throw new Exception("Application detail not found");
            }

            if ($refApprovedDetails->approve_end_date > $today) {
                throw new Exception("Application not expired! Can't apply for renewal");
            }

            # Check Params for renewal of Application
            $this->checkParamForRenewal($refApprovedDetails->registration_id, $refApprovedDetails);
            $newReq = new PetRegistrationReq([
                "address"           => $refApprovedDetails->address,
                "applyThrough"      => $refApprovedDetails->apply_through,
                "breed"             => $refApprovedDetails->breed,
                "ownerCategory"     => $refApprovedDetails->ref_owner_type,
                "color"             => $refApprovedDetails->color,
                "dateOfLepVaccine"  => $request->dateOfLepVaccine,
                "dateOfRabies"      => $request->dateOfRabiesVac,
                "doctorName"        => $request->doctorName,
                "doctorRegNo"       => $request->doctorRegNo,
                "petBirthDate"      => $refApprovedDetails->dob,
                "petFrom"           => $refApprovedDetails->occurrence_type_id,
                "petGender"         => $refApprovedDetails->sex,
                "petIdentity"       => $refApprovedDetails->identification_mark,
                "petName"           => $refApprovedDetails->pet_name,
                "petType"           => $refApprovedDetails->pet_type,
                "ulbId"             => $refApprovedDetails->ulb_id,
                "ward"              => $refApprovedDetails->ward_id,
                "applicantName"     => $refApprovedDetails->applicant_name,
                "mobileNo"          => $refApprovedDetails->mobile_no,
                "email"             => $request->email ?? $refApprovedDetails->email,
                "panNo"             => $refApprovedDetails->pan_no,
                "telephone"         => $refApprovedDetails->telephone,
                "propertyNo"        => $refApprovedDetails->holding_no ?? $refApprovedDetails->saf_no,

                "registrationId"    => $refApprovedDetails->registration_id,        // Important
                "isRenewal"         => $renewal,                                    // Static
                "auth"              => $request->auth
            ]);

            $this->begin();
            # Apply so that appliction get to workflow
            $applyDetails = $this->applyPetRegistration($newReq);                   // Here 
            if ($applyDetails->original['status'] == false) {
                throw new Exception($applyDetails->original['message'] ?? "Renewal Process can't be done");
            };
            # Update the details for renewal
            $this->updateRenewalDetails($refApprovedDetails);
            $this->commit();
            $returnDetails = $applyDetails->original['data'];
            return responseMsgs(true, "Application applied for renewal", $returnDetails, "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            $this->rollback();
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }

    /**
     * | check param for renewal of pet 
        | Serial No :
        | Under con
        | ❗❗ Uncomment the restriction for yearly licence check ❗❗
     */
    public function checkParamForRenewal($renewalId, $refApprovedDetails)
    {
        // $now = Carbon::now();
        $mPetActiveRegistration = new PetActiveRegistration();
        $isRenewalInProcess = $mPetActiveRegistration->getApplicationByRegId($renewalId)
            ->where('renewal', 1)
            ->first();
        if ($isRenewalInProcess) {
            throw new Exception("Renewal of the Application is in process");
        }

        # Check the lecence year difference 
        // $approveDate = Carbon::parse($refApprovedDetails->approve_date);
        // $approveDate = $approveDate->copy()->addDays(7);
        // $yearDifferernce = $approveDate->diffInYears($now);
        // if ($yearDifferernce <= 0) {
        //     throw new Exception("Application has an active licence please apply Later!");
        // }
    }

    /**
     * | Update the status for the renewal process in approved table and to other related table
        | Serial No :
        | Working
     */
    public function updateRenewalDetails($previousApproveDetils)
    {
        $mPetApprovedRegistration = new PetApprovedRegistration();
        $updateReq = [
            'status' => 2                                                                       // Static
        ];
        $mPetApprovedRegistration->updateRelatedStatus($previousApproveDetils->approveId, $updateReq);
    }

    /**
     * | Search active applications according to certain search category
        | Serial No :
        | Working
     */
    public function searchApplication1(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'filterBy'  => 'required|in:mobileNo,applicantName,applicationNo,holdingNo,safNo',
                'parameter' => 'required',
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            # Variable assigning
            $key        = $request->filterBy;
            $paramenter = $request->parameter;
            $pages      = $request->perPage ?? 10;
            $refstring  = Str::snake($key);
            $msg        = "Pet active appliction details according to parameter";

            $mPetActiveRegistration = new PetActiveRegistration();
            $mPetActiveApplicant    = new PetActiveApplicant();

            # Distrubtion of search category
            switch ($key) {
                case ("mobileNo"):                                                                                                                      // Static
                    $activeApplication = $mPetActiveApplicant->getRelatedApplicationDetails($request, $refstring, $paramenter)->paginate($pages);
                    break;
                case ("applicationNo"):
                    $activeApplication = $mPetActiveRegistration->getActiveApplicationDetails($request, $refstring, $paramenter)->paginate($pages);
                    break;
                case ("applicantName"):
                    $activeApplication = $mPetActiveApplicant->getRelatedApplicationDetails($request, $refstring, $paramenter)->paginate($pages);
                    break;
                case ("holdingNo"):
                    $activeApplication = $mPetActiveRegistration->getActiveApplicationDetails($request, $refstring, $paramenter)->paginate($pages);
                    break;
                case ("safNo"):
                    $activeApplication = $mPetActiveRegistration->getActiveApplicationDetails($request, $refstring, $paramenter)->paginate($pages);
                    break;
                default:
                    throw new Exception("Invalid Data");
            }
            # Check if data not exist
            $checkVal = collect($activeApplication)->last();
            if (!$checkVal || $checkVal == 0) {
                $msg = "No data found";
            }
            return responseMsgs(true, $msg, remove_null($activeApplication), "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }

    #written by prity pandey

    public function searchApplication(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'filterBy'  => 'nullable|in:mobileNo,applicantName,applicationNo,holdingNo,safNo',              // Static
                'parameter' => 'nullable',
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $user = authUser($request);
            $ulbId = $user->ulb_id;
            $key        = $request->filterBy;
            $parameter = $request->parameter;
            $pages      = $request->perPage ?? 10;
            $refstring                  = Str::snake($key);
            $msg                        = "Pending application list";
            $baseQuerry = PetActiveApplicant::select(
                'pet_active_registrations.id',
                'pet_active_registrations.application_no',
                DB::raw("REPLACE(pet_active_registrations.application_type, '_', ' ') AS application_type"),
                'pet_active_registrations.payment_status',
                'pet_active_registrations.application_apply_date',
                'pet_active_registrations.doc_upload_status',
                'pet_active_registrations.renewal',
                'pet_active_applicants.mobile_no',
                'pet_active_applicants.applicant_name'
            )
                ->join('pet_active_registrations', 'pet_active_registrations.id', 'pet_active_applicants.application_id')
                ->where('pet_active_registrations.status', 1)
                ->where('pet_active_registrations.ulb_id', $ulbId)
                ->orderByDesc('pet_active_registrations.id');

            if ($key && $request->parameter) {
                $msg = "Pet appliction details according to $key";
                switch ($key) {
                    case ("mobileNo"):
                        $activeApplication = $baseQuerry->where('pet_active_applicants.mobile_no', 'LIKE', "%$parameter%")->paginate($pages);
                        break;
                    case ("applicationNo"):
                        $activeApplication = $baseQuerry->where('pet_active_registrations.application_no', 'LIKE', "%$parameter%")->paginate($pages);
                        break;
                    case ("applicantName"):
                        $activeApplication = $baseQuerry->where('pet_active_applicants.applicant_name', 'LIKE', "%$parameter%")->paginate($pages);
                        break;
                    case ("holdingNo"):
                        $activeApplication = $baseQuerry->where('pet_approved_registrations.holding_no', 'LIKE', "%$parameter%")->paginate($pages);
                        break;
                    case ("safNo"):
                        $activeApplication = $baseQuerry->where('pet_approved_registrations.saf_no', 'LIKE', "%$parameter%")->paginate($pages);
                        break;
                    default:
                        throw new Exception("Invalid Data");
                }
                $checkVal = collect($activeApplication)->last();
                if (!$checkVal || $checkVal == 0) {
                    $msg = "No data found";
                }
                return responseMsgs(true, $msg, remove_null($activeApplication), "", "01", responseTime(), $request->getMethod(), $request->deviceId);
            }
            $returnData = $baseQuerry->paginate($pages);
            return responseMsgs(true, $msg, remove_null($returnData), "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }


    /**
     * | Search approved applications according to certain search category
        | Serial No :
        | Working
     */
    public function searchApprovedApplication1(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'filterBy'  => 'required|in:mobileNo,applicantName,applicationNo,holdingNo,safNo',              // Static
                'parameter' => 'required',
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            # Variable assigning
            $key        = $request->filterBy;
            $paramenter = $request->parameter;
            $pages      = $request->perPage ?? 10;
            $refstring  = Str::snake($key);
            $msg        = "Pet approved appliction details according to $key";

            $mPetApprovedRegistration   = new PetApprovedRegistration();
            $mPetApproveApplicant       = new PetApproveApplicant();

            # Distrubtion of search category  ❗❗ Static
            switch ($key) {
                case ("mobileNo"):
                    $activeApplication = $mPetApproveApplicant->getRelatedApproveApplicationDetails($request, $refstring, $paramenter)->paginate($pages);
                    break;
                case ("applicationNo"):
                    $activeApplication = $mPetApprovedRegistration->getApprovedApplicationDetails($request, $refstring, $paramenter)->paginate($pages);
                    break;
                case ("applicantName"):
                    $activeApplication = $mPetApproveApplicant->getRelatedApproveApplicationDetails($request, $refstring, $paramenter)->paginate($pages);
                    break;
                case ("holdingNo"):
                    $activeApplication = $mPetApprovedRegistration->getApprovedApplicationDetails($request, $refstring, $paramenter)->paginate($pages);
                    break;
                case ("safNo"):
                    $activeApplication = $mPetApprovedRegistration->getApprovedApplicationDetails($request, $refstring, $paramenter)->paginate($pages);
                    break;
                default:
                    throw new Exception("Invalid Data");
            }
            # Check if data not exist
            $checkVal = collect($activeApplication)->last();
            if (!$checkVal || $checkVal == 0) {
                $msg = "No data found";
            }
            return responseMsgs(true, $msg, remove_null($activeApplication), "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }


    #writen by prity pandey


    public function searchApprovedApplication(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'filterBy'  => 'nullable|in:mobileNo,applicantName,applicationNo,holdingNo,safNo',              // Static
                'parameter' => 'nullable',
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $user = authUser($request);
            $ulbId = $user->ulb_id;
            $key        = $request->filterBy;
            $parameter  = $request->parameter;
            $pages      = $request->perPage ?? 10;
            $refstring  = Str::snake($key);
            $msg        = "Approve application list!";
            $mPetApprovedRegistration   = new PetApprovedRegistration();

            $baseQuery = DB::table('pet_approved_registrations')
                ->select(
                    'pet_approved_registrations.id',
                    'pet_approved_registrations.holding_no',
                    'pet_approved_registrations.application_no',
                    DB::raw("REPLACE(pet_approved_registrations.application_type, '_', ' ') AS application_type"),
                    'pet_approved_registrations.payment_status',
                    'pet_approved_registrations.saf_no',
                    'pet_approved_registrations.application_apply_date',
                    'pet_approved_registrations.doc_upload_status',
                    'pet_approved_registrations.renewal',
                    'pet_approved_registrations.registration_id',
                    'pet_approve_applicants.mobile_no',
                    'pet_approve_applicants.applicant_name',)
                   // "pet_renewal_registrations.id as renewal_id",

                // ->leftJoin('pet_renewal_registrations', 'pet_renewal_registrations.registration_id', 'pet_approved_registrations.registration_id')
                ->join('pet_approve_applicants', 'pet_approve_applicants.application_id', 'pet_approved_registrations.id')
                ->where('pet_approved_registrations.status', 1)
                ->where('pet_approved_registrations.ulb_id', $ulbId)
                ->orderByDesc('pet_approved_registrations.id');

            if ($key && $parameter) {
                $msg = "Pet approved application details according to $key!";
                switch ($key) {
                    case "mobileNo":
                        $activeApplication = $baseQuery->where('pet_approve_applicants.mobile_no', 'LIKE', "%$parameter%")->paginate($pages);
                        break;
                    case ("applicationNo"):
                        $activeApplication = $baseQuery->where('pet_approved_registrations.application_no', 'LIKE', "%$parameter%")->paginate($pages);
                        break;
                    case "applicantName":
                        $activeApplication = $baseQuery->where('pet_approve_applicants.applicant_name', 'LIKE', "%$parameter%")->paginate($pages);
                        break;
                    case "holdingNo":
                        $activeApplication = $baseQuery->where('pet_approved_registrations.holding_no', 'LIKE', "%$parameter%")->paginate($pages);
                        break;
                    case "safNo":
                        $activeApplication = $baseQuery->where('pet_approved_registrations.saf_no', 'LIKE', "%$parameter%")->paginate($pages);
                        break;
                    default:
                        throw new Exception("Invalid Data");
                }
                if ($activeApplication->isEmpty()) {
                    $msg = "No data found";
                }
                return responseMsgs(true, $msg, remove_null($activeApplication), "", "01", responseTime(), $request->getMethod(), $request->deviceId);
            }
            $returnData = $baseQuery->paginate($pages);
            return responseMsgs(true, $msg, remove_null($returnData), "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }


    /**
     * | Get Approved application details by application id
     * | collective data with registration charges
        | Serial No :
        | Working
     */
    public function getApprovedApplicationDetails(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'registrationId' => 'required'
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $user                       = authUser($req);
            $viewRenewButton            = false;
            $applicationId              = $req->registrationId;
            $mPetApprovedRegistration   = new PetApprovedRegistration();
            $mPetRegistrationCharge     = new PetRegistrationCharge();
            $mPetTran                   = new PetTran();

            $approveApplicationDetails = $mPetApprovedRegistration->getPetApprovedApplicationById($applicationId)
                ->where('pet_approved_registrations.status',1)                                                       
                ->first();
            if (is_null($approveApplicationDetails)) {
                throw new Exception("Application not found");
            }
            $chargeDetails = $mPetRegistrationCharge->getChargesbyId($approveApplicationDetails->application_id)
                ->select(
                    'id AS chargeId',
                    'amount',
                    'registration_fee',
                    'paid_status',
                    'charge_category',
                    'charge_category_name'
                )
                ->where('paid_status', 1)                                                                                   // Static
                ->first();
            if (is_null($chargeDetails)) {
                throw new Exception("Charges for respective application not found");
            }
            # Get Transaction details 
            $tranDetails = $mPetTran->getTranByApplicationId($approveApplicationDetails->application_id)->first();
            if (!$tranDetails) {
                throw new Exception("Transaction details not found there is some error in data !");
            }
            # Check for jsk for renewal button
            // if ($user->user_type == 'JSK') {                                                                                // Static
            //     $viewRenewButton = true;
            // }

            # return Details 
            $approveApplicationDetails['transactionDetails']    = $tranDetails;
            $chargeDetails['roundAmount']                       = round($chargeDetails['amount']);
            $approveApplicationDetails['charges']               = $chargeDetails;
            $approveApplicationDetails['viewRenewalButton']     = $viewRenewButton;
            return responseMsgs(true, "Listed application details!", remove_null($approveApplicationDetails), "", "01", ".ms", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }


    /**
     * | Search rejected applications 
        | Serial No :
        | Working
     */
    public function searchRejectedApplication1(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'filterBy'  => 'required|in:mobileNo,applicantName,applicationNo,holdingNo,safNo',              // Static
                'parameter' => 'required',
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            # Variable assigning
            $key        = $request->filterBy;
            $paramenter = $request->parameter;
            $pages      = $request->perPage ?? 10;
            $refstring  = Str::snake($key);
            $msg        = "Pet rejected appliction details according to $key!";

            $mPetRejectedRegistration   = new PetRejectedRegistration();
            $mPetRejectedApplicant      = new PetRejectedApplicant();

            # Distrubtion of search category  ❗❗ Static
            switch ($key) {
                case ("mobileNo"):
                    $rejectedApplications = $mPetRejectedApplicant->getRelatedRejectedApplicationDetails($request, $refstring, $paramenter)->paginate($pages);
                    break;
                case ("applicationNo"):
                    $rejectedApplications = $mPetRejectedRegistration->getRejectedApplicationDetails($request, $refstring, $paramenter)->paginate($pages);
                    break;
                case ("applicantName"):
                    $rejectedApplications = $mPetRejectedApplicant->getRelatedRejectedApplicationDetails($request, $refstring, $paramenter)->paginate($pages);
                    break;
                case ("holdingNo"):
                    $rejectedApplications = $mPetRejectedRegistration->getRejectedApplicationDetails($request, $refstring, $paramenter)->paginate($pages);
                    break;
                case ("safNo"):
                    $rejectedApplications = $mPetRejectedRegistration->getRejectedApplicationDetails($request, $refstring, $paramenter)->paginate($pages);
                    break;
                default:
                    throw new Exception("Invalid Data");
            }
            # Check if data not exist
            $checkVal = collect($rejectedApplications)->last();
            if (!$checkVal || $checkVal == 0) {
                $msg = "No data found";
            }
            return responseMsgs(true, $msg, remove_null($rejectedApplications), "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }

    # written by prity pandey


    public function searchRejectedApplication(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'filterBy'  => 'nullable|in:mobileNo,applicantName,applicationNo,holdingNo,safNo',              // Static
                'parameter' => 'nullable',
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $user       = authUser($request);
            $ulbId      = $user->ulb_id;
            $key        = $request->filterBy;
            $parameter  = $request->parameter;
            $pages      = $request->perPage ?? 10;
            $refstring                  = Str::snake($key);
            $msg                        = "Rejected application list!";
            $mPetApprovedRegistration   = new PetApprovedRegistration();

            $baseQuerry =  DB::table('pet_rejected_registrations')
                ->select(
                    'pet_rejected_registrations.id',
                    'pet_rejected_registrations.holding_no',
                    'pet_rejected_registrations.saf_no',
                    'pet_rejected_registrations.application_no',
                    DB::raw("REPLACE(pet_rejected_registrations.application_type, '_', ' ') AS application_type"),
                    'pet_rejected_registrations.payment_status',
                    'pet_rejected_registrations.application_apply_date',
                    'pet_rejected_registrations.doc_upload_status',
                    'pet_rejected_registrations.renewal',
                    'pet_rejected_registrations.registration_id',
                    'pet_rejected_applicants.mobile_no',
                    'pet_rejected_applicants.applicant_name'
                )
                ->join('pet_rejected_applicants', 'pet_rejected_registrations.id', 'pet_rejected_applicants.application_id')
                ->where('pet_rejected_registrations.status', 1)
                ->where('pet_rejected_registrations.ulb_id', $ulbId)
                ->orderByDesc('pet_rejected_registrations.id');


            if ($key && $request->parameter) {
                $msg = "Pet rejected appliction details according to $key!";
                switch ($key) {
                    case ("mobileNo"):
                        $activeApplication = $baseQuerry->where('pet_rejected_applicants.mobile_no', 'LIKE', "%$parameter%")->paginate($pages);
                        break;
                    case ("applicationNo"):
                        $activeApplication = $baseQuerry->where('pet_rejected_registrations.application_no', 'LIKE', "%$parameter%")->paginate($pages);
                        break;
                    case ("applicantName"):
                        $activeApplication = $baseQuerry->where('pet_rejected_applicants.applicant_name', 'LIKE', "%$parameter%")
                            ->paginate($pages);
                        break;
                    case ("holdingNo"):
                        $activeApplication = $baseQuerry->where('pet_rejected_registrations.holding_no', 'LIKE', "%$parameter%")
                            ->paginate($pages);
                        break;
                    case ("safNo"):
                        $activeApplication = $baseQuerry->where('pet_rejected_registrations.saf_no', 'LIKE', "%$parameter%")
                            ->paginate($pages);
                        break;
                    default:
                        throw new Exception("Invalid Data");
                }
                $checkVal = collect($activeApplication)->last();
                if (!$checkVal || $checkVal == 0) {
                    $msg = "No data found";
                }
                return responseMsgs(true, $msg, remove_null($activeApplication), "", "01", responseTime(), $request->getMethod(), $request->deviceId);
            }
            $returnData = $baseQuerry->paginate($pages);
            return responseMsgs(true, $msg, remove_null($returnData), "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }

    /**
     * | Get the rejected application details 
        | Serial No :
        | Working
     */
    public function getRejectedApplicationDetails(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'registrationId' => 'required'
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $viewRenewButton            = false;
            $applicationId              = $req->registrationId;
            $mPetRejectedRegistration   = new PetRejectedRegistration();
            $mPetRegistrationCharge     = new PetRegistrationCharge();
            $mPetTran                   = new PetTran();

            $rejectedApplicationDetails = $mPetRejectedRegistration->getPetRejectedApplicationById($applicationId)
                ->where('pet_rejected_registrations.status', '<>', 0)                                                       // Static
                ->first();
            if (is_null($rejectedApplicationDetails)) {
                throw new Exception("Application not found");
            }
            $chargeDetails = $mPetRegistrationCharge->getChargesbyId($rejectedApplicationDetails->application_id)
                ->select(
                    'id AS chargeId',
                    'amount',
                    'registration_fee',
                    'paid_status',
                    'charge_category',
                    'charge_category_name'
                )
                ->where('paid_status', 1)                                                                                   // Static
                ->first();
            if (is_null($chargeDetails)) {
                throw new Exception("Charges for respective application not found");
            }
            # Get Transaction details 
            $tranDetails = $mPetTran->getTranByApplicationId($rejectedApplicationDetails->application_id)->first();
            if (!$tranDetails) {
                throw new Exception("Transaction details not found there is some error in data !");
            }

            # return Details 
            $rejectedApplicationDetails['transactionDetails']    = $tranDetails;
            $chargeDetails['roundAmount']                       = round($chargeDetails['amount']);
            $rejectedApplicationDetails['charges']               = $chargeDetails;
            $rejectedApplicationDetails['viewRenewalButton']     = $viewRenewButton;
            return responseMsgs(true, "Listed application details!", remove_null($rejectedApplicationDetails), "", "01", ".ms", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }


    /**
     * | get the rejected applications for respective user
        | Serial No :
        | Working
     */
    public function getRejectedRegistration(Request $req)
    {
        try {
            $user                       = authUser($req);
            $confUserType               = $this->_userType;
            $mPetRejectedRegistration   = new PetRejectedRegistration();

            if ($user->user_type != $confUserType['1']) {                                       // If not a citizen
                throw new Exception("You are not an autherised Citizen!");
            }
            # Collect querry Exceptions 
            try {
                $refRejectedDetails = $mPetRejectedRegistration->getAllRejectedApplicationDetails()
                    ->select(
                        DB::raw("REPLACE(pet_rejected_registrations.application_type, '_', ' ') AS ref_application_type"),
                        DB::raw("TO_CHAR(pet_rejected_registrations.application_apply_date, 'DD-MM-YYYY') as ref_application_apply_date"),
                        "pet_rejected_registrations.*",
                        "pet_rejected_applicants.applicant_name",
                        "wf_roles.role_name",
                        "pet_rejected_registrations.status as registrationSatus",
                        DB::raw("CASE 
                        WHEN pet_rejected_registrations.status = 1 THEN 'Approved'
                        WHEN pet_rejected_registrations.status = 2 THEN 'Under Renewal Process'
                        END as current_status")
                    )
                    ->where('pet_rejected_registrations.status', '<>', 0)
                    ->where('pet_rejected_registrations.citizen_id', $user->id)
                    ->orderByDesc('pet_rejected_registrations.id')
                    ->get();
            } catch (QueryException $qurry) {
                return responseMsgs(false, "An error occurred during the query!", $qurry->getMessage(), "", "01", responseTime(), $req->getMethod(), $req->deviceId);
            }
            return responseMsgs(true, "list of active registration!", remove_null($refRejectedDetails), "", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }


    /**
     * | Get the renewal application list / history
        | Srial No :
        | Under con
     */
    public function getRenewalHistory(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'registrationId' => 'required'
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $mPetRenewalRegistration = new PetRenewalRegistration();
            $registrationId = $request->registrationId;
            $msg = "Renewal application list!";
            $renewalList = $mPetRenewalRegistration->getRenewalApplicationByRegId($registrationId)
                ->select(
                    'pet_renewal_registrations.id',
                    'ulb_masters.ulb_name',
                    'pet_renewal_registrations.application_no',
                    'pet_renewal_applicants.applicant_name',
                    'pet_renewal_registrations.address',
                    'pet_renewal_registrations.approve_date AS validFrom',
                    'pet_renewal_registrations.approve_end_date AS validUpto'
                )->get();
            if (!$renewalList->first()) {
                $msg = "No data found";
            }
            return responseMsgs(true, $msg, remove_null($renewalList), "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }


    /**
     * | get the renewal application details according to registration Id
        | Serial No :
        | Under Con
     */
    public function getRenewalApplicationDetails(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'registrationId' => 'required'
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $viewRenewButton            = false;
            $applicationId              = $req->registrationId;
            $mPetRenewalRegistration    = new PetRenewalRegistration();
            $mPetTran                   = new PetTran();

            # Application detial 
            $renewalApplicationDetails = $mPetRenewalRegistration->getPetRenewalApplicationById($applicationId)
                ->where('pet_renewal_registrations.status', '<>', 0)                                                       // Static
                ->first();
            if (is_null($renewalApplicationDetails)) {
                throw new Exception("Application not found");
            }
            # Get Transaction details 
            $tranDetails = $mPetTran->getTranByApplicationId($renewalApplicationDetails->application_id)->first();
            if (!$tranDetails) {
                throw new Exception("Transaction details not found there is some error in data !");
            }

            # Return Details 
            $renewalApplicationDetails['transactionDetails']    = $tranDetails;
            $renewalApplicationDetails['viewRenewalButton']     = $viewRenewButton;
            return responseMsgs(true, "Listed application details!", remove_null($renewalApplicationDetails), "", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01",  responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    //written by prity pandey
    public function petDashbordDtls(Request $request)
    {
        try {
            $user = authUser($request);
            $userId = $user->id;
            $ulbId = $user->ulb_id;
            $userType = $user->user_type;
            $mPetActiveRegistration = new PetActiveRegistration();
            $mWfWorkflowRoleMaps = new WfWorkflowrolemap();
            $roleId = $this->getRoleIdByUserId($userId)->pluck('wf_role_id');
            $workflowIds = $mWfWorkflowRoleMaps->getWfByRoleId($roleId)->pluck('workflow_id');
            $data['recentApplications'] = $mPetActiveRegistration->recentApplication($workflowIds, $roleId, $ulbId);
            if ($userType == 'JSK') {
                $data['recentApplications'] = $mPetActiveRegistration->recentApplicationJsk($userId, $ulbId);
            }
            $data['pendingApplicationCount'] = $mPetActiveRegistration->pendingApplicationCount($ulbId);
            $data['approvedApplicationCount'] = $mPetActiveRegistration->approvedApplicationCount($ulbId);
            return responseMsgs(true, "Recent Application", remove_null($data), "011901", "1.0", "", "POST", $request->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "011901", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }
}
