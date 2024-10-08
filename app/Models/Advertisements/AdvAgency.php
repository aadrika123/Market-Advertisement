<?php

namespace App\Models\Advertisements;

use App\MicroServices\IdGenerator\PrefixIdGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Config;

class AdvAgency extends Model
{
    use HasFactory;

    /**
     * | Get Agency Details by Agency Id
     */
    public function getagencyDetails($email)
    {
        $details1 = DB::table('adv_agencies')
            ->select('adv_agencies.*', 'et.string_parameter as entity_type_name')
            ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', 'adv_agencies.entity_type')
            ->where('adv_agencies.email', $email)
            ->first();
        $details = json_decode(json_encode($details1), true);
        if (!empty($details)) {
            $details['expiry_date'] = $details['valid_upto'];
            $warning_date = carbon::parse($details['valid_upto'])->subDay(30)->format('Y-m-d');;
            $details['warning_date'] = $warning_date;
            $current_date = date('Y-m-d');
            if ($current_date < $warning_date) {
                $details['warning'] = 0; // Warning Not Enabled
            } elseif ($current_date >= $warning_date) {
                $details['warning'] = 1; // Warning Enabled
            }
            if ($current_date > $details['expiry_date']) {
                $details['warning'] = 2;  // Expired
            }
            $directors = DB::table('adv_active_agencydirectors')
                ->select(
                    'adv_active_agencydirectors.*',
                    DB::raw("CONCAT(adv_active_agencydirectors.relative_path,'/',adv_active_agencydirectors.doc_name) as document_path")
                )
                ->where('agency_id', $details['id'])
                ->get();
            $details['directors'] = remove_null($directors->toArray());
        }
        return $details;
    }

    /**
     * Summary of allApproveList
     * @return void
     */
    public function allApproveList()
    {
        return AdvAgency::select(
            'adv_agencies.id',
            'adv_agencies.application_no',
            DB::raw("TO_CHAR(adv_agencies.application_date, 'DD-MM-YYYY') as application_date"),
            'adv_agencies.application_type',
            'adv_agencies.entity_name',
            'adv_agencies.payment_status',
            'adv_agencies.mobile_no',
            'adv_agencies.payment_amount',
            'adv_agencies.approve_date',
            'adv_agencies.valid_upto',
            'adv_agencies.valid_from',
            'adv_agencies.citizen_id',
            'adv_agencies.user_id',
            'adv_agencies.ulb_id',
            'adv_agencies.workflow_id',
            'adv_agencies.license_no',
            'adv_agencies.payment_id',
            DB::raw("'agency' as type"),
            DB::raw("'' as owner_name"),
            'um.ulb_name as ulb_name',
        )
            ->join('ulb_masters as um', 'um.id', '=', 'adv_agencies.ulb_id')
            ->orderByDesc('adv_agencies.id')
            ->get();
    }

    /**
     * | Get Application Approve List by Role Ids
     */
    public function listApproved($citizenId, $userType)
    {
        $allApproveList = $this->allApproveList();
        if ($userType == 'Citizen') {
            return collect($allApproveList)->where('citizen_id', $citizenId)->values();
        } else {
            return collect($allApproveList)->values();
        }
    }

    /**
     * | Get Application Approve List by Role Ids
     */
    public function listjskApprovedApplication()
    {
        return AdvAgency::select(
            'adv_agencies.id',
            'adv_agencies.application_no',
            DB::raw("TO_CHAR(adv_agencies.application_date, 'DD-MM-YYYY') as application_date"),
            'adv_agencies.application_type',
            'adv_agencies.entity_name',
            'adv_agencies.payment_status',
            'adv_agencies.mobile_no',
            'adv_agencies.payment_amount',
            'adv_agencies.approve_date',
            'adv_agencies.valid_upto',
            'adv_agencies.valid_from',
            'adv_agencies.citizen_id',
            'adv_agencies.user_id',
            'adv_agencies.ulb_id',
            'adv_agencies.workflow_id',
            'adv_agencies.license_no',
            'adv_agencies.payment_id',
            DB::raw("STRING_AGG(agd.director_name, ', ') as director_names"),
            DB::raw("'agency' as type"),
            DB::raw("'' as owner_name"),
            'um.ulb_name as ulb_name',
            DB::raw("CASE WHEN user_id IS NOT NULL THEN 'jsk' ELSE 'citizen' END AS applied_by")
        )
            ->join('adv_active_agencydirectors as agd', 'agd.agency_id', '=', 'adv_agencies.id')
            ->join('ulb_masters as um', 'um.id', '=', 'adv_agencies.ulb_id')
            ->groupBy(
                'adv_agencies.id',
                'adv_agencies.application_no',
                'adv_agencies.application_date',
                'adv_agencies.application_type',
                'adv_agencies.entity_name',
                'adv_agencies.payment_status',
                'adv_agencies.mobile_no',
                'adv_agencies.payment_amount',
                'adv_agencies.approve_date',
                'adv_agencies.valid_upto',
                'adv_agencies.valid_from',
                'adv_agencies.citizen_id',
                'adv_agencies.user_id',
                'adv_agencies.ulb_id',
                'adv_agencies.workflow_id',
                'adv_agencies.license_no',
                'adv_agencies.payment_id',
                'um.ulb_name'
            )
            ->orderByDesc('adv_agencies.id');
        //->get();
    }


