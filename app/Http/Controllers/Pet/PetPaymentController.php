<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pet\PetPaymentReq;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class PetPaymentController extends Controller
{
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
    }

    /**
     * | Pay the registration charges in offline mode 
        | Serial no :
        | Under construction 
     */
    public function offlinePayment(PetPaymentReq $req)
    {
        $req->validate([
            'amount' => 'required|int',
            'remarks' => 'sometimes'
        ]);
        try {
            $m = 
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }
}
