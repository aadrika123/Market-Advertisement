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

    public function addTransaction($req, $moduleId, $moduleType, $paymentMode)
    {
        $addData = new AdvMarTransaction();

        $addData->module_id         = $moduleId;
        $addData->workflow_id       = $req->workflow_id;
        $addData->application_id    = $req->id;
        $addData->module_type       = $moduleType;
        $addData->transaction_id    = $req->payment_id;
        $addData->transaction_no    = $req->payment_id;
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
    /**
     * |
     */
    public function addTransactions($req, $appDetails, $moduleId, $moduleType,)
    {
        $addData = new AdvMarTransaction();
        $user    = authUser($req);
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
        $addData->payment_details   = $appDetails->payment_details;
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
    public function getTranByApplicationId($applicationId, $data)
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
            ->where('adv_mar_transactions.workflow_id',$data->workflow_id)
            ->where('adv_mar_transactions.status',1)
            ->where('adv_mar_transactions.status',1);
        // ->where('adv_mar_transactions.module_type', '=', "Market");
        //->get();
    }

    public function cashDtl($date)
    {
        return AdvMarTransaction::select('adv_mar_transactions.*')
            ->where('verify_status', 0)
            ->where('transaction_date', $date);
    }





    /**
     * | Cheque Dtl And Transaction Dtl
     */
    public function chequeTranDtl($ulbId)
    {
        return AdvMarTransaction::select(
            'adv_cheque_dtls.id',
            DB::raw("TO_CHAR(transaction_date, 'DD-MM-YYYY') as transaction_date"),
            'adv_mar_transactions.transaction_no',
            'payment_mode',
            'amount',
            DB::raw("TO_CHAR(adv_cheque_dtls.cheque_date, 'DD-MM-YYYY') as cheque_date"),
            "adv_cheque_dtls.bank_name",
            "adv_cheque_dtls.branch_name",
            "adv_cheque_dtls.cheque_no",
            DB::raw("TO_CHAR(clear_bounce_date, 'DD-MM-YYYY') as clear_bounce_date"),
            "users.name as user_name",
            'adv_cheque_dtls.status'
        )
            ->join('adv_cheque_dtls', 'adv_cheque_dtls.transaction_id', '=', 'adv_mar_transactions.id')
            ->leftJoin('users', 'users.id', 'adv_cheque_dtls.user_id')
            ->whereIn('payment_mode', ['CHEQUE', 'DD'])
            ->where('adv_mar_transactions.ulb_id', $ulbId)
            ->orderby('adv_cheque_dtls.id', 'Desc');
    }

    public function deactivateTransaction($transactionId)
    {

        AdvMarTransaction::where('id', $transactionId)
            ->update(
                [
                    'status' => 0,
                ]
            );
    }

    public function getTransByTranNoBm($tranNo, $bmwWorkflow)
    {
        return DB::table('adv_mar_transactions as t')
            ->select(
                't.id as transaction_id',
                't.transaction_no as transaction_no',
                't.amount',
                't.payment_mode',
                DB::raw("TO_CHAR(t.transaction_date, 'DD-MM-YYYY') as transaction_date"),
                't.module_type',
                't.module_id',
                't.workflow_id',
                't.status',
                't.cheque_dd_no',
                't.bank_name',
                'mar_banqute_halls.application_no'
            )
            ->leftjoin('mar_banqute_halls', 'mar_banqute_halls.id', '=', 't.application_id')
            ->where('t.transaction_no', $tranNo)
            ->where('t.workflow_id', $bmwWorkflow)
            ->where('t.verify_status', 0)
            ->where('t.status', 1)
            ->get();
    }
    public function getDeactivatedTranBmHall($bmwWorkflow)
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
            'adv_mar_transactions.verify_status',
            'mar_banqute_halls.application_no',
            DB::raw("TO_CHAR(transaction_deactivate_dtls.deactive_date, 'DD-MM-YYYY') as deactive_date"),
            "transaction_deactivate_dtls.reason",
            "users.name as deactivated_by"
        )
            ->join('transaction_deactivate_dtls', 'transaction_deactivate_dtls.tran_id', '=', 'adv_mar_transactions.id')
            ->join('mar_banqute_halls', 'mar_banqute_halls.id', '=', 'adv_mar_transactions.application_id')
            ->join('users', 'users.id', '=', 'transaction_deactivate_dtls.deactivated_by')
            ->where('adv_mar_transactions.workflow_id', $bmwWorkflow)
            ->where("adv_mar_transactions.status", 0);
        //->get();
    }

    public function getTransByTranNo($tranNo, $bmwWorkflow)
    {
        return DB::table('adv_mar_transactions as t')
            ->select(
                't.id as transaction_id',
                't.transaction_no as transaction_no',
                't.amount',
                't.payment_mode',
                DB::raw("TO_CHAR(t.transaction_date, 'DD-MM-YYYY') as transaction_date"),
                't.module_type',
                't.module_id',
                't.workflow_id',
                't.status',
                't.cheque_dd_no',
                't.bank_name',
                'mar_lodges.application_no'
            )
            ->leftjoin('mar_lodges', 'mar_lodges.id', '=', 't.application_id')
            ->where('t.transaction_no', $tranNo)
            ->where('t.workflow_id', $bmwWorkflow)
            ->where('t.verify_status', 0)
            ->where('t.status', 1)
            ->get();
    }
    public function getDeactivatedTranLodge($lodgewWorkflow)
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
            'adv_mar_transactions.verify_status',
            'mar_lodges.application_no',
            DB::raw("TO_CHAR(transaction_deactivate_dtls.deactive_date, 'DD-MM-YYYY') as deactive_date"),
            "transaction_deactivate_dtls.reason",
            "users.name as deactivated_by"
        )
            ->join('transaction_deactivate_dtls', 'transaction_deactivate_dtls.tran_id', '=', 'adv_mar_transactions.id')
            ->join('mar_lodges', 'mar_lodges.id', '=', 'adv_mar_transactions.application_id')
            ->join('users', 'users.id', '=', 'transaction_deactivate_dtls.deactivated_by')
            ->where('adv_mar_transactions.workflow_id', $lodgewWorkflow)
            ->where("adv_mar_transactions.status", 0);
        //->get();
    }

    public function getTransByTranNoHs($tranNo, $bmwWorkflow)
    {
        return DB::table('adv_mar_transactions as t')
            ->select(
                't.id as transaction_id',
                't.transaction_no as transaction_no',
                't.amount',
                't.payment_mode',
                DB::raw("TO_CHAR(t.transaction_date, 'DD-MM-YYYY') as transaction_date"),
                't.module_type',
                't.module_id',
                't.workflow_id',
                't.status',
                't.cheque_dd_no',
                't.bank_name',
                'mar_hostel.application_no'
            )
            ->leftjoin('mar_hostel', 'mar_hostel.id', '=', 't.application_id')
            ->where('t.transaction_no', $tranNo)
            ->where('t.workflow_id', $bmwWorkflow)
            ->where('t.verify_status', 0)
            ->where('t.status', 1)
            ->get();
    }
    public function getDeactivatedTranHostel($hostelWorkflow)
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
            'adv_mar_transactions.verify_status',
            'mar_hostel.application_no',
            DB::raw("TO_CHAR(transaction_deactivate_dtls.deactive_date, 'DD-MM-YYYY') as deactive_date"),
            "transaction_deactivate_dtls.reason",
            "users.name as deactivated_by"
        )
            ->join('transaction_deactivate_dtls', 'transaction_deactivate_dtls.tran_id', '=', 'adv_mar_transactions.id')
            ->join('mar_hostel', 'mar_hostel.id', '=', 'adv_mar_transactions.application_id')
            ->join('users', 'users.id', '=', 'transaction_deactivate_dtls.deactivated_by')
            ->where('adv_mar_transactions.workflow_id', $hostelWorkflow)
            ->where("adv_mar_transactions.status", 0);
        //->get();
    }

    public function getTransByTranNoDh($tranNo, $bmwWorkflow)
    {
        return DB::table('adv_mar_transactions as t')
            ->select(
                't.id as transaction_id',
                't.transaction_no as transaction_no',
                't.amount',
                't.payment_mode',
                DB::raw("TO_CHAR(t.transaction_date, 'DD-MM-YYYY') as transaction_date"),
                't.module_type',
                't.module_id',
                't.workflow_id',
                't.status',
                't.cheque_dd_no',
                't.bank_name',
                'mar_dharamshalas.application_no'
            )
            ->leftjoin('mar_dharamshalas', 'mar_dharamshalas.id', '=', 't.application_id')
            ->where('t.transaction_no', $tranNo)
            ->where('t.workflow_id', $bmwWorkflow)
            ->where('t.verify_status', 0)
            ->where('t.status', 1)
            ->get();
    }
    public function getDeactivatedTranDh($dharamshalalWorkflow)
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
            'adv_mar_transactions.verify_status',
            'mar_dharamshalas.application_no',
            DB::raw("TO_CHAR(transaction_deactivate_dtls.deactive_date, 'DD-MM-YYYY') as deactive_date"),
            "transaction_deactivate_dtls.reason",
            "users.name as deactivated_by"
        )
            ->join('transaction_deactivate_dtls', 'transaction_deactivate_dtls.tran_id', '=', 'adv_mar_transactions.id')
            ->join('mar_dharamshalas', 'mar_dharamshalas.id', '=', 'adv_mar_transactions.application_id')
            ->join('users', 'users.id', '=', 'transaction_deactivate_dtls.deactivated_by')
            ->where('adv_mar_transactions.workflow_id', $dharamshalalWorkflow)
            ->where("adv_mar_transactions.status", 0);
        //->get();
    }


    public function getTransByTranNoSelf($tranNo, $bmwWorkflow)
    {
        return DB::table('adv_mar_transactions as t')
            ->select(
                't.id as transaction_id',
                't.transaction_no as transaction_no',
                't.amount',
                't.payment_mode',
                DB::raw("TO_CHAR(t.transaction_date, 'DD-MM-YYYY') as transaction_date"),
                't.module_type',
                't.module_id',
                't.workflow_id',
                't.status',
                't.cheque_dd_no',
                't.bank_name',
                'adv_selfadvertisements.application_no'
            )
            ->leftjoin('adv_selfadvertisements', 'adv_selfadvertisements.id', '=', 't.application_id')
            ->where('t.transaction_no', $tranNo)
            ->where('t.workflow_id', $bmwWorkflow)
            ->where('t.verify_status', 0)
            ->where('t.status', 1)
            ->get();
    }
    public function getDeactivatedTranself($Workflow)
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
            'adv_mar_transactions.verify_status',
            'adv_selfadvertisements.application_no',
            DB::raw("TO_CHAR(transaction_deactivate_dtls.deactive_date, 'DD-MM-YYYY') as deactive_date"),
            "transaction_deactivate_dtls.reason",
            "users.name as deactivated_by"
        )
            ->leftjoin('transaction_deactivate_dtls', 'transaction_deactivate_dtls.tran_id', '=', 'adv_mar_transactions.id')
            ->join('adv_selfadvertisements', 'adv_selfadvertisements.id', '=', 'adv_mar_transactions.application_id')
            ->join('users', 'users.id', '=', 'transaction_deactivate_dtls.deactivated_by')
            ->where('adv_mar_transactions.workflow_id', $Workflow)
            ->where("adv_mar_transactions.status", 0);
        //->get();
    }

    public function getTransByTranNovh($tranNo, $bmwWorkflow)
    {
        return DB::table('adv_mar_transactions as t')
            ->select(
                't.id as transaction_id',
                't.transaction_no as transaction_no',
                't.amount',
                't.payment_mode',
                DB::raw("TO_CHAR(t.transaction_date, 'DD-MM-YYYY') as transaction_date"),
                't.module_type',
                't.module_id',
                't.workflow_id',
                't.status',
                't.cheque_dd_no',
                't.bank_name',
                'adv_vehicles.application_no'
            )
            ->leftjoin('adv_vehicles', 'adv_vehicles.id', '=', 't.application_id')
            ->where('t.transaction_no', $tranNo)
            ->where('t.workflow_id', $bmwWorkflow)
            ->where('t.verify_status', 0)
            ->where('t.status', 1)
            ->get();
    }
    public function getDeactivatedTranVehicle($Workflow)
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
            'adv_mar_transactions.verify_status',
            'adv_vehicles.application_no',
            DB::raw("TO_CHAR(transaction_deactivate_dtls.deactive_date, 'DD-MM-YYYY') as deactive_date"),
            "transaction_deactivate_dtls.reason",
            "users.name as deactivated_by"
        )
            ->leftjoin('transaction_deactivate_dtls', 'transaction_deactivate_dtls.tran_id', '=', 'adv_mar_transactions.id')
            ->join('adv_vehicles', 'adv_vehicles.id', '=', 'adv_mar_transactions.application_id')
            ->join('users', 'users.id', '=', 'transaction_deactivate_dtls.deactivated_by')
            ->where('adv_mar_transactions.workflow_id', $Workflow)
            ->where("adv_mar_transactions.status", 0);
        //->get();
    }

    public function getTransByTranNoPvtLAnd($tranNo, $bmwWorkflow)
    {
        return DB::table('adv_mar_transactions as t')
            ->select(
                't.id as transaction_id',
                't.transaction_no as transaction_no',
                't.amount',
                't.payment_mode',
                DB::raw("TO_CHAR(t.transaction_date, 'DD-MM-YYYY') as transaction_date"),
                't.module_type',
                't.module_id',
                't.workflow_id',
                't.status',
                't.cheque_dd_no',
                't.bank_name',
                'adv_privatelands.application_no'
            )
            ->leftjoin('adv_privatelands', 'adv_privatelands.id', '=', 't.application_id')
            ->where('t.transaction_no', $tranNo)
            ->where('t.workflow_id', $bmwWorkflow)
            ->where('t.verify_status', 0)
            ->where('t.status', 1)
            ->get();
    }

    public function getDeactivatedTranPvtLand($Workflow)
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
            'adv_mar_transactions.verify_status',
            'adv_privatelands.application_no',
            DB::raw("TO_CHAR(transaction_deactivate_dtls.deactive_date, 'DD-MM-YYYY') as deactive_date"),
            "transaction_deactivate_dtls.reason",
            "users.name as deactivated_by"
        )
            ->leftjoin('transaction_deactivate_dtls', 'transaction_deactivate_dtls.tran_id', '=', 'adv_mar_transactions.id')
            ->join('adv_privatelands', 'adv_privatelands.id', '=', 'adv_mar_transactions.application_id')
            ->join('users', 'users.id', '=', 'transaction_deactivate_dtls.deactivated_by')
            ->where('adv_mar_transactions.workflow_id', $Workflow)
            ->where("adv_mar_transactions.status", 0);
        //->get();
    }

    public function getTransByTranNoAgency($tranNo, $bmwWorkflow)
    {
        return DB::table('adv_mar_transactions as t')
            ->select(
                't.id as transaction_id',
                't.transaction_no as transaction_no',
                't.amount',
                't.payment_mode',
                DB::raw("TO_CHAR(t.transaction_date, 'DD-MM-YYYY') as transaction_date"),
                't.module_type',
                't.module_id',
                't.workflow_id',
                't.status',
                't.cheque_dd_no',
                't.bank_name',
                'adv_agencies.application_no'
            )
            ->leftjoin('adv_agencies', 'adv_agencies.id', '=', 't.application_id')
            ->where('t.transaction_no', $tranNo)
            ->where('t.workflow_id', $bmwWorkflow)
            ->where('t.verify_status', 0)
            ->where('t.status', 1)
            ->get();
    }

    public function getDeactivatedTranAgency($Workflow)
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
            'adv_mar_transactions.verify_status',
            'adv_agencies.application_no',
            DB::raw("TO_CHAR(transaction_deactivate_dtls.deactive_date, 'DD-MM-YYYY') as deactive_date"),
            "transaction_deactivate_dtls.reason",
            "users.name as deactivated_by"
        )
            ->leftjoin('transaction_deactivate_dtls', 'transaction_deactivate_dtls.tran_id', '=', 'adv_mar_transactions.id')
            ->join('adv_agencies', 'adv_agencies.id', '=', 'adv_mar_transactions.application_id')
            ->join('users', 'users.id', '=', 'transaction_deactivate_dtls.deactivated_by')
            ->where('adv_mar_transactions.workflow_id', $Workflow)
            ->where("adv_mar_transactions.status", 0);
        //->get();
    }
}