    /**
     * | Get Application Details FOr Payments
     */
    public function getApplicationDetailsForPayment($id)
    {
        return AdvAgency::where('id', $id)
            ->select(
                'id',
                'application_no',
                DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"),
                'entity_name',
                'payment_status',
                'payment_amount',
                'approve_date',
                'ulb_id',
                'workflow_id',
            )
            ->first();
    }


    /**
     * | Check Login User is Asgency or Not
     */
    public function checkAgency($citizenId)
    {
        $details = AdvAgency::where('citizen_id', $citizenId)->select('*')
            ->first();
        $details = json_decode(json_encode($details), true);
        if (!empty($details)) {
            $temp_id = $details['id'];
            // Convert Std Class to Array
            $directors = DB::table('adv_active_agencydirectors')
                ->select(
                    'adv_active_agencydirectors.*',
                    DB::raw("CONCAT(adv_active_agencydirectors.relative_path,'/',adv_active_agencydirectors.doc_name) as document_path")
                )
                ->where('agency_id', $temp_id)
                ->get();
            $details['directors'] = remove_null($directors->toArray());
        }
        return $details;
    }

    /**
     * | Get payment details
     */
    public function getPaymentDetails($paymentId)
    {
        $details = AdvAgency::select(
            'adv_agencies.payment_amount',
            'adv_agencies.payment_id',
            'adv_agencies.payment_date',
            'adv_agencies.address',
            'adv_agencies.entity_name',
            'adv_agencies.application_no',
            'adv_agencies.license_no',
            'adv_agencies.workflow_id',
            'adv_agencies.payment_details',
            'adv_agencies.payment_mode',
            'adv_agencies.valid_from',
            'adv_agencies.valid_upto',
            'et.string_parameter as entityType',
            'adv_agencies.application_date as applyDate',
            'adv_agencies.ulb_id',
            'ulb_masters.toll_free_no',
            'ulb_masters.current_website as website',
            DB::raw("'Advertisement' as module")
        )
            ->leftjoin('ulb_masters', 'adv_agencies.ulb_id', '=', 'ulb_masters.id')
            ->leftjoin('ref_adv_paramstrings as et', 'adv_agencies.entity_type', '=', 'et.id')
            ->where('adv_agencies.payment_id', $paymentId)
            ->first();
        $details->payment_details = json_decode($details->payment_details);
        $details->towards = "Agency";
        $details->payment_date = Carbon::createFromFormat('Y-m-d H:i:s',  $details->payment_date)->format('d-m-Y');
        $details->valid_from = Carbon::createFromFormat('Y-m-d',  $details->valid_from)->format('d-m-Y');
        $details->valid_upto = Carbon::createFromFormat('Y-m-d',  $details->valid_upto)->format('d-m-Y');
        $details->applyDate = Carbon::createFromFormat('Y-m-d',  $details->applyDate)->format('d-m-Y');
        return $details;
    }

    /**
     * | Search application by name or mobile no
     */
    public function searchByNameorMobile($req)
    {
        $list = AdvAgency::select(
            'adv_agencies.*',
            'et.string_parameter as entityType',
            'adv_agencies.entity_type as entity_type_id',
            DB::raw("'Agency' as workflow_name")
        )
            ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', 'adv_agencies.entity_type');
        if ($req->filterBy == 'mobileNo') {
            $filterList = $list->where('adv_agencies.mobile_no', $req->parameter);
        }
        if ($req->filterBy == 'entityName') {
            $filterList = $list->where('adv_agencies.entity_name', $req->parameter);
        }
        return $filterList->get();
    }

