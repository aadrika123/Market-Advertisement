<?php

namespace App\Http\Controllers\Markets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\BanquetMarriageHall\StoreRequest;
use App\Models\Markets\MarActiveBanquteHall;
use App\Traits\WorkflowTrait;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * | Created on - 06-02-2023
 * | Created By - Bikash Kumar
 * | Banquet Marriage Hall
 * | Status - Open
 */
class BanquetMarriageHallController extends Controller
{

    use WorkflowTrait;

    //Constructor
    public function __construct()
    {
        $this->_modelObj = new MarActiveBanquteHall();
    }
    protected $_modelObj;  //  Generate Model Instance

    /**
     * | Store 
     * | @param StoreRequest Request
     */
    public function store(StoreRequest $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mMarActiveBanquteHall = $this->_modelObj;
            if (authUser()->user_type == 'JSK') {
                $userId = ['userId' => authUser()->id];
                $req->request->add($userId);
            } else {
                $citizenId = ['citizenId' => authUser()->id];
                $req->request->add($citizenId);
            }

            DB::beginTransaction();
            $applicationNo = $mMarActiveBanquteHall->store($req);       //<--------------- Model function to store 
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Successfully Submitted the application !!", ['status' => true, 'ApplicationNo' => $applicationNo], "050101", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "050101", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | Inbox List
     * | @param Request $req
     */
    public function inbox(Request $req)
    {
        try {
            $startTime = microtime(true);
            $mMarActiveBanquteHall = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $inboxList = $mMarActiveBanquteHall->inbox($roleIds);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Inbox Applications", remove_null($inboxList->toArray()), "050103", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050103", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }



    /**
     * | Outbox List
     */
    public function outbox(Request $req)
    {
        try {
            $startTime = microtime(true);
            $mMarActiveBanquteHall = $this->_modelObj;
            $bearerToken = $req->bearerToken();
            $workflowRoles = collect($this->getRoleByUserId($bearerToken));             // <----- Get Workflow Roles roles 
            $roleIds = collect($workflowRoles)->map(function ($workflowRole) {          // <----- Filteration Role Ids
                return $workflowRole['wf_role_id'];
            });
            $outboxList = $mMarActiveBanquteHall->outbox($roleIds);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Outbox Lists", remove_null($outboxList->toArray()), "050104", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050104", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    /**
     * | Application Details
     */

    public function details(Request $req)
    {
        try {
            $startTime = microtime(true);
            $mMarActiveBanquteHall = $this->_modelObj;
            $fullDetailsData = array();
            if (isset($req->type)) {
                $type = $req->type;
            } else {
                $type = NULL;
            }
            if ($req->applicationId) {
                $data = $mMarActiveBanquteHall->details($req->applicationId, $type);
            } else {
                throw new Exception("Not Pass Application Id");
            }

            if (!$data)
                throw new Exception("Application Not Found");
            // Basic Details
            $basicDetails = $this->generateBasicDetails($data); // Trait function to get Basic Details
            $basicElement = [
                'headerTitle' => "Basic Details",
                "data" => $basicDetails
            ];

            $cardDetails = $this->generateCardDetails($data);
            $cardElement = [
                'headerTitle' => "About Advertisement",
                'data' => $cardDetails
            ];
            $fullDetailsData['fullDetailsData']['dataArray'] = new Collection([$basicElement]);
            $fullDetailsData['fullDetailsData']['cardArray'] = new Collection($cardElement);

            // return ($data);

            // $metaReqs['customFor'] = 'Banqute-Marrige Hall';
            // $metaReqs['wfRoleId'] = $data['current_role_id'];
            // $metaReqs['workflowId'] = $data['workflow_id'];

            // $req->request->add($metaReqs);
            // $forwardBackward = $this->getRoleDetails($req);
            // $fullDetailsData['roleDetails'] = collect($forwardBackward)['original']['data'];

            $fullDetailsData = remove_null($fullDetailsData);

            $fullDetailsData['application_no'] = $data['application_no'];
            $fullDetailsData['apply_date'] = $data['application_date'];
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, 'Data Fetched', $fullDetailsData, "050105", "1.0", "$executionTime Sec", "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    public function getRoleDetails(Request $request)
    {
        $ulbId = auth()->user()->ulb_id;
        $request->validate([
            'workflowId' => 'required|int'

        ]);
        $roleDetails = DB::table('wf_workflowrolemaps')
            ->select(
                'wf_workflowrolemaps.id',
                'wf_workflowrolemaps.workflow_id',
                'wf_workflowrolemaps.wf_role_id',
                'wf_workflowrolemaps.forward_role_id',
                'wf_workflowrolemaps.backward_role_id',
                'wf_workflowrolemaps.is_initiator',
                'wf_workflowrolemaps.is_finisher',
                'r.role_name as forward_role_name',
                'rr.role_name as backward_role_name'
            )
            ->leftJoin('wf_roles as r', 'wf_workflowrolemaps.forward_role_id', '=', 'r.id')
            ->leftJoin('wf_roles as rr', 'wf_workflowrolemaps.backward_role_id', '=', 'rr.id')
            ->where('workflow_id', $request->workflowId)
            ->where('wf_role_id', $request->wfRoleId)
            ->first();
        return responseMsgs(true, "Data Retrived", remove_null($roleDetails));
    }



    /**
     * Summary of getCitizenApplications
     * @param Request $req
     * @return void
     */
    public function getCitizenApplications(Request $req)
    {
        echo "getCitizenApplications";
    }


    /**
     *  | Escalate
     * @param Request $req
     * @return void
     */
    public function escalate(Request $req)
    {
        echo 'Esclate';
    }


    /**
     *  Inbox List
     * @param Request $req
     * @return void
     */
    public function specialInbox(Request $req)
    {
        echo "Special Inbox";
    }



    /**
     * Forward or Backward Application
     * @param Request $req
     * @return void
     */
    public function postNextLevel(Request $req)
    {
        echo "Post Next Level";
    }



    /**
     * Post Independent Comment
     * @param Request $req
     * @return void
     */
    public function commentIndependent(Request $req)
    {
        echo "Comment Independent";
    }


    /**
     * Get Uploaded Document by application ID
     * @param Request $req
     * @return void
     */
    public function uploadDocumentsView(Request $req)
    {
        echo "Upload Documents View";
    }



    /**
     * Final Approval and Rejection of the Application
     * @param Request $req
     * @return void
     */
    public function finalApprovalRejection(Request $req)
    {
        echo "final Approval Rejection";
    }

    /**
     * Approved Application List for Citizen
     * @param Request $req
     * @return void
     */
    public function approvedList(Request $req)
    {
        echo "approved List";
    }



    /**
     * Rejected Application List
     * @param Request $req
     * @return void
     */
    public function rejectedList(Request $req)
    {
        echo "Rejected List";
    }



    /**
     * get JSK Applications
     * @param Request $req
     * @return void
     */
    public function getJSKApplications(Request $req)
    {
        echo "get JSK Applications";
    }



    /**
     * jsk Approved Application List
     * @param Request $req
     * @return void
     */
    public function jskApprovedList(Request $req)
    {
        echo "jsk Approved List";
    }




    /**
     * jsk Rejected Applications List
     * @param Request $req
     * @return void
     */
    public function jskRejectedList(Request $req)
    {
        echo "jsk Rejected List";
    }



    /**
     * generate Payment OrderId for Payment
     * @param Request $req
     * @return void
     */
    public function generatePaymentOrderId(Request $req)
    {
        echo "generate Payment Order Id";
    }


    /**
     * Get application Details For Payment
     * @return void
     */
    public function applicationDetailsForPayment()
    {
        echo "application Details For Payment";
    }
}
