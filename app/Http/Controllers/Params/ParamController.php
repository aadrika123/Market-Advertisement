<?php

namespace App\Http\Controllers\Params;

use App\Http\Controllers\Controller;
use App\MicroServices\DocumentUpload;
use App\Models\Advertisements\AdvActiveSelfadvertisement;
use App\Models\Markets\MarRejectedDharamshala;
use App\Models\Param\RefAdvParamstring;
use App\Models\Advertisements\AdvActiveSelfadvetdocument;
use App\Models\Advertisements\AdvAgency;
use App\Models\Advertisements\AdvAgencyLicense;
use App\Models\Advertisements\AdvAgencyRenewal;
use App\Models\Advertisements\AdvHoarding;
use App\Models\Advertisements\AdvHoardingRenewal;
use App\Models\Advertisements\AdvPrivateland;
use App\Models\Advertisements\AdvPrivatelandRenewal;
use App\Models\Advertisements\AdvRejectedAgency;
use App\Models\Advertisements\AdvRejectedHoarding;
use App\Models\Advertisements\AdvRejectedPrivateland;
use App\Models\Advertisements\AdvRejectedSelfadvertisement;
use App\Models\Advertisements\AdvRejectedVehicle;
use App\Models\Advertisements\AdvSelfadvertisement;
use App\Models\Advertisements\AdvSelfadvetRenewal;
use App\Models\Advertisements\AdvTypologyMstr;
use App\Models\Advertisements\AdvVehicle;
use App\Models\Advertisements\AdvVehicleRenewal;
use App\Models\Advertisements\RefRequiredDocument;
use App\Models\Advertisements\WfActiveDocument;
use App\Models\Markets\MarBanquteHall;
use App\Models\Markets\MarBanquteHallRenewal;
use App\Models\Markets\MarDharamshala;
use App\Models\Markets\MarDharamshalaRenewal;
use App\Models\Markets\MarHostel;
use App\Models\Markets\MarHostelRenewal;
use App\Models\Markets\MarLodge;
use App\Models\Markets\MarLodgeRenewal;
use App\Models\Markets\MarRejectedBanquteHall;
use App\Models\Markets\MarRejectedHostel;
use App\Models\Markets\MarRejectedLodge;
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
    protected $_advtModuleId;
    protected $_marketModuleId;
    
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

        
        $this->_advtModuleId = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
        $this->_marketModuleId = Config::get('workflow-constants.MARKET_MODULE_ID');
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
            $data = json_decode(Redis::get('adv_param_strings'));      // Get Value from Redis Cache Memory
            if (!$data) {                                                        // If Cache Memory is not available
                $data = array();
                $mParamString = new RefAdvParamstring();
                $strings = $mParamString->masters();
                $data['paramCategories'] = remove_null($strings->groupBy('param_category')->toArray());

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
     * | All Document List
     */
    public function listDocument(){
      $mRefRequiredDocument=new RefRequiredDocument();
      $listDocs=$mRefRequiredDocument->listDocument($this->_advtModuleId, $this->_marketModuleId);
      $documentList=array();
      foreach($listDocs as $key => $val){
         $alldocs=explode("#",$val['requirements']);
          foreach($alldocs as $kinn => $valinn){
              $arr=explode(',',$valinn);
              $documentList[$val['code']][$kinn]['docType']=$arr[0];
              $documentList[$val['code']][$kinn]['docCode']=$arr[1];
              $documentList[$val['code']][$kinn]['docVal']=ucwords(strtolower(str_replace('_',' ',$arr[1])));
              $documentList[$val['code']][$kinn]['document_name']=ucwords(strtolower(str_replace('_',' ',$arr[1])));
              $documentList[$val['code']][$kinn]['code']=$val['code'];
          }
      }
      return $documentList;
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
                    $details=AdvSelfadvetRenewal::select('valid_upto')
                                                ->where('license_no',$mAdvSelfadvertisement->license_no)
                                                ->orderByDesc('id')
                                                ->skip(1)->first();
                    $mAdvSelfadvertisement->valid_from = $details->valid_upto;
                    $mAdvSelfadvertisement->valid_upto = Carbon::createFromFormat('Y-m-d', $details->valid_upto)->addYears(1)->subDay(1);
                }
                $mAdvSelfadvertisement->save();

                $updateData['payment_amount'] = $req->amount;
                $updateData['valid_from'] = $mAdvSelfadvertisement->valid_from;
                $updateData['valid_upto'] = $mAdvSelfadvertisement->valid_upto;
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
                    $details=AdvVehicleRenewal::select('valid_upto')
                                                ->where('license_no',$mAdvVehicle->license_no)
                                                ->orderByDesc('id')
                                                ->skip(1)->first();
                    $mAdvVehicle->valid_from = $details->valid_upto;
                    $mAdvVehicle->valid_upto = Carbon::createFromFormat('Y-m-d', $details->valid_upto)->addYears(1)->subDay(1);
                }
                $mAdvVehicle->save();
               
                $updateData['payment_amount'] = $req->amount;
                $updateData['valid_from'] = $mAdvVehicle->valid_from;
                $updateData['valid_upto'] = $mAdvVehicle->valid_upto;
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
                    $details=AdvAgencyRenewal::select('valid_upto')
                                                ->where('license_no',$mAdvAgency->license_no)
                                                ->orderByDesc('id')
                                                ->skip(1)->first();
                    $mAdvAgency->valid_from = $details->valid_upto;
                    $mAdvAgency->valid_upto = Carbon::createFromFormat('Y-m-d', $details->valid_upto)->addYears(5)->subDay(1);
                }
                $mAdvAgency->save();

                $updateData['payment_amount'] = $req->amount;
                $updateData['valid_from'] = $mAdvAgency->valid_from;
                $updateData['valid_upto'] = $mAdvAgency->valid_upto;
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
                    $details=AdvPrivatelandRenewal::select('valid_upto')
                                                ->where('license_no',$mAdvPrivateland->license_no)
                                                ->orderByDesc('id')
                                                ->skip(1)->first();
                    $mAdvPrivateland->valid_from = $details->valid_upto;
                    $mAdvPrivateland->valid_upto = Carbon::createFromFormat('Y-m-d', $details->valid_upto)->addYears(1)->subDay(1);
                }
                $mAdvPrivateland->save();

                $updateData['payment_amount'] = $req->amount;
                $updateData['valid_from'] = $mAdvPrivateland->valid_from;
                $updateData['valid_upto'] = $mAdvPrivateland->valid_upto;
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
                    $details=AdvHoardingRenewal::select('valid_upto')
                                                ->where('license_no',$mAdvHoarding->license_no)
                                                ->orderByDesc('id')
                                                ->skip(1)->first();
                    $mAdvHoarding->valid_from = $details->valid_upto;
                    $mAdvHoarding->valid_upto = Carbon::createFromFormat('Y-m-d', $details->valid_upto)->addYears(1)->subDay(1);
                }
                $mAdvHoarding->save();

                $updateData['payment_amount'] = $req->amount;
                $updateData['valid_from'] = $mAdvHoarding->valid_from;
                $updateData['valid_upto'] = $mAdvHoarding->valid_upto;
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
                    $sad=$mMarLodge->valid_upto;
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

    public function advertDashboard(){
        try{
            $madvSelfAdvertisement=new AdvSelfadvertisement(); 
            $approveList=$madvSelfAdvertisement->allApproveList();              // Find Self Advertisement Approve Applications
            $advert['selfApprovedApplications']=$approveList;

            $mAdvRejectedSelfadvertisement=new AdvRejectedSelfadvertisement();
            $rejectList=$mAdvRejectedSelfadvertisement->rejectedApplication();  // Find Self Advertisement Rejected Applications
            $advert['selfRejectedApplications']=$rejectList;

            
            $mAdvPrivateland=new AdvPrivateland(); 
            $pvtapproveList=$mAdvPrivateland->allApproveList();                 // Find Pvt Land Approve Applications
            $advert['pvtLandApprovedApplications']=$pvtapproveList;

            $mAdvRejectedPrivateland=new AdvRejectedPrivateland();
            $pvtRejectList=$mAdvRejectedPrivateland->rejectedApplication();     // Find Pvt Land Rejected Applications
            $advert['pvtLandRejectedApplications']=$pvtRejectList;

            
            $mAdvVehicle=new AdvVehicle(); 
            $vehicleApproveList=$mAdvVehicle->allApproveList();                // Find Vehicle Approve Applications
            $advert['vehicleApprovedApplications']=$vehicleApproveList;

            $mAdvRejectedVehicle=new AdvRejectedVehicle();
            $vehicleRejectList=$mAdvRejectedVehicle->rejectedApplication();    // Find Vehicle Rejected Applications
            $advert['vehicleRejectedApplications']=$vehicleRejectList;
            

            $mAdvAgency=new AdvAgency(); 
            $agencyApproveList=$mAdvAgency->allApproveList();                  // Find Agency Approve Applications
            $advert['agencyApprovedApplications']=$agencyApproveList;

            $mAdvRejectedAgency=new AdvRejectedAgency();
            $agencyRejectList=$mAdvRejectedAgency->rejectedApplication();      // Find Agency Rejected Applications
            $advert['agencyRejectedApplications']=$agencyRejectList;


            $mAdvHoarding=new AdvHoarding(); 
            $hoardingApproveList=$mAdvHoarding->allApproveList();              // Find Hoarding Approve Applications
            $advert['hoardingApprovedApplications']=$hoardingApproveList;

            $mAdvRejectedHoarding=new AdvRejectedHoarding();
            $hoardingRejectList=$mAdvRejectedHoarding->rejectedApplication();  // Find Hoarding Rejected Applications
            $advert['hoardingRejectedApplications']=$hoardingRejectList;

            return responseMsgs(true, 'Data Fetched',  $advert, "050124", "1.0", "2 Sec", "POST");
        }catch(Exception $e){
            responseMsgs(false, $e->getMessage(), "");
        }
    }


    public function marketDashboard(){
        try{
            $mMarBanquteHall=new MarBanquteHall(); 
            $approveList=$mMarBanquteHall->allApproveList();              // Find Banquet Hall Approve Applications
            $market['banquetApprovedApplications']=$approveList;

            $mMarRejectedBanquteHall=new MarRejectedBanquteHall();
            $rejectList=$mMarRejectedBanquteHall->rejectedApplication();  // Find Banquet Hall Rejected Applications
            $market['banquetRejectedApplications']=$rejectList;

            
            $mMarHostel=new MarHostel(); 
            $hostelapproveList=$mMarHostel->allApproveList();                 // Find Hostel Approve Applications
            $market['hostelApprovedApplications']=$hostelapproveList;

            $mMarRejectedHostel=new MarRejectedHostel();
            $hostelRejectList=$mMarRejectedHostel->rejectedApplication();     // Find Hostel Rejected Applications
            $market['hostelRejectedApplications']=$hostelRejectList;

            
            $mMarLodge=new MarLodge(); 
            $lodgeApproveList=$mMarLodge->allApproveList();                // Find Lodge Approve Applications
            $market['lodgeApprovedApplications']=$lodgeApproveList;

            $mMarRejectedLodge=new MarRejectedLodge();
            $lodgeRejectList=$mMarRejectedLodge->rejectedApplication();    // Find Lodge Rejected Applications
            $market['lodgeRejectedApplications']=$lodgeRejectList;
            

            $mMarDharamshala=new MarDharamshala(); 
            $dharamshalaApproveList=$mMarDharamshala->allApproveList();                  // Find Dharamshala Approve Applications
            $market['dharamshalaApprovedApplications']=$dharamshalaApproveList;

            $mMarRejectedDharamshala=new MarRejectedDharamshala();
            $dharamshalaRejectList=$mMarRejectedDharamshala->rejectedApplication();      // Find Dharamshala Rejected Applications
            $market['dharamshalaRejectedApplications']=$dharamshalaRejectList;


            return responseMsgs(true, 'Data Fetched',  $market, "050124", "1.0", "2 Sec", "POST");
        }catch(Exception $e){
            responseMsgs(false, $e->getMessage(), "");
        }
    }

}
