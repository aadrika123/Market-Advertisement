<?php

namespace App\Models\Param;

use App\Models\Advertisements\AdvSelfadvertisement;
use App\Models\Payment\TempTransaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdvMarTransaction extends Model
{
    use HasFactory;
    /**
     * | Add every transaction in transaction table
     */
    // public function addTransaction($req, $moduleId, $moduleType, $paymentMode)
    // {
    //     $addData = new AdvMarTransaction();
    //     $addData->user_id = $req->userId;
    //     $addData->module_id         = $moduleId;
    //     $addData->user_id         = $req->userId;
    //     $addData->workflow_id       = $req->workflow_id;
    //     $addData->application_id    = $req->id;
    //     $addData->module_type       = $moduleType;
    //     $addData->transaction_id    = $req->payment_id;
    //     $addData->transaction_no    = "$req->id-" . time();
    //     $addData->transaction_date  = $req->payment_date;
    //     $addData->amount            = $req->payment_amount;
    //     if (isset($req->demand_amount)) {
    //         $addData->demand_amount     = $req->demand_amount;
    //     }
    //     $addData->payment_details   = $req->payment_details;
    //     $addData->payment_mode      = $paymentMode;
    //     if (isset($req->entity_ward_id)) {
    //         $addData->entity_ward_id    = $req->entity_ward_id;
    //     }
    //     $addData->ulb_id            = $req->ulb_id;
    //     $addData->verify_date       = Carbon::now();
    //     $addData->verify_status     = 1;
    //     $addData->save();
    // }

    public function addTransaction($req, $appDetails, $moduleId, $moduleType,)
    {
        $addData = new AdvMarTransaction();
        $user    = authuser($req);
        $ulbId    = $user->ulb_id;
        $isjsk  = false;
        if ($user->user_type == 'JSK') {
            $isjsk == true;
        }

        $addData->module_id         = $moduleId;
        $addData->workflow_id       = $appDetails->workflow_id;
        $addData->application_id    = $appDetails->id;
        $addData->module_type       = $moduleType;
        $addData->transaction_id    = $appDetails->payment_id;
        $addData->transaction_no    = $appDetails->payment_id;
        $addData->transaction_date  = $appDetails->payment_date;
        $addData->amount            = $appDetails->payment_amount;
        if (isset($req->demand_amount)) {
            $addData->demand_amount     = $req->demand_amount;
        }
        $addData->payment_details   = $req->payment_details;
        $addData->payment_mode      = $req->paymentMode;
        if (isset($req->entity_ward_id)) {
            $addData->entity_ward_id    = $req->entity_ward_id;
        }
        $addData->ulb_id            = $ulbId;
        $addData->user_id           = $user->id;
        $addData->citizen_id        = $req->citizenId;
        $addData->is_jsk            = $isjsk;

        $addData->cheque_dd_no      = $req->chequeNo;
        $addData->cheque_date       = $req->chequeDate;
        $addData->bank_name         = $req->bankName;
        $addData->branch_name       = $req->branchName;
        // $addData->verify_date       = Carbon::now();
        // $addData->verify_status     = 0;
        $addData->save();
        $transactionId = $addData->id;
        return $transactionId;
    }
    public function getTranByApplicationId($applicationId)
    {
        return AdvMarTransaction::select(
            'adv_mar_transactions.id',
            'adv_mar_transactions.transaction_no',
            DB::raw("TO_CHAR(adv_mar_transactions.transaction_date, 'DD-MM-YYYY') as transaction_date"),
            'adv_mar_transactions.amount',
            'adv_mar_transactions.payment_mode',
            'adv_mar_transactions.demand_amount',
            'adv_mar_transactions.ulb_id',
            'adv_mar_transactions.cheque_dd_no',
            'adv_mar_transactions.cheque_date',
            'adv_mar_transactions.bank_name',
            'adv_mar_transactions.branch_name',
            'adv_mar_transactions.verify_status'
        )
            ->where('adv_mar_transactions.application_id', '=', $applicationId)
            ->where('adv_mar_transactions.module_type', '=', "Advertisement");
        //->get();
    }

    public function cashDtl($date)
    {
        return AdvMarTransaction::select('adv_mar_transactions.*')
            ->where('verify_status', 0)
            ->where('transaction_date', $date);
    }

    public function getTransByTranNo($tranNo)
    {
        return DB::table('adv_mar_transactions as t')
            ->select(
                't.id as transaction_id',
                't.tran_no as transaction_no',
                't.amount',
                't.payment_mode',
                't.tran_date',
                't.tran_type as module_name',
                DB::raw("
                CASE
                    WHEN t.property_id is not null THEN t.property_id
                    ELSE t.saf_id
                END as application_id
            "),
                DB::raw('1 as moduleId'),
                't.status'
            )
            ->where('t.tran_no', $tranNo)
            ->where('status', 1);
            
    }

    /**
     * | Cheque Dtl And Transaction Dtl
     */
    public function chequeTranDtl($ulbId)
    {
        return AdvMarTransaction::select(
            'adv_cheque_dtls.*',
            DB::raw("1 as module_id"),
            DB::raw(
                "case when prop_transactions.property_id is not null then 'Property' when 
                prop_transactions.saf_id is not null then 'Saf' end as tran_type"
            ),
            DB::raw("TO_CHAR(tran_date, 'DD-MM-YYYY') as tran_date"),
            'tran_no',
            'payment_mode',
            'amount',
            DB::raw("TO_CHAR(cheque_date, 'DD-MM-YYYY') as cheque_date"),
            "bank_name",
            "branch_name",
            "bounce_status",
            "cheque_no",
            DB::raw("TO_CHAR(clear_bounce_date, 'DD-MM-YYYY') as clear_bounce_date"),
            "users.name as user_name"
        )
            ->join('adv_cheque_dtls', 'adv_cheque_dtls.transaction_id', 'prop_transactions.id')
            ->join('users', 'users.id', 'prop_cheque_dtls.user_id')
            ->whereIn('payment_mode', ['CHEQUE', 'DD'])
            // ->where('prop_transactions.status', 1)
            ->where('prop_transactions.ulb_id', $ulbId);
    }
}
