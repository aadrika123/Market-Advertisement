<?php

namespace App\Models\Markets;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
            'id',
            'application_no',
            'application_date',
            'entity_address',
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
        return MarLodge::where('id', $id)
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
            // Lodge Table Update
            $mMarLodge = MarLodge::find($req->applicationId);
            $mMarLodge->payment_status = $req->status;
            $pay_id=$mMarLodge->payment_id = "Cash-$req->applicationId/".time();
            // $mAdvCheckDtls->remarks = $req->remarks;
            $mMarLodge->payment_date = Carbon::now();
            $mMarLodge->payment_details = "By Cash";
            $mMarLodge->save();
            $renewal_id = $mMarLodge->last_renewal_id;

            // Renewal Table Updation
            $mMarLodgeRenewal = MarLodgeRenewal::find($renewal_id);
            $mMarLodgeRenewal->payment_status = 1;
            $mMarLodgeRenewal->payment_id =  $pay_id;
            $mMarLodgeRenewal->payment_date = Carbon::now();
            $mMarLodgeRenewal->payment_details = "By Cash";
            return $mMarLodgeRenewal->save();
        }
    }
}
