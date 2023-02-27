<?php

namespace App\Models\Advertisements;

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
    public function __construct()
    {
        $this->_selfAdvt = Config::get('workflow-constants.ADVERTISEMENT_WORKFLOWS');
        $this->_pvtLand = Config::get('workflow-constants.PRIVATE_LANDS_WORKFLOWS');
        $this->_movableVehicle = Config::get('workflow-constants.MOVABLE_VEHICLE_WORKFLOWS');
        $this->_agency = Config::get('workflow-constants.AGENCY_WORKFLOWS');
        $this->_hording = Config::get('workflow-constants.AGENCY_HORDING_WORKFLOWS');
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
                // update on Agency Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => "By CHEQUE/DD",
                        'payment_status' => "1",
                        'payment_date' => Carbon::now()
                    ],
                );
                AdvAgency::where('temp_id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_agencies')->where('temp_id', $applicationId)->first()->payment_amount;
                // update on Agency  renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => "By CHEQUE/DD",
                        'payment_status' => "1",
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
                        'payment_details' => "By CHEQUE/DD",
                        'payment_status' => "1",
                        'payment_date' => Carbon::now()
                    ],
                );
                AdvSelfadvertisement::where('temp_id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_selfadvertisements')->where('temp_id', $applicationId)->first()->payment_amount;
                // update on SelfAdvertiesment  renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => "By CHEQUE/DD",
                        'payment_status' => "1",
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return AdvSelfadvetRenewal::where('selfadvet_id', $applicationId)->update($metaReqs);
            }

            elseif ($workflowId == $this->_pvtLand) {
                // update on Privateland Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => "By CHEQUE/DD",
                        'payment_status' => "1",
                        'payment_date' => Carbon::now()
                    ],
                );
                AdvPrivateland::where('temp_id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_privatelands')->where('temp_id', $applicationId)->first()->payment_amount;
                // update on Privateland  renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => "By CHEQUE/DD",
                        'payment_status' => "1",
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return AdvPrivatelandRenewal::where('privateland_id', $applicationId)->update($metaReqs);
            }
            elseif ($workflowId == $this->_movableVehicle) {
                // update on Vehicle Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => "By CHEQUE/DD",
                        'payment_status' => "1",
                        'payment_date' => Carbon::now()
                    ],
                );
                AdvVehicle::where('temp_id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_vehicles')->where('temp_id', $applicationId)->first()->payment_amount;
                // update on Vehicle  renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => "By CHEQUE/DD",
                        'payment_status' => "1",
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return AdvVehicleRenewal::where('vechcleadvet_id', $applicationId)->update($metaReqs);
            }
            elseif ($workflowId == $this->_hording) {
                // update on Vehicle Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => "By CHEQUE/DD",
                        'payment_status' => "1",
                        'payment_date' => Carbon::now()
                    ],
                );
                AdvAgencyLicense::where('temp_id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_agency_licenses')->where('temp_id', $applicationId)->first()->payment_amount;
                // update on Agency Hording  renewal Table
                $metaReqs = array_merge(
                    [
                        'payment_id' => $payment_id,
                        'payment_details' => "By CHEQUE/DD",
                        'payment_status' => "1",
                        'payment_date' => Carbon::now(),
                        'payment_amount' => $amount,
                    ],
                );
                return AdvAgencyLicenseRenewal::where('licenseadvet_id', $applicationId)->update($metaReqs);
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
                AdvAgency::where('temp_id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_agencies')->where('temp_id', $applicationId)->first()->payment_amount;
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
                AdvSelfadvertisement::where('temp_id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_selfadvertisements')->where('temp_id', $applicationId)->first()->payment_amount;
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
                return AdvSelfadvetRenewal::where('selfadvet_id', $applicationId)->update($metaReqs);
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
                AdvPrivateland::where('temp_id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_privatelands')->where('temp_id', $applicationId)->first()->payment_amount;
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
                return AdvPrivatelandRenewal::where('privateland_id', $applicationId)->update($metaReqs);
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
                AdvVehicle::where('temp_id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_vehicles')->where('temp_id', $applicationId)->first()->payment_amount;
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
                return AdvVehicleRenewal::where('vechcleadvet_id', $applicationId)->update($metaReqs);
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
                AdvAgencyLicense::where('temp_id', $applicationId)->update($metaReqs);
                $amount = DB::table('adv_agency_licenses')->where('temp_id', $applicationId)->first()->payment_amount;
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
        }
    }
}
