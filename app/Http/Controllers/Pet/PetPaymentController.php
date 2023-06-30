<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pet\PetPaymentReq;
use App\MicroServices\IdGenerator\PrefixIdGenerator;
use App\Models\Payment\TempTransaction;
use App\Models\Pet\PetActiveRegistration;
use App\Models\Pet\PetChequeDtl;
use App\Models\Pet\PetRegistrationCharge;
use App\Models\Pet\PetTran;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
    private $_applicationType;
    private $_offlineVerificationModes;
    private $_paymentMode;
    private $_offlineMode;
    # Class constructer 
    public function __construct()
    {
        $this->_masterDetails           = Config::get("pet.MASTER_DATA");
        $this->_propertyType            = Config::get("pet.PROP_TYPE");
        $this->_occupancyType           = Config::get("pet.PROP_OCCUPANCY_TYPE");
        $this->_workflowMasterId        = Config::get("pet.WORKFLOW_MASTER_ID");
        $this->_petParamId              = Config::get("pet.PARAM_ID");
        $this->_petModuleId             = Config::get('pet.PET_MODULE_ID');
        $this->_userType                = Config::get("pet.REF_USER_TYPE");
        $this->_petWfRoles              = Config::get("pet.ROLE_LABEL");
        $this->_docReqCatagory          = Config::get("pet.DOC_REQ_CATAGORY");
        $this->_dbKey                   = Config::get("pet.DB_KEYS");
        $this->_fee                     = Config::get("pet.FEE_CHARGES");
        $this->_applicationType         = Config::get("pet.APPLICATION_TYPE");
        $this->_offlineVerificationModes = Config::get("pet.VERIFICATION_PAYMENT_MODES");
        $this->_paymentMode             = Config::get("pet.PAYMENT_MODE");
        $this->_offlineMode             = Config::get("pet.OFFLINE_PAYMENT_MODE");
    }

    /**
     * | Pay the registration charges in offline mode 
        | Serial no :
        | Under construction 
     */
    public function offlinePayment(PetPaymentReq $req)
    {
        $req->validate([
            'amount' => 'required|numeric|min:0',
            'remarks' => 'sometimes'
        ]);
        try {
            $user = authUser();
            $petParamId = $this->_petParamId;
            $offlineVerificationModes = $this->_offlineVerificationModes;
            $todayDate = Carbon::now();
            $mPetActiveRegistration = new PetActiveRegistration();
            $mPetRegistrationCharge = new PetRegistrationCharge();
            $mPetTran = new PetTran();

            $payRelatedDetails = $this->checkParamForPayment($req);
            $ulbId = $payRelatedDetails['applicationDetails']['ulb_id'];
            $wardId = $payRelatedDetails['applicationDetails']['ward_id'];
            $tranType = $payRelatedDetails['applicationDetails']['application_type'];
            $tranTypeId = $payRelatedDetails['chargeCategory'];

            DB::beginTransaction();
            $idGeneration = new PrefixIdGenerator($petParamId['TRANSACTION'], $ulbId);
            $petTranNo = $idGeneration->generate();

            # Water Transactions
            $req->merge([
                'empId'         => $user->id,
                'userType'      => $user->user_type,
                'todayDate'     => $todayDate->format('Y-m-d'),
                'tranNo'        => $petTranNo,
                'ulbId'         => $ulbId,
                'isJsk'         => true,
                'wardId'        => $wardId,
                'tranType'      => $tranType,                                                              // Static
                'tranTypeId'    => $tranTypeId,
                'roundAmount'   => $req->amount
            ]);

            # Save the Details of the transaction
            $petTrans = $mPetTran->saveTranDetails($req);

            # Save the Details for the Cheque,DD,nfet
            if (in_array($req['paymentMode'], $offlineVerificationModes)) {
                $req->merge([
                    'chequeDate'    => $req['chequeDate'],
                    'tranId'        => $petTrans['transactionId'],
                    'applicationNo' => $payRelatedDetails['applicationDetails']['chargeCategory'],
                    'workflowId'    => $payRelatedDetails['applicationDetails']['workflow_id'],
                    'ref_ward_id'   => $payRelatedDetails['applicationDetails']['ward_id']
                ]);
                $this->postOtherPaymentModes($req);

                // $mPetActiveRegistration->

            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }


    /**
     * | Check the details and the function for the payment 
     * | return details for payment process
     * | @param req
        | Serial No: 
        | Under Construction
     */
    public function checkParamForPayment($req)
    {
        $applicationId          = $req->id;
        $confApplicationType    = $this->_applicationType;
        $mPetActiveRegistration = new PetActiveRegistration();
        $mPetRegistrationCharge = new PetRegistrationCharge();
        $mPetTran               = new PetTran();

        # Application details 
        $applicationDetail = $mPetActiveRegistration->getPetApplicationById($applicationId)
            ->where('pet_active_details.status', 1)
            ->where('pet_active_applicants.status', 1)
            ->first();
        if (is_null($applicationDetail)) {
            throw new Exception("Application details not found for ID:$applicationId!");
        }

        # Application type hence the charge type
        switch ($applicationDetail->renewal) {
            case (0):
                $chargeCategory = $confApplicationType['NEW_APPLY'];
                break;

            case (1):
                $chargeCategory = $confApplicationType['RENEWAL'];
                break;
        }

        # Charges for the application
        $regisCharges = $mPetRegistrationCharge->getChargesbyId($applicationId)
            ->where('charge_category', $chargeCategory)
            ->where('paid_status', 0)
            ->first();

        if (is_null($regisCharges)) {
            throw new Exception("Charges not found!");
        }
        if (round($regisCharges->amount) != $req->amount) {
            throw new Exception("Amount Not matched!");
        }
        if (in_array($regisCharges->paid_status, [1, 2])) {
            throw new Exception("Payment has been done!");
        }

        # Transaction details
        $transDetails = $mPetTran->getTranDetails($applicationId, $chargeCategory)->first();
        if ($transDetails) {
            throw new Exception("Transaction has been Done!");
        }

        return [
            "applicationDetails"    => $applicationDetail,
            "PetCharges"            => $regisCharges,
            "chargeCategory"        => $chargeCategory
        ];
    }


    /**
     * | Post Other Payment Modes for Cheque,DD,Neft
     * | @param req
        | Serial No : 0
        | Working
        | Common function
     */
    public function postOtherPaymentModes($req)
    {
        $paymentMode        = $this->_offlineMode;
        $moduleId           = $this->_petModuleId;
        $mTempTransaction   = new TempTransaction();
        $mPetChequeDtl      = new PetChequeDtl();

        if ($req['paymentMode'] != $paymentMode[4]) {                                   // Not Cash
            $chequeReqs = [
                'user_id'           => $req['userId'],
                'application_id'    => $req['id'],
                'transaction_id'    => $req['tranId'],
                'cheque_date'       => $req['chequeDate'],
                'bank_name'         => $req['bankName'],
                'branch_name'       => $req['branchName'],
                'cheque_no'         => $req['chequeNo']
            ];
            $mPetChequeDtl->postChequeDtl($chequeReqs);
        }

        $tranReqs = [
            'transaction_id'    => $req['tranId'],
            'application_id'    => $req['id'],
            'module_id'         => $moduleId,
            'workflow_id'       => $req['workflowId'],
            'transaction_no'    => $req['tranNo'],
            'application_no'    => $req['applicationNo'],
            'amount'            => $req['amount'],
            'payment_mode'      => strtoupper($req['paymentMode']),
            'cheque_dd_no'      => $req['chequeNo'],
            'bank_name'         => $req['bankName'],
            'tran_date'         => $req['todayDate'],
            'user_id'           => $req['userId'],
            'ulb_id'            => $req['ulbId'],
            'ward_no'           => $req['ref_ward_id']
        ];
        $mTempTransaction->tempTransaction($tranReqs);
    }
}
