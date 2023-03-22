<?php

namespace App\Http\Controllers\Params;

use App\Http\Controllers\Controller;
use App\MicroServices\DocumentUpload;
use App\Models\Advertisements\AdvActiveSelfadvertisement;
use App\Models\Param\RefAdvParamstring;
use App\Models\Advertisements\AdvActiveSelfadvetdocument;
use App\Models\Advertisements\AdvAgency;
use App\Models\Advertisements\AdvAgencyLicense;
use App\Models\Advertisements\AdvAgencyRenewal;
use App\Models\Advertisements\AdvHoarding;
use App\Models\Advertisements\AdvHoardingRenewal;
use App\Models\Advertisements\AdvPrivateland;
use App\Models\Advertisements\AdvPrivatelandRenewal;
use App\Models\Advertisements\AdvSelfadvertisement;
use App\Models\Advertisements\AdvSelfadvetRenewal;
use App\Models\Advertisements\AdvTypologyMstr;
use App\Models\Advertisements\AdvVehicle;
use App\Models\Advertisements\AdvVehicleRenewal;
use App\Models\Advertisements\WfActiveDocument;
use App\Models\Markets\MarBanquteHall;
use App\Models\Markets\MarBanquteHallRenewal;
use App\Models\Markets\MarDharamshala;
use App\Models\Markets\MarDharamshalaRenewal;
use App\Models\Markets\MarHostel;
use App\Models\Markets\MarHostelRenewal;
use App\Models\Markets\MarLodge;
use App\Models\Markets\MarLodgeRenewal;
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
        $this->_lodge = Config::get('workflow-constants.LODGE_WORKFLOWS');
        $this->_dharamshala = Config::get('workflow-constants.DHARAMSHALA_WORKFLOWS');
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
            // $data = json_decode(Redis::get('adv_param_strings' . $mUlbId));      // Get Value from Redis Cache Memory
            $data = json_decode(Redis::get('adv_param_strings'));      // Get Value from Redis Cache Memory
            // $bearer = $req->bearerToken();
            if (!$data) {                                                        // If Cache Memory is not available
                $data = array();
                // $baseUrl = Config::get('constants.BASE_URL');
                $mParamString = new RefAdvParamstring();
                // $strings = $mParamString->masters($mUlbId);
                $strings = $mParamString->masters();
                $data['paramCategories'] = remove_null($strings->groupBy('param_category')->toArray());
                // Get Wards By Ulb Id
                // $mWards = Http::withHeaders([
                //     "Authorization" => "Bearer $bearer",
                //     "contentType" => "application/json"

                // ])->post($baseUrl . 'api/workflow/getWardByUlb', [
                //     "ulbId" => $mUlbId
                // ]);

                // if (!$mWards)
                //     throw new Exception("Wards not found");

                // $data['wards'] = $mWards['data'];

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
                // DB::table('adv_selfadvertisements')
                //     ->where('id', $req->id)
                //     ->update($updateData);

                $mAdvSelfadvertisement = AdvSelfadvertisement::find($req->id);

                $mAdvSelfadvertisement->payment_date= Carbon::now();
                $mAdvSelfadvertisement->payment_status= 1;
                $mAdvSelfadvertisement->payment_id= $req->paymentId;
                $mAdvSelfadvertisement->payment_details= $req->all();

                if($mAdvSelfadvertisement->renew_no==NULL){
                    $mAdvSelfadvertisement->valid_from = Carbon::now();
                    $mAdvSelfadvertisement->valid_upto = Carbon::now()->addYears(1)->subDay(1);
                }else{
                    $details=AdvSelfadvetRenewal::select('payment_date')
                                                ->where('application_no',$mAdvSelfadvertisement->application_no)
                                                ->orderByDesc('id')
                                                ->skip(1)->first();
                    $mAdvSelfadvertisement->valid_from = date("Y-m-d ",strtotime("+1 Years -1 days", $details->Payment_date));
                    $mAdvSelfadvertisement->valid_upto = date("Y-m-d ",strtotime("+2 Years -1 days", $details->Payment_date));
                }
                $mAdvSelfadvertisement->save();

                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('adv_selfadvet_renewals')
                    ->where('id', $mAdvSelfadvertisement->last_renewal_id)
                    ->update($updateData);
            } elseif ($req->workflowId == $this->_movableVehicle) { // Movable Vechicles Payment
                $mAdvVehicle = AdvVehicle::find($req->id);

                $mAdvVehicle->payment_date= Carbon::now();
                $mAdvVehicle->payment_status= 1;
                $mAdvVehicle->payment_id= $req->paymentId;
                $mAdvVehicle->payment_details= $req->all();

                if($mAdvVehicle->renew_no==NULL){
                    $mAdvVehicle->valid_from = Carbon::now();
                    $mAdvVehicle->valid_upto = Carbon::now()->addYears(1)->subDay(1);
                }else{
                    $details=AdvVehicleRenewal::select('payment_date')
                                                ->where('application_no',$mAdvVehicle->application_no)
                                                ->orderByDesc('id')
                                                ->skip(1)->first();
                    $mAdvVehicle->valid_from = date("Y-m-d ",strtotime("+1 Years -1 days", $details->Payment_date));
                    $mAdvVehicle->valid_upto = date("Y-m-d ",strtotime("+2 Years -1 days", $details->Payment_date));
                }
                $mAdvVehicle->save();
               
                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('adv_vehicle_renewals')
                    ->where('id', $mAdvVehicle->last_renewal_id)
                    ->update($updateData);
            } elseif ($req->workflowId ==  $this->_agency) { // Agency Apply Payment

                // DB::table('adv_agencies')
                //     ->where('id', $req->id)
                //     ->update($updateData);

                $mAdvAgency = AdvAgency::find($req->id);

                $mAdvAgency->payment_date= Carbon::now();
                $mAdvAgency->payment_status= 1;
                $mAdvAgency->payment_id= $req->paymentId;
                $mAdvAgency->payment_details= $req->all();

                if($mAdvAgency->renew_no==NULL){
                    $mAdvAgency->valid_from = Carbon::now();
                    $mAdvAgency->valid_upto = Carbon::now()->addYears(5)->subDay(1);
                }else{
                    $details=AdvAgencyRenewal::select('payment_date')
                                                ->where('license_no',$mAdvAgency->license_no)
                                                ->orderByDesc('id')
                                                ->skip(1)->first();
                    $mAdvAgency->valid_from = date("Y-m-d ",strtotime("+5 Years -1 days", $details->Payment_date));
                    $mAdvAgency->valid_upto = date("Y-m-d ",strtotime("+10 Years -1 days", $details->Payment_date));
                }
                $mAdvAgency->save();

                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('adv_agency_renewals')
                    ->where('id', $mAdvAgency->last_renewal_id)
                    ->update($updateData);
            } elseif ($req->workflowId == $this->_pvtLand) { // Private Land Payment

                // DB::table('adv_privatelands')
                //     ->where('id', $req->id)
                //     ->update($updateData);

                $mAdvPrivateland = AdvPrivateland::find($req->id);
                $mAdvPrivateland->payment_date= Carbon::now();
                $mAdvPrivateland->payment_status= 1;
                $mAdvPrivateland->payment_id= $req->paymentId;
                $mAdvPrivateland->payment_details= $req->all();

                if($mAdvPrivateland->renew_no==NULL){
                    $mAdvPrivateland->valid_from = Carbon::now();
                    $mAdvPrivateland->valid_upto = Carbon::now()->addYears(1)->subDay(1);
                }else{
                    $details=AdvPrivatelandRenewal::select('payment_date')
                                                ->where('license_no',$mAdvPrivateland->license_no)
                                                ->orderByDesc('id')
                                                ->skip(1)->first();
                    $mAdvPrivateland->valid_from = date("Y-m-d ",strtotime("+1 Years -1 days", $details->Payment_date));
                    $mAdvPrivateland->valid_upto = date("Y-m-d ",strtotime("+2 Years -1 days", $details->Payment_date));
                }
                $mAdvPrivateland->save();

                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('adv_privateland_renewals')
                    ->where('id', $mAdvPrivateland->last_renewal_id)
                    ->update($updateData);
            } elseif ($req->workflowId == $this->_hording) { // Hording Apply Payment

                // DB::table('adv_agency_licenses')
                //     ->where('id', $req->id)
                //     ->update($updateData);

                $mAdvHoarding = AdvHoarding::find($req->id);
                $mAdvHoarding->payment_date= Carbon::now();
                $mAdvHoarding->payment_status= 1;
                $mAdvHoarding->payment_id= $req->paymentId;
                $mAdvHoarding->payment_details= $req->all();

                if($mAdvHoarding->renew_no==NULL){
                    $mAdvHoarding->valid_from = Carbon::now();
                    $mAdvHoarding->valid_upto = Carbon::now()->addYears(1)->subDay(1);
                }else{
                    $details=AdvHoardingRenewal::select('payment_date')
                                                ->where('license_no',$mAdvHoarding->license_no)
                                                ->orderByDesc('id')
                                                ->skip(1)->first();
                    $mAdvHoarding->valid_from = date("Y-m-d ",strtotime("+1 Years -1 days", $details->Payment_date));
                    $mAdvHoarding->valid_upto = date("Y-m-d ",strtotime("+2 Years -1 days", $details->Payment_date));
                }
                $mAdvHoarding->save();

                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('adv_hoarding_renewals')
                    ->where('id', $mAdvHoarding->last_renewal_id)
                    ->update($updateData);
            } elseif ($req->workflowId == $this->_banquetHall) { // Hording Apply Payment

                // DB::table('mar_banqute_halls')
                //     ->where('id', $req->id)
                //     ->update($updateData);

                $mMarBanquteHall = MarBanquteHall::find($req->id);
                $mMarBanquteHall->payment_date= Carbon::now();
                $mMarBanquteHall->payment_status= 1;
                $mMarBanquteHall->payment_id= $req->paymentId;
                $mMarBanquteHall->payment_details= $req->all();

                if($mMarBanquteHall->renew_no==NULL){
                    $mMarBanquteHall->valid_from = Carbon::now();
                    $mMarBanquteHall->valid_upto = Carbon::now()->addYears(1)->subDay(1);
                }else{
                    $details=MarBanquteHallRenewal::select('valid_upto')
                                                ->where('application_no',$mMarBanquteHall->application_no)
                                                ->orderByDesc('id')
                                                ->skip(1)->first();
                    $mMarBanquteHall->valid_from = $details->valid_upto;
                    // $mMarBanquteHall->valid_upto = date("Y-m-d",strtotime("+1 Years -1 days", $details->valid_upto));
                    $mMarBanquteHall->valid_upto = Carbon::createFromFormat('Y-m-d', $details->valid_upto)->addYears(1)->subDay(1);
                }
                $mMarBanquteHall->save();

                $updateData['payment_amount'] = $req->amount;
                $updateData['valid_from'] = $mMarBanquteHall->valid_from;
                $updateData['valid_upto'] = $mMarBanquteHall->valid_upto;
                // update in Renewals Table
                DB::table('mar_banqute_hall_renewals')
                    ->where('id', $mMarBanquteHall->last_renewal_id)
                    ->update($updateData);

                // $updateData['payment_amount'] = $req->amount;
                // // update in Renewals Table
                // DB::table('mar_banqute_hall_renewals')
                //     ->where('id', $mMarBanquteHall->last_renewal_id)
                //     ->update($updateData);
            } elseif ($req->workflowId == $this->_hostel) { // Hostel Apply Payment

                // DB::table('mar_hostels')
                //     ->where('id', $req->id)
                //     ->update($updateData);

                $mMarHostel = MarHostel::find($req->id);
                $mMarHostel->payment_date= Carbon::now();
                $mMarHostel->payment_status= 1;
                $mMarHostel->payment_id= $req->paymentId;
                $mMarHostel->payment_details= $req->all();

                if($mMarHostel->renew_no==NULL){
                    $mMarHostel->valid_from = Carbon::now();
                    $mMarHostel->valid_upto = Carbon::now()->addYears(1)->subDay(1);
                }else{
                    $details=MarHostelRenewal::select('valid_upto')
                                                ->where('application_no',$mMarHostel->application_no)
                                                ->orderByDesc('id')
                                                ->skip(1)->first();
                    $mMarHostel->valid_from = $details->valid_upto;
                    $mMarHostel->valid_upto = Carbon::createFromFormat('Y-m-d', $details->valid_upto)->addYears(1)->subDay(1);
                }
                $mMarHostel->save();

                $updateData['payment_amount'] = $req->amount;
                $updateData['valid_from'] = $mMarHostel->valid_from;
                $updateData['valid_upto'] = $mMarHostel->valid_upto;
                // update in Renewals Table
                DB::table('mar_hostel_renewals')
                    ->where('id', $mMarHostel->last_renewal_id)
                    ->update($updateData);
            } elseif ($req->workflowId == $this->_lodge) { // Lodge Apply Payment

                $mMarLodge = MarLodge::find($req->id);
                $mMarLodge->payment_date= Carbon::now();
                $mMarLodge->payment_status= 1;
                $mMarLodge->payment_id= $req->paymentId;
                $mMarLodge->payment_details= $req->all();

                if($mMarLodge->renew_no==NULL){
                    $mMarLodge->valid_from = Carbon::now();
                    $mMarLodge->valid_upto = Carbon::now()->addYears(1)->subDay(1);
                }else{
                    $details=MarLodgeRenewal::select('valid_upto')
                                                ->where('application_no',$mMarLodge->application_no)
                                                ->orderByDesc('id')
                                                ->skip(1)->first();
                    $mMarLodge->valid_from = $details->valid_upto;
                    $mMarLodge->valid_upto = Carbon::createFromFormat('Y-m-d', $details->valid_upto)->addYears(1)->subDay(1);
                }
                $mMarLodge->save();

                $updateData['payment_amount'] = $req->amount;
                $updateData['valid_from'] = $mMarLodge->valid_from;
                $updateData['valid_upto'] = $mMarLodge->valid_upto;
                // update in Renewals Table
                DB::table('mar_lodge_renewals')
                    ->where('id', $mMarLodge->last_renewal_id)
                    ->update($updateData);
            } elseif ($req->workflowId == $this->_dharamshala) { // Dharamshala Apply Payment
                $mMarDharamshala = MarDharamshala::find($req->id);
                $mMarDharamshala->payment_date= Carbon::now();
                $mMarDharamshala->payment_status= 1;
                $mMarDharamshala->payment_id= $req->paymentId;
                $mMarDharamshala->payment_details= $req->all();

                if($mMarDharamshala->renew_no==NULL){
                    $mMarDharamshala->valid_from = Carbon::now();
                    $mMarDharamshala->valid_upto = Carbon::now()->addYears(1)->subDay(1);
                }else{
                    $details=MarDharamshalaRenewal::select('valid_upto')
                                                ->where('application_no',$mMarDharamshala->application_no)
                                                ->orderByDesc('id')
                                                ->skip(1)->first();
                    $mMarDharamshala->valid_from = $details->valid_upto;
                    $mMarDharamshala->valid_upto = Carbon::createFromFormat('Y-m-d', $details->valid_upto)->addYears(1)->subDay(1);
                }
                $mMarDharamshala->save();
                $a=$mMarDharamshala->valid_upto;
                $updateData['payment_amount'] = $req->amount;
                $updateData['valid_from'] = $mMarDharamshala->valid_from;
                $updateData['valid_upto'] = $mMarDharamshala->valid_upto;
                // update in Renewals Table
                DB::table('mar_dharamshala_renewals')
                    ->where('id', $mMarDharamshala->last_renewal_id)
                    ->update($updateData);
                // $updateData['payment_amount'] = $req->amount;
                // // update in Renewals Table
                // DB::table('mar_dharamshala_renewals')
                //     ->where('id', $mMarDharamshala->last_renewal_id)
                //     ->update($updateData);
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
                // $paymentDetails->inWords='Twenty Thousand Rupees Only';
                $paymentDetails->inWords=getIndianCurrency($paymentDetails->payment_amount);
            } elseif ($req->workflowId == $this->_hording) {
                $mAdvHoarding = new AdvHoarding();
                $paymentDetails = $mAdvHoarding->getPaymentDetails($req->paymentId);
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
