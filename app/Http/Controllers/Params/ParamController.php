<?php

namespace App\Http\Controllers\Params;

use App\Http\Controllers\Controller;
use App\MicroServices\DocumentUpload;
use App\Models\Advertisements\AdvActiveSelfadvertisement;
use App\Models\Param\RefAdvParamstring;
use App\Models\Advertisements\AdvActiveSelfadvetdocument;
use App\Models\Advertisements\AdvAgency;
use App\Models\Advertisements\AdvAgencyLicense;
use App\Models\Advertisements\AdvPrivateland;
use App\Models\Advertisements\AdvSelfadvertisement;
use App\Models\Advertisements\AdvTypologyMstr;
use App\Models\Advertisements\AdvVehicle;
use App\Models\Advertisements\WfActiveDocument;
use App\Models\Markets\MarBanquteHall;
use App\Models\Markets\MarDharamshala;
use App\Models\Markets\MarHostel;
use App\Models\Markets\MarLodge;
use App\Models\Workflows\WfRoleusermap;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class ParamController extends Controller
{

    protected $_selfAdvt;
    protected $_pvtLand;
    protected $_movableVehicle;
    protected $_agency;
    protected $_hording;
    protected $_banquetHall;
    protected $_hostel;
    protected $_lodge;
    protected $_dharamshala;
    //Constructor
    public function __construct()
    {
        $this->_selfAdvt = Config::get('workflow-constants.ADVERTISEMENT_WORKFLOWS');
        $this->_pvtLand = Config::get('workflow-constants.PRIVATE_LANDS_WORKFLOWS');
        $this->_movableVehicle = Config::get('workflow-constants.MOVABLE_VEHICLE_WORKFLOWS');
        $this->_agency = Config::get('workflow-constants.AGENCY_WORKFLOWS');
        $this->_hording = Config::get('workflow-constants.AGENCY_HORDING_WORKFLOWS');
        $this->_banquetHall = Config::get('workflow-constants.BANQUTE_MARRIGE_HALL_WORKFLOWS');
        $this->_hostel = Config::get('workflow-constants.HOSTEL_WORKFLOWS');
        $this->_lodge = Config::get('workflow-constants.HOSTEL_WORKFLOWS');
        $this->_dharamshala = Config::get('workflow-constants.HOSTEL_WORKFLOWS');
    }



    /**
     * | String Parameters values
     * | @param request $req
     */
    public function paramStrings(Request $req)
    {
        $redis = Redis::connection();
        try {
            $mUlbId = $req->ulbId;
            $data = json_decode(Redis::get('adv_param_strings' . $mUlbId));      // Get Value from Redis Cache Memory
            $bearer = $req->bearerToken();
            if (!$data) {                                                        // If Cache Memory is not available
                $data = array();
                $baseUrl = Config::get('constants.BASE_URL');
                $mParamString = new RefAdvParamstring();
                $strings = $mParamString->masters($mUlbId);
                $data['paramCategories'] = remove_null($strings->groupBy('param_category')->toArray());
                // Get Wards By Ulb Id
                $mWards = Http::withHeaders([
                    "Authorization" => "Bearer $bearer",
                    "contentType" => "application/json"

                ])->post($baseUrl . 'api/workflow/getWardByUlb', [
                    "ulbId" => $mUlbId
                ]);

                if (!$mWards)
                    throw new Exception("Wards not found");

                $data['wards'] = $mWards['data'];

                $mAdvTypologyMstr = new AdvTypologyMstr();
                $typologyList = $mAdvTypologyMstr->listTypology();                  // Get Topology List

                $data['paramCategories']['typology'] = $typologyList;

                $redis->set('adv_param_strings' . $mUlbId, json_encode($data));      // Set Key on Param Strings
            }
            return responseMsgs(true, "Param Strings", $data, "040201", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Get Document Masters from our localstorage db
     */
    public function documentMstrs()
    {
        $startTime = microtime(true);
        $documents = json_decode(file_get_contents(storage_path() . "/local-db/advDocumentMstrs.json", true));
        $documents = remove_null($documents);
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        return responseMsgs(true, "Document Masters", $documents, "040202", "1.0", $executionTime . " Sec", "POST");
    }



    /**
     * | Get Document Masters from our localstorage db
     */
    public function districtMstrs()
    {
        $startTime = microtime(true);
        $districts = json_decode(file_get_contents(storage_path() . "/local-db/districtMstrs.json", true));
        $districts = remove_null($districts);
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        return responseMsgs(true, "District Masters", $districts, "040202", "1.0", $executionTime . " Sec", "POST");
    }

    public function metaReqs($req)
    {
        $metaReqs = [
            'verified_by' => $req['roleId'],
            'verify_status' => $req['verifyStatus'],
            'remarks' => $req['remarks'],
            'verified_on' => Carbon::now()->format('Y-m-d')
        ];
        return $metaReqs;
    }




    /**
     * Summary of payment Success Failure of all Types of Advertisment 
     * @return void
     * @param request $req
     */
    public function paymentSuccessFailure(Request $req)
    {
        try {
            $startTime = microtime(true);
            DB::beginTransaction();
            $updateData = [
                'payment_date' => Carbon::now(),
                'payment_status' => 1,
                'payment_id' => $req->paymentId,
                'payment_details' => $req->all(),
            ];


            if ($req->workflowId == $this->_selfAdvt) { // Self Advertisement Payment

                DB::table('adv_selfadvertisements')
                    ->where('id', $req->id)
                    ->update($updateData);

                $mAdvSelfadvertisement = AdvSelfadvertisement::find($req->id);

                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('adv_selfadvet_renewals')
                    ->where('id', $mAdvSelfadvertisement->last_renewal_id)
                    ->update($updateData);
            } elseif ($req->workflowId == $this->_movableVehicle) { // Movable Vechicles Payment

                DB::table('adv_vehicles')
                    ->where('id', $req->id)
                    ->update($updateData);

                $mAdvVehicle = AdvVehicle::find($req->id);

                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('adv_vehicle_renewals')
                    ->where('id', $mAdvVehicle->last_renewal_id)
                    ->update($updateData);
            } elseif ($req->workflowId ==  $this->_agency) { // Agency Apply Payment

                DB::table('adv_agencies')
                    ->where('id', $req->id)
                    ->update($updateData);

                $mAdvVehicle = AdvAgency::find($req->id);

                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('adv_agency_renewals')
                    ->where('id', $mAdvVehicle->last_renewal_id)
                    ->update($updateData);
            } elseif ($req->workflowId == $this->_pvtLand) { // Private Land Payment

                DB::table('adv_privatelands')
                    ->where('id', $req->id)
                    ->update($updateData);

                $mAdvPrivateland = AdvPrivateland::find($req->id);

                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('adv_privateland_renewals')
                    ->where('id', $mAdvPrivateland->last_renewal_id)
                    ->update($updateData);
            } elseif ($req->workflowId == $this->_hording) { // Hording Apply Payment

                DB::table('adv_agency_licenses')
                    ->where('id', $req->id)
                    ->update($updateData);

                $mAdvAgencyLicense = AdvAgencyLicense::find($req->id);

                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('adv_agency_license_renewals')
                    ->where('id', $mAdvAgencyLicense->last_renewal_id)
                    ->update($updateData);
            } elseif ($req->workflowId == $this->_banquetHall) { // Hording Apply Payment

                DB::table('mar_banqute_halls')
                    ->where('id', $req->id)
                    ->update($updateData);

                $mMarBanquteHall = MarBanquteHall::find($req->id);

                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('mar_banqute_hall_renewals')
                    ->where('id', $mMarBanquteHall->last_renewal_id)
                    ->update($updateData);
            } elseif ($req->workflowId == $this->_hostel) { // Hostel Apply Payment

                DB::table('mar_hostels')
                    ->where('id', $req->id)
                    ->update($updateData);

                $mMarHostel = MarHostel::find($req->id);

                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('mar_hostel_renewals')
                    ->where('id', $mMarHostel->last_renewal_id)
                    ->update($updateData);
            } elseif ($req->workflowId == $this->_lodge) { // Lodge Apply Payment

                DB::table('mar_lodges')
                    ->where('id', $req->id)
                    ->update($updateData);

                $mMarLodge = MarLodge::find($req->id);

                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('mar_hostel_renewals')
                    ->where('id', $mMarLodge->last_renewal_id)
                    ->update($updateData);
            } elseif ($req->workflowId == $this->_dharamshala) { // Dharamshala Apply Payment

                DB::table('mar_dharamshalas')
                    ->where('id', $req->id)
                    ->update($updateData);

                $mMarDharamshala = MarDharamshala::find($req->id);

                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('mar_dharamshala_renewals')
                    ->where('id', $mMarDharamshala->last_renewal_id)
                    ->update($updateData);
            }

            DB::commit();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            $msg = "Payment Accepted Successfully !!!";
            return responseMsgs(true, $msg, "", '050206', 01, "$executionTime Sec", 'Post', $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", '050206', 01, "", 'Post', $req->deviceId);
        }
    }


    public function getPaymentDetails(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'paymentId' => 'required|string',
            'workflowId' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {

            // Get Advertesement Payment Details
            if ($req->workflowId == $this->_selfAdvt) {
                $mAdvSelfadvertisement = new AdvSelfadvertisement();
                $paymentDetails = $mAdvSelfadvertisement->getPaymentDetails($req->paymentId);
            } elseif ($req->workflowId == $this->_pvtLand) {
                $mAdvPrivateland = new AdvPrivateland();
                $paymentDetails = $mAdvPrivateland->getPaymentDetails($req->paymentId);
            } elseif ($req->workflowId ==  $this->_movableVehicle) {
                $mAdvVehicle = new AdvVehicle();
                $paymentDetails = $mAdvVehicle->getPaymentDetails($req->paymentId);
            } elseif ($req->workflowId == $this->_agency) {
                $mAdvAgency = new AdvAgency();
                $paymentDetails = $mAdvAgency->getPaymentDetails($req->paymentId);
            } elseif ($req->workflowId == $this->_hording) {
                $mAdvAgencyLicense = new AdvAgencyLicense();
                $paymentDetails = $mAdvAgencyLicense->getLicensePaymentDetails($req->paymentId);
            }

            // Get Market Payment Details
            elseif ($req->workflowId == $this->_banquetHall) {
                $mMarBanquteHall = new MarBanquteHall();
                $paymentDetails = $mMarBanquteHall->getPaymentDetails($req->paymentId);
            } elseif ($req->workflowId == $this->_hostel) {
                $mMarHostel = new MarHostel();
                $paymentDetails = $mMarHostel->getPaymentDetails($req->paymentId);
            } elseif ($req->workflowId == $this->_lodge) {
                $mMarLodge = new MarLodge();
                $paymentDetails = $mMarLodge->getPaymentDetails($req->paymentId);
            } elseif ($req->workflowId == $this->_dharamshala) {
                $mMarDharamshala = new MarDharamshala();
                $paymentDetails = $mMarDharamshala->getPaymentDetails($req->paymentId);
            }


            if (empty($paymentDetails)) {
                throw new Exception("Payment Details Not Found By Given Paymenst Id !!!");
            } else {
                return responseMsgs(true, 'Data Fetched',  $paymentDetails, "050124", "1.0", "2 Sec", "POST", $req->deviceId);
            }
        } catch (Exception $e) {
            responseMsgs(false, $e->getMessage(), "");
        }
    }

}
