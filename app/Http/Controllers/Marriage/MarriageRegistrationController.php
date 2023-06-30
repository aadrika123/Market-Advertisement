<?php

namespace App\Http\Controllers\Marriage;

use App\Http\Controllers\Controller;
use App\MicroServices\IdGenerator\PrefixIdGenerator;
use App\Models\Workflows\WfWorkflow;
use App\Traits\Workflow\Workflow;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

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
    # Class constructer 
    public function __construct()
    {
        $this->_workflowMasterId    = Config::get("marriage.WORKFLOW_MASTER_ID");
        $this->_marriageParamId     = Config::get("marriage.PARAM_ID");
        $this->_marriageModuleId    = Config::get('marriage.MODULE_ID');
        $this->_userType            = Config::get("marriage.REF_USER_TYPE");
        $this->_marriageWfRoles     = Config::get("marriage.ROLE_LABEL");
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
            $user                       = authUser();
            $ulbId                      = $user->ulb_id ?? $req->ulbId;
            $workflowMasterId           = $this->_workflowMasterId;
            $marriageParamId            = $this->_marriageParamId;
            $feeId                      = $this->_fee;
            $confApplicationType        = $this->_applicationType;

            # Get iniciater and finisher for the workflow 
            $ulbWorkflowId = $mWfWorkflow->getulbWorkflowId($workflowMasterId, $ulbId);
            if (!$ulbWorkflowId) {
                throw new Exception("Respective Ulb is not maped to 'marriage Registration' Workflow!");
            }
            $registrationCharges = 100;
            $refInitiatorRoleId = $this->getInitiatorId($ulbWorkflowId->id);
            $refFinisherRoleId  = $this->getFinisherId($ulbWorkflowId->id);
            $finisherRoleId     = collect(DB::select($refFinisherRoleId))->first()->role_id;
            $initiatorRoleId    = collect(DB::select($refInitiatorRoleId))->first()->role_id;

            # Data Base interaction 
            DB::beginTransaction();
            $idGeneration = new PrefixIdGenerator($marriageParamId, $ulbId);
            $marriageApplicationNo = $idGeneration->generate();
            $refData = [
                "finisherRoleId"    => $finisherRoleId,
                "initiatorRoleId"   => $initiatorRoleId,
                "workflowId"        => $ulbWorkflowId->id,
                "applicationNo"     => $marriageApplicationNo,
            ];
            return $req->merge($refData);

            # Renewal and the New Registration
            if ($req->isRenewal == 0 || !isset($req->isRenewal)) {
                if (isset($req->registrationId)) {
                    throw new Exception("Registration No is Not Req for new marriage Registraton!");
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
                $mmarriageApprovedRegistration->deactivateOldRegistration($req->registrationId);
            }
            # Save active details 
            $applicationDetails = $mmarriageActiveRegistration->saveRegistration($req, $user);
            $mmarriageActiveApplicant->saveApplicants($req, $applicationDetails['id']);
            $mmarriageActiveDetail->savemarriageDetails($req, $applicationDetails['id']);

            # Save registration charges
            $metaRequest = new Request([
                "applicationId"     => $applicationDetails['id'],
                "applicationType"   => $req->applicationType,
                "amount"            => $registrationCharges->amount,
                "registrationFee"   => $registrationCharges->amount,
                "applicationTypeId" => $req->applicationTypeId
            ]);
            $mmarriageRegistrationCharge->saveRegisterCharges($metaRequest);
            DB::commit();

            $returnData = [
                "id" => $applicationDetails['id'],
                "applicationNo" => $applicationDetails['applicationNo'],
            ];
            return responseMsgs(true, "marriage Registration application submitted!", $returnData, "", "01", ".ms", "POST", $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }
}
