<?php

namespace App\Models\Advertisements;

use App\Models\Markets\MarDharamshala;
use App\Models\Markets\MarDharamshalaRenewal;
use App\Models\Markets\MarHostel;
use App\Models\Markets\MarHostelRenewal;
use App\Models\Markets\MarLodge;
use App\Models\Markets\MarLodgeRenewal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class AdvChequeDtl extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $_selfAdvt;
    protected $_pvtLand;
    protected $_movableVehicle;
    protected $_agency;
    protected $_hording;
    protected $_lodge;
    protected $_hostel;
    protected $_dharamshala;
    public function __construct()
    {
        $this->_selfAdvt = Config::get('workflow-constants.ADVERTISEMENT_WORKFLOWS');
        $this->_pvtLand = Config::get('workflow-constants.PRIVATE_LANDS_WORKFLOWS');
        $this->_movableVehicle = Config::get('workflow-constants.MOVABLE_VEHICLE_WORKFLOWS');
        $this->_agency = Config::get('workflow-constants.AGENCY_WORKFLOWS');
        $this->_hording = Config::get('workflow-constants.AGENCY_HORDING_WORKFLOWS');
        $this->_lodge = Config::get('workflow-constants.LODGE_WORKFLOWS');
        $this->_hostel = Config::get('workflow-constants.HOSTEL_WORKFLOWS');
        $this->_dharamshala=Config::get('workflow-constants.DHARAMSHALA_WORKFLOWS');
    }

    public function entryChequeDd($req)
    {
        $date = date('Y-m-d');
        $financial_year = $this->getFinancialYear($date);
        $metaReqs = array_merge(
            [
                'application_id' => $req->applicationId,                        //  temp_id of Application
                'workflow_id' => $req->workflowId,
                'bank_name' => $req->bankName,
                'branch_name' => $req->branchName,
                'cheque_no' => $req->chequeNo,
                'cheque_date' => Carbon::now(),
                'transaction_no' => $financial_year
            ],
        );
        // return $metaReqs;
        $id = AdvChequeDtl::create($metaReqs)->id;
        return $financial_year . "/" . $id;
    }

    public function getFinancialYear($inputDate, $format = "Y")
    {
        $date = date_create($inputDate);
        if (date_format($date, "m") >= 4) { //On or After April (FY is current year - next year)
            $financial_year = (date_format($date, $format)) . '-' . (date_format($date, $format) + 1);
        } else { //On or Before March (FY is previous year - current year)
            $financial_year = (date_format($date, $format) - 1) . '-' . date_format($date, $format);
        }

        return $financial_year;
    }

    public function clearOrBounceCheque($req)
    {
        $mAdvCheckDtls = AdvChequeDtl::find($req->paymentId);
        $mAdvCheckDtls->status = $req->status;
        $mAdvCheckDtls->remarks = $req->remarks;
        $mAdvCheckDtls->bounce_amount = $req->bounceAmount;
        $mAdvCheckDtls->clear_bounce_date = Carbon::now();
        $mAdvCheckDtls->save();
        $payId = $mAdvCheckDtls->id;
        $applicationId = $mAdvCheckDtls->application_id;
        $workflowId = $mAdvCheckDtls->workflow_id;
        $payment_id = $mAdvCheckDtls->transaction_no . "/" . $payId;

        if ($req->status == '1') {   // Paid Case

            if ($workflowId == $this->_agency) {
                $mAdvAgency=AdvAgency::find($applicationId);

                $payDetails=array('paymentMode'=>'CHEQUE/DD','id'=>$req->applicationId,'amount'=>$mAdvAgency->payment_amount,'workflowId'=>$mAdvAgency->workflow_id,'userId'=>$mAdvAgency->citizen_id,'ulbId'=>$mAdvAgency->ulb_id,'transDate'=>Carbon::now(),'paymentId'=>$payment_id);

                if($mAdvAgency->renew_no==NULL){
                    $valid_from = Carbon::now();
                    $valid_upto = Carbon::now()->addYears(1)->subDay(1);
                }else{
                    $details=AdvVehicleRenewal::select('payment_date')
                                    ->where('application_no',$mAdvAgency->application_no)
                                    ->orderByDesc('id')
                                    ->skip(1)->first();
                    $valid_from = date("Y-m-d ",strtotime("+1 Years -1 days", $details->Payment_date));
                    $valid_upto = date("Y-m-d ",strtotime("+2 Years -1 days", $details->Payment_date));
                } 
                // update on Agency Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => json_encode($payDetails),
                        'payment_status' => "1",
                        'payment_date' => Carbon::now(),
                        'valid_from' => $valid_from,
                        'valid_upto' => $valid_upto,
                    ],
                );
                AdvAgency::where('id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_agencies')->where('id', $applicationId)->first()->payment_amount;
                // update on Agency  renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => json_encode($payDetails),
                        'payment_status' => "1",
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return AdvAgencyRenewal::where('agencyadvet_id', $applicationId)->update($metaReqs);
            }
            elseif ($workflowId == $this->_selfAdvt) {
                // update on SelfAdvertiesment Table
                $mAdvSelfadvertisement=AdvSelfadvertisement::find($applicationId);

                $payDetails=array('paymentMode'=>'CHEQUE/DD','id'=>$req->applicationId,'amount'=>$mAdvSelfadvertisement->payment_amount,'workflowId'=>$mAdvSelfadvertisement->workflow_id,'userId'=>$mAdvSelfadvertisement->citizen_id,'ulbId'=>$mAdvSelfadvertisement->ulb_id,'transDate'=>Carbon::now(),'paymentId'=>$payment_id);

                if($mAdvSelfadvertisement->renew_no==NULL){
                    $valid_from = Carbon::now();
                    $valid_upto = Carbon::now()->addYears(1)->subDay(1);
                }else{
                    // $previousApplication=$this->findPreviousApplication($mAdvSelfadvertisement->application_no);
                    $details=AdvSelfadvetRenewal::select('payment_date')
                                    ->where('application_no',$mAdvSelfadvertisement->application_no)
                                    ->orderByDesc('id')
                                    ->skip(1)->first();
                    $valid_from = date("Y-m-d ",strtotime("+1 Years -1 days", $details->Payment_date));
                    $valid_upto = date("Y-m-d ",strtotime("+2 Years -1 days", $details->Payment_date));
                } 
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => json_encode($payDetails),
                        'payment_status' => "1",
                        'payment_date' => Carbon::now(),
                        'valid_from' => $valid_from,
                        'valid_upto' => $valid_upto,
                    ],
                );
                AdvSelfadvertisement::where('id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_selfadvertisements')->where('id', $applicationId)->first()->payment_amount;
                // update on SelfAdvertiesment  renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => json_encode($payDetails),
                        'payment_status' => "1",
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return AdvSelfadvetRenewal::where('id', $applicationId)->update($metaReqs);
            }
            elseif ($workflowId == $this->_pvtLand) {
                $mAdvPrivateland=AdvPrivateland::find($applicationId);

                $payDetails=array('paymentMode'=>'CHEQUE/DD','id'=>$req->applicationId,'amount'=>$mAdvPrivateland->payment_amount,'workflowId'=>$mAdvPrivateland->workflow_id,'userId'=>$mAdvPrivateland->citizen_id,'ulbId'=>$mAdvPrivateland->ulb_id,'transDate'=>Carbon::now(),'paymentId'=>$payment_id);

                if($mAdvPrivateland->renew_no==NULL){
                    $valid_from = Carbon::now();
                    $valid_upto = Carbon::now()->addYears(1)->subDay(1);
                }else{
                    $details=AdvPrivatelandRenewal::select('payment_date')
                                    ->where('application_no',$mAdvPrivateland->application_no)
                                    ->orderByDesc('id')
                                    ->skip(1)->first();
                    $valid_from = date("Y-m-d ",strtotime("+1 Years -1 days", $details->Payment_date));
                    $valid_upto = date("Y-m-d ",strtotime("+2 Years -1 days", $details->Payment_date));
                } 
                // update on Privateland Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => json_encode($payDetails),
                        'payment_status' => "1",
                        'payment_date' => Carbon::now(),
                        'valid_from' => $valid_from,
                        'valid_upto' => $valid_upto,
                    ],
                );
                AdvPrivateland::where('id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_privatelands')->where('id', $applicationId)->first()->payment_amount;
                // update on Privateland  renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => json_encode($payDetails),
                        'payment_status' => "1",
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return AdvPrivatelandRenewal::where('id', $applicationId)->update($metaReqs);
            }
            elseif ($workflowId == $this->_movableVehicle) {
                $mAdvVehicle=AdvVehicle::find($applicationId);
                
                $payDetails=array('paymentMode'=>'CHEQUE/DD','id'=>$req->applicationId,'amount'=>$mAdvVehicle->payment_amount,'workflowId'=>$mAdvVehicle->workflow_id,'userId'=>$mAdvVehicle->citizen_id,'ulbId'=>$mAdvVehicle->ulb_id,'transDate'=>Carbon::now(),'paymentId'=>$payment_id);

                if($mAdvVehicle->renew_no==NULL){
                    $valid_from = Carbon::now();
                    $valid_upto = Carbon::now()->addYears(1)->subDay(1);
                }else{
                    $details=AdvVehicleRenewal::select('payment_date')
                                    ->where('application_no',$mAdvVehicle->application_no)
                                    ->orderByDesc('id')
                                    ->skip(1)->first();
                    $valid_from = date("Y-m-d ",strtotime("+1 Years -1 days", $details->Payment_date));
                    $valid_upto = date("Y-m-d ",strtotime("+2 Years -1 days", $details->Payment_date));
                } 
                // update on Vehicle Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => json_encode($payDetails),
                        'payment_status' => "1",
                        'payment_date' => Carbon::now(),
                        'valid_from' => $valid_from,
                        'valid_upto' => $valid_upto,
                    ],
                );
                AdvVehicle::where('id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_vehicles')->where('id', $applicationId)->first()->payment_amount;
                // update on Vehicle  renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => json_encode($payDetails),
                        'payment_status' => "1",
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return AdvVehicleRenewal::where('id', $applicationId)->update($metaReqs);
            }
            elseif ($workflowId == $this->_hording) {
                $mAdvAgencyLicense=AdvAgencyLicense::find($applicationId);
                
                $payDetails=array('paymentMode'=>'CHEQUE/DD','id'=>$req->applicationId,'amount'=>$mAdvAgencyLicense->payment_amount,'workflowId'=>$mAdvAgencyLicense->workflow_id,'userId'=>$mAdvAgencyLicense->citizen_id,'ulbId'=>$mAdvAgencyLicense->ulb_id,'transDate'=>Carbon::now(),'paymentId'=>$payment_id);

                if($mAdvAgencyLicense->renew_no==NULL){
                    $valid_from = Carbon::now();
                    $valid_upto = Carbon::now()->addYears(1)->subDay(1);
                }else{
                    $details=AdvVehicleRenewal::select('payment_date')
                                    ->where('application_no',$mAdvAgencyLicense->application_no)
                                    ->orderByDesc('id')
                                    ->skip(1)->first();
                    $valid_from = date("Y-m-d ",strtotime("+1 Years -1 days", $details->Payment_date));
                    $valid_upto = date("Y-m-d ",strtotime("+2 Years -1 days", $details->Payment_date));
                } 
                // update on Vehicle Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => json_encode($payDetails),
                        'payment_status' => "1",
                        'payment_date' => Carbon::now()
                    ],
                );
                AdvAgencyLicense::where('id', $applicationId)->update($metaReqs);
                
                $amount = DB::table('adv_agency_licenses')->where('id', $applicationId)->first()->payment_amount;
                // update on Agency Hording  renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => json_encode($payDetails),
                        'payment_status' => "1",
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return AdvAgencyLicenseRenewal::where('licenseadvet_id', $applicationId)->update($metaReqs);
            }
            elseif ($workflowId == $this->_lodge) {
                // update on Lodge Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => "By CHEQUE/DD",
                        'payment_status' => "1",
                        'payment_date' => Carbon::now()
                    ],
                );
                MarLodge::where('id', $applicationId)->update($metaReqs);
                $amount = DB::table('mar_lodges')->where('id', $applicationId)->first()->payment_amount;
                // update on Lodge renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => "By CHEQUE/DD",
                        'payment_status' => "1",
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return MarLodgeRenewal::where('app_id', $applicationId)->update($metaReqs);
            }
            elseif ($workflowId == $this->_hostel) {
                // update on Hostel Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => "By CHEQUE/DD",
                        'payment_status' => "1",
                        'payment_date' => Carbon::now()
                    ],
                );
                MarHostel::where('id', $applicationId)->update($metaReqs);
                $amount = DB::table('mar_hostels')->where('id', $applicationId)->first()->payment_amount;
                // update on Hostel renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => "By CHEQUE/DD",
                        'payment_status' => "1",
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return MarHostelRenewal::where('app_id', $applicationId)->update($metaReqs);
            }
            elseif ($workflowId == $this->_dharamshala) {
                // update on Dharamshala Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => "By CHEQUE/DD",
                        'payment_status' => "1",
                        'payment_date' => Carbon::now()
                    ],
                );
                MarDharamshala::where('id', $applicationId)->update($metaReqs);
                $amount = DB::table('mar_dharamshalas')->where('id', $applicationId)->first()->payment_amount;
                // update on Dharamshala renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => "By CHEQUE/DD",
                        'payment_status' => "1",
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return MarDharamshalaRenewal::where('app_id', $applicationId)->update($metaReqs);
            }
        }elseif($req->status=='2'){   // Cheque Cancelled 
            if ($workflowId == $this->_agency) {
                // update on Agency Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => $req->remarks,
                        'payment_status' => $req->status,
                        'payment_date' => Carbon::now()
                    ],
                );
                AdvAgency::where('id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_agencies')->where('id', $applicationId)->first()->payment_amount;
                // update on Agency  renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => $req->remarks,
                        'payment_status' =>$req->status,
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return AdvAgencyRenewal::where('agencyadvet_id', $applicationId)->update($metaReqs);
            }
            elseif ($workflowId == $this->_selfAdvt) {
                // update on SelfAdvertiesment Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => $req->remarks,
                        'payment_status' => $req->status,
                        'payment_date' => Carbon::now()
                    ],
                );
                AdvSelfadvertisement::where('id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_selfadvertisements')->where('id', $applicationId)->first()->payment_amount;
                // update on SelfAdvertiesment  renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => $req->remarks,
                        'payment_status' => $req->status,
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return AdvSelfadvetRenewal::where('id', $applicationId)->update($metaReqs);
            }
            elseif ($workflowId == $this->_pvtLand) {
                // update on Privateland Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => $req->remarks,
                        'payment_status' => $req->status,
                        'payment_date' => Carbon::now()
                    ],
                );
                AdvPrivateland::where('id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_privatelands')->where('id', $applicationId)->first()->payment_amount;
                // update on Privateland  renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => $req->remarks,
                        'payment_status' => $req->status,
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return AdvPrivatelandRenewal::where('id', $applicationId)->update($metaReqs);
            }
            elseif ($workflowId == $this->_movableVehicle) {
                // update on Vehicle Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => $req->remarks,
                        'payment_status' => $req->status,
                        'payment_date' => Carbon::now()
                    ],
                );
                AdvVehicle::where('id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_vehicles')->where('id', $applicationId)->first()->payment_amount;
                // update on Vehicle  renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => $req->remarks,
                        'payment_status' => $req->status,
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return AdvVehicleRenewal::where('id', $applicationId)->update($metaReqs);
            }
            elseif ($workflowId == $this->_hording) {
                // update on Vehicle Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => $req->remarks,
                        'payment_status' => $req->status,
                        'payment_date' => Carbon::now()
                    ],
                );
                AdvAgencyLicense::where('id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_agency_licenses')->where('id', $applicationId)->first()->payment_amount;
                // update on Agency Hording  renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => $req->remarks,
                        'payment_status' => $req->status,
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return AdvAgencyLicenseRenewal::where('licenseadvet_id', $applicationId)->update($metaReqs);
            }
            elseif ($workflowId == $this->_lodge) {
                // update on Vehicle Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => $req->remarks,
                        'payment_status' => $req->status,
                        'payment_date' => Carbon::now()
                    ],
                );
                MarLodge::where('id', $applicationId)->update($metaReqs);
                $amount = DB::table('mar_lodges')->where('id', $applicationId)->first()->payment_amount;
                // update on Agency Hording  renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => $req->remarks,
                        'payment_status' => $req->status,
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return MarLodgeRenewal::where('app_id', $applicationId)->update($metaReqs);
            }
            elseif ($workflowId == $this->_hostel) {
                // update on Hostel Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => $req->remarks,
                        'payment_status' => $req->status,
                        'payment_date' => Carbon::now()
                    ],
                );
                MarHostel::where('id', $applicationId)->update($metaReqs);
                $amount = DB::table('mar_hostels')->where('id', $applicationId)->first()->payment_amount;
                // update on Hostel  renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => $req->remarks,
                        'payment_status' => $req->status,
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return MarHostelRenewal::where('app_id', $applicationId)->update($metaReqs);
            }
            elseif ($workflowId == $this->_dharamshala) {
                // update on Dharamshala Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => $req->remarks,
                        'payment_status' => $req->status,
                        'payment_date' => Carbon::now()
                    ],
                );
                MarDharamshala::where('id', $applicationId)->update($metaReqs);
                $amount = DB::table('mar_dharamshalas')->where('id', $applicationId)->first()->payment_amount;
                // update on Dharamshala  renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => $req->remarks,
                        'payment_status' => $req->status,
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return MarDharamshalaRenewal::where('app_id', $applicationId)->update($metaReqs);
            }
        }
    }
}
