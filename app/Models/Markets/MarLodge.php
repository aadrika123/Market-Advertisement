<?php

namespace App\Models\Markets;

use App\MicroServices\IdGenerator\PrefixIdGenerator;
use App\Models\Advertisements\WfActiveDocument;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class MarLodge extends Model
{
    use HasFactory;

    /**
     * Summary of allApproveList
     * @return void
     */
    public function allApproveList()
    {
        return MarLodge::select(
            'mar_lodges.id',
            'mar_lodges.application_no',
            'mar_lodges.application_date',
            'mar_lodges.entity_address',
            'mar_lodges.entity_name',
            'mar_lodges.applicant',
            'mar_lodges.applicant as owner_name',
            'mar_lodges.mobile as mobile_no',
            'mar_lodges.payment_amount',
            'mar_lodges.payment_status',
            'mar_lodges.approve_date',
            'mar_lodges.citizen_id',
            'mar_lodges.ulb_id',
            'mar_lodges.valid_upto',
            'mar_lodges.workflow_id',
            'mar_lodges.license_no',
            'mar_lodges.application_type',
            'mar_lodges.payment_id',
            DB::raw("'lodge' as type"),
            'um.ulb_name as ulb_name',
        )
            ->join('ulb_masters as um', 'um.id', '=', 'mar_lodges.ulb_id')
            ->orderByDesc('mar_lodges.id')
            ->get();
    }

    /**
     * | Get Application Approve List by Role Ids
     */
    public function listApproved($citizenId, $userType)
    {
        $allApproveList = $this->allApproveList();
        foreach ($allApproveList as $key => $list) {
            $activeLodge = MarActiveLodge::where('application_no', $list['application_no'])->count();
            $current_date = carbon::now()->format('Y-m-d');
            $notify_date = carbon::parse($list['valid_upto'])->subDay(30)->format('Y-m-d');
            if ($current_date >= $notify_date) {
                if ($activeLodge == 0) {
                    $allApproveList[$key]['renew_option'] = '1';     // Renew option Show
                } else {
                    $allApproveList[$key]['renew_option'] = '0';     // Already Renew
                }
            }
            if ($current_date < $notify_date) {
                $allApproveList[$key]['renew_option'] = '0';      // Renew option Not Show 0
            }
            if ($list['valid_upto'] < $current_date) {
                $allApproveList[$key]['renew_option'] = 'Expired';    // Renew Expired 
            }
        }
        if ($userType == 'Citizen') {
            return collect($allApproveList)->where('citizen_id', $citizenId)->values();
        } else {
            return collect($allApproveList)->values();
        }
    }

    /**
     * | Get Application Details FOr Payments
     */
    public function getApplicationDetailsForPayment($id)
    {
        return MarLodge::where('id', $id)
            ->select(
                'id',
                'applicant',
                'application_no',
                'application_date',
                'entity_name',
                'payment_amount',
                'approve_date',
                'ulb_id',
                'workflow_id',
            )
            ->first();
    }

    /**
     * | Application via Cash
     */
    public function paymentByCash($req)
    {

        if ($req->status == '1') {
            // Lodge Table Update
            $mMarLodge = MarLodge::find($req->applicationId);
            $mMarLodge->payment_status = $req->status;
            //$mMarLodge->payment_mode = "Cash";
            $PaymentMode = $req->paymentMode;
            // $pay_id = $mMarLodge->payment_id = "Cash-$req->applicationId-" . time();
            // $mMarLodge->payment_date = Carbon::now();
            $receiptIdParam                = Config::get('constants.PARAM_IDS.TRN');
            $idGeneration                  = new PrefixIdGenerator($receiptIdParam, $mMarLodge->ulb_id);
            $pay_id = $idGeneration->generate();

            $mMarLodge->payment_id = $pay_id;
            $mMarLodge->payment_mode = $PaymentMode;
            // $mAdvCheckDtls->remarks = $req->remarks;
            $mMarLodge->payment_date = Carbon::now();

            $payDetails = array('paymentMode' => $PaymentMode, 'id' => $req->applicationId, 'amount' => $mMarLodge->payment_amount, 'demand_amount' => $mMarLodge->demand_amount, 'workflowId' => $mMarLodge->workflow_id, 'userId' => $mMarLodge->user_id, 'ulbId' => $mMarLodge->ulb_id, 'transDate' => Carbon::now(), 'paymentId' => $pay_id);

            $mMarLodge->payment_details = json_encode($payDetails);

            if ($mMarLodge->renew_no == NULL) {
                $mMarLodge->valid_from = Carbon::now();
                $mMarLodge->valid_upto = Carbon::now()->addYears(1)->subDay(1);
            } else {
                $previousApplication = $this->findPreviousApplication($mMarLodge->application_no);
                $mMarLodge->valid_from = $previousApplication->valid_upto;
                $mMarLodge->valid_upto = Carbon::createFromFormat('Y-m-d', $previousApplication->valid_upto)->addYears(1)->subDay(1);
            }
            $mMarLodge->save();
            $renewal_id = $mMarLodge->last_renewal_id;

            // Renewal Table Updation
            $mMarLodgeRenewal = MarLodgeRenewal::find($renewal_id);
            $mMarLodgeRenewal->payment_status = 1;
            $mMarLodgeRenewal->payment_mode = $PaymentMode;
            $mMarLodgeRenewal->payment_id =  $pay_id;
            $mMarLodgeRenewal->payment_amount =  $mMarLodge->payment_amount;
            $mMarLodgeRenewal->demand_amount =  $mMarLodge->demand_amount;
            $mMarLodgeRenewal->payment_date = Carbon::now();
            $mMarLodgeRenewal->valid_from = $mMarLodge->valid_from;
            $mMarLodgeRenewal->valid_upto = $mMarLodge->valid_upto;
            $mMarLodgeRenewal->payment_details = json_encode($payDetails);
            $status = $mMarLodgeRenewal->save();
            $returnData['status'] = $status;
            $returnData['payment_id'] = $pay_id;
            return $returnData;
        }
    }

    // Find Previous Payment Date
    public function findPreviousApplication($application_no)
    {
        return $details = MarLodgeRenewal::select('valid_upto')
            ->where('application_no', $application_no)
            ->orderByDesc('id')
            ->skip(1)->first();
    }

    /**
     * | Get Application Details For Renew Applications
     */
    public function applicationDetailsForRenew($appId)
    {
        $details = MarLodge::select(
            'mar_lodges.*',
            'mar_lodges.lodge_type as lodge_type_id',
            'mar_lodges.organization_type as organization_type_id',
            'mar_lodges.land_deed_type as land_deed_type_id',
            'mar_lodges.mess_type as mess_type_id',
            'mar_lodges.water_supply_type as water_supply_type_id',
            'mar_lodges.electricity_type as electricity_type_id',
            'mar_lodges.security_type as security_type_id',
            'ly.string_parameter as license_year_name',
            'rw.ward_name as resident_ward_name',
            'lt.string_parameter as lodge_type_name',
            'ot.string_parameter as organization_type_name',
            'ldt.string_parameter as land_deed_type_name',
            'mt.string_parameter as mess_type_name',
            'wt.string_parameter as water_supply_type_name',
            'et.string_parameter as electricity_type_name',
            'st.string_parameter as security_type_name',
            'ew.ward_name as entity_ward_name',
            'pw.ward_name as permanent_ward_name',
            'ulb.ulb_name',
        )
            ->leftJoin('ref_adv_paramstrings as ly', 'ly.id', '=', DB::raw('mar_lodges.license_year::int'))
            ->leftJoin('ulb_ward_masters as rw', 'rw.id', '=', DB::raw('mar_lodges.residential_ward_id::int'))
            ->leftJoin('ref_adv_paramstrings as lt', 'lt.id', '=', DB::raw('mar_lodges.lodge_type::int'))
            ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', DB::raw('mar_lodges.organization_type::int'))
            ->leftJoin('ref_adv_paramstrings as ldt', 'ldt.id', '=', DB::raw('mar_lodges.land_deed_type::int'))
            ->leftJoin('ref_adv_paramstrings as mt', 'mt.id', '=', DB::raw('mar_lodges.mess_type::int'))
            ->leftJoin('ref_adv_paramstrings as wt', 'wt.id', '=', DB::raw('mar_lodges.water_supply_type::int'))
            ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', DB::raw('mar_lodges.electricity_type::int'))
            ->leftJoin('ref_adv_paramstrings as st', 'st.id', '=', DB::raw('mar_lodges.security_type::int'))
            ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'mar_lodges.entity_ward_id')
            ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'mar_lodges.permanent_ward_id')
            ->leftJoin('ulb_masters as ulb', 'ulb.id', '=', 'mar_lodges.ulb_id')
            ->where('mar_lodges.id', $appId)->first();
        if (!empty($details)) {
            $mWfActiveDocument = new WfActiveDocument();
            $documents = $mWfActiveDocument->uploadDocumentsViewById($appId, $details->workflow_id);
            $details['documents'] = $documents;
        }
        return $details;
    }

    /**
     * | Get Payment Details After Payment
     */
    public function getPaymentDetails($paymentId)
    {
        $details = MarLodge::select(
            'mar_lodges.payment_amount',
            'mar_lodges.payment_id',
            'mar_lodges.payment_date',
            'mar_lodges.payment_mode',
            'mar_lodges.permanent_address as address',
            'mar_lodges.applicant',
            'mar_lodges.entity_name',
            'mar_lodges.payment_details',
            'mar_lodges.holding_no',
            'mar_lodges.trade_license_no',
            'mar_lodges.no_of_rooms',
            'mar_lodges.no_of_beds',
            'mar_lodges.application_no',
            'mar_lodges.application_date as applyDate',
            'mar_lodges.license_no',
            'mar_lodges.rule',
            'mar_lodges.workflow_id',
            'mar_lodges.valid_from',
            'mar_lodges.valid_upto',
            'mar_lodges.ulb_id',
            'mar_lodges.no_of_beds',
            'ulb_masters.ulb_name as ulbName',
            'ulb_masters.logo as ulbLogo',
            'ulb_masters.toll_free_no',
            'ulb_masters.current_website as website',
            'ly.string_parameter as licenseYear',
            'lt.string_parameter as lodgeType',
            'wn.ward_name as wardNo',
            DB::raw("'Market' as module"),
        )
            ->leftjoin('ulb_masters', 'mar_lodges.ulb_id', '=', 'ulb_masters.id')
            ->leftjoin('ulb_ward_masters as wn', 'mar_lodges.entity_ward_id', '=', 'wn.id')
            ->leftjoin('ref_adv_paramstrings as ly', DB::raw('mar_lodges.license_year::int'), '=', 'ly.id')
            ->leftjoin('ref_adv_paramstrings as lt', DB::raw('mar_lodges.lodge_type::int'), '=', 'lt.id')
            ->where('mar_lodges.payment_id', $paymentId)
            ->first();
        $details->payment_details = json_decode($details->payment_details);
        $details->towards = "Lodge";
        $details->payment_date = Carbon::createFromFormat('Y-m-d', $details->payment_date)->format('d-m-Y');
        $details->applyDate = Carbon::createFromFormat('Y-m-d', $details->applyDate)->format('d-m-Y');
        $details->valid_from = Carbon::createFromFormat('Y-m-d', $details->valid_from)->format('d-m-Y');
        $details->valid_upto = Carbon::createFromFormat('Y-m-d', $details->valid_upto)->format('d-m-Y');
        return $details;
    }

    /**
     * | Approve List For Report
     */
    public function approveListForReport()
    {
        return MarLodge::select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'lodge_type', 'license_year', 'ulb_id', DB::raw("'Approve' as application_status"));
    }

    /**
     * | Get Reciept Details 
     * | Created On : 23/6/2023
     */
    public function getApprovalLetter($applicationId)
    {
        $recieptDetails = MarLodge::select(
            'mar_lodges.id',
            'mar_lodges.workflow_id',
            'mar_lodges.approve_date',
            'mar_lodges.applicant as applicant_name',
            'mar_lodges.application_no',
            'mar_lodges.license_no',
            'ulb_id',
            'mar_lodges.payment_date as license_start_date',
            DB::raw('CONCAT(application_date,id) AS reciept_no')
        )
            ->where('mar_lodges.id', $applicationId)
            ->first();
        return $recieptDetails;
    }

    public function getApplicationFinancialYearWise($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;
        $fyear = $request->fyear;
        list($currentfyStartDate, $currentfyEndDate) = explode('-', $fyear);
        $currentfyStartDate = $currentfyStartDate . "-04-01";
        $currentfyEndDate = $currentfyEndDate . "-03-31";
        $approved = MarLodge::select('id', 'entity_name', 'application_no', 'applicant',  DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'lodge_type', 'license_year', 'ulb_id', DB::raw("'Approved' as application_status"))
            ->where('ulb_id', $ulbId);

        $active = MarActiveLodge::select('id', 'entity_name', 'application_no', 'applicant',  DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'lodge_type', 'license_year', 'ulb_id', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId);

        $rejected = MarRejectedLodge::select('id', 'entity_name', 'application_no', 'applicant',  DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'lodge_type', 'license_year', 'ulb_id', DB::raw("'Reject' as application_status"))
            ->where('ulb_id', $ulbId);
        if ($request->wardNo) {
            $approved->where('mar_lodges.entity_ward_id', $request->wardNo);
            $active->where('mar_active_lodges.entity_ward_id', $request->wardNo);
            $rejected->where('mar_rejected_lodges.entity_ward_id', $request->wardNo);
        }
        if ($request->applicationType) {
            $approved->where('mar_lodges.application_type', $request->applicationType);
            $active->where('mar_active_lodges.application_type', $request->applicationType);
            $rejected->where('mar_rejected_lodges.application_type', $request->applicationType);
        }
        if ($request->fyear) {
            $approved->whereBetween('mar_lodges.application_date', [$currentfyStartDate, $currentfyEndDate]);
            $active->whereBetween('mar_active_lodges.application_date', [$currentfyStartDate, $currentfyEndDate]);
            $rejected->whereBetween('mar_rejected_lodges.application_date', [$currentfyStartDate, $currentfyEndDate]);
        }
        $data = $approved->union($active)->union($rejected);
        if ($perPage) {
            $data = $data->paginate($perPage);
        } else {
            $data = $data->get();
        }
        return [
            'current_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->currentPage() : 1,
            'last_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->lastPage() : 1,
            'data' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->items() : $data,
            'total' => $data->total()
        ];
    }

    public function payCollection($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;
        $lodgewWorkflow = Config::get('workflow-constants.LODGE_WORKFLOWS');
        $dateFrom = $request->dateFrom ?: Carbon::now()->format('Y-m-d');
        $dateUpto = $request->dateUpto ?: Carbon::now()->format('Y-m-d');
        $approved = DB::table('mar_lodge_renewals')
            ->select('mar_lodge_renewals.id', 'mar_lodge_renewals.entity_name', 'mar_lodge_renewals.application_no', 'mar_lodge_renewals.applicant',  DB::raw("TO_CHAR(mar_lodge_renewals.application_date, 'DD-MM-YYYY') as application_date"), 'mar_lodge_renewals.application_type', 'mar_lodge_renewals.entity_ward_id', DB::raw("'Approve' as application_status"), 'mar_lodge_renewals.payment_amount',  DB::raw("TO_CHAR(payment_date, 'DD-MM-YYYY') as payment_date"), 'adv_mar_transactions.payment_mode', 'adv_mar_transactions.transaction_no')
            ->join('adv_mar_transactions', 'adv_mar_transactions.transaction_id', '=', 'mar_lodge_renewals.payment_id')
            ->where('mar_lodge_renewals.payment_status', 1)
            ->where('mar_lodge_renewals.status', 1)
            ->where('mar_lodge_renewals.ulb_id', $ulbId)
            ->where('adv_mar_transactions.workflow_id', $lodgewWorkflow)
            ->whereBetween('payment_date', [$dateFrom, $dateUpto]);;

        if ($request->wardNo) {
            $approved->where('mar_lodge_renewals.entity_ward_id', $request->wardNo);
        }
        if ($request->applicationType) {
            $approved->where('mar_lodge_renewals.application_type', $request->applicationType);
        }
        if ($request->payMode == 'All') {
            $data = $approved;
        }
        if ($request->payMode == 'ONLINE') {
            $data = $approved->where('adv_mar_transactions.payment_mode', $request->payMode);
        }
        if ($request->payMode == 'CASH') {
            $data = $approved->where('adv_mar_transactions.payment_mode', $request->payMode);
        }
        if ($request->payMode == 'CHEQUE') {
            $data = $approved->where('adv_mar_transactions.payment_mode', $request->payMode);
        }
        if ($request->payMode == 'DD') {
            $data = $approved->where('adv_mar_transactions.payment_mode', $request->payMode);
        }
         // Clone the query for counts and sums
       $approveListForCounts = clone $approved;
       $approveListForSums = clone $approved;

       // Count of transactions
       $cashCount = (clone $approveListForCounts)->where('adv_mar_transactions.payment_mode', 'CASH')->count();
        $ddCount = (clone $approveListForCounts)->where('adv_mar_transactions.payment_mode', 'DD')->count();
        $chequeCount = (clone $approveListForCounts)->where('adv_mar_transactions.payment_mode', 'CHEQUE')->count();
       $onlineCount = (clone $approveListForCounts)->where('adv_mar_transactions.payment_mode', 'ONLINE')->count();

       // Sum of transactions
       $cashPayment = (clone $approveListForSums)->where('adv_mar_transactions.payment_mode', 'CASH')->sum('payment_amount');
        $ddPayment = (clone $approveListForSums)->where('adv_mar_transactions.payment_mode', 'DD')->sum('payment_amount');
        $chequePayment = (clone $approveListForSums)->where('adv_mar_transactions.payment_mode', 'CHEQUE')->sum('payment_amount');
       $onlinePayment = (clone $approveListForSums)->where('adv_mar_transactions.payment_mode', 'ONLINE')->sum('payment_amount');

       # transaction by jsk 
       $cashCountJsk = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', true)->where('adv_mar_transactions.payment_mode', 'CASH')->count();
        $chequeCountJsk = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', true)->where('adv_mar_transactions.payment_mode', 'CHEQUE')->count();
        $ddCountJsk = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', true)->where('adv_mar_transactions.payment_mode', 'DD')->count();
       $onlineCountJsk = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', true)->where('adv_mar_transactions.payment_mode', 'ONLINE')->count();
       #transaction by citizen
       $cashCountCitizen = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', false)->where('adv_mar_transactions.payment_mode', 'CASH')->count();
       $chequeCountCitizen = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', false)->where('adv_mar_transactions.payment_mode', 'CHEQUE')->count();
        $ddCountCitizen = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', false)->where('adv_mar_transactions.payment_mode', 'DD')->count();
       $onlineCountcitizen = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', false)->where('adv_mar_transactions.payment_mode', 'ONLINE')->count();

       $totalCountJsk = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', true)->count();
       $totalCountCitizen = (clone $approveListForCounts)->where('adv_mar_transactions.is_jsk', false)->count();




       $totalAmount  = (clone $approveListForSums)->sum('payment_amount');

       $data = $approved;
       if ($perPage) {
           $data = $data->paginate($perPage);
       } else {
           $data = $data->get();
       }
       return [
           'current_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->currentPage() : 1,
           'last_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->lastPage() : 1,
           'data' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->items() : $data,
           'total' => $data->total(),
         
               //"total" => $paginator->total(),
               'CashCount' => $cashCount,
               'ddCount' => $ddCount,
               'chequeCount' => $chequeCount,
               'onlineCount' => $onlineCount,
               'cashPayment' => $cashPayment,
               'ddPayment' => $ddPayment,
               'chequePayment' => $chequePayment,
               'onlinePayment' => $onlinePayment,
               'cashCountJsk' => $cashCountJsk,
              'chequeCountJsk' => $chequeCountJsk,
               'ddCountJsk' => $ddCountJsk,
               'onlineCountJsk' => $onlineCountJsk,
               'cashCountCitizen' => $cashCountCitizen,
               'chequeCountCitizen' => $chequeCountCitizen,
               'ddCountCitizen' => $ddCountCitizen,
               'onlineCountcitizen' => $onlineCountcitizen,
               'totalAmount' => $totalAmount,
               'totalCountJsk' => $totalCountJsk,
               'totalCountCitizen' => $totalCountCitizen
              // 'userType' => $userType
       ];
    }

    public function getApplicationWithStatus($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;
        $dateFrom = $request->dateFrom ?: Carbon::now()->format('Y-m-d');
        $dateUpto = $request->dateUpto ?: Carbon::now()->format('Y-m-d');
        $approved = MarLodge::select('id', 'entity_name', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'lodge_type', 'license_year', 'ulb_id', DB::raw("'Approve' as application_status"))
            ->where('ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);

        $rejected = MarRejectedLodge::select('id', 'entity_name', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'lodge_type', 'license_year', 'ulb_id', DB::raw("'Reject' as application_status"))
            ->where('ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        if ($request->wardNo) {
            $approved->where('mar_lodges.entity_ward_id', $request->wardNo);
            $rejected->where('mar_rejected_lodges.entity_ward_id', $request->wardNo);
        }
        if ($request->applicationType) {
            $approved->where('mar_lodges.application_type', $request->applicationType);
            $rejected->where('mar_rejected_lodges.application_type', $request->applicationType);
        }
        $data = null;
        if ($request->applicationStatus == 'All') {
            $data = $approved->union($rejected);
        } elseif ($request->applicationStatus == 'Reject') {
            $data = $rejected;
        } elseif ($request->applicationStatus == 'Approve') {
            $data = $approved;
        } else $data = $approved->union($rejected);
        if ($data) {
            $data = $data->paginate($perPage);
        } else {
            $data = collect([]);
        }

        return [
            'current_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->currentPage() : 1,
            'last_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->lastPage() : 1,
            'data' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->items() : $data,
            'total' => $data->total()
        ];
    }

    public function getApplicationWithRule($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;
        $dateFrom = $request->dateFrom ?: Carbon::now()->format('Y-m-d');
        $dateUpto = $request->dateUpto ?: Carbon::now()->format('Y-m-d');
        $approved = MarLodge::select('id', 'entity_name', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'lodge_type', 'license_year', 'ulb_id', DB::raw("'Approved' as application_status"))
            ->where('ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        $active = MarActiveLodge::select('id', 'entity_name', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'lodge_type', 'license_year', 'ulb_id', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        $rejected = MarRejectedLodge::select('id', 'entity_name', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'lodge_type', 'license_year', 'ulb_id', DB::raw("'Reject' as application_status"))
            ->where('ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        if ($request->wardNo) {
            $approved->where('mar_lodges.entity_ward_id', $request->wardNo);
            $active->where('mar_active_lodges.entity_ward_id', $request->wardNo);
            $rejected->where('mar_rejected_lodges.entity_ward_id', $request->wardNo);
        }
        if ($request->applicationType) {
            $approved->where('mar_lodges.application_type', $request->applicationType);
            $active->where('mar_active_lodges.application_type', $request->applicationType);
            $rejected->where('mar_rejected_lodges.application_type', $request->applicationType);
        }
        if ($request->ruleType) {
            $approved->where('mar_lodges.rule', $request->ruleType);
            $active->where('mar_active_lodges.rule', $request->ruleType);
            $rejected->where('mar_rejected_lodges.rule', $request->ruleType);
        }
        $data = null;
        if ($request->applicationStatus == 'All') {
            $data = $approved->union($active)->union($rejected);;
        } elseif ($request->applicationStatus == 'Reject') {
            $data = $rejected;
        } elseif ($request->applicationStatus == 'Approved') {
            $data = $approved;
        } else $data = $approved->union($active)->union($rejected);;
        if ($data) {
            $data = $data->paginate($perPage);
        } else {
            $data = collect([]);
        }

        return [
            'current_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->currentPage() : 1,
            'last_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->lastPage() : 1,
            'data' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->items() : $data,
            'total' => $data->total()
        ];
    }

    public function getApplicationWithLodgeType($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;
        $dateFrom = $request->dateFrom ?: Carbon::now()->format('Y-m-d');
        $dateUpto = $request->dateUpto ?: Carbon::now()->format('Y-m-d');
        $approved = MarLodge::select('mar_lodges.id', 'mar_lodges.entity_name', 'mar_lodges.application_no', 'mar_lodges.applicant', DB::raw("TO_CHAR(mar_lodges.application_date, 'DD-MM-YYYY') as application_date"), 'mar_lodges.application_type', 'mar_lodges.entity_ward_id', 'mar_lodges.rule', 'mar_lodges.organization_type', 'mar_lodges.lodge_type as lodge_id', 'mar_lodges.license_year', 'mar_lodges.ulb_id', DB::raw("'Approved' as application_status"), 'ref_adv_paramstrings.string_parameter as lodgeType')
            ->leftJoin('ref_adv_paramstrings', 'ref_adv_paramstrings.id', '=', 'mar_lodges.lodge_type')
            ->where('mar_lodges.ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        $active = MarActiveLodge::select('mar_active_lodges.id', 'mar_active_lodges.entity_name', 'mar_active_lodges.application_no', 'mar_active_lodges.applicant', DB::raw("TO_CHAR(mar_active_lodges.application_date, 'DD-MM-YYYY') as application_date"), 'mar_active_lodges.application_type', 'mar_active_lodges.entity_ward_id', 'mar_active_lodges.rule', 'mar_active_lodges.organization_type', 'mar_active_lodges.lodge_type as lodge_id', 'mar_active_lodges.license_year', 'mar_active_lodges.ulb_id', DB::raw("'Active' as application_status"), 'ref_adv_paramstrings.string_parameter as lodgeType')
            ->leftJoin('ref_adv_paramstrings', 'ref_adv_paramstrings.id', '=', 'mar_active_lodges.lodge_type')
            ->where('mar_active_lodges.ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        $rejected = MarRejectedLodge::select('mar_rejected_lodges.id', 'mar_rejected_lodges.entity_name', 'mar_rejected_lodges.application_no', 'mar_rejected_lodges.applicant', DB::raw("TO_CHAR(mar_rejected_lodges.application_date, 'DD-MM-YYYY') as application_date"), 'mar_rejected_lodges.application_type', 'mar_rejected_lodges.entity_ward_id', 'mar_rejected_lodges.rule', 'mar_rejected_lodges.organization_type', 'mar_rejected_lodges.lodge_type as lodge_id', 'mar_rejected_lodges.license_year', 'mar_rejected_lodges.ulb_id', DB::raw("'Reject' as application_status"), 'ref_adv_paramstrings.string_parameter as lodgeType')
            ->leftJoin('ref_adv_paramstrings', 'ref_adv_paramstrings.id', '=', 'mar_rejected_lodges.lodge_type')
            ->where('mar_rejected_lodges.ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        if ($request->wardNo) {
            $approved->where('mar_lodges.entity_ward_id', $request->wardNo);
            $active->where('mar_active_lodges.entity_ward_id', $request->wardNo);
            $rejected->where('mar_rejected_lodges.entity_ward_id', $request->wardNo);
        }
        if ($request->applicationType) {
            $approved->where('mar_lodges.application_type', $request->applicationType);
            $active->where('mar_active_lodges.application_type', $request->applicationType);
            $rejected->where('mar_rejected_lodges.application_type', $request->applicationType);
        }
        if ($request->ruleType) {
            $approved->where('mar_lodges.rule', $request->ruleType);
            $active->where('mar_active_lodges.rule', $request->ruleType);
            $rejected->where('mar_rejected_lodges.rule', $request->ruleType);
        }

        if ($request->lodgeType) {
            $approved->where('ref_adv_paramstrings.id', $request->lodgeType);
            $active->where('ref_adv_paramstrings.id', $request->lodgeType);
            $rejected->where('ref_adv_paramstrings.id', $request->lodgeType);
        }
        $data = null;
        if ($request->applicationStatus == 'All') {
            $data = $approved->union($active)->union($rejected);;
        } elseif ($request->applicationStatus == 'Reject') {
            $data = $rejected;
        } elseif ($request->applicationStatus == 'Approved') {
            $data = $approved;
        } else $data = $approved->union($active)->union($rejected);;
        if ($data) {
            $data = $data->paginate($perPage);
        } else {
            $data = collect([]);
        }

        return [
            'current_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->currentPage() : 1,
            'last_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->lastPage() : 1,
            'data' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->items() : $data,
            'total' => $data->total()
        ];
    }

    /**
     * | Get Application Approve List by Role Ids
     */
    public function listjskApprovedApplication()
    {
        return MarLodge::select(
            'mar_lodges.id',
            'application_no',
            DB::raw("TO_CHAR(mar_lodges.application_date, 'DD-MM-YYYY') as application_date"),
            'mar_lodges.application_type',
            'mar_lodges.applicant',
            'mar_lodges.applicant as owner_name',
            'mar_lodges.entity_name',
            'mar_lodges.license_no',
            'mar_lodges.payment_status',
            'mar_lodges.payment_amount',
            'mar_lodges.approve_date',
            'mar_lodges.citizen_id',
            'mar_lodges.valid_upto',
            'mar_lodges.valid_from',
            'mar_lodges.user_id',
            'mobile as mobile_no',
            DB::raw("CASE WHEN user_id IS NOT NULL THEN 'jsk' ELSE 'citizen' END AS applied_by")
        )
            ->orderByDesc('id');
        //->get();
    }

    public function getDetailsById($applicationId)
    {
        return MarLodge::select(
            'mar_lodges.id',
            'mar_lodges.application_no',
            'mar_lodges.application_date',
            'mar_lodges.entity_address',
            'mar_lodges.entity_name',
            'mar_lodges.applicant',
            'mar_lodges.applicant as owner_name',
            'mar_lodges.mobile as mobile_no',
            'mar_lodges.payment_amount',
            'mar_lodges.payment_status',
            'mar_lodges.approve_date',
            'mar_lodges.citizen_id',
            'mar_lodges.ulb_id',
            'mar_lodges.valid_upto',
            'mar_lodges.workflow_id',
            'mar_lodges.license_no',
            'mar_lodges.application_type',
            'mar_lodges.payment_id',
            'um.ulb_name as ulb_name',
            'entity_ward_id as ward_no',
            'holding_no',
            'father',
            'mar_lodges.email',
            'aadhar_card as aadhar_card',
            'permanent_ward_id as permanent_ward_no',
            'permanent_address',
            'doc_upload_status'
        )
            ->leftjoin('ulb_masters as um', 'um.id', '=', 'mar_lodges.ulb_id')
            ->where('mar_lodges.id', $applicationId)
            ->orderByDesc('mar_lodges.id');
        //->get();
    }

    public function lodgeDaAppliaction($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;
        $active = MarActiveLodge::select('id', 'entity_name', 'application_no', 'applicant',  DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'lodge_type', 'license_year', 'ulb_id', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId)
            ->where('mar_active_lodges.current_role_id', '=', 6);
            ;
        if ($request->wardNo) {
            $active->where('mar_active_lodges.entity_ward_id', $request->wardNo);
        }
        
        $data = $active;
        if ($perPage) {
            $data = $data->paginate($perPage);
        } else {
            $data = $data->get();
        }
        return [
            'current_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->currentPage() : 1,
            'last_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->lastPage() : 1,
            'data' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->items() : $data,
            'total' => $data->total()
        ];
    }
    public function lodgeAeAppliaction($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;
        $active = MarActiveLodge::select('id', 'entity_name', 'application_no', 'applicant',  DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'lodge_type', 'license_year', 'ulb_id', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId)
            ->where('mar_active_lodges.current_role_id', '=', 14);
            ;
        if ($request->wardNo) {
            $active->where('mar_active_lodges.entity_ward_id', $request->wardNo);
        }
        
        $data = $active;
        if ($perPage) {
            $data = $data->paginate($perPage);
        } else {
            $data = $data->get();
        }
        return [
            'current_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->currentPage() : 1,
            'last_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->lastPage() : 1,
            'data' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->items() : $data,
            'total' => $data->total()
        ];
    }
    public function lodgeSiAppliaction($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;
        $active = MarActiveLodge::select('id', 'entity_name', 'application_no', 'applicant',  DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'lodge_type', 'license_year', 'ulb_id', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId)
            ->where('mar_active_lodges.current_role_id', '=', 9);
            ;
        if ($request->wardNo) {
            $active->where('mar_active_lodges.entity_ward_id', $request->wardNo);
        }
        
        $data = $active;
        if ($perPage) {
            $data = $data->paginate($perPage);
        } else {
            $data = $data->get();
        }
        return [
            'current_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->currentPage() : 1,
            'last_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->lastPage() : 1,
            'data' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->items() : $data,
            'total' => $data->total()
        ];
    }
    public function lodgeCmAppliaction($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;
        $active = MarActiveLodge::select('id', 'entity_name', 'application_no', 'applicant',  DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'lodge_type', 'license_year', 'ulb_id', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId)
            ->where('mar_active_lodges.current_role_id', '=', 32);
        if ($request->wardNo) {
            $active->where('mar_active_lodges.entity_ward_id', $request->wardNo);
        }
        
        $data = $active;
        if ($perPage) {
            $data = $data->paginate($perPage);
        } else {
            $data = $data->get();
        }
        return [
            'current_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->currentPage() : 1,
            'last_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->lastPage() : 1,
            'data' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->items() : $data,
            'total' => $data->total()
        ];
    }
    public function lodgeEoAppliaction($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;
        $active = MarActiveLodge::select('id', 'entity_name', 'application_no', 'applicant',  DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'lodge_type', 'license_year', 'ulb_id', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId)
            ->where('mar_active_lodges.current_role_id', '=', 10);
            ;
        if ($request->wardNo) {
            $active->where('mar_active_lodges.entity_ward_id', $request->wardNo);
        }
        
        $data = $active;
        if ($perPage) {
            $data = $data->paginate($perPage);
        } else {
            $data = $data->get();
        }
        return [
            'current_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->currentPage() : 1,
            'last_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->lastPage() : 1,
            'data' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->items() : $data,
            'total' => $data->total()
        ];
    }
}
