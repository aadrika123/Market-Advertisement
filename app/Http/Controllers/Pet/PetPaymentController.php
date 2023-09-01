<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pet\PetPaymentReq;
use App\Http\Requests\Pet\PetRegistrationReq;
use App\MicroServices\IdGenerator\PrefixIdGenerator;
use App\Models\Payment\TempTransaction;
use App\Models\Pet\PetActiveRegistration;
use App\Models\Pet\PetApprovedRegistration;
use App\Models\Pet\PetChequeDtl;
use App\Models\Pet\PetRazorPayRequest;
use App\Models\Pet\PetRazorPayResponse;
use App\Models\Pet\PetRegistrationCharge;
use App\Models\Pet\PetRenewalRegistration;
use App\Models\Pet\PetTran;
use App\Models\Pet\PetTranDetail;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

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
    private $_PaymentUrl;
    private $_apiKey;
    private $_refStatus;

    # Class constructer 
    public function __construct()
    {
        $this->_masterDetails               = Config::get("pet.MASTER_DATA");
        $this->_propertyType                = Config::get("pet.PROP_TYPE");
        $this->_occupancyType               = Config::get("pet.PROP_OCCUPANCY_TYPE");
        $this->_workflowMasterId            = Config::get("pet.WORKFLOW_MASTER_ID");
        $this->_petParamId                  = Config::get("pet.PARAM_ID");
        $this->_petModuleId                 = Config::get('pet.PET_MODULE_ID');
        $this->_userType                    = Config::get("pet.REF_USER_TYPE");
        $this->_petWfRoles                  = Config::get("pet.ROLE_LABEL");
        $this->_docReqCatagory              = Config::get("pet.DOC_REQ_CATAGORY");
        $this->_dbKey                       = Config::get("pet.DB_KEYS");
        $this->_fee                         = Config::get("pet.FEE_CHARGES");
        $this->_applicationType             = Config::get("pet.APPLICATION_TYPE");
        $this->_offlineVerificationModes    = Config::get("pet.VERIFICATION_PAYMENT_MODES");
        $this->_paymentMode                 = Config::get("pet.PAYMENT_MODE");
        $this->_offlineMode                 = Config::get("pet.OFFLINE_PAYMENT_MODE");
        $this->_PaymentUrl                  = Config::get('constants.95_PAYMENT_URL');
        $this->_apiKey                      = Config::get('pet.API_KEY_PAYMENT');
        $this->_refStatus                   = Config::get('pet.REF_STATUS');
    }

    /**
     * | Pay the registration charges in offline mode 
        | Serial no :
        | Under construction 
     */
    public function offlinePayment(PetPaymentReq $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'remarks' => 'nullable',
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            # Variable declaration
            $user                       = authUser($req);
            $todayDate                  = Carbon::now();
            $petParamId                 = $this->_petParamId;
            $offlineVerificationModes   = $this->_offlineVerificationModes;
            $mPetTran                   = new PetTran();

            # Check the params for checking payment method
            $payRelatedDetails  = $this->checkParamForPayment($req, $req->paymentMode);
            $ulbId              = $payRelatedDetails['applicationDetails']['ulb_id'];
            $wardId             = $payRelatedDetails['applicationDetails']['ward_id'];
            $tranType           = $payRelatedDetails['applicationDetails']['application_type'];
            $tranTypeId         = $payRelatedDetails['chargeCategory'];

            DB::beginTransaction();
            # Generate transaction no 
            $idGeneration   = new PrefixIdGenerator($petParamId['TRANSACTION'], $ulbId);
            $petTranNo      = $idGeneration->generate();

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
                'amount'        => $payRelatedDetails['refRoundAmount'],
                'roundAmount'   => $payRelatedDetails['regAmount']
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
            }
            $this->savePetRequestStatus($req, $offlineVerificationModes, $payRelatedDetails['PetCharges'], $petTrans['transactionId'], $payRelatedDetails['applicationDetails']);
            DB::commit();
            $returnData = [
                "transactionNo" => $petTranNo
            ];
            return responseMsgs(true, "Paymet done!", $returnData, "", "01", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }


    /**
     * | Save the status in active consumer table, transaction, 
        | Serial No :
        | Working
     */
    public function savePetRequestStatus($request, $offlinePaymentVerModes, $charges, $waterTransId, $activeConRequest)
    {
        $mPetTranDetail         = new PetTranDetail();
        $mPetActiveRegistration = new PetActiveRegistration();
        $mPetTran               = new PetTran();

        if (in_array($request['paymentMode'], $offlinePaymentVerModes)) {
            $charges->paid_status = 2;
            $refReq = [
                "payment_status" => 2,
            ];
            $tranReq = [
                "verify_status" => 2
            ];                                                              // Update Demand Paid Status // Static
            $mPetTran->saveStatusInTrans($waterTransId, $tranReq);
            $mPetActiveRegistration->saveApplicationStatus($activeConRequest->id, $refReq);
        } else {
            $charges->paid_status = 1;                                      // Update Demand Paid Status // Static
            $refReq = [
                "payment_status"    => 1,
                "current_role_id"   => $activeConRequest->initiator_role_id
            ];
            $mPetActiveRegistration->saveApplicationStatus($activeConRequest->id, $refReq);
        }
        $charges->save();                                                                   // Save Demand

        $refTranDetails = [
            "id"            => $activeConRequest->id,
            "refChargeId"   => $charges->id,
            "roundAmount"   => $request->roundAmount,
            "tranTypeId"    => $request->tranType
        ];
        # Save Trans Details                                                   
        $mPetTranDetail->saveTransDetails($waterTransId, $refTranDetails);
    }


    /**
     * | Check the details and the function for the payment 
     * | return details for payment process
     * | @param req
        | Serial No: 
        | Under Construction
     */
    public function checkParamForPayment($req, $paymentMode)
    {
        $applicationId          = $req->id;
        $confPaymentMode        = $this->_paymentMode;
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
        if ($applicationDetail->payment_status != 0) {
            throw new Exception("payment is updated for application");
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
        if (in_array($regisCharges->paid_status, [1, 2])) {
            throw new Exception("Payment has been done!");
        }
        if ($paymentMode == $confPaymentMode['1']) {
            if ($applicationDetail->citizen_id != authUser($req)->id) {
                throw new Exception("You are not he Autherized User!");
            }
        }

        # Transaction details
        $transDetails = $mPetTran->getTranDetails($applicationId, $chargeCategory)->first();
        if ($transDetails) {
            throw new Exception("Transaction has been Done!");
        }

        return [
            "applicationDetails"    => $applicationDetail,
            "PetCharges"            => $regisCharges,
            "chargeCategory"        => $chargeCategory,
            "chargeId"              => $regisCharges->id,
            "regAmount"             => $regisCharges->amount,
            "refRoundAmount"        => round($regisCharges->amount)
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


    /**
     * | Ineciate online payment
        | Serail No : 
        | Working
     */
    public function handelOnlinePayment(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'id' => 'required|digits_between:1,9223372036854775807',
            ]
        );
        if ($validated->fails())
            return validationError($validated);

        try {
            $refUser                = authUser($request);
            $confModuleId           = $this->_petModuleId;
            $applicationId          = $request->id;
            $paymentMode            = $this->_paymentMode;
            $paymentUrl             = $this->_PaymentUrl;
            $confApiKey             = $this->_apiKey;
            $paymentDetails         = $this->checkParamForPayment($request, $paymentMode['1']);
            $mPetRazorPayRequest    = new PetRazorPayRequest();
            $myRequest = [
                'amount'        => $paymentDetails['refRoundAmount'],
                'workflowId'    => $paymentDetails['applicationDetails']['workflow_id'],
                'id'            => $applicationId,
                'departmentId'  => $confModuleId,
                'auth'          => $request->auth
            ];

            DB::beginTransaction();
            # Api Calling for OrderId
            $refResponse = Http::withHeaders([
                "api-key" => "$confApiKey"
            ])
                ->withToken($request->bearerToken())
                ->post($paymentUrl . 'api/payment/generate-orderid', $myRequest);               // Static

            $orderData = json_decode($refResponse);
            if ($orderData->status == false) {
                throw new Exception(collect($orderData->message)->first());
            }
            $jsonIncodedData = json_encode($orderData);

            $refPaymentRequest = new Request([
                "chargeCategory"    => $paymentDetails['chargeCategory'],
                "amount"            => $orderData->data->amount,
                "chargeId"          => $paymentDetails["chargeId"],
                "orderId"           => $orderData->data->orderId,
                "departmentId"      => $orderData->data->departmentId,
                "regAmount"         => $paymentDetails['regAmount'],
                "ip"                => $request->ip()
            ]);
            $mPetRazorPayRequest->savePetRazorpayReq($applicationId, $refPaymentRequest, $jsonIncodedData);
            DB::commit();
            $returnData = [
                'name'               => $refUser->user_name,
                'mobile'             => $refUser->mobile,
                'email'              => $refUser->email,
                'userId'             => $refUser->id,
                'ulbId'              => $refUser->ulb_id ?? $orderData->data->ulbId,
            ];
            $returnData = collect($returnData)->merge($orderData->data);
            return responseMsgs(true, "Order Id generated successfully", $returnData);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $request->deviceId);
        }
    }


    /**
     * | Handel online payment form razorpay Webohook
        | Serial No :
        | Working
     */
    public function endOnlinePayment(Request $req)
    {
        try {
            $payStatus          = 1;
            $refUserId          = $req->userId;
            $refUlbId           = $req->ulbId;
            $applicationId      = $req->id;
            $currentDateTime    = Carbon::now();
            $epoch              = strtotime($currentDateTime);
            $petRoles           = $this->_petWfRoles;

            $mPetTran               = new PetTran();
            $mPetTranDetail         = new PetTranDetail();
            $mPetRazorPayRequest    = new PetRazorPayRequest();
            $mPetRazorPayResponse   = new PetRazorPayResponse();
            $mPetActiveRegistration = new PetActiveRegistration();
            $mPetRegistrationCharge = new PetRegistrationCharge();

            $RazorPayRequest = $mPetRazorPayRequest->getRazorpayRequest($req);
            if (!$RazorPayRequest) {
                throw new Exception("Data Not Found");
            }

            # Handel the fake data or error data 
            $applicationDetails = $mPetActiveRegistration->getPetApplicationById($applicationId)->first();
            if (!$applicationDetails) {
                Storage::disk('public/suspecious')->put($epoch . '.json', json_encode($req->all()));
                throw new Exception("Application Not found!");
            }
            $chargeDetails = $mPetRegistrationCharge->getChargesbyId($applicationId)
                ->where('charge_category', $RazorPayRequest->payment_from)
                ->where('paid_status', 0)
                ->first();
            $this->CheckChargeDetails($chargeDetails, $epoch, $req, $RazorPayRequest);

            DB::beginTransaction();
            # save razorpay webhook response
            $paymentResponseId = $mPetRazorPayResponse->savePaymentResponse($RazorPayRequest, $req);
            # save the razorpay request status as 1
            $RazorPayRequest->status = 1;                                       // Static
            $RazorPayRequest->update();

            # save the transaction details 
            $tranReq = [
                "id"                => $applicationId,
                'amount'            => $req->amount,
                'todayDate'         => $currentDateTime,
                'tranNo'            => $req->transactionNo,
                'paymentMode'       => "ONLINE",                                // Static
                'citId'             => $refUserId,
                'userType'          => "Citizen",                               // Check here // Static
                'ulbId'             => $refUlbId,
                'pgResponseId'      => $paymentResponseId['razorpayResponseId'],
                'pgId'              => $req->gatewayType,
                'wardId'            => $applicationDetails->ward_id,
                'tranTypeId'        => $RazorPayRequest->payment_from,
                'isJsk'             => FALSE,                                   // Static
                'roundAmount'       => $RazorPayRequest->round_amount,
                'refChargeId'       => $chargeDetails->id
            ];
            $transDetails = $mPetTran->saveTranDetails($tranReq);
            $mPetTranDetail->saveTransDetails($transDetails['transactionId'], $tranReq);

            # Save charges payment status
            $chargeStatus = ["paid_status" => $payStatus];
            $mPetRegistrationCharge->saveStatus($chargeDetails->id, $chargeStatus);

            # Save application payment status
            $AppliationStatus = [
                "payment_status"    => $payStatus,                                                  // Static
                "current_role_id"   => $petRoles['DA'],
                "last_role_id"      => $petRoles['DA']
            ];
            $mPetActiveRegistration->saveApplicationStatus($applicationId, $AppliationStatus);
            DB::commit();
            return responseMsgs(true, "Online Payment Success!", []);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }

    /**
     * | Save the Error Request data in file while online payment
        | Serial No :
        | Under Con  
     */
    public function CheckChargeDetails($chargeDetails, $epoch, $req, $RazorPayRequest)
    {
        if (!$chargeDetails) {
            Storage::disk('public/suspecious')->put($epoch . '.json', json_encode($req->all()));
            throw new Exception("Demand Not found!");
        }
        if ($chargeDetails->amount != $req->amount) {
            Storage::disk('public/suspecious')->put($epoch . '.json', json_encode($req->all()));
            throw new Exception("amount Not found!");
        }
        if ($req->amount != $RazorPayRequest->amount) {
            Storage::disk('public/suspecious')->put($epoch . '.json', json_encode($req->all()));
            throw new Exception("Amount Not Match from request!");
        }
    }


    /**
     * | Get data for payment Receipt
        | Serial No :
        | Under Con
     */
    public function generatePaymentReceipt(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'transactionNo' => 'required|',
            ]
        );
        if ($validated->fails())
            return validationError($validated);
        try {
            $now            = Carbon::now();
            $toward         = "pet Registration";
            $mPetTran       = new PetTran();
            $mPetChequeDtl  = new PetChequeDtl();
            $confVerifyMode = $this->_offlineVerificationModes;

            # Get transaction details according to trans no
            return $transactionDetails = $mPetTran->getTranDetailsByTranNo($request->transactionNo)->first();
            if (!$transactionDetails) {
                throw new Exception("transaction details not found! for $request->transactionNo");
            }

            # Check for bank details for dd,cheque,neft
            if (in_array($transactionDetails->payment_mode, $confVerifyMode)) {
                $bankRelatedDetails = $mPetChequeDtl->getDetailsByTranId($transactionDetails->refTransId)->first();
            }
            # check the transaction related details in related table
            $applicationDetails = $this->getApplicationRelatedDetails($transactionDetails);

            $returnData = [
                "todayDate"     => $now,
                "applicationNo" => $applicationDetails->application_no,
                "applicantName" => $applicationDetails->applicant_name,
                "paidAmount"    => $transactionDetails->amount,
                "toward"        => $toward,
                "paymentMode"   => $transactionDetails->payment_mode,
                "bankName"      => $bankRelatedDetails->bank_name ?? null,
                "branchName"    => $bankRelatedDetails->branch_name ?? null,
                "chequeNo"      => $bankRelatedDetails->cheque_no ?? null,
                "chequeDate"    => $bankRelatedDetails->cheque_date ?? null,
                "ulb"           => $applicationDetails->ulb_name,
                "paymentDate"   => $transactionDetails->tran_date
            ];
            return responseMsgs(true, 'payment Receipt!', remove_null($returnData), "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }

    /**
     * | Serch application from every registration table
        | Serial No 
        | Under Con
     */
    public function getApplicationRelatedDetails($transactionDetails)
    {
        $mPetActiveRegistration     = new PetActiveRegistration();
        $mPetApprovedRegistration   = new PetApprovedRegistration();
        $mPetRenewalRegistration    = new PetRenewalRegistration();

        # first level chain
        $refApplicationDetails = $mPetActiveRegistration->getApplicationById($transactionDetails->related_id)
            ->selectRaw(
                'ulb_masters.ulb_name',
                'pet_active_registrations.application_no',
                'pet_active_applicants.applicant_name'
            )->first();
        if (!$mPetActiveRegistration) {
            # Second level chain
            $refApplicationDetails = $mPetApprovedRegistration->getApproveDetailById($transactionDetails->related_id)
                ->selectRaw(
                    'ulb_masters.ulb_name',
                    'pet_approved_registrations.application_no',
                    'pet_approve_applicants.applicant_name'
                )->first();
            if (!$refApplicationDetails) {
                # Third level chain
                $refApplicationDetails = $mPetRenewalRegistration->getRenewalApplicationById($transactionDetails->related_id)
                    ->selectRaw(
                        'ulb_masters.ulb_name',
                        'pet_renewal_applicants.application_no',
                        'pet_renewal_registrations.applicant_name'
                    )->first();;
            }
        }

        # Check the existence of final data
        if (!$refApplicationDetails) {
            throw new Exception("application details not found!");
        }
        return $refApplicationDetails;
    }
}
