<?php

namespace App\Models\Advertisements;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class AdvSelfadvertisement extends Model
{
    use HasFactory;


    public function allApproveList()
    {

        return AdvSelfadvertisement::select(
            'id',
            'temp_id',
            'application_no',
            'application_date',
            'applicant',
            'entity_name',
            'entity_address',
            'old_application_no',
            'payment_status',
            'payment_amount',
            'approve_date',
            'ulb_id',
            'workflow_id',
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
            return collect($allApproveList->where('citizen_id', $citizenId))->values();
        } else {
            return collect($allApproveList)->values();
        }
        // return AdvSelfadvertisement::where('citizen_id', $citizenId)
        //     ->select(
        //         'id',
        //         'temp_id',
        //         'application_no',
        //         'application_date',
        //         'applicant',
        //         'entity_name',
        //         'entity_address',
        //         'old_application_no',
        //         'payment_status',
        //         'payment_amount',
        //         'approve_date',
        //         'ulb_id',
        //         'workflow_id',
        //     )
        //     ->orderByDesc('temp_id')
        //     ->get();
    }

    /**
     * | Get Application Approve List by Role Ids
     */
    public function listJskApprovedApplication($userId)
    {
        return AdvSelfadvertisement::where('user_id', $userId)
            ->select(
                'id',
                'temp_id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
                'old_application_no',
                'payment_status',
                'payment_amount',
                'approve_date',
                'ulb_id',
                'workflow_id',
            )
            ->orderByDesc('temp_id')
            ->get();
    }


    /**
     * | Get Application Details For Payments
     */
    public function applicationDetailsForPayment($id)
    {
        return AdvSelfadvertisement::where('id', $id)
            ->select(
                'id',
                'temp_id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
                'old_application_no',
                'payment_status',
                'payment_amount',
                'approve_date',
                'ulb_id',
                'workflow_id',
            )
            ->first();
    }

    
    public function getPaymentDetails($paymentId){
        $details=AdvSelfadvertisement::select('payment_details')
        ->where('payment_id', $paymentId)
        ->first();
       return json_decode($details->payment_details);
    }

    public function paymentByCash($req){

        if ($req->status == '1') {
            // Self Advertisement Table Update
            $mAdvSelfadvertisement = AdvSelfadvertisement::find($req->applicationId);
            $mAdvSelfadvertisement->payment_status = $req->status;
            $pay_id=$mAdvSelfadvertisement->payment_id = "Cash-$req->applicationId/".time();
            // $mAdvCheckDtls->remarks = $req->remarks;
            $mAdvSelfadvertisement->payment_date = Carbon::now();
            $mAdvSelfadvertisement->payment_details = "By Cash";
            $mAdvSelfadvertisement->save();
            $renewal_id = $mAdvSelfadvertisement->last_renewal_id;


            // Renewal Table Updation
            $mAdvSelfAdvertRenewal = AdvSelfadvetRenewal::find($renewal_id);
            $mAdvSelfAdvertRenewal->payment_status = 1;
            $mAdvSelfAdvertRenewal->payment_id =  $pay_id;
            $mAdvSelfAdvertRenewal->payment_date = Carbon::now();
            $mAdvSelfAdvertRenewal->payment_details = "By Cash";
            return $mAdvSelfAdvertRenewal->save();
        }
    }
}
