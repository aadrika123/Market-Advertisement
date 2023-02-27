<?php

namespace App\Models\Advertisements;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvPrivateland extends Model
{
    use HasFactory;


    /**
     * | Get Application All Approve List
     */
    public function allApproveList(){
        return AdvPrivateland::select(
            'id',
            'temp_id',
            'application_no',
            'application_date',
            'payment_amount',
            'approve_date',
            'citizen_id',
            'user_id',
        )
        ->orderByDesc('temp_id')
        ->get();
    }

    /**
     * | Get Application Approve List by Role Ids
     */
    public function listApproved($citizenId,$userType)
    {
        $allApproveList = $this->allApproveList();
        if ($userType == 'Citizen') {
            return  collect($allApproveList->where('citizen_id', $citizenId))->values();
        }else{
            return collect($allApproveList)->values();
        }
    }
    

    /**
     * | Get Application Approve List by Role Ids
     */
    public function listjskApprovedApplication($userId)
    {
        return AdvPrivateland::where('user_id', $userId)
            ->select(
                'id',
                'temp_id',
                'application_no',
                'application_date',
                // 'entity_address',
                // 'old_application_no',
                //  'payment_status',
                'payment_amount',
                'approve_date',
            )
            ->orderByDesc('temp_id')
            ->get();
    }


    /**
     * | Get Application Details FOr Payments
     */
    public function getApplicationDetailsForPayment($id)
    {
        return AdvPrivateland::where('id', $id)
            ->select(
                'id',
                'temp_id',
                'application_no',
                'application_date',
                // 'applicant',
                'entity_name',
                'payment_status',
                'payment_amount',
                'approve_date',
                'ulb_id',
                'workflow_id',
            )
            ->first();
    }

    public function getPaymentDetails($paymentId){
        $details=AdvPrivateland::select('payment_details')
        ->where('payment_id', $paymentId)
        ->first();
       return json_decode($details->payment_details);
    }

    
    public function paymentByCash($req){

        if ($req->status == '1') {
            // Self Privateland Table Update
            $mAdvPrivateland = AdvPrivateland::find($req->applicationId);        // Application ID
            $mAdvPrivateland->payment_status = $req->status;
            $pay_id=$mAdvPrivateland->payment_id = "Cash-$req->applicationId/".time();
            // $mAdvCheckDtls->remarks = $req->remarks;
            $mAdvPrivateland->payment_date = Carbon::now();
            $mAdvPrivateland->payment_details = "By Cash";
            $mAdvPrivateland->save();
            $renewal_id = $mAdvPrivateland->last_renewal_id;


            // Privateland Renewal Table Updation
            $mAdvPrivatelandRenewal = AdvPrivatelandRenewal::find($renewal_id);
            $mAdvPrivatelandRenewal->payment_status = 1;
            $mAdvPrivatelandRenewal->payment_id =  $pay_id;
            $mAdvPrivatelandRenewal->payment_date = Carbon::now();
            $mAdvPrivatelandRenewal->payment_details = "By Cash";
            return $mAdvPrivatelandRenewal->save();
        }
    }

}
