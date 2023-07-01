<?php

namespace App\Http\Controllers\Marriage;

use App\Http\Controllers\Controller;
use App\MicroServices\IdGenerator\PrefixIdGenerator;
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
