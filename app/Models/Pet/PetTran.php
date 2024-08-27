<?php

namespace App\Models\Pet;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class PetTran extends Model
{
    use HasFactory;

    /**
     * | Get transaction details accoring to related Id and transaction type
     */
    public function getTranDetails($relatedId, $tranType)
    {
        return PetTran::where('related_id', $relatedId)
            ->where('tran_type_id', $tranType)
            ->where('status', 1)
            ->orderByDesc('id');
    }

    /**
     * | Save the transaction details 
     */
    public function saveTranDetails($req)
    {
        $paymentMode = Config::get("pet.PAYMENT_MODE");

        $mPetTran = new PetTran();
        $mPetTran->related_id   = $req['id'];
        $mPetTran->ward_id      = $req['wardId'];
        $mPetTran->ulb_id       = $req['ulbId'];
        $mPetTran->tran_date    = $req['todayDate'];
        $mPetTran->tran_no      = $req['tranNo'];
        $mPetTran->payment_mode = $req['paymentMode'];
        $mPetTran->amount       = $req['amount'];
        $mPetTran->emp_dtl_id   = $req['empId'] ?? null;
        $mPetTran->ip_address   = $req['ip'] ?? null;
        $mPetTran->user_type    = $req['userType'];
        $mPetTran->is_jsk       = $req['isJsk'] ?? false;
        $mPetTran->citizen_id   = $req['citId'] ?? null;
        $mPetTran->tran_type_id = $req['tranTypeId'];
        $mPetTran->round_amount = $req['roundAmount'];
        $mPetTran->token_no     = $req['tokenNo'];

        # For online payment
        if ($req['paymentMode'] == $paymentMode['1']) {
            $mPetTran->pg_response_id = $req['pgResponseId'];                               // Online response id
            $mPetTran->pg_id = $req['pgId'];                                                // Payment gateway id
        }
        $mPetTran->save();

        return [
            'transactionNo' => $req['tranNo'],
            'transactionId' => $mPetTran->id
        ];
    }

    /**
     * | Get transaction by application No
     */
    public function getTranByApplicationId($applicationId)
    {
        return PetTran::where('related_id', $applicationId)
            //->where('status', 1)
            ->orderByDesc('id');
    }

    /**
     * | Update request for transaction table
     */
    public function saveStatusInTrans($id, $refReq)
    {
        PetTran::where('id', $id)
            ->update($refReq);
    }

    /**
     * | Get transaction details according to transaction no
     */
    public function getTranDetailsByTranNo($tranNo)
    {
        return PetTran::select(
            'pet_trans.id AS refTransId',
            'pet_trans.tran_no',
            'pet_trans.*',
            'pet_tran_details.*',
        )
            ->join('pet_tran_details', 'pet_tran_details.tran_id', 'pet_trans.id')
            ->where('pet_trans.tran_no', $tranNo)
            ->where('pet_trans.status', 1)
            ->orderByDesc('pet_trans.id');
    }

