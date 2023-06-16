<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pet\PetRegistrationReq;
use App\Models\ApiMaster;
use App\Models\Pet\MPetOccurrenceType;
use App\Models\Pet\PetActiveApplicant;
use App\Models\Pet\PetActiveDetail;
use App\Models\Pet\PetActiveRegistration;
use App\Models\Property\PropActiveSaf;
use App\Models\Property\PropActiveSafsFloor;
use App\Models\Property\PropFloor;
use App\Models\Property\PropProperty;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * | Created On-02-01-20222 
 * | Created By- Sam Kerketta
 * | Pet Registration Operations
 */

class PetRegistrationController extends Controller
{
    private $_masterDetails;
    private $_propertyType;
    private $_occupancyType;
    // Class constructer 
    public function __construct()
    {
        $this->_masterDetails = Config::get("pet.MASTER_DATA");
        $this->_propertyType = Config::get("pet.PROP_TYPE");
        $this->_occupancyType = Config::get("pet.PROP_OCCUPANCY_TYPE");
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
            $refMasterDetails = $this->_masterDetails;
            $returnData = [
                "occurenceType"         => $occurenceType,
                "registrationThrough"   => $refMasterDetails['REGISTRATION_THROUGH'],
                "ownertype"             => $refMasterDetails['OWNER_TYPE_MST'],
                "petGender"             => $refMasterDetails['PET_GENDER']
            ];
            $message = "list for Pet Module's master data!";
            return responseMsgs(true, $message, $returnData, "", "01", ".ms", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }


    /**
     * | Apply for the pet Registration
     * | Save form data 
     * | @param req
        | Serial No : 0
        | Under Construction
     */
    public function applyPetRegistration(Request $req)
    {
        try {
            $mPetActiveDetail       = new PetActiveDetail();
            $mPetActiveRegistration = new PetActiveRegistration();
            $mPetActiveApplicant    = new PetActiveApplicant();

            $refValidatedDetails = $this->checkParamForRegister($req);

            DB::beginTransaction();
            $mPetActiveRegistration->saveRegistration();
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
        $isTenant           = false;

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
            return true;
        }
        return false;
    }
}
