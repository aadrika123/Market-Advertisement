<?php

namespace App\Models\Markets;

use App\MicroServices\IdGenerator\PrefixIdGenerator;
use App\Models\Advertisements\WfActiveDocument;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Return_;

class MarHostel extends Model
{
    use HasFactory;

    /**
     * Summary of allApproveList
     * @return void
     */
    public function allApproveList()
    {
        return MarHostel::select(
            'mar_hostels.id',
            'mar_hostels.application_no',
            'mar_hostels.application_date',
            'mar_hostels.entity_address',
            'mar_hostels.entity_name',
            'mar_hostels.applicant',
            'mar_hostels.applicant as owner_name',
            'mar_hostels.mobile as mobile_no',
            'mar_hostels.payment_status',
            'mar_hostels.payment_amount',
            'mar_hostels.approve_date',
            'mar_hostels.citizen_id',
            'mar_hostels.ulb_id',
            'mar_hostels.valid_upto',
            'mar_hostels.workflow_id',
            'mar_hostels.license_no',
            'mar_hostels.application_type',
            'mar_hostels.payment_id',
            DB::raw("'hostel' as type"),
            'um.ulb_name as ulb_name',
        )
            ->join('ulb_masters as um', 'um.id', '=', 'mar_hostels.ulb_id')
            ->orderByDesc('mar_hostels.id')
            ->get();
    }

