<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pet\PetRegistrationReq;
use App\MicroServices\IdGenerator\PrefixIdGenerator;
use App\Models\ApiMaster;
use App\Models\Pet\MPetOccurrenceType;
use App\Models\Pet\PetActiveApplicant;
use App\Models\Pet\PetActiveDetail;
use App\Models\Pet\PetActiveRegistration;
use App\Models\Pet\PetApprovedRegistration;
use App\Models\Pet\PetRenewalApplicant;
use App\Models\Pet\PetRenewalDetail;
use App\Models\Pet\PetRenewalRegistration;
use App\Models\Property\PropActiveSaf;
use App\Models\Property\PropActiveSafsFloor;
use App\Models\Property\PropFloor;
use App\Models\Property\PropProperty;
use App\Models\Workflows\WfWorkflow;
use App\Traits\Workflow\Workflow;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
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
    // Class constructer 
    public function __construct()
    {
        $this->_masterDetails = Config::get("pet.MASTER_DATA");
        $this->_propertyType = Config::get("pet.PROP_TYPE");
        $this->_occupancyType = Config::get("pet.PROP_OCCUPANCY_TYPE");
        $this->_workflowMasterId = Config::get("pet.WORKFLOW_MASTER_ID");
        $this->_petParamId = Config::get("pet.PARAM_ID");
    }

    /**
     * | Get all master data
     * | Collect the master data related pet module
        | Serial No : 0
        | Under Construction
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
                throw new Exception("master Data not Found!");
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
        | Under Construction
     */
    public function applyPetRegistration(PetRegistrationReq $req)
    {
        try {
            $mPetActiveDetail           = new PetActiveDetail();
            $mPetActiveRegistration     = new PetActiveRegistration();
            $mPetActiveApplicant        = new PetActiveApplicant();
            $mPetApprovedRegistration   = new PetApprovedRegistration();
            $mWfWorkflow                = new WfWorkflow();
            $user                       = authUser();
            $ulbId                      = $req->ulbId ?? 2;
            $workflowMasterId           = $this->_workflowMasterId;
            $petParamId                 = $this->_petParamId;

            # Get iniciater and finisher for the workflow 
            $ulbWorkflowId = $mWfWorkflow->getulbWorkflowId($workflowMasterId, $ulbId);
            if (!$ulbWorkflowId) {
                throw new Exception("Respective Ulb is not maped to 'Pet Registration' Workflow!");
            }
            $refInitiatorRoleId = $this->getInitiatorId($ulbWorkflowId->id);
            $refFinisherRoleId  = $this->getFinisherId($ulbWorkflowId->id);
            $finisherRoleId     = collect(DB::select($refFinisherRoleId))->first()->role_id;
            $initiatorRoleId    = collect(DB::select($refInitiatorRoleId))->first()->role_id;

            $refValidatedDetails = $this->checkParamForRegister($req);
            DB::beginTransaction();
            $idGeneration = new PrefixIdGenerator($petParamId, $ulbId);
            $petApplicationNo = $idGeneration->generate();
            $refData = [
                "finisherRoleId"    => $finisherRoleId,
                "initiatorRoleId"   => $initiatorRoleId,
                "holdingNo"         => collect($refValidatedDetails['propDetails'])['holding_no'] ?? null,
                "safNo"             => collect($refValidatedDetails['propDetails'])['saf_no'] ?? null,
                "workflowId"        => $ulbWorkflowId->id,
                "applicationNo"     => $petApplicationNo,
            ];
            $req->merge($refData);

            if ($req->isRenewal == 0 || !isset($req->isRenewal)) {
                if (isset($req->registrationId)) {
                    throw new Exception("Registration No is Not Req for new Pet Registraton!");
                }
                $refData = [
                    "applicationType" => "New_Apply"
                ];
                $req->merge($refData);
            }
            if ($req->isRenewal == 1) {
                $refData = [
                    "applicationType" => "Renewal",
                    "registrationId"  => $req->registrationId
                ];
                $req->merge($refData);
                $mPetApprovedRegistration->deactivateOldRegistration($req->registrationId);
            }
            # Save active details 
            $applicationDetails = $mPetActiveRegistration->saveRegistration($req, $user);
            $mPetActiveApplicant->saveApplicants($req, $applicationDetails['id']);
            $mPetActiveDetail->savePetDetails($req, $applicationDetails['id']);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
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
        | Under Construction
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

        switch ($req->applyThrough) {
            case ($req->applyThrough == $confApplyThrough['Holding']):
                $refPropDetails = $mPropProperty->getPropDtls()->where('prop_properties.holding_no', $req->propertyNo)
                    ->first();
                if (is_null($refPropDetails)) {
                    throw new Exception("property according to $req->propertyNo not found!");
                }
                if ($refPropDetails->prop_type_mstr_id != $confPropertyType['VACANT_LAND']) {
                    $floorsDetails = $mPropFloor->getPropFloors($refPropDetails->id)->get();
                    $isTenant = $this->getPropOccupancyType($floorsDetails);
                    if ($req->ownerCategory == $ownertype['Tenant'] && $isTenant == false) {
                        throw new Exception("Respective property dont have tenant!");
                    }
                }
                $returnDetails = [
                    "tenant"        => $isTenant,
                    "propDetails"   => $refPropDetails,
                ];
                break;

            case ($req->applyThrough == $confApplyThrough['Saf']):
                $refSafDetails = $mPropActiveSaf->getSafDtlBySaf()->where('s.saf_no', $req->propertyNo)
                    ->first();
                if (is_null($refSafDetails)) {
                    throw new Exception("property according to $req->propertyNo not found!");
                }
                if ($refSafDetails->prop_type_mstr_id != $confPropertyType['VACANT_LAND']) {
                    $floorsDetails = $mPropActiveSafsFloor->getSafFloors($refSafDetails->id)->get();
                    $isTenant = $this->getPropOccupancyType($floorsDetails);
                    if ($req->ownerCategory == $ownertype['Tenant'] && $isTenant == false) {
                        throw new Exception("Respective property dont have tenant!");
                    }
                }
                $returnDetails = [
                    "tenant"        => $isTenant,
                    "propDetails"   => $refSafDetails,
                ];
                break;
        }
        return $returnDetails;
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
}
