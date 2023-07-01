<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\Models\Pet\PetActiveRegistration;
use App\Models\Workflows\WfWardUser;
use App\Models\Workflows\WfWorkflow;
use App\Models\Workflows\WfWorkflowrolemap;
use App\Models\Workflows\WorkflowTrack;
use App\Traits\Workflow\Workflow;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class PetWorkflowController extends Controller
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
     * | Inbox
     * | workflow
        | Serial No :
        | Working
     */
    public function inbox(Request $request)
    {
        try {
            $user   = authUser();
            $userId = $user->id;
            $ulbId  = $user->ulb_id;
            $mWfWorkflowRoleMaps = new WfWorkflowrolemap();

            $occupiedWards = $this->getWardByUserId($userId)->pluck('ward_id');
            $roleId = $this->getRoleIdByUserId($userId)->pluck('wf_role_id');
            $workflowIds = $mWfWorkflowRoleMaps->getWfByRoleId($roleId)->pluck('workflow_id');

            return $waterList = $this->getPetApplicatioList($workflowIds, $ulbId)
                ->whereIn('pet_active_registrations.current_role_id', $roleId)
                ->whereIn('pet_active_registrations.ward_id', $occupiedWards)
                ->where('pet_active_registrations.is_escalate', false)
                ->where('pet_active_registrations.parked', false)
                ->get();
            $filterWaterList = collect($waterList)->unique('id')->values();
            return responseMsgs(true, "Inbox List Details!", remove_null($filterWaterList), '', '02', '', 'Post', '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $request->deviceId);
        }
    }

    /**
     * | Common function
        | Move the function in trait 
        | Caution remove the function 
     */
    public function getPetApplicatioList($workflowIds, $ulbId)
    {
        return PetActiveRegistration::select(
            'pet_active_registrations.id',
            'pet_active_registrations.application_no',
            'pet_active_applicants.id as owner_id',
            'pet_active_applicants.applicant_name as owner_name',
            'pet_active_registrations.ward_id',
            'u.ward_name as ward_no',
            'pet_active_registrations.workflow_id',
            'pet_active_registrations.current_role_id as role_id',
            'pet_active_registrations.application_apply_date',
            'pet_active_registrations.parked'
        )
            ->join('ulb_ward_masters as u', 'u.id', '=', 'pet_active_registrations.ward_id')
            ->join('pet_active_applicants', 'pet_active_applicants.application_id', 'pet_active_registrations.id')
            ->join('pet_active_details', 'pet_active_details.application_id', 'pet_active_registrations.id')
            ->where('pet_active_registrations.status', 1)
            // ->where('pet_active_registrations.payment_status', 1)
            ->where('pet_active_registrations.ulb_id', $ulbId)
            ->whereIn('pet_active_registrations.workflow_id', $workflowIds)
            ->orderByDesc('pet_active_applicants.id');
    }


    /**
     * | OutBox
     * | Outbox details for display
        | Serial No :
        | Working
     */
    public function outbox(Request $req)
    {
        try {
            $user                   = authUser();
            $userId                 = $user->id;
            $ulbId                  = $user->ulb_id;
            $mWfWorkflowRoleMaps    = new WfWorkflowrolemap();

            $occupiedWards  = $this->getWardByUserId($userId)->pluck('ward_id');
            $roleId         = $this->getRoleIdByUserId($userId)->pluck('wf_role_id');
            $workflowIds    = $mWfWorkflowRoleMaps->getWfByRoleId($roleId)->pluck('workflow_id');

            $waterList = $this->getPetApplicatioList($workflowIds, $ulbId)
                ->whereNotIn('pet_active_registrations.current_role_id', $roleId)
                ->whereIn('pet_active_registrations.ward_id', $occupiedWards)
                ->orderByDesc('pet_active_registrations.id')
                ->get();
            $filterWaterList = collect($waterList)->unique('id')->values();
            return responseMsgs(true, "Outbox List", remove_null($filterWaterList), '', '01', '.ms', 'Post', '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }


    /**
     * | Post next level 
     */
    public function postNextLevel(Request $req)
    {
        $wfLevels = $this->_petWfRoles;
        $req->validate([
            'applicationId'     => 'required',
            'senderRoleId'      => 'required',
            'receiverRoleId'    => 'required',
            'action'            => 'required|In:forward,backward',
            'comment'           => $req->senderRoleId == $wfLevels['BO'] ? 'nullable' : 'required',
        ]);
        try {
            $mWfRoleMaps        = new WfWorkflowrolemap();
            $current            = Carbon::now();
            $wfLevels           = $wfLevels;
            $petApplication     = PetActiveRegistration::findOrFail($req->applicationId);

            # Derivative Assignments
            $senderRoleId = $petApplication->current_role_id;
            $ulbWorkflowId = $petApplication->workflow_id;
            $ulbWorkflowMaps = WfWorkflow::findOrFail($ulbWorkflowId);
            $roleMapsReqs = new Request([
                'workflowId' => $ulbWorkflowMaps->id,
                'roleId' => $senderRoleId
            ]);
            $forwardBackwardIds = $mWfRoleMaps->getWfBackForwardIds($roleMapsReqs);

            DB::beginTransaction();
            if ($req->action == 'forward') {
                $this->checkPostCondition($req->senderRoleId, $wfLevels, $petApplication);            // Check Post Next level condition
                $metaReqs['verificationStatus'] = 1;
                $metaReqs['receiverRoleId']     = $forwardBackwardIds->forward_role_id;
                $petApplication->current_role   = $forwardBackwardIds->forward_role_id;
                $petApplication->last_role_id   =  $forwardBackwardIds->forward_role_id;                                      // Update Last Role Id
            }
            if ($req->action == 'backward') {
                $petApplication->current_role   = $forwardBackwardIds->backward_role_id;
                $metaReqs['verificationStatus'] = 0;
                $metaReqs['receiverRoleId']     = $forwardBackwardIds->backward_role_id;
            }
            $petApplication->save();

            $metaReqs['moduleId']           = $this->_petModuleId;
            $metaReqs['workflowId']         = $petApplication->workflow_id;
            $metaReqs['refTableDotId']      = 'pet_active_registrations.id';                                                // Static
            $metaReqs['refTableIdValue']    = $req->applicationId;
            $metaReqs['user_id']            = authUser()->id;
            $req->request->add($metaReqs);

            $waterTrack = new WorkflowTrack();
            $waterTrack->saveTrack($req);

            # check in all the cases the data if entered in the track table 
            // Updation of Received Date
            $preWorkflowReq = [
                'workflowId'        => $petApplication->workflow_id,
                'refTableDotId'     => "water_applications.id",
                'refTableIdValue'   => $req->applicationId,
                'receiverRoleId'    => $senderRoleId
            ];

            $previousWorkflowTrack = $waterTrack->getWfTrackByRefId($preWorkflowReq);
            $previousWorkflowTrack->update([
                'forward_date' => $current->format('Y-m-d'),
                'forward_time' => $current->format('H:i:s')
            ]);
            DB::commit();
            return responseMsgs(true, "Successfully Forwarded The Application!!", "", "", "", '01', '.ms', 'Post', '');
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }


    /**
     * | Check the condition before forward
        | Serial No :
        | Under Construction
     */
    public function checkPostCondition($senderRoleId, $wfLevels, $application)
    {
        switch ($senderRoleId) {
            case $wfLevels['BO']:                                                                       // Back Office Condition
                if ($application->doc_upload_status == false || $application->payment_status != 1)
                    throw new Exception("Document Not Fully Uploaded or Payment in not Done!");
                break;
            case $wfLevels['DA']:
                if ($application->doc_upload_status == false || $application->payment_status != 1)
                    throw new Exception("Document Not Fully Uploaded or Payment in not Done!");                                                                      // DA Condition
                if ($application->doc_verify_status == false)
                    throw new Exception("Document Not Fully Verified!");
                break;
        }
    }
}
