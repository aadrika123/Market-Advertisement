<?php

namespace App\Models\Param;

use App\Models\Advertisements\AdvSelfadvertisement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function addTransaction($req, $moduleId, $moduleType, $paymentMode)
    {
        $addData = new AdvMarTransaction();

        $addData->module_id         = $moduleId;
        $addData->workflow_id       = $req->workflow_id;
        $addData->application_id    = $req->id;
        $addData->module_type       = $moduleType;
        $addData->transaction_id    = $req->payment_id;
        $addData->transaction_no    = "$req->id-" . time();
        $addData->transaction_date  = $req->payment_date;
        $addData->amount            = $req->payment_amount;
        if (isset($req->demand_amount)) {
            $addData->demand_amount     = $req->demand_amount;
        }
        $addData->payment_details   = $req->payment_details;
        $addData->payment_mode      = $paymentMode;
        if (isset($req->entity_ward_id)) {
            $addData->entity_ward_id    = $req->entity_ward_id;
        }
        $addData->ulb_id            = $req->ulb_id;
        $addData->user_id           = $req->userId;
        $addData->citizen_id        = $req->citizenId;
        $addData->is_jsk            = $req->isJsk;

        $addData->cheque_dd_no      = $req->chequeNo;
        $addData->cheque_date       = $req->chequeDate;
        $addData->bank_name         = $req->bankName;
        $addData->branch_name       = $req->branchName;
        // $addData->verify_date       = Carbon::now();
        // $addData->verify_status     = 0;
        $addData->save();
        return $addData->id;
    }
    public function getTranByApplicationId($applicationId)
    {
        return AdvMarTransaction::select(
            'adv_mar_transactions.id',
            'adv_mar_transactions.transaction_no',
            'adv_mar_transactions.transaction_date',
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
}