    /**
     * | Get Application Approve List by Role Ids
     */
    public function listApproved($citizenId, $userType)
    {
        $allApproveList = $this->allApproveList();

        foreach ($allApproveList as $key => $list) {
            $activeHostel = MarActiveHostel::where('application_no', $list['application_no'])->count();
            $current_date = carbon::now()->format('Y-m-d');
            $notify_date = carbon::parse($list['valid_upto'])->subDay(30)->format('Y-m-d');
            if ($current_date >= $notify_date) {
                if ($activeHostel == 0) {
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
        return MarHostel::where('id', $id)
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'payment_amount',
                'approve_date',
                'ulb_id',
                'workflow_id',
            )
            ->first();
    }

    /**
     * | Application payment via cash
     */
    public function paymentByCash($req)
    {

        if ($req->status == '1') {
            // Hostel Table Update
            $mMarHostel = MarHostel::find($req->applicationId);
            $mMarHostel->payment_status = $req->status;
            //$mMarHostel->payment_mode = "Cash";
            //$pay_id = $mMarHostel->payment_id = "Cash-$req->applicationId-" . time();
            $mMarHostel->payment_date = Carbon::now();
            $mMarHostel->payment_mode = $req->paymentMode;
            //$pay_id = $mMarBanquteHall->payment_id = "Cash-$req->applicationId-" . time();
            $mMarHostel->payment_date = Carbon::now();
            $receiptIdParam                = Config::get('constants.PARAM_IDS.TRN');
            $idGeneration                  = new PrefixIdGenerator($receiptIdParam, $mMarHostel->ulb_id);
            $pay_id = $idGeneration->generate();
            $mMarHostel->payment_id = $pay_id;
            $payDetails = array('paymentMode' => $mMarHostel->paymentMode, 'id' => $req->applicationId, 'amount' => $mMarHostel->payment_amount, 'demand_amount' => $mMarHostel->demand_amount, 'workflowId' => $mMarHostel->workflow_id, 'userId' => $mMarHostel->user_id, 'ulbId' => $mMarHostel->ulb_id, 'transDate' => Carbon::now(), 'paymentId' => $pay_id);

            $mMarHostel->payment_details = json_encode($payDetails);

            if ($mMarHostel->renew_no == NULL) {
                $mMarHostel->valid_from = Carbon::now();
                $mMarHostel->valid_upto = Carbon::now()->addYears(1)->subDay(1);
            } else {
                $previousApplication = $this->findPreviousApplication($mMarHostel->application_no);
                $mMarHostel->valid_from = $previousApplication->valid_upto;
                $mMarHostel->valid_upto = Carbon::createFromFormat('Y-m-d', $previousApplication->valid_upto)->addYears(1)->subDay(1);
            }
            $mMarHostel->save();
            $renewal_id = $mMarHostel->last_renewal_id;

            // Renewal Table Updation
            $mMarHostelRenewal = MarHostelRenewal::find($renewal_id);
            $mMarHostelRenewal->payment_status = 1;
            $mMarHostelRenewal->payment_mode = $mMarHostel->paymentMode;
            $mMarHostelRenewal->payment_id =  $pay_id;
            $mMarHostelRenewal->payment_date = Carbon::now();
            $mMarHostelRenewal->payment_amount = $mMarHostel->payment_amount;
            $mMarHostelRenewal->demand_amount = $mMarHostel->demand_amount;
            $mMarHostelRenewal->valid_from = $mMarHostel->valid_from;
            $mMarHostelRenewal->valid_upto = $mMarHostel->valid_upto;
            $mMarHostelRenewal->payment_details = json_encode($payDetails);
            $status = $mMarHostelRenewal->save();
            $returnData['status'] = $status;
            $returnData['payment_id'] = $pay_id;
            return $returnData;
        }
    }

    // Find Previous Payment Date
    public function findPreviousApplication($application_no)
    {
        return $details = MarHostelRenewal::select('valid_upto')
            ->where('application_no', $application_no)
            ->orderByDesc('id')
            ->skip(1)->first();
    }

    /**
     * | Get Application Details For Renew Applications
     */
    public function applicationDetailsForRenew($appId)
    {
        $details = MarHostel::select(
            'mar_hostels.*',
            'mar_hostels.hostel_type as hostel_type_id',
            'mar_hostels.organization_type as organization_type_id',
            'mar_hostels.land_deed_type as land_deed_type_id',
            'mar_hostels.mess_type as mess_type_id',
            'mar_hostels.water_supply_type as water_supply_type_id',
            'mar_hostels.electricity_type as electricity_type_id',
            'mar_hostels.security_type as security_type_id',
            'ly.string_parameter as license_year_name',
            DB::raw("case when mar_hostels.is_approve_by_govt = true then 'Yes'
                        else 'No' end as is_approve_by_govt_name"),
            DB::raw("case when mar_hostels.is_approve_by_govt = true then 1
                        else 0 end as is_approve_by_govt_id"),
            'lt.string_parameter as hostel_type_name',
            'ot.string_parameter as organization_type_name',
            'ldt.string_parameter as land_deed_type_name',
            'mt.string_parameter as mess_type_name',
            'wt.string_parameter as water_supply_type_name',
            'et.string_parameter as electricity_type_name',
            'st.string_parameter as security_type_name',
            'pw.ward_name as permanent_ward_name',
            'ew.ward_name as entity_ward_name',
            'rw.ward_name as residential_ward_name',
            'ulb.ulb_name',
        )
            ->leftJoin('ref_adv_paramstrings as ly', 'ly.id', '=', DB::raw('mar_hostels.license_year::int'))
            ->leftJoin('ulb_ward_masters as rw', 'rw.id', '=', DB::raw('mar_hostels.residential_ward_id::int'))
            ->leftJoin('ref_adv_paramstrings as lt', 'lt.id', '=', DB::raw('mar_hostels.hostel_type::int'))
            ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', DB::raw('mar_hostels.organization_type::int'))
            ->leftJoin('ref_adv_paramstrings as ldt', 'ldt.id', '=', DB::raw('mar_hostels.land_deed_type::int'))
            ->leftJoin('ref_adv_paramstrings as mt', 'mt.id', '=', DB::raw('mar_hostels.mess_type::int'))
            ->leftJoin('ref_adv_paramstrings as wt', 'wt.id', '=', DB::raw('mar_hostels.water_supply_type::int'))
            ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', DB::raw('mar_hostels.electricity_type::int'))
            ->leftJoin('ref_adv_paramstrings as st', 'st.id', '=', DB::raw('mar_hostels.security_type::int'))
            ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'mar_hostels.entity_ward_id')
            ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'mar_hostels.permanent_ward_id')
            ->leftJoin('ulb_masters as ulb', 'ulb.id', '=', 'mar_hostels.ulb_id')
            ->where('mar_hostels.id', $appId)->first();
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
        $details = MarHostel::select(
            'mar_hostels.payment_amount',
            'mar_hostels.payment_id',
            'mar_hostels.payment_date',
            'mar_hostels.permanent_address as address',
            'mar_hostels.applicant',
            'mar_hostels.entity_name',
            'mar_hostels.payment_details',
            'mar_hostels.payment_mode',
            'mar_hostels.valid_from',
            'mar_hostels.valid_upto',
            'mar_hostels.holding_no',
            'mar_hostels.trade_license_no',
            'mar_hostels.no_of_rooms',
            'mar_hostels.no_of_beds',
            'mar_hostels.rule',
            'mar_hostels.license_no',
            'mar_hostels.workflow_id',
            'mar_hostels.application_no',
            'mar_hostels.ulb_id',
            'mar_hostels.no_of_beds',
            'mar_hostels.application_date as applyDate',
            'ulb_masters.ulb_name as ulbName',
            'ulb_masters.logo as ulbLogo',
            'ulb_masters.toll_free_no',
            'ulb_masters.current_website as website',
            'ly.string_parameter as licenseYear',
            'ht.string_parameter as hostelType',
            'wn.ward_name as wardNo',
            DB::raw("'Market' as module"),
        )
            ->leftjoin('ulb_masters', 'mar_hostels.ulb_id', '=', 'ulb_masters.id')
            ->leftjoin('ulb_ward_masters as wn', 'mar_hostels.entity_ward_id', '=', 'wn.id')
            ->leftjoin('ref_adv_paramstrings as ly', DB::raw('mar_hostels.license_year::int'), '=', 'ly.id')
            ->leftjoin('ref_adv_paramstrings as ht', DB::raw('mar_hostels.hostel_type::int'), '=', 'ht.id')
            ->where('mar_hostels.payment_id', $paymentId)
            ->first();
        $details->payment_details = json_decode($details->payment_details);
        $details->towards = "Hostel";
        $details->payment_date = Carbon::createFromFormat('Y-m-d', $details->payment_date)->format('d-m-Y');
        $details->applyDate = Carbon::createFromFormat('Y-m-d', $details->applyDate)->format('d-m-Y');
        $details->valid_from = Carbon::createFromFormat('Y-m-d', $details->valid_from)->format('d-m-Y');
        $details->valid_upto = Carbon::createFromFormat('Y-m-d', $details->valid_upto)->format('d-m-Y');
        return $details;
    }

    /**
     * | Get Approved list For Report
     */
    public function approveListForReport()
    {
        return MarHostel::select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'hostel_type', 'ulb_id', 'license_year', DB::raw("'Approve' as application_status"));
    }

    /**
     * | Get Reciept Details 
     * | Created On : 23/6/2023
     */
    public function getApprovalLetter($applicationId)
    {
        $recieptDetails = MarHostel::select(
            'mar_hostels.id',
            'mar_hostels.workflow_id',
            'mar_hostels.approve_date',
            'mar_hostels.applicant as applicant_name',
            'mar_hostels.application_no',
            'mar_hostels.license_no',
            'ulb_id',
            'mar_hostels.payment_date as license_start_date',
            DB::raw('CONCAT(application_date,id) AS reciept_no')
        )
            ->where('mar_hostels.id', $applicationId)
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
        $approved = MarHostel::select('id', 'entity_name', 'application_no', 'applicant',  DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'hostel_type', 'ulb_id', 'license_year', DB::raw("'Approved' as application_status"))
            ->where('ulb_id', $ulbId);

        $active = MarActiveHostel::select('id', 'entity_name', 'application_no', 'applicant',  DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'hostel_type', 'ulb_id', 'license_year', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId);

        $rejected = MarRejectedHostel::select('id', 'entity_name', 'application_no', 'applicant',  DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'hostel_type', 'ulb_id', 'license_year', DB::raw("'Reject' as application_status"))
            ->where('ulb_id', $ulbId);
        if ($request->wardNo) {
            $approved->where('mar_hostels.entity_ward_id', $request->wardNo);
            $active->where('mar_active_hostels.entity_ward_id', $request->wardNo);
            $rejected->where('mar_rejected_hostels.entity_ward_id', $request->wardNo);
        }
        if ($request->applicationType) {
            $approved->where('mar_hostels.application_type', $request->applicationType);
            $active->where('mar_active_hostels.application_type', $request->applicationType);
            $rejected->where('mar_rejected_hostels.application_type', $request->applicationType);
        }
        if ($request->fyear) {
            $approved->whereBetween('mar_hostels.application_date', [$currentfyStartDate, $currentfyEndDate]);
            $active->whereBetween('mar_active_hostels.application_date', [$currentfyStartDate, $currentfyEndDate]);
            $rejected->whereBetween('mar_rejected_hostels.application_date', [$currentfyStartDate, $currentfyEndDate]);
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
        $hostelWorkflow = Config::get('workflow-constants.HOSTEL_WORKFLOWS');
        $dateFrom = $request->dateFrom ?: Carbon::now()->format('Y-m-d');
        $dateUpto = $request->dateUpto ?: Carbon::now()->format('Y-m-d');
        $approved = DB::table('mar_hostel_renewals')
            ->select('mar_hostel_renewals.id', 'mar_hostel_renewals.application_no', 'mar_hostel_renewals.applicant',  DB::raw("TO_CHAR(mar_hostel_renewals.application_date, 'DD-MM-YYYY') as application_date"), 'mar_hostel_renewals.application_type', 'mar_hostel_renewals.entity_ward_id', DB::raw("'Approve' as application_status"), 'mar_hostel_renewals.payment_amount',  DB::raw("TO_CHAR(payment_date, 'DD-MM-YYYY') as payment_date"), 'mar_hostel_renewals.payment_mode', 'mar_hostel_renewals.entity_name', 'adv_mar_transactions.transaction_no')
            ->join('adv_mar_transactions', 'adv_mar_transactions.transaction_id', '=', 'mar_hostel_renewals.payment_id')
            ->where('payment_status', 1)
            ->where('mar_hostel_renewals.status', 1)
            ->where('mar_hostel_renewals.ulb_id', $ulbId)
            ->where('adv_mar_transactions.workflow_id', $hostelWorkflow)
            ->whereBetween('payment_date', [$dateFrom, $dateUpto]);;

        if ($request->wardNo) {
            $approved->where('mar_hostel_renewals.entity_ward_id', $request->wardNo);
        }
        if ($request->applicationType) {
            $approved->where('mar_hostel_renewals.application_type', $request->applicationType);
        }
        if ($request->payMode == 'All') {
            $data = $approved;
        }
        if ($request->payMode == 'Online') {
            $data = $approved->where('payment_mode', $request->payMode);
        }
        if ($request->payMode == 'Cash') {
            $data = $approved->where('payment_mode', $request->payMode);
        }
        if ($request->payMode == 'Cheque/DD') {
            $data = $approved->where('payment_mode', $request->payMode);
        }
       // Clone the query for counts and sums
       $approveListForCounts = clone $approved;
       $approveListForSums = clone $approved;

       // Count of transactions
       $cashCount = (clone $approveListForCounts)->where('mar_hostel_renewals.payment_mode', 'CASH')->count();
        $ddCount = (clone $approveListForCounts)->where('mar_hostel_renewals.payment_mode', 'DD')->count();
        $chequeCount = (clone $approveListForCounts)->where('mar_hostel_renewals.payment_mode', 'CHEQUE')->count();
       $onlineCount = (clone $approveListForCounts)->where('mar_hostel_renewals.payment_mode', 'ONLINE')->count();

       // Sum of transactions
       $cashPayment = (clone $approveListForSums)->where('mar_hostel_renewals.payment_mode', 'CASH')->sum('payment_amount');
        $ddPayment = (clone $approveListForSums)->where('mar_hostel_renewals.payment_mode', 'DD')->sum('payment_amount');
        $chequePayment = (clone $approveListForSums)->where('mar_hostel_renewals.payment_mode', 'CHEQUE')->sum('payment_amount');
       $onlinePayment = (clone $approveListForSums)->where('mar_hostel_renewals.payment_mode', 'ONLINE')->sum('payment_amount');

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
        $approved = MarHostel::select('id', 'entity_name', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'hostel_type', 'ulb_id', 'license_year', DB::raw("'Approve' as application_status"))
            ->where('ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);

        $rejected = MarRejectedHostel::select('id', 'entity_name', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'hostel_type', 'ulb_id', 'license_year', DB::raw("'Reject' as application_status"))
            ->where('ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        if ($request->wardNo) {
            $approved->where('mar_hostels.entity_ward_id', $request->wardNo);
            $rejected->where('mar_rejected_hostels.entity_ward_id', $request->wardNo);
        }
        if ($request->applicationType) {
            $approved->where('mar_hostels.application_type', $request->applicationType);
            $rejected->where('mar_rejected_hostels.application_type', $request->applicationType);
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
        $approved = MarHostel::select('id', 'entity_name', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'hostel_type', 'ulb_id', 'license_year', DB::raw("'Approved' as application_status"))
            ->where('ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        $active = MarActiveHostel::select('id', 'entity_name', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'hostel_type', 'ulb_id', 'license_year', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        $rejected = MarRejectedHostel::select('id', 'entity_name', 'application_no', 'applicant', DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'hostel_type', 'ulb_id', 'license_year', DB::raw("'Reject' as application_status"))
            ->where('ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        if ($request->wardNo) {
            $approved->where('mar_hostels.entity_ward_id', $request->wardNo);
            $active->where('mar_active_hostels.entity_ward_id', $request->wardNo);
            $rejected->where('mar_rejected_hostels.entity_ward_id', $request->wardNo);
        }
        if ($request->applicationType) {
            $approved->where('mar_hostels.application_type', $request->applicationType);
            $active->where('mar_active_hostels.application_type', $request->applicationType);
            $rejected->where('mar_rejected_hostels.application_type', $request->applicationType);
        }
        if ($request->ruleType) {
            $approved->where('mar_hostels.rule', $request->ruleType);
            $active->where('mar_active_hostels.rule', $request->ruleType);
            $rejected->where('mar_rejected_hostels.rule', $request->ruleType);
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

    public function getApplicationWithHostelType($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;
        $dateFrom = $request->dateFrom ?: Carbon::now()->format('Y-m-d');
        $dateUpto = $request->dateUpto ?: Carbon::now()->format('Y-m-d');
        $approved = MarHostel::select('mar_hostels.id', 'mar_hostels.entity_name', 'mar_hostels.application_no', 'mar_hostels.applicant', DB::raw("TO_CHAR(mar_hostels.application_date, 'DD-MM-YYYY') as application_date"), 'mar_hostels.application_type', 'mar_hostels.entity_ward_id', 'mar_hostels.rule', 'mar_hostels.organization_type', 'mar_hostels.hostel_type as hostel_id', 'mar_hostels.ulb_id', 'mar_hostels.license_year', DB::raw("'Approved' as application_status"), 'ref_adv_paramstrings.string_parameter as hostelType')
            ->leftJoin('ref_adv_paramstrings', 'ref_adv_paramstrings.id', '=', 'mar_hostels.hostel_type')
            ->where('mar_hostels.ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        $active = MarActiveHostel::select('mar_active_hostels.id', 'mar_active_hostels.entity_name', 'mar_active_hostels.application_no', 'mar_active_hostels.applicant',  DB::raw("TO_CHAR(mar_active_hostels.application_date, 'DD-MM-YYYY') as application_date"), 'mar_active_hostels.application_type', 'mar_active_hostels.entity_ward_id', 'mar_active_hostels.rule', 'mar_active_hostels.organization_type', 'mar_active_hostels.hostel_type as hostel_id', 'mar_active_hostels.ulb_id', 'mar_active_hostels.license_year', DB::raw("'Active' as application_status"), 'ref_adv_paramstrings.string_parameter as hostelType')
            ->leftJoin('ref_adv_paramstrings', 'ref_adv_paramstrings.id', '=', 'mar_active_hostels.hostel_type')
            ->where('mar_active_hostels.ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        $rejected = MarRejectedHostel::select('mar_rejected_hostels.id', 'mar_rejected_hostels.entity_name', 'mar_rejected_hostels.application_no', 'mar_rejected_hostels.applicant',  DB::raw("TO_CHAR(mar_rejected_hostels.application_date, 'DD-MM-YYYY') as application_date"), 'mar_rejected_hostels.application_type', 'mar_rejected_hostels.entity_ward_id', 'mar_rejected_hostels.rule', 'mar_rejected_hostels.organization_type', 'mar_rejected_hostels.hostel_type as hostel_id', 'mar_rejected_hostels.ulb_id', 'mar_rejected_hostels.license_year', DB::raw("'Reject' as application_status"), 'ref_adv_paramstrings.string_parameter as hostelType')
            ->leftJoin('ref_adv_paramstrings', 'ref_adv_paramstrings.id', '=', 'mar_rejected_hostels.hostel_type')
            ->where('mar_rejected_hostels.ulb_id', $ulbId)
            ->whereBetween('application_date', [$dateFrom, $dateUpto]);
        if ($request->wardNo) {
            $approved->where('mar_hostels.entity_ward_id', $request->wardNo);
            $active->where('mar_active_hostels.entity_ward_id', $request->wardNo);
            $rejected->where('mar_rejected_hostels.entity_ward_id', $request->wardNo);
        }
        if ($request->applicationType) {
            $approved->where('mar_hostels.application_type', $request->applicationType);
            $active->where('mar_active_hostels.application_type', $request->applicationType);
            $rejected->where('mar_rejected_hostels.application_type', $request->applicationType);
        }
        if ($request->ruleType) {
            $approved->where('mar_hostels.rule', $request->ruleType);
            $active->where('mar_active_hostels.rule', $request->ruleType);
            $rejected->where('mar_rejected_hostels.rule', $request->ruleType);
        }

        if ($request->hostelType) {
            $approved->where('ref_adv_paramstrings.id', $request->hostelType);
            $active->where('ref_adv_paramstrings.id', $request->hostelType);
            $rejected->where('ref_adv_paramstrings.id', $request->hostelType);
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
    public function listjskApprovedApplication()
    {
        return MarHostel::select(
            'mar_hostels.id',
            'application_no',
            DB::raw("TO_CHAR(mar_hostels.application_date, 'DD-MM-YYYY') as application_date"),
            'mar_hostels.application_type',
            'mar_hostels.applicant',
            'mar_hostels.applicant as owner_name',
            'mar_hostels.entity_name',
            'mar_hostels.license_no',
            'mar_hostels.payment_status',
            'mar_hostels.payment_amount',
            'mar_hostels.approve_date',
            'mar_hostels.citizen_id',
            'mar_hostels.valid_upto',
            'mar_hostels.valid_from',
            'mar_hostels.user_id',
            'mobile as mobile_no',
            DB::raw("CASE WHEN user_id IS NOT NULL THEN 'jsk' ELSE 'citizen' END AS applied_by")
        )
            ->orderByDesc('id');
        //->get();
    }

    public function getDetailsById($applicationId)
    {
        return MarHostel::select(
            'mar_hostels.id',
            'mar_hostels.application_no',
            'mar_hostels.application_date',
            'mar_hostels.entity_address',
            'mar_hostels.entity_name',
            'mar_hostels.applicant',
            'mar_hostels.applicant as owner_name',
            'mar_hostels.mobile as mobile_no',
            'mar_hostels.payment_status',
            'mar_hostels.payment_amount',
            'mar_hostels.approve_date',
            'mar_hostels.citizen_id',
            'mar_hostels.ulb_id',
            'mar_hostels.valid_upto',
            'mar_hostels.workflow_id',
            'mar_hostels.license_no',
            'mar_hostels.application_type',
            'mar_hostels.payment_id',
            DB::raw("'hostel' as type"),
            'um.ulb_name as ulb_name',
            'entity_ward_id as ward_no',
            'holding_no',
            'father',
            'mar_hostels.email',
            'aadhar_card as aadhar_card',
            'permanent_ward_id as permanent_ward_no',
            'permanent_address',
            'doc_upload_status'
        )
            ->leftjoin('ulb_masters as um', 'um.id', '=', 'mar_hostels.ulb_id')
            ->where('mar_hostels.id', $applicationId)
            ->orderByDesc('mar_hostels.id');
        //->get();
    }

    public function hostleDaAppliaction($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;

        $active = MarActiveHostel::select('id', 'entity_name', 'application_no', 'applicant',  DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'hostel_type', 'ulb_id', 'license_year', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId)
            ->where('mar_active_hostels.current_role_id', '=', 6);;

        if ($request->wardNo) {

            $active->where('mar_active_hostels.entity_ward_id', $request->wardNo);
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

    public function hostleAeAppliaction($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;

        $active = MarActiveHostel::select('id', 'entity_name', 'application_no', 'applicant',  DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'hostel_type', 'ulb_id', 'license_year', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId)
            ->where('mar_active_hostels.current_role_id', '=', 14);

        if ($request->wardNo) {

            $active->where('mar_active_hostels.entity_ward_id', $request->wardNo);
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
    public function hostleSiAppliaction($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;

        $active = MarActiveHostel::select('id', 'entity_name', 'application_no', 'applicant',  DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'hostel_type', 'ulb_id', 'license_year', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId)
            ->where('mar_active_hostels.current_role_id', '=', 9);;

        if ($request->wardNo) {

            $active->where('mar_active_hostels.entity_ward_id', $request->wardNo);
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
    public function hostleCmAppliaction($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;

        $active = MarActiveHostel::select('id', 'entity_name', 'application_no', 'applicant',  DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'hostel_type', 'ulb_id', 'license_year', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId)
            ->where('mar_active_hostels.current_role_id', '=', 32);;

        if ($request->wardNo) {

            $active->where('mar_active_hostels.entity_ward_id', $request->wardNo);
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
    public function hostleEoAppliaction($request)
    {
        $user = Auth()->user();
        $ulbId = $user->ulb_id ?? null;
        $perPage = $request->perPage ?: 10;

        $active = MarActiveHostel::select('id', 'entity_name', 'application_no', 'applicant',  DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"), 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'hostel_type', 'ulb_id', 'license_year', DB::raw("'Active' as application_status"))
            ->where('ulb_id', $ulbId)
            ->where('mar_active_hostels.current_role_id', '=', 10);;

        if ($request->wardNo) {

            $active->where('mar_active_hostels.entity_ward_id', $request->wardNo);
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
