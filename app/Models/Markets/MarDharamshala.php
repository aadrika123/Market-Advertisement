<?php

namespace App\Models\Markets;

use App\MicroServices\IdGenerator\PrefixIdGenerator;
use App\Models\Advertisements\WfActiveDocument;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MarDharamshala extends Model
{
    use HasFactory;

    /**
     * Summary of allApproveList
     * @return void
     */
    public function allApproveList()
    {
        return MarDharamshala::select(
            'mar_dharamshalas.id',
            'mar_dharamshalas.application_no',
            'mar_dharamshalas.application_date',
            'mar_dharamshalas.entity_address',
            'mar_dharamshalas.payment_amount',
            'mar_dharamshalas.entity_name',
            'mar_dharamshalas.applicant',
            'mar_dharamshalas.applicant as owner_name',
            'mar_dharamshalas.mobile as mobile_no',
            'mar_dharamshalas.approve_date',
            'mar_dharamshalas.payment_status',
            'mar_dharamshalas.citizen_id',
            'mar_dharamshalas.ulb_id',
            'mar_dharamshalas.valid_upto',
            'mar_dharamshalas.workflow_id',
            'mar_dharamshalas.license_no',
            'mar_dharamshalas.application_type',
            'mar_dharamshalas.payment_id',
            DB::raw("'dharamshala' as type"),
            'um.ulb_name as ulb_name',
        )
            ->join('ulb_masters as um', 'um.id', '=', 'mar_dharamshalas.ulb_id')
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
            $activeDharamashala = MarActiveDharamshala::where('application_no', $list['application_no'])->count();
            $current_date = carbon::now()->format('Y-m-d');
            $notify_date = carbon::parse($list['valid_upto'])->subDay(30)->format('Y-m-d');
            if ($current_date >= $notify_date) {
                if ($activeDharamashala == 0) {
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
        return MarDharamshala::where('id', $id)
            ->select(
                'id',
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
     * | Get Payment vai Cash
     */
    public function paymentByCash($req)
    {

        if ($req->status == '1') {
            // Dharamshala Table Update
            $mMarDharamshala = MarDharamshala::find($req->applicationId);
            $mMarDharamshala->payment_status = $req->status;
            // $mMarDharamshala->payment_mode = "Cash";
            // $pay_id = $mMarDharamshala->payment_id = "Cash-$req->applicationId-" . time();
            // $mAdvCheckDtls->remarks = $req->remarks;
            $paymentMode = $req->paymentMode;
            $mMarDharamshala->payment_date = Carbon::now();
            $mMarDharamshala->payment_date = Carbon::now();
            $receiptIdParam                = Config::get('constants.PARAM_IDS.TRN');
            $idGeneration                  = new PrefixIdGenerator($receiptIdParam, $mMarDharamshala->ulb_id);
            $pay_id = $idGeneration->generate();
            $mMarDharamshala->payment_id = $pay_id;
            $mMarDharamshala->payment_mode = $paymentMode;
            $payDetails = array('paymentMode' => $paymentMode, 'id' => $req->applicationId, 'amount' => $mMarDharamshala->payment_amount, 'demand_amount' => $mMarDharamshala->demand_amount, 'workflowId' => $mMarDharamshala->workflow_id, 'userId' => $mMarDharamshala->user_id, 'ulbId' => $mMarDharamshala->ulb_id, 'transDate' => Carbon::now(), 'paymentId' => $pay_id);

            $mMarDharamshala->payment_details = json_encode($payDetails);
            if ($mMarDharamshala->renew_no == NULL) {
                $mMarDharamshala->valid_from = Carbon::now();
                $mMarDharamshala->valid_upto = Carbon::now()->addYears(1)->subDay(1);
            } else {
                $previousApplication = $this->findPreviousApplication($mMarDharamshala->application_no);
                $mMarDharamshala->valid_from = $previousApplication->valid_upto;
                $mMarDharamshala->valid_upto = Carbon::createFromFormat('Y-m-d', $previousApplication->valid_upto)->addYears(1)->subDay(1);
            }
            $mMarDharamshala->save();
            $renewal_id = $mMarDharamshala->last_renewal_id;

            // Renewal Table Updation
            $mMarDharamshalaRenewal = MarDharamshalaRenewal::find($renewal_id);
            $mMarDharamshalaRenewal->payment_status = 1;
            $mMarDharamshalaRenewal->payment_mode = $paymentMode;
            $mMarDharamshalaRenewal->payment_id =  $pay_id;
            $mMarDharamshalaRenewal->payment_amount =  $mMarDharamshala->payment_amount;
            $mMarDharamshalaRenewal->demand_amount =  $mMarDharamshala->demand_amount;
            $mMarDharamshalaRenewal->payment_date = Carbon::now();
            $mMarDharamshalaRenewal->valid_from = $mMarDharamshala->valid_from;
            $mMarDharamshalaRenewal->valid_upto = $mMarDharamshala->valid_upto;
            $mMarDharamshalaRenewal->payment_details = json_encode($payDetails);
            $status = $mMarDharamshalaRenewal->save();
            $returnData['status'] = $status;
            $returnData['payment_id'] = $pay_id;
            return $returnData;
        }
    }


    // Find Previous Payment Date
    public function findPreviousApplication($application_no)
    {
        return $details = MarDharamshalaRenewal::select('valid_upto')
            ->where('application_no', $application_no)
            ->orderByDesc('id')
            ->skip(1)->first();
    }


    /**
     * | Get Application Details For Renew Applications
     */
    public function applicationDetailsForRenew($appId)
    {
        $details = MarDharamshala::select(
            'mar_dharamshalas.*',
            'mar_dharamshalas.organization_type as organization_type_id',
            'mar_dharamshalas.land_deed_type as land_deed_type_id',
            'mar_dharamshalas.water_supply_type as water_supply_type_id',
            'mar_dharamshalas.electricity_type as electricity_type_id',
            'mar_dharamshalas.security_type as security_type_id',
            'ly.string_parameter as license_year_name',
            'rw.ward_name as resident_ward_name',
            'ot.string_parameter as organization_type_name',
            'ldt.string_parameter as land_deed_type_name',
            'wt.string_parameter as water_supply_type_name',
            'et.string_parameter as electricity_type_name',
            'ew.ward_name as entity_ward_name',
            'st.string_parameter as security_type_name',
            'pw.ward_name as permanent_ward_name',
            'ulb.ulb_name',
        )
            ->leftJoin('ref_adv_paramstrings as ly', 'ly.id', '=', DB::raw('mar_dharamshalas.license_year::int'))
            ->leftJoin('ulb_ward_masters as rw', 'rw.id', '=', DB::raw('mar_dharamshalas.residential_ward_id::int'))
            ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', DB::raw('mar_dharamshalas.organization_type::int'))
            ->leftJoin('ref_adv_paramstrings as ldt', 'ldt.id', '=', DB::raw('mar_dharamshalas.land_deed_type::int'))
            ->leftJoin('ref_adv_paramstrings as wt', 'wt.id', '=', DB::raw('mar_dharamshalas.water_supply_type::int'))
            ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', DB::raw('mar_dharamshalas.electricity_type::int'))
            ->leftJoin('ref_adv_paramstrings as st', 'st.id', '=', DB::raw('mar_dharamshalas.security_type::int'))
            ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'mar_dharamshalas.entity_ward_id')
            ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'mar_dharamshalas.permanent_ward_id')
            ->leftJoin('ulb_masters as ulb', 'ulb.id', '=', 'mar_dharamshalas.ulb_id')
            ->where('mar_dharamshalas.id', $appId)->first();
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
        $details = MarDharamshala::select(
            'mar_dharamshalas.payment_amount',
            'mar_dharamshalas.payment_id',
            'mar_dharamshalas.payment_date',
            'mar_dharamshalas.permanent_address as address',
            'mar_dharamshalas.applicant',
            'mar_dharamshalas.entity_name',
            'mar_dharamshalas.application_no',
            'mar_dharamshalas.license_no',
            'mar_dharamshalas.valid_from',
            'mar_dharamshalas.valid_upto',
            'mar_dharamshalas.application_date as applyDate',
            'mar_dharamshalas.holding_no',
            'mar_dharamshalas.trade_license_no',
            'mar_dharamshalas.rule',
            'mar_dharamshalas.workflow_id',
            'mar_dharamshalas.payment_details',
            'mar_dharamshalas.payment_mode',
            'mar_dharamshalas.floor_area',
            'mar_dharamshalas.ulb_id',
            'mar_dharamshalas.application_date as applyDate',
            'ulb_masters.ulb_name as ulbName',
            'ulb_masters.logo as ulbLogo',
            'ulb_masters.toll_free_no',
            'ulb_masters.current_website as website',
            'ly.string_parameter as licenseYear',
            'wn.ward_name as wardNo',
            DB::raw("'Market' as module"),
        )
            ->leftjoin('ulb_masters', 'mar_dharamshalas.ulb_id', '=', 'ulb_masters.id')
            ->leftjoin('ulb_ward_masters as wn', 'mar_dharamshalas.entity_ward_id', '=', 'wn.id')
            ->leftjoin('ref_adv_paramstrings as ly', DB::raw('mar_dharamshalas.license_year::int'), '=', 'ly.id')
            ->where('mar_dharamshalas.payment_id', $paymentId)
            ->first();
        $details->payment_details = json_decode($details->payment_details);
        $details->towards = "Dharamshala";
        $details->payment_date = Carbon::createFromFormat('Y-m-d', $details->payment_date)->format('d-m-Y');
        $details->applyDate = Carbon::createFromFormat('Y-m-d', $details->applyDate)->format('d-m-Y');
        $details->valid_from = Carbon::createFromFormat('Y-m-d', $details->valid_from)->format('d-m-Y');
        $details->valid_upto = Carbon::createFromFormat('Y-m-d', $details->valid_upto)->format('d-m-Y');
        return $details;
    }

    /**
     * | Get Approve List For Report
     */
    public function approveListForReport()
    {
        return MarDharamshala::select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'ulb_id', 'license_year', DB::raw("'Approve' as application_status"));
    }

    /**
     * | Get Reciept Details 
     * | Created On : 23/6/2023
     */
    public function getApprovalLetter($applicationId)
    {
        $recieptDetails = MarDharamshala::select(
            'mar_dharamshalas.id',
            'mar_dharamshalas.workflow_id',
            'mar_dharamshalas.approve_date',
            'mar_dharamshalas.applicant as applicant_name',
            'mar_dharamshalas.application_no',
            'mar_dharamshalas.license_no',
            'ulb_id',
            'mar_dharamshalas.payment_date as license_start_date',
            DB::raw('CONCAT(application_date,id) AS reciept_no')
        )
            ->where('mar_dharamshalas.id', $applicationId)
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
        $approved = MarDharamshala::select(
            'id',
            'entity_name',
            'application_no',
            'applicant',
            DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"),
            'application_type',
            'entity_ward_id',
            'rule',
            'organization_type',
            'ulb_id',
            'license_year',
            DB::raw("'Approved' as application_status")
        )
            ->where('ulb_id', $ulbId);

        $active = MarActiveDharamshala::select('id', 'entity_name', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'ulb_id', 'license_year', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId);

        $rejected = MarRejectedDharamshala::select('id', 'entity_name', 'application_no', 'applicant',  DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'ulb_id', 'license_year', DB::raw("'Reject' as application_status"))
            ->where('ulb_id', $ulbId);
        if ($request->wardNo) {
            $approved->where('mar_dharamshalas.entity_ward_id', $request->wardNo);
            $active->where('mar_active_dharamshalas.entity_ward_id', $request->wardNo);
            $rejected->where('mar_rejected_dharamshalas.entity_ward_id', $request->wardNo);
        }
        if ($request->applicationType) {
            $approved->where('mar_dharamshalas.application_type', $request->applicationType);
            $active->where('mar_active_dharamshalas.application_type', $request->applicationType);
            $rejected->where('mar_rejected_dharamshalas.application_type', $request->applicationType);
        }
        if ($request->fyear) {
            $approved->whereBetween('mar_dharamshalas.application_date', [$currentfyStartDate, $currentfyEndDate]);
            $active->whereBetween('mar_active_dharamshalas.application_date', [$currentfyStartDate, $currentfyEndDate]);
            $rejected->whereBetween('mar_rejected_dharamshalas.application_date', [$currentfyStartDate, $currentfyEndDate]);
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
        $dharamshalalWorkflow = Config::get('workflow-constants.DHARAMSHALA_WORKFLOWS');
        $dateFrom = $request->dateFrom ?: Carbon::now()->format('Y-m-d');
        $dateUpto = $request->dateUpto ?: Carbon::now()->format('Y-m-d');
        $approved = DB::table('mar_dharamshala_renewals')
            ->select('mar_dharamshala_renewals.id', 'mar_dharamshala_renewals.application_no', 'mar_dharamshala_renewals.applicant',  DB::raw("TO_CHAR(mar_dharamshala_renewals.application_date, 'DD-MM-YYYY') as application_date"), 'mar_dharamshala_renewals.application_type', 'mar_dharamshala_renewals.entity_ward_id', DB::raw("'Approve' as application_status"), 'mar_dharamshala_renewals.payment_amount',  DB::raw("TO_CHAR(payment_date, 'DD-MM-YYYY') as payment_date"), 'adv_mar_transactions.payment_mode', 'mar_dharamshala_renewals.entity_name', 'adv_mar_transactions.transaction_no')
            ->join('adv_mar_transactions', 'adv_mar_transactions.transaction_id', '=', 'mar_dharamshala_renewals.payment_id')
            ->where('payment_status', 1)
            ->where('mar_dharamshala_renewals.status', 1)
            ->where('mar_dharamshala_renewals.ulb_id', $ulbId)
            ->where('adv_mar_transactions.workflow_id', $dharamshalalWorkflow)
            ->whereBetween('payment_date', [$dateFrom, $dateUpto]);;

        if ($request->wardNo) {
            $approved->where('mar_dharamshala_renewals.entity_ward_id', $request->wardNo);
        }
        if ($request->applicationType) {
            $approved->where('mar_dharamshala_renewals.application_type', $request->applicationType);
        }
        if ($request->payMode == 'All') {
            $data = $approved;
        }
        if ($request->payMode == 'ONLINE') {
            $data = $approved->where('payment_mode', $request->payMode);
        }
        if ($request->payMode == 'CASH') {
            $data = $approved->where('payment_mode', $request->payMode);
        }
        if ($request->payMode == 'CHEQUE') {
            $data = $approved->where('payment_mode', $request->payMode);
        }

        if ($request->payMode == 'DD') {
            $data = $approved->where('payment_mode', $request->payMode);
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
        $approved = MarDharamshala::select(
            'mar_dharamshalas.id',
            'mar_dharamshalas.entity_name',
            'mar_dharamshalas.application_no',
            'mar_dharamshalas.applicant',
            DB::raw("TO_CHAR(mar_dharamshalas.application_date, 'DD-MM-YYYY') as application_date"),
            'mar_dharamshalas.application_type',
            'ulb_ward_masters.ward_name as entity_ward_no',
            'mar_dharamshalas.rule',
            'mar_dharamshalas.organization_type',
            'mar_dharamshalas.ulb_id',
            'mar_dharamshalas.license_year',
            DB::raw("'Approve' as application_status")
        )
            ->join('ulb_ward_masters', 'ulb_ward_masters.id', 'mar_dharamshalas.entity_ward_id')
            ->where('mar_dharamshalas.ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);

        $rejected = MarRejectedDharamshala::select(
            'mar_rejected_dharamshalas.id',
            'mar_rejected_dharamshalas.entity_name',
            'mar_rejected_dharamshalas.application_no',
            'mar_rejected_dharamshalas.applicant',
            DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"),
            'mar_rejected_dharamshalas.application_type',
            'mar_rejected_dharamshalas.entity_ward_id',
            'mar_rejected_dharamshalas.rule',
            'mar_rejected_dharamshalas.organization_type',
            'mar_rejected_dharamshalas.ulb_id',
            'mar_rejected_dharamshalas.license_year',
            DB::raw("'Reject' as application_status")
        )
            ->join('ulb_ward_masters', 'ulb_ward_masters.id', 'mar_rejected_dharamshalas.entity_ward_id')
            ->where('mar_rejected_dharamshalas.ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        if ($request->wardNo) {
            $approved->where('mar_dharamshalas.entity_ward_id', $request->wardNo);
            $rejected->where('mar_rejected_dharamshalas.entity_ward_id', $request->wardNo);
        }
        if ($request->applicationType) {
            $approved->where('mar_dharamshalas.application_type', $request->applicationType);
            $rejected->where('mar_rejected_dharamshalas.application_type', $request->applicationType);
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
        $approved = MarDharamshala::select('id', 'entity_name', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'ulb_id', 'license_year', DB::raw("'Approved' as application_status"))
            ->where('ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        $active = MarActiveDharamshala::select('id', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'ulb_id', 'license_year', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        $rejected = MarRejectedDharamshala::select('id', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'ulb_id', 'license_year', DB::raw("'Reject' as application_status"))
            ->where('ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        if ($request->wardNo) {
            $approved->where('mar_dharamshalas.entity_ward_id', $request->wardNo);
            $active->where('mar_active_dharamshalas.entity_ward_id', $request->wardNo);
            $rejected->where('mar_rejected_dharamshalas.entity_ward_id', $request->wardNo);
        }
        if ($request->applicationType) {
            $approved->where('mar_dharamshalas.application_type', $request->applicationType);
            $active->where('mar_active_dharamshalas.application_type', $request->applicationType);
            $rejected->where('mar_rejected_dharamshalas.application_type', $request->applicationType);
        }
        if ($request->ruleType) {
            $approved->where('mar_dharamshalas.rule', $request->ruleType);
            $active->where('mar_active_dharamshalas.rule', $request->ruleType);
            $rejected->where('mar_rejected_dharamshalas.rule', $request->ruleType);
        }
        $data = null;
        if ($request->applicationStatus == 'All') {
            $data = $approved->union($active)->union($rejected);
        } elseif ($request->applicationStatus == 'Reject') {
            $data = $rejected;
        } elseif ($request->applicationStatus == 'Approved') {
            $data = $approved;
        } else $data = $approved->union($active)->union($rejected);
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

    public function getOrganizationTypeApplication($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;
        $dateFrom = $request->dateFrom ?: Carbon::now()->format('Y-m-d');
        $dateUpto = $request->dateUpto ?: Carbon::now()->format('Y-m-d');
        $approved = MarDharamshala::select('mar_dharamshalas.id', 'mar_dharamshalas.entity_name', 'mar_dharamshalas.application_no', 'mar_dharamshalas.applicant', DB::raw("TO_CHAR(mar_dharamshalas.application_date, 'DD-MM-YYYY') as application_date"), 'mar_dharamshalas.application_type', 'mar_dharamshalas.entity_ward_id', 'mar_dharamshalas.rule', 'mar_dharamshalas.organization_type as organization_id', 'mar_dharamshalas.ulb_id', 'mar_dharamshalas.license_year', DB::raw("'Approved' as application_status"), 'ref_adv_paramstrings.string_parameter as organizationType')
            ->leftJoin('ref_adv_paramstrings', 'ref_adv_paramstrings.id', '=', 'mar_dharamshalas.organization_type')
            ->where('mar_dharamshalas.ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        $active = MarActiveDharamshala::select('mar_active_dharamshalas.id', 'mar_active_dharamshalas.entity_name', 'mar_active_dharamshalas.application_no', 'mar_active_dharamshalas.applicant', DB::raw("TO_CHAR(mar_active_dharamshalas.application_date, 'DD-MM-YYYY') as application_date"), 'mar_active_dharamshalas.application_type', 'mar_active_dharamshalas.entity_ward_id', 'mar_active_dharamshalas.rule', 'mar_active_dharamshalas.organization_type  as organization_id', 'mar_active_dharamshalas.ulb_id', 'mar_active_dharamshalas.license_year', DB::raw("'Active' as application_status"), 'ref_adv_paramstrings.string_parameter as organizationType')
            ->leftJoin('ref_adv_paramstrings', 'ref_adv_paramstrings.id', '=', 'mar_active_dharamshalas.organization_type')
            ->where('mar_active_dharamshalas.ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        $rejected = MarRejectedDharamshala::select('mar_rejected_dharamshalas.id', 'mar_rejected_dharamshalas.entity_name', 'mar_rejected_dharamshalas.application_no', 'mar_rejected_dharamshalas.applicant', DB::raw("TO_CHAR(mar_rejected_dharamshalas.application_date, 'DD-MM-YYYY') as application_date"), 'mar_rejected_dharamshalas.application_type', 'mar_rejected_dharamshalas.entity_ward_id', 'mar_rejected_dharamshalas.rule', 'mar_rejected_dharamshalas.organization_type  as organization_id', 'mar_rejected_dharamshalas.ulb_id', 'mar_rejected_dharamshalas.license_year', DB::raw("'Reject' as application_status"), 'ref_adv_paramstrings.string_parameter as organizationType')
            ->leftJoin('ref_adv_paramstrings', 'ref_adv_paramstrings.id', '=', 'mar_rejected_dharamshalas.organization_type')
            ->where('mar_rejected_dharamshalas.ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        if ($request->wardNo) {
            $approved->where('mar_dharamshalas.entity_ward_id', $request->wardNo);
            $active->where('mar_active_dharamshalas.entity_ward_id', $request->wardNo);
            $rejected->where('mar_rejected_dharamshalas.entity_ward_id', $request->wardNo);
        }
        if ($request->applicationType) {
            $approved->where('mar_dharamshalas.application_type', $request->applicationType);
            $active->where('mar_active_dharamshalas.application_type', $request->applicationType);
            $rejected->where('mar_rejected_dharamshalas.application_type', $request->applicationType);
        }
        if ($request->ruleType) {
            $approved->where('mar_dharamshalas.rule', $request->ruleType);
            $active->where('mar_active_dharamshalas.rule', $request->ruleType);
            $rejected->where('mar_rejected_dharamshalas.rule', $request->ruleType);
        }
        if ($request->organizationType) {
            $approved->where('ref_adv_paramstrings.id', $request->organizationType);
            $active->where('ref_adv_paramstrings.id', $request->organizationType);
            $rejected->where('ref_adv_paramstrings.id', $request->organizationType);
        }
        $data = null;
        if ($request->applicationStatus == 'All') {
            $data = $approved->union($active)->union($rejected);
        } elseif ($request->applicationStatus == 'Reject') {
            $data = $rejected;
        } elseif ($request->applicationStatus == 'Approved') {
            $data = $approved;
        } else $data = $approved->union($active)->union($rejected);
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
    public function listjskApprovedApplication($ulbId)
    {
        return MarDharamshala::select(
            'mar_dharamshalas.id',
            'application_no',
            DB::raw("TO_CHAR(mar_dharamshalas.application_date, 'DD-MM-YYYY') as application_date"),
            'mar_dharamshalas.application_type',
            'mar_dharamshalas.applicant',
            'mar_dharamshalas.applicant as owner_name',
            'mar_dharamshalas.entity_name',
            'mar_dharamshalas.license_no',
            'mar_dharamshalas.payment_status',
            'mar_dharamshalas.payment_amount',
            'mar_dharamshalas.approve_date',
            'mar_dharamshalas.citizen_id',
            'mar_dharamshalas.valid_upto',
            'mar_dharamshalas.valid_from',
            'mar_dharamshalas.user_id',
            'mobile as mobile_no',
            DB::raw("CASE WHEN user_id IS NOT NULL THEN 'jsk' ELSE 'citizen' END AS applied_by")
        )
            ->where('mar_dharamshalas.ulb_id', $ulbId)
            ->orderByDesc('id');
        //->get();
    }

    public function getDetailsById($applicationId)
    {
        return MarDharamshala::select(
            'mar_dharamshalas.*',
            'mar_dharamshalas.organization_type as organization_type_id',
            'mar_dharamshalas.land_deed_type as land_deed_type_id',
            'mar_dharamshalas.water_supply_type as water_supply_type_id',
            'mar_dharamshalas.electricity_type as electricity_type_id',
            'mar_dharamshalas.security_type as security_type_id',
            'mar_dharamshalas.no_of_rooms as noOfRooms',
            'mar_dharamshalas.no_of_beds as noOfBeds',
            'ly.string_parameter as license_year_name',
            'ot.string_parameter as organization_type_name',
            'ldt.string_parameter as land_deed_type_name',
            'wt.string_parameter as water_supply_type_name',
            'et.string_parameter as electricity_type_name',
            'st.string_parameter as security_type_name',
            'pw.ward_name as permanent_ward_name',
            'ew.ward_name as entity_ward_name',
            'rw.ward_name as residential_ward_name',
            'ulb.ulb_name',
            DB::raw("'Dharamshala' as headerTitle")
        )
            ->leftJoin('ref_adv_paramstrings as ly', 'ly.id', '=', DB::raw('mar_dharamshalas.license_year::int'))
            ->leftJoin('ulb_ward_masters as rw', 'rw.id', '=', DB::raw('mar_dharamshalas.residential_ward_id::int'))
            ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', DB::raw('mar_dharamshalas.organization_type::int'))
            ->leftJoin('ref_adv_paramstrings as ldt', 'ldt.id', '=', DB::raw('mar_dharamshalas.land_deed_type::int'))
            ->leftJoin('ref_adv_paramstrings as wt', 'wt.id', '=', DB::raw('mar_dharamshalas.water_supply_type::int'))
            ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', DB::raw('mar_dharamshalas.electricity_type::int'))
            ->leftJoin('ref_adv_paramstrings as st', 'st.id', '=', DB::raw('mar_dharamshalas.security_type::int'))
            ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'mar_dharamshalas.entity_ward_id')
            ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'mar_dharamshalas.permanent_ward_id')
            ->leftJoin('ulb_masters as ulb', 'ulb.id', '=', 'mar_dharamshalas.ulb_id')
            ->where('mar_dharamshalas.id', $applicationId);
        //->get();
    }

    public function dharamshalaDaApplication($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;

        $active = MarActiveDharamshala::select('id', 'entity_name', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'ulb_id', 'license_year', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId)
            ->where('mar_active_dharamshalas.current_role_id', '=', 6);

        if ($request->wardNo) {

            $active->where('mar_active_dharamshalas.entity_ward_id', $request->wardNo);
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
    public function dharamshalaEoApplication($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;

        $active = MarActiveDharamshala::select('id', 'entity_name', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'ulb_id', 'license_year', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId)
            ->where('mar_active_dharamshalas.current_role_id', '=', 10);

        if ($request->wardNo) {

            $active->where('mar_active_dharamshalas.entity_ward_id', $request->wardNo);
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
    public function dharamshalaCmApplication($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;

        $active = MarActiveDharamshala::select('id', 'entity_name', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'ulb_id', 'license_year', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId)
            ->where('mar_active_dharamshalas.current_role_id', '=', 32);

        if ($request->wardNo) {

            $active->where('mar_active_dharamshalas.entity_ward_id', $request->wardNo);
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
    public function dharamshalaAeApplication($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;

        $active = MarActiveDharamshala::select('id', 'entity_name', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'ulb_id', 'license_year', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId)
            ->where('mar_active_dharamshalas.current_role_id', '=', 14);

        if ($request->wardNo) {

            $active->where('mar_active_dharamshalas.entity_ward_id', $request->wardNo);
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
    public function dharamshalaSiApplication($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;

        $active = MarActiveDharamshala::select('id', 'entity_name', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'ulb_id', 'license_year', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId)
            ->where('mar_active_dharamshalas.current_role_id', '=', 9);

        if ($request->wardNo) {

            $active->where('mar_active_dharamshalas.entity_ward_id', $request->wardNo);
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