    /**
     * | List Uncleared cheque or DD
     */
    public function listUnverifiedCashPayment($req)
    {
        return  DB::table('pet_trans')
            ->select(
                'pet_trans.id',
                'pet_trans.tran_no',
                'pet_trans.tran_date',
                'pet_trans.amount',
                't1.application_no',
                'pet_active_applicants.applicant_name',
                'pet_active_applicants.mobile_no',
                DB::raw("CASE 
                WHEN pet_trans.verify_status = '0' THEN 'Not Verified'
                WHEN pet_trans.verify_status = '1' THEN 'Verified'
                END AS cashVerifiedStatus"),
            )
            ->join('pet_active_registrations as t1', 'pet_trans.related_id', '=', 't1.id')
            ->join('pet_active_applicants', 'pet_active_applicants.application_id', 'pet_trans.related_id')
            ->where('payment_mode', '=', 'CASH');
    }

    /**
     * | Details for Cash Verification
     */
    public function cashDtl($date)
    {
        return PetTran::select('pet_trans.*', 'users.user_name', 'users.id as user_id', 'mobile')
            ->join('users', 'users.id', 'pet_trans.emp_dtl_id')
            ->where('pet_trans.status', 1)
            ->where('verify_status', 0)
            ->where('tran_date', $date);
    }

    public function dailyCollection($request)
    {
        $dateFrom = $request->dateFrom ?: Carbon::now()->format('Y-m-d');
        $dateUpto = $request->dateUpto ?: Carbon::now()->format('Y-m-d');
        $perPage = $request->perPage ?: 10;

        $query = PetTran::select('pet_trans.*', 'users.user_name', 'users.id as user_id', 'mobile', 'ulb_ward_masters.ward_name', 'pet_active_registrations.application_no')
            ->leftjoin('pet_active_registrations', 'pet_active_registrations.id', '=', 'pet_trans.related_id')
            ->leftjoin('pet_approved_registrations', 'pet_approved_registrations.id', '=', 'pet_trans.related_id')
            ->leftjoin('ulb_ward_masters', 'ulb_ward_masters.id', 'pet_trans.ward_id')
            ->leftjoin('users', 'users.id', 'pet_trans.emp_dtl_id')
            ->where('pet_trans.status', 1)
            ->whereBetween('pet_trans.tran_date', [$dateFrom, $dateUpto]);

        if ($request->paymentMode) {
            $query->where('pet_trans.payment_mode', $request->paymentMode);
        }
        if ($request->wardNo) {
            $query->where('pet_trans.ward_id', $request->wardNo);
        }
        if ($request->collectedBy) {
            if ($request->collectedBy == 'JSK') {
                $query->where('pet_trans.user_type', 'JSK');
            } else {
                $query->where('pet_trans.user_type', 'Citizen');
            }
        }

        $summaryQuery = clone $query;
        $transactions = $query->paginate($perPage);
        $collectAmount = $summaryQuery->sum('pet_trans.amount');
        $totalTransactions = $summaryQuery->count();

        $cashSummaryQuery = clone $summaryQuery;
        $cashSummaryQuery->where('pet_trans.payment_mode', 'CASH');
        $cashAmount = $cashSummaryQuery->sum('pet_trans.amount');
        $cashCount = $cashSummaryQuery->count();

        $onlineSummaryQuery = clone $summaryQuery;
        $onlineSummaryQuery->where('pet_trans.payment_mode', 'ONLINE');
        $onlineAmount = $onlineSummaryQuery->sum('pet_trans.amount');
        $onlineCount = $onlineSummaryQuery->count();

        // JSK cash collection
        $jskCashCollection = clone $summaryQuery;
        $jskCashCollection->where('pet_trans.payment_mode', 'CASH')->where('pet_trans.user_type', 'JSK');
        $jskCashAmount = $jskCashCollection->sum('pet_trans.amount');
        $jskCashCount = $jskCashCollection->count();

        // JSK online collection
        $jskOnlineCollection = clone $summaryQuery;
        $jskOnlineCollection->where('pet_trans.payment_mode', 'ONLINE')->where('pet_trans.user_type', 'JSK');
        $jskOnlineAmount = $jskOnlineCollection->sum('pet_trans.amount');
        $jskOnlineCount = $jskOnlineCollection->count();

        // Citizen cash collection
        $citizenCashCollection = clone $summaryQuery;
        $citizenCashCollection->where('pet_trans.payment_mode', 'CASH')->where('pet_trans.user_type', 'Citizen');
        $citizenCashAmount = $citizenCashCollection->sum('pet_trans.amount');
        $citizenCashCount = $citizenCashCollection->count();

        // Citizen online collection
        $citizenOnlineCollection = clone $summaryQuery;
        $citizenOnlineCollection->where('pet_trans.payment_mode', 'ONLINE')->where('pet_trans.user_type', 'Citizen');
        $citizenOnlineAmount = $citizenOnlineCollection->sum('pet_trans.amount');
        $citizenOnlineCount = $citizenOnlineCollection->count();

        $totalJskCount = $jskCashCount + $jskOnlineCount;
        $totalCitizenCount = $citizenCashCount + $citizenOnlineCount;

        return [
            'current_page' => $transactions->currentPage(),
            'last_page' => $transactions->lastPage(),
            'data' => $transactions->items(),
            'total' => $transactions->total(),
            'collectAmount' => $collectAmount,
            'totalTransactions' => $totalTransactions,
            'cashCollection' => $cashAmount,
            'cashTranCount' => $cashCount,
            'onlineCollection' => $onlineAmount,
            'onlineTranCount' => $onlineCount,
            'jskCashCollectionAmount' => $jskCashAmount,
            'jskCashCollectionCount' => $jskCashCount,
            'jskOnlineCollectionAmount' => $jskOnlineAmount,
            'jskOnlineCollectionCount' => $jskOnlineCount,
            'citizenCashCollectionAmount' => $citizenCashAmount,
            'citizenCashCollectionCount' => $citizenCashCount,
            'citizenOnlineCollectionAmount' => $citizenOnlineAmount,
            'citizenOnlineCollectionCount' => $citizenOnlineCount,
            'totalJskCount' => $totalJskCount,
            'totalCitizenCount' => $totalCitizenCount
        ];
    }

    public function getTransByTranNo($transactionNo)
    {
        return PetTran::select(
            'pet_trans.id',
            'pet_trans.tran_no',
            DB::raw("TO_CHAR(pet_trans.tran_date, 'DD-MM-YYYY') as tran_date"),
            'pet_trans.amount',
            'pet_trans.payment_mode',
            'pet_trans.status',
            'pet_trans.ulb_id',
            't1.application_no',
            'pet_cheque_dtls.cheque_no',
            'pet_cheque_dtls.bank_name',
            DB::raw("'9' as module_id"),
            'users.user_name',
            'users.id as user_id',
            'mobile'
        )
            ->leftjoin('pet_cheque_dtls', 'pet_cheque_dtls.transaction_id', '=', 'pet_trans.id')
            ->join('pet_active_registrations as t1', 'pet_trans.related_id', '=', 't1.id')
            ->join('users', 'users.id', 'pet_trans.emp_dtl_id')
            ->where('pet_trans.status', 1)
            ->where('verify_status', 0)
            ->where('tran_no', $transactionNo)
            ->get();
    }

    public function deactivateTransaction($transactionId)
    {

        PetTran::where('id', $transactionId)
            ->update(
                [
                    'status' => 0,
                ]
            );
    }

    public function getDeactivatedTran()
    {
        return PetTran::select(
            'pet_trans.id',
            'pet_trans.tran_no',
            DB::raw("TO_CHAR(pet_trans.tran_date, 'DD-MM-YYYY') as tran_date"),
            'pet_trans.amount',
            'pet_trans.payment_mode',
            'pet_trans.status',
            'pet_trans.ulb_id',
            't1.application_no',
            'pet_cheque_dtls.cheque_no',
            'pet_cheque_dtls.bank_name',
            DB::raw("'9' as module_id"),
            DB::raw("TO_CHAR(transaction_deactivate_dtls.deactive_date, 'DD-MM-YYYY') as deactive_date"),
            "transaction_deactivate_dtls.reason",
            "users.name as deactivated_by"
        )
            ->leftjoin('pet_cheque_dtls', 'pet_cheque_dtls.transaction_id', '=', 'pet_trans.id')
            ->join('pet_active_registrations as t1', 'pet_trans.related_id', '=', 't1.id')
            ->join('transaction_deactivate_dtls', 'transaction_deactivate_dtls.tran_id', '=', 'pet_trans.id')
            ->join('users', 'users.id', 'transaction_deactivate_dtls.deactivated_by')
            ->where('pet_trans.status', 0);

        //->get();
    }
}
