<?php

namespace App\Models\Markets;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
            'id',
            'application_no',
            'application_date',
            'entity_address',
            // 'old_application_no',
            // 'payment_status',
            'payment_amount',
            'approve_date',
            'citizen_id',
        )
            ->orderByDesc('id')
            ->get();
    }

    /**
     * | Get Application Approve List by Role Ids
     */
    public function listApproved($citizenId, $userType)
    {
        $allApproveList = $this->allApproveList();
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
                // 'applicant',
                'entity_name',
                'payment_amount',
                'approve_date',
                'ulb_id',
                'workflow_id',
            )
            ->first();
    }

    
    public function paymentByCash($req){

        if ($req->status == '1') {
            // Dharamshala Table Update
            $mMarDharamshala = MarDharamshala::find($req->applicationId);
            $mMarDharamshala->payment_status = $req->status;
            $pay_id=$mMarDharamshala->payment_id = "Cash-$req->applicationId/".time();
            // $mAdvCheckDtls->remarks = $req->remarks;
            $mMarDharamshala->payment_date = Carbon::now();
            $mMarDharamshala->payment_details = "By Cash";
            $mMarDharamshala->save();
            $renewal_id = $mMarDharamshala->last_renewal_id;

            // Renewal Table Updation
            $mMarDharamshalaRenewal = MarDharamshalaRenewal::find($renewal_id);
            $mMarDharamshalaRenewal->payment_status = 1;
            $mMarDharamshalaRenewal->payment_id =  $pay_id;
            $mMarDharamshalaRenewal->payment_date = Carbon::now();
            $mMarDharamshalaRenewal->payment_details = "By Cash";
            return $mMarDharamshalaRenewal->save();
        }
    }
}
