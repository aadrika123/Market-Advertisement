<?php

namespace App\Models\Markets;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return MarHostel::where('id', $id)
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
            // Hostel Table Update
            $mMarHostel = MarHostel::find($req->applicationId);
            $mMarHostel->payment_status = $req->status;
            $pay_id=$mMarHostel->payment_id = "Cash-$req->applicationId/".time();
            // $mAdvCheckDtls->remarks = $req->remarks;
            $mMarHostel->payment_date = Carbon::now();
            $mMarHostel->payment_details = "By Cash";
            $mMarHostel->save();
            $renewal_id = $mMarHostel->last_renewal_id;

            // Renewal Table Updation
            $mMarHostelRenewal = MarHostelRenewal::find($renewal_id);
            $mMarHostelRenewal->payment_status = 1;
            $mMarHostelRenewal->payment_id =  $pay_id;
            $mMarHostelRenewal->payment_date = Carbon::now();
            $mMarHostelRenewal->payment_details = "By Cash";
            return $mMarHostelRenewal->save();
        }
    }
}