    /**
     * | Payment via Cash
     */
    public function offlinePayment($req)
    {

        if ($req->status == '1') {
            // Agency Table Update
            $mAdvAgency = AdvAgency::find($req->applicationId);
            $PaymentMode = $req->paymentMode;
            if ($mAdvAgency->payment_status == 1) {
                throw new Exception('Payment is Already Done!');
            }
            // $pay_id = $mAdvAgency->payment_id = "Cash-$req->applicationId-" . time();
            $receiptIdParam                = Config::get('constants.PARAM_IDS.TRN');
            $idGeneration                  = new PrefixIdGenerator($receiptIdParam, $mAdvAgency->ulb_id);
            $pay_id = $idGeneration->generate();

            $mAdvAgency->payment_id = $pay_id;
            $mAdvAgency->payment_status = $req->status;
            $mAdvAgency->payment_date = Carbon::now();

            $payDetails = array('paymentMode' => $PaymentMode, 'id' => $req->applicationId, 'amount' => $mAdvAgency->payment_amount, 'demand_amount' => $mAdvAgency->demand_amount, 'workflowId' => $mAdvAgency->workflow_id, 'userId' => $mAdvAgency->citizen_id, 'ulbId' => $mAdvAgency->ulb_id, 'transDate' => Carbon::now(), 'transactionNo' => $pay_id);

            $mAdvAgency->payment_details = json_encode($payDetails);
            if ($mAdvAgency->renew_no == NULL) {
                $mAdvAgency->valid_from = Carbon::now();
                $mAdvAgency->valid_upto = Carbon::now()->addYears(5)->subDay(1);
            } else {
                $previousApplication = $this->findPreviousApplication($mAdvAgency->license_no);
                $mAdvAgency->valid_from = $previousApplication->valid_upto;
                $mAdvAgency->valid_upto = Carbon::createFromFormat('Y-m-d', $previousApplication->valid_upto)->addYears(5)->subDay(1);
            }
            $mAdvAgency->save();
            $renewal_id = $mAdvAgency->last_renewal_id;

            // Renewal Table Updation
            $mAdvAgencyRenewal = AdvAgencyRenewal::find($renewal_id);
            $mAdvAgencyRenewal->payment_status = 1;
            $mAdvAgencyRenewal->payment_amount =  $mAdvAgency->payment_amount;
            $mAdvAgencyRenewal->demand_amount =  $mAdvAgency->demand_amount;
            $mAdvAgencyRenewal->payment_id =  $pay_id;
            $mAdvAgencyRenewal->payment_date = Carbon::now();
            $mAdvAgencyRenewal->payment_mode = "Cash";
            $mAdvAgencyRenewal->valid_from = $mAdvAgency->valid_from;
            $mAdvAgencyRenewal->valid_upto = $mAdvAgency->valid_upto;
            $mAdvAgencyRenewal->payment_details = json_encode($payDetails);
            $ret['status'] = $mAdvAgencyRenewal->save();
            $ret['paymentId'] = $pay_id;
            return  $ret;
        }
    }


    // Find Previous Application Valid Date
    public function findPreviousApplication($license_no)
    {
        return $details = AdvAgencyRenewal::select('valid_upto')
            ->where('license_no', $license_no)
            ->orderByDesc('id')
            ->skip(1)->first();
    }


    /**
     * | Get Reciept Details 
     */
    public function getApprovalLetter($applicationId)
    {
        $recieptDetails = AdvAgency::select(
            'adv_agencies.id',
            'adv_agencies.workflow_id',
            'adv_agencies.approve_date',
            'adv_agencies.entity_name as applicant_name',
            'adv_agencies.application_no',
            'adv_agencies.license_no',
            'ulb_id',
            'adv_agencies.payment_date as license_start_date',
            DB::raw('CONCAT(application_date,id) AS reciept_no')
        )
            ->where('adv_agencies.id', $applicationId)
            ->first();
        // $recieptDetails->payment_details=json_decode($recieptDetails->payment_details);
        return $recieptDetails;
    }

    /**
     * | Approve List For Report
     */
    public function approveListForReport()
    {
        return AdvAgency::select('id', 'application_no', 'entity_name', 'application_date', 'application_type', 'ulb_id', DB::raw("'Approve' as application_status"));
    }

    public function getDetailsById($appId)
    {
        return AdvAgency::select(
            'adv_agencies.*',
            'adv_agencies.application_no',
            // 'adv_agencies.zone as zone_id',
            // 'adv_agencies.display_type as display_type_id',
            // 'adv_agencies.installation_location as installation_location_id',
            // 'il.string_parameter as installation_location',
            // 'dt.string_parameter as display_type',
            // 'typo.descriptions as typology',
            'adv_agencies.entity_name',
            'adv_agencies.address',
            // 'w.ward_name',
            // 'pw.ward_name as permanent_ward_name',
            // 'ew.ward_name as entity_ward_name',
            'ulb.ulb_name',
            'adv_active_agencydirectors.director_name',
            'adv_active_agencydirectors.director_mobile',
            'adv_active_agencydirectors.director_email',
            'adv_active_agencydirectors.agency_id as application_id'
        )
            // ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', DB::raw('adv_agencies.installation_location::int'))
            // ->leftJoin('adv_typology_mstrs as typo', 'typo.id', '=', 'adv_agencies.typology')
            // ->leftJoin('ref_adv_paramstrings as dt', 'dt.id', '=', DB::raw('adv_agencies.display_type::int'))
            // ->leftJoin('ulb_ward_masters as w', 'w.id', '=', DB::raw('adv_agencies.ward_id::int'))
            // ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', DB::raw('adv_agencies.permanent_ward_id::int'))
            // ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', DB::raw('adv_agencies.entity_ward_id::int'))
            ->join('adv_active_agencydirectors', 'adv_active_agencydirectors.agency_id', '=', 'adv_agencies.id')
            ->leftJoin('ulb_masters as ulb', 'ulb.id', '=', DB::raw('adv_agencies.ulb_id::int'))
            ->where('adv_agencies.id', $appId);
    }

    public function directorDetails($appId)
    {
        return AdvAgency::select(
            'adv_active_agencydirectors.*'
            
        )
            ->join('adv_active_agencydirectors', 'adv_active_agencydirectors.agency_id', '=', 'adv_agencies.id')
            ->where('adv_active_agencydirectors.agency_id', $appId);
    }


}
