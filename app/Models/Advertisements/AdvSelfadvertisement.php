<?php

namespace App\Models\Advertisements;

use App\MicroServices\IdGenerator\PrefixIdGenerator;
use App\Models\Param\AdvMarTransaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class AdvSelfadvertisement extends Model
{
    use HasFactory;

    public function allApproveList()
    {
        return AdvSelfadvertisement::select(
            'adv_selfadvertisements.id',
            'adv_selfadvertisements.application_no',
            DB::raw("TO_CHAR(adv_selfadvertisements.application_date, 'DD-MM-YYYY') as application_date"),
            'adv_selfadvertisements.applicant',
            'adv_selfadvertisements.applicant as owner_name',
            'adv_selfadvertisements.entity_name',
            'adv_selfadvertisements.entity_ward_id',
            'adv_selfadvertisements.mobile_no',
            'adv_selfadvertisements.entity_address',
            'adv_selfadvertisements.payment_status',
            'adv_selfadvertisements.payment_amount',
            'adv_selfadvertisements.approve_date',
            'adv_selfadvertisements.ulb_id',
            'adv_selfadvertisements.workflow_id',
            'adv_selfadvertisements.citizen_id',
            'adv_selfadvertisements.license_no',
            'adv_selfadvertisements.valid_upto',
            'adv_selfadvertisements.valid_from',
            'adv_selfadvertisements.user_id',
            'adv_selfadvertisements.application_type',
            'adv_selfadvertisements.payment_id',
            DB::raw("'selfAdvt' as type"),
            DB::raw("'Approved' as applicationStatus"),
            'um.ulb_name as ulb_name',
        )
            ->join('ulb_masters as um', 'um.id', '=', 'adv_selfadvertisements.ulb_id')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * | Get Application Approve List by Role Ids
     */
    public function listApproved($citizenId, $userType)
    {
        $allApproveList = $this->allApproveList();
        foreach ($allApproveList as $key => $list) {
            $activeSelf = AdvActiveSelfadvertisement::where('application_no', $list['application_no'])->count();
            $current_date = carbon::now()->format('Y-m-d');
            $notify_date = carbon::parse($list['valid_upto'])->subDay(30)->format('Y-m-d');
            if ($current_date >= $notify_date) {
                if ($activeSelf == 0) {
                    $allApproveList[$key]['renew_option'] = '1';     // Renew option Show
                } else {
                    $allApproveList[$key]['renew_option'] = '0';     // Already Renew
                }
            }
            if ($current_date < $notify_date) {
                $allApproveList[$key]['renew_option'] = '0';      // Renew option Not Show
            }
            if ($list['valid_upto'] < $current_date) {
                $allApproveList[$key]['renew_option'] = 'Expired';    // Renew Expired
            }
        }
        if ($userType == 'Citizen') {
            return collect($allApproveList->where('citizen_id', $citizenId))->values();
        } else {
            return collect($allApproveList)->values();
        }
    }

    /**
     * | Get Application Approve List by Role Ids
     */
    public function listJskApprovedApplication()
    {
        return AdvSelfadvertisement::select(
            'id',
            'application_no',
            DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"),
            'applicant',
            'entity_name',
            'entity_address',
            'payment_status',
            'payment_amount',
            'approve_date',
            'license_no',
            'ulb_id',
            'workflow_id',
            'mobile_no',
            'user_id',
            'citizen_id',
            DB::raw("CASE WHEN user_id IS NOT NULL THEN 'jsk' ELSE 'citizen' END AS applied_by")
        )
            ->orderByDesc('id');
        //->get();
    }


    /**
     * | Get Application Details For Payments
     */
    public function applicationDetailsForPayment($id)
    {
        return AdvSelfadvertisement::where('id', $id)
            ->select(
                'id',
                'application_no',
                DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"),
                'applicant',
                'entity_name',
                'entity_address',
                'payment_status',
                'payment_amount',
                'license_no',
                'approve_date',
                'ulb_id',
                'workflow_id',
            )
            ->first();
    }

    /**
     * | Get Payment Details
     */
    public function getPaymentDetails($paymentId)
    {
        $details = AdvSelfadvertisement::select(
            'adv_selfadvertisements.payment_amount',
            'adv_selfadvertisements.payment_id as transactionNo',
            'adv_selfadvertisements.payment_date',
            'adv_selfadvertisements.license_no',
            'adv_selfadvertisements.application_no',
            'adv_selfadvertisements.entity_address as address',
            'adv_selfadvertisements.applicant',
            'adv_selfadvertisements.payment_details',
            'adv_selfadvertisements.valid_from',
            'adv_selfadvertisements.valid_upto',
            'adv_selfadvertisements.payment_mode',
            'adv_selfadvertisements.holding_no',
            'adv_selfadvertisements.workflow_id',
            'adv_selfadvertisements.application_date as applyDate',
            'adv_selfadvertisements.trade_license_no',
            'adv_selfadvertisements.ulb_id',
            'ulb_masters.toll_free_no',
            'ulb_masters.current_website as website',
            'ly.string_parameter as licenseYear',
            'wn.ward_name as wardNo',
            DB::raw("'Advertisement' as module"),
        )
            ->leftjoin('ulb_masters', 'adv_selfadvertisements.ulb_id', '=', 'ulb_masters.id')
            ->leftjoin('ref_adv_paramstrings as ly', 'adv_selfadvertisements.license_year', '=', 'ly.id')
            ->leftjoin('ulb_ward_masters as wn', 'adv_selfadvertisements.ward_id', '=', 'wn.id')
            ->where('adv_selfadvertisements.payment_id', $paymentId)
            ->first();
        $details->payment_details = json_decode($details->payment_details);
        $details->towards = "Self Advertisement";
        $details->payment_date = Carbon::createFromFormat('Y-m-d', $details->payment_date)->format('d-m-Y');
        $details->valid_from = Carbon::createFromFormat('Y-m-d', $details->valid_from)->format('d-m-Y');
        $details->valid_upto = Carbon::createFromFormat('Y-m-d', $details->valid_upto)->format('d-m-Y');
        $details->applyDate = Carbon::createFromFormat('Y-m-d',  $details->applyDate)->format('d-m-Y');
        return $details;
    }
    /**
     * | offline Payment
     */
    public function offlinePayment($req)
    {
        if ($req->status == '1') {
            // Self Advertisement Table Update
            $mAdvSelfadvertisement = AdvSelfadvertisement::find($req->applicationId);
            $receiptIdParam                = Config::get('constants.PARAM_IDS.TRN');
            $mAdvSelfadvertisement->payment_status = $req->status;
            $PaymentMode = $req->paymentMode;
            $idGeneration                  = new PrefixIdGenerator($receiptIdParam, $mAdvSelfadvertisement->ulb_id);
            $pay_id = $idGeneration->generate();
            $mAdvSelfadvertisement->payment_id = $pay_id;
            // $mAdvCheckDtls->remarks = $req->remarks;
            $mAdvSelfadvertisement->payment_date = Carbon::now();
            $mAdvSelfadvertisement->payment_mode = $PaymentMode;
            $payDetails = array('paymentMode' => $PaymentMode, 'id' => $req->applicationId, 'amount' => $mAdvSelfadvertisement->payment_amount, 'demand_amount' => $mAdvSelfadvertisement->demand_amount, 'workflowId' => $mAdvSelfadvertisement->workflow_id, 'userId' => $mAdvSelfadvertisement->citizen_id, 'ulbId' => $mAdvSelfadvertisement->ulb_id, 'transDate' => Carbon::now(), 'transactionNo' => $pay_id);
            $mAdvSelfadvertisement->payment_details = json_encode($payDetails);
            if ($mAdvSelfadvertisement->renew_no == NULL) {                             // Fresh Application Time 
                $mAdvSelfadvertisement->valid_from = Carbon::now();
                $mAdvSelfadvertisement->valid_upto = Carbon::now()->addYears(1)->subDay(1);
            } else {                                                              // Renewal Application Time 
                $previousApplication = $this->findPreviousApplication($mAdvSelfadvertisement->license_no);
                $mAdvSelfadvertisement->valid_from = $previousApplication->valid_upto;
                $mAdvSelfadvertisement->valid_upto = Carbon::createFromFormat('Y-m-d', $previousApplication->valid_upto)->addYears(1)->subDay(1);
            }
            $mAdvSelfadvertisement->save();
            $renewal_id = $mAdvSelfadvertisement->last_renewal_id;

            // Renewal Table Updation
            $mAdvSelfAdvertRenewal = AdvSelfadvetRenewal::find($renewal_id);
            $mAdvSelfAdvertRenewal->payment_status = 1;
            $mAdvSelfAdvertRenewal->payment_id =  $pay_id;
            $mAdvSelfAdvertRenewal->payment_date = Carbon::now();
            $mAdvSelfAdvertRenewal->payment_mode = $PaymentMode;
            $mAdvSelfAdvertRenewal->payment_amount =  $mAdvSelfadvertisement->payment_amount;
            $mAdvSelfAdvertRenewal->demand_amount =  $mAdvSelfadvertisement->demand_amount;
            $mAdvSelfAdvertRenewal->valid_from = $mAdvSelfadvertisement->valid_from;
            $mAdvSelfAdvertRenewal->valid_upto = $mAdvSelfadvertisement->valid_upto;
            $mAdvSelfAdvertRenewal->payment_details = json_encode($payDetails);
            $status = $mAdvSelfAdvertRenewal->save();
            $returnData['status'] = $status;
            $returnData['payment_id'] = $pay_id;
            return $returnData;
        }
    }

    /**
     * | Get Previous application valid date for renewal
     */
    public function findPreviousApplication($license_no)
    {
        return  AdvSelfadvetRenewal::select('valid_upto')
            ->where('license_no', $license_no)
            ->orderByDesc('id')
            ->skip(1)->first();
    }


    /**
     * | Get Application Details for Renew
     */
    public function applicationDetailsForRenew($appId)
    {
        $details = AdvSelfadvertisement::select(
            'adv_selfadvertisements.*',
            'adv_selfadvertisements.license_year as license_year_id',
            'adv_selfadvertisements.installation_location as installation_location_id',
            'ly.string_parameter as license_year_name',
            'ew.ward_name as entity_ward_name',
            'il.string_parameter as installation_location_name',
            'w.ward_name',
            'pw.ward_name as permanent_ward_name',
            'cat.type as advt_category_name',
            'ulb.ulb_name',
        )
            ->leftJoin('ref_adv_paramstrings as ly', 'ly.id', '=', DB::raw('adv_selfadvertisements.license_year::int'))
            ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', DB::raw('adv_selfadvertisements.entity_ward_id::int'))
            ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', DB::raw('adv_selfadvertisements.installation_location::int'))
            ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_selfadvertisements.ward_id')
            ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_selfadvertisements.permanent_ward_id')
            ->leftJoin('ulb_masters as ulb', 'ulb.id', '=', 'adv_selfadvertisements.ulb_id')
            ->leftJoin('adv_selfadv_categories as cat', 'cat.id', '=', 'adv_selfadvertisements.advt_category')
            ->where('adv_selfadvertisements.id', $appId)->first();
        if (!empty($details)) {
            $mWfActiveDocument = new WfActiveDocument();
            $documents = $mWfActiveDocument->uploadDocumentsViewById($appId, $details->workflow_id);
            $details['documents'] = $documents;
        }
        return $details;
    }

    /**
     * | Search Application by Name or Mobile 
     */
    public function searchByNameorMobile($req)
    {
        $list = AdvSelfadvertisement::select('adv_agencies.*', 'et.string_parameter as entityType', 'adv_agencies.entity_type as entity_type_id')
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
     * | Get Reciept Details 
     */
    public function getApprovalLetter($applicationId)
    {
        $recieptDetails = AdvSelfadvertisement::select(
            'adv_selfadvertisements.id',
            'adv_selfadvertisements.workflow_id',
            'adv_selfadvertisements.approve_date',
            // DB::raw('CONVERT(date, adv_selfadvertisements.approve_date, 105) as approve_date'),
            'adv_selfadvertisements.applicant as applicant_name',
            'ulb_id',
            'adv_selfadvertisements.application_no',
            'adv_selfadvertisements.license_no',
            'adv_selfadvertisements.payment_date as license_start_date',
            DB::raw('case when adv_selfadvertisements.payment_date is NULL then adv_selfadvertisements.approve_date END as license_start_date'),
            DB::raw('CONCAT(application_date,id) AS reciept_no')
        )
            ->where('adv_selfadvertisements.id', $applicationId)
            ->first();
        // $recieptDetails->payment_details=json_decode($recieptDetails->payment_details);
        return $recieptDetails;
    }

    /**
     * | Get Approve list for Report
     */
    public function allApproveListForReport()
    {
        return AdvSelfadvertisement::select(
            'id',
            'application_no',
            'application_date',
            'applicant',
            'applicant as owner_name',
            'entity_name',
            'entity_ward_id',
            'mobile_no',
            'entity_address',
            'payment_status',
            'payment_amount',
            'approve_date',
            'ulb_id',
            'workflow_id',
            'citizen_id',
            'license_no',
            'valid_upto',
            'valid_from',
            'user_id',
            'application_type',
            DB::raw("'selfAdvt' as type"),
            DB::raw("'Approved' as applicationStatus"),
        )
            ->orderByDesc('id')->get();
    }

    /**
     * | Get Approve Application List For Report
     */
    public function approveListForReport()
    {
        return AdvSelfadvertisement::select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', 'ulb_id', 'license_year', 'display_type', DB::raw("'Approve' as application_status"));
    }

    public function getDetailsById($applicationId)
    {
        return  AdvSelfadvertisement::select(
            'adv_selfadvertisements.*',
            'u.ulb_name',
            'p.string_parameter as m_license_year',
            'w.ward_name as ward_no',
            'pw.ward_name as permanent_ward_no',
            'ew.ward_name as entity_ward_no',
            'dp.string_parameter as m_display_type',
            'il.string_parameter as m_installation_location',
            'r.role_name as m_current_role',
            'cat.type as advt_category_type'
        )
            ->where('adv_selfadvertisements.id', $applicationId)
            ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_selfadvertisements.ulb_id')
            ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'adv_selfadvertisements.license_year')
            ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_selfadvertisements.ward_id')
            ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_selfadvertisements.permanent_ward_id')
            ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'adv_selfadvertisements.entity_ward_id')
            ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', 'adv_selfadvertisements.display_type')
            ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', 'adv_selfadvertisements.installation_location')
            ->leftJoin('wf_roles as r', 'r.id', '=', 'adv_selfadvertisements.current_role_id')
            ->leftJoin('adv_selfadv_categories as cat', 'cat.id', '=', 'adv_selfadvertisements.advt_category');
        //->first();

        //return $details;
    }

    /**
     * |
     */
    public function getAllById($id)
    {
        try {
            $test = AdvSelfadvertisement::select("id")->find($id);
            $table = "adv_selfadvertisements";
            $application = AdvSelfadvertisement::select(
                "adv_selfadvertisements.*",
                "ref_adv_paramstrings.string_parameter as display_type",
                "adv_masters.string_parameter as installation_location",
                "adv_string.string_parameter as license_year",
                "ulb_ward_masters.ward_name  as ward_id",
                "adv_selfadv_categories.type as advt_category",
                "ulb_masters.ulb_name",
                // DB::raw("ulb_ward_masters.ward_name AS ward_no, new_ward.ward_name as new_ward_no,ulb_masters.ulb_name, '$table' AS tbl")
            );
            if (!$test) {
                $test = AdvRejectedSelfadvertisement::select("id")->find($id);
                $table = "adv_rejected_selfadvertisements";
                $application = AdvRejectedSelfadvertisement::select(
                    "adv_rejected_selfadvertisements.*",
                    "ref_adv_paramstrings.string_parameter as display_type",
                    "adv_masters.string_parameter as installation_location",
                    "adv_string.string_parameter as license_year",
                    "ulb_ward_masters.ward_name as  ward_id",
                    "adv_selfadv_categories.type as advt_category",
                    "ulb_masters.ulb_name",
                    // DB::raw("ulb_ward_masters.ward_name AS ward_no, new_ward.ward_name as new_ward_no,ulb_masters.ulb_name,'$table' AS tbl")
                );
            }
            if (!$test) {
                $test = AdvActiveSelfadvertisement::select("id")->find($id);
                $table = "adv_active_selfadvertisements";
                $application = AdvActiveSelfadvertisement::select(
                    "adv_active_selfadvertisements.*",
                    "ref_adv_paramstrings.string_parameter as display_type",
                    "adv_masters.string_parameter as installation_location",
                    "adv_string.string_parameter as license_year",
                    "ulb_ward_masters.ward_name  as ward_id",
                    "permanantWard.ward_name as permanent_ward_id",
                    "entityWard.ward_name as entity_ward_id",
                    "adv_selfadv_categories.type as advt_category",
                    "ulb_masters.ulb_name",
                    // DB::raw("ulb_ward_masters.ward_name AS ward_no, 
                    // new_ward.ward_name as new_ward_no,ulb_masters.ulb_name,'$table' AS tbl")
                );
            }
            if (!$test) {
                $table = "trade_renewals";
                $application = AdvSelfadvetRenewal::select(
                    "adv_selfadvet_renewals.*",
                    "ref_adv_paramstrings.string_parameter as display_type",
                    "adv_masters.string_parameter as installation_location",
                    "adv_string.string_parameter as license_year",
                    "ulb_ward_masters.ward_name as ward_id",
                    "entityWard.ward_name as entity_ward_id",
                    "adv_selfadv_categories.type as advt_category",
                    "ulb_masters.ulb_name",
                    // DB::raw("ulb_ward_masters.ward_name AS ward_no, 
                    // new_ward.ward_name as new_ward_no,ulb_masters.ulb_name,'$table' AS tbl")
                );
            }

            $application = $application
                ->leftjoin("ulb_masters", function ($join) use ($table) {
                    $join->on("ulb_masters.id", "=", $table . ".ulb_id");
                })
                ->leftjoin("ref_adv_paramstrings", function ($join) use ($table) {
                    $join->on("ref_adv_paramstrings.id", "=", $table . ".display_type");
                })
                ->leftjoin("ref_adv_paramstrings as adv_masters", function ($join) use ($table) {
                    $join->on("adv_masters.id", "=", $table . ".installation_location");
                })
                ->join('ref_adv_paramstrings as adv_string', 'adv_string.id', $table . ".license_year")
                ->join('ulb_ward_masters', 'ulb_ward_masters.id', $table . ".ward_id")
                ->join('ulb_ward_masters as permanantWard', 'permanantWard.id', $table . ".permanent_ward_id")
                ->join('ulb_ward_masters as entityWard', 'entityWard.id', $table . ".entity_ward_id")
                ->join('adv_selfadv_categories', 'adv_selfadv_categories.id', $table . ".advt_category")
                // ->join("ulb_masters", "ulb_masters.id", $table . ".ulb_id")
                ->where($table . '.id', $id)
                ->first();
            return $application;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
