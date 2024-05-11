<?php

namespace App\Models\Pet;

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
            ->where('status', 1)
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
}
