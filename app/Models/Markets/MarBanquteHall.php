<?php

namespace App\Models\Markets;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarBanquteHall extends Model
{
    use HasFactory;

    
    /**
     * Summary of allApproveList
     * @return void
     */
    public function allApproveList()
    {
        return MarBanquteHall::select(
            'id',
            'temp_id',
            'application_no',
            'application_date',
            // 'entity_address',
            // 'old_application_no',
            'payment_status',
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
        return MarBanquteHall::where('id', $id)
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

    public function paymentByCash($req){

        if ($req->status == '1') {
            // Self Advertisement Table Update
            $mMarBanquteHall = MarBanquteHall::find($req->applicationId);
            $mMarBanquteHall->payment_status = $req->status;
            $pay_id=$mMarBanquteHall->payment_id = "Cash-$req->applicationId/".time();
            // $mAdvCheckDtls->remarks = $req->remarks;
            $mMarBanquteHall->payment_date = Carbon::now();
            $mMarBanquteHall->payment_details = "By Cash";
            $mMarBanquteHall->save();
            $renewal_id = $mMarBanquteHall->last_renewal_id;

            // Renewal Table Updation
            $mMarBanquteHallRenewal = MarBanquteHallRenewal::find($renewal_id);
            $mMarBanquteHallRenewal->payment_status = 1;
            $mMarBanquteHallRenewal->payment_id =  $pay_id;
            $mMarBanquteHallRenewal->payment_date = Carbon::now();
            $mMarBanquteHallRenewal->payment_details = "By Cash";
            return $mMarBanquteHallRenewal->save();
        }
    }
}
