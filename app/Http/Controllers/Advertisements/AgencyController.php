<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agency\StoreRequest;
use App\Models\Advertisements\AdvActiveAgency;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

use App\Traits\AdvDetailsTraits;
use App\Models\Workflows\WfWardUser;
use App\Repositories\SelfAdvets\iSelfAdvetRepo;

/**
 * | Created On-02-01-20222 
 * | Created By-Anshu Kumar
 * | Agency Operations
 */
class AgencyController extends Controller
{

    use AdvDetailsTraits;

    protected $_modelObj;

    protected $Repository;

    protected $_workflowIds;
    public function __construct(iSelfAdvetRepo $agency_repo)
    {
        $this->_modelObj = new AdvActiveAgency();
        $this->_workflowIds = Config::get('workflow-constants.AGENCY_WORKFLOWS');
        $this->Repository = $agency_repo;
    }
    /**
     * | Store 
     * | @param StoreRequest Request
     */
    public function store(StoreRequest $req)
    {
        try {
            $agency = new AdvActiveAgency();
            $citizenId = ['citizenId' => authUser()->id];
            $req->request->add($citizenId);
            DB::beginTransaction();
            $applicationNo = $agency->store($req);       //<--------------- Model function to store 
            DB::commit();
            return responseMsgs(
                true,
                "Successfully Submitted the application !!",
                [
                    'status' => true,
                    'ApplicationNo' => $applicationNo
                ],
                "040501",
                "1.0",
                "",
                'POST',
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(
                true,
                $e->getMessage(),
                "",
                "040501",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        }
    }

    
    /**
     * | Application Details
     */

     public function details(Request $req)
    {
         try {
             $mAdvActiveAgency = new AdvActiveAgency();
             // $data = array();
             $fullDetailsData = array();
             if ($req->applicationId) {
                 $data = $mAdvActiveAgency->details($req->applicationId);
             }


             // Basic Details
             $basicDetails = $this->generateAgencyBasicDetails($data); // Trait function to get Basic Details
             $basicElement = [
                 'headerTitle' => "Basic Agency Details",
                 "data" => $basicDetails
             ];
 
             $cardDetails = $this->generateAgencyCardDetails($data);
             $cardElement = [
                 'headerTitle' => "About Agency",
                 'data' => $cardDetails
             ];
            
            $fullDetailsData['fullDetailsData']['dataArray'] = new Collection([$basicElement]);
             $fullDetailsData['fullDetailsData']['cardArray'] = new Collection($cardElement);
 
             $fullDetailsData = remove_null($fullDetailsData);
 
             $fullDetailsData['application_no'] = $data['application_no'];
             $fullDetailsData['apply_date'] = $data['application_date'];
             $fullDetailsData['directors'] = $data['directors'];
 
             return responseMsgs(true, 'Data Fetched', $fullDetailsData, "010104", "1.0", "303ms", "POST", $req->deviceId);
         } catch (Exception $e) {
             return responseMsgs(false, $e->getMessage(), "");
         }
     }

       /**
     * | Get Applied Applications by Logged In Citizen
     */
    public function getCitizenApplications(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $mAdvActiveAgency = new AdvActiveAgency();
            $applications = $mAdvActiveAgency->getCitizenApplications($citizenId);
            $totalApplication=$applications->count();
            remove_null($applications);
            $data1['data'] = $applications;
            $data1['arrayCount'] =  $totalApplication;
            return responseMsgs(
                true,
                "Applied Applications",
                $data1,
                "040106",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "040106",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        }
    }

      /**
     * | Escalate
     */
    public function escalate(Request $request)
    {
        $request->validate([
            "escalateStatus" => "required|int",
            "applicationId" => "required|int",
        ]);
        try {
            $userId = auth()->user()->id;
            $applicationId = $request->applicationId;
            $data = AdvActiveAgency::find($applicationId);
            $data->is_escalate = $request->escalateStatus;
            $data->escalate_by = $userId;
            $data->save();
            return responseMsgs(true, $request->escalateStatus == 1 ? 'Agency is Escalated' : "Agency is removed from Escalated", '', "010106", "1.0", "353ms", "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $request->all());
        }
    }

    
    public function specialInbox(Request $req)
    {
        try {
            $mWfWardUser = new WfWardUser();
            $userId = authUser()->id;
            $ulbId = authUser()->ulb_id;

            $occupiedWard = $mWfWardUser->getWardsByUserId($userId);                        // Get All Occupied Ward By user id using trait
            $wardId = $occupiedWard->map(function ($item, $key) {                           // Filter All ward_id in an array using laravel collections
                return $item->ward_id;
            });

            // print_r($wardId);
            
            $advData = $this->Repository->specialAgencyInbox($this->_workflowIds)                      // Repository function to get Advertiesment Details
                ->where('is_escalate', 1)
                ->where('adv_active_agencies.ulb_id', $ulbId)
                // ->whereIn('ward_mstr_id', $wardId)
                ->get();
            return responseMsgs(true, "Data Fetched", remove_null($advData), "010107", "1.0", "251ms", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

}
