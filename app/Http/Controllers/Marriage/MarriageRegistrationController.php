<?php

namespace App\Http\Controllers\Marriage;

use App\Http\Controllers\Controller;
use App\Traits\Workflow\Workflow;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MarriageRegistrationController extends Controller
{

    use Workflow;

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
    # Class constructer 
    public function __construct()
    {
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
    }
    /**
     * | Apply for marriage registration
     */
    public function apply(Request $request)
    {
        try {
            return  $user                       = authUser();
            $ulbId                      = $req->ulbId;
            $workflowMasterId           = $this->_workflowMasterId;
            $petParamId                 = $this->_petParamId;
            $feeId                      = $this->_fee;
            $confApplicationType        = $this->_applicationType;

            # Get iniciater and finisher for the workflow 
            $ulbWorkflowId = $mWfWorkflow->getulbWorkflowId($workflowMasterId, $ulbId);
            if (!$ulbWorkflowId) {
                throw new Exception("Respective Ulb is not maped to 'Pet Registration' Workflow!");
            }
            $registrationCharges = $mMPetFee->getFeeById($feeId['REGISTRATION']);
            if (!$registrationCharges) {
                throw new Exception("Currently charges are not available!");
            }
            $refInitiatorRoleId = $this->getInitiatorId($ulbWorkflowId->id);
            $refFinisherRoleId  = $this->getFinisherId($ulbWorkflowId->id);
            $finisherRoleId     = collect(DB::select($refFinisherRoleId))->first()->role_id;
            $initiatorRoleId    = collect(DB::select($refInitiatorRoleId))->first()->role_id;

            $refValidatedDetails = $this->checkParamForRegister($req);

            # Data Base interaction 
            DB::beginTransaction();
            $idGeneration = new PrefixIdGenerator($petParamId, $ulbId);
            $petApplicationNo = $idGeneration->generate();
            $refData = [
                "finisherRoleId"    => $finisherRoleId,
                "initiatorRoleId"   => $initiatorRoleId,
                "workflowId"        => $ulbWorkflowId->id,
                "applicationNo"     => $petApplicationNo,
            ];
            if ($req->applyThrough == $confApplyThrough['Holding']) {
                $refData["holdingNo"] = collect($refValidatedDetails['propDetails'])['holding_no'] ?? null;
            }
            if ($req->applyThrough == $confApplyThrough['Saf']) {
                $refData["safNo"] = collect($refValidatedDetails['propDetails'])['saf_no'] ?? null;
            }
            $req->merge($refData);

            # Renewal and the New Registration
            if ($req->isRenewal == 0 || !isset($req->isRenewal)) {
                if (isset($req->registrationId)) {
                    throw new Exception("Registration No is Not Req for new Pet Registraton!");
                }
                $refData = [
                    "applicationType" => "New_Apply",
                    "applicationTypeId" => $confApplicationType['NEW_APPLY']
                ];
                $req->merge($refData);
            }
            if ($req->isRenewal == 1) {
                $refData = [
                    "applicationType" => "Renewal",
                    "registrationId"  => $req->registrationId,
                    "applicationTypeId" => $confApplicationType['RENEWAL']
                ];
                $req->merge($refData);
                $mPetApprovedRegistration->deactivateOldRegistration($req->registrationId);
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
            DB::commit();

            $returnData = [
                "id" => $applicationDetails['id'],
                "applicationNo" => $applicationDetails['applicationNo'],
            ];
            return responseMsgs(true, "Pet Registration application submitted!", $returnData, "", "01", ".ms", "POST", $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }
}
