<?php

namespace App\Models\Advertisements;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvVehicle extends Model
{
    use HasFactory;


    public function allApproveList()
    {
        return AdvVehicle::select(
            'id',
            'temp_id',
            'application_no',
            'application_date',
            'applicant',
            'entity_name',
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
    public function listApproved($citizenId,$userType)
    {

        $allApproveList = $this->allApproveList();
        if($userType=='Citizen'){
            return collect($allApproveList->where('citizen_id', $citizenId))->values();
        }else{
            return collect($allApproveList)->values(); 
        }
        // return AdvVehicle::where('citizen_id', $citizenId)
        //     ->select(
        //         'id',
        //         'temp_id',
        //         'application_no',
        //         'application_date',
        //         'applicant',
        //         'entity_name',
        //         // 'entity_address',
        //         // 'old_application_no',
        //         'payment_status',
        //         'payment_amount',
        //         'approve_date',
        //     )
        //     ->orderByDesc('temp_id')
        //     ->get();
    }

    /**
     * | Get Application Approve List by Role Ids
     */
    public function listjskApprovedApplication($userId)
    {
        return AdvVehicle::where('user_id', $userId)
            ->select(
                'id',
                'temp_id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                // 'entity_address',
                // 'old_application_no',
                'payment_status',
                'payment_amount',
                'approve_date',
            )
            ->orderByDesc('temp_id')
            ->get();
    }

    /**
     * | Get Application Details FOr Payments
     */
    public function detailsForPayments($id)
    {
        return AdvVehicle::where('id', $id)
            ->select(
                'id',
                'temp_id',
                'application_no',
                'application_date',
                'applicant',
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
        $details=AdvVehicle::select('payment_details')
        ->where('payment_id', $paymentId)
        ->first();
       return json_decode($details->payment_details);
    }

    
    public function paymentByCash($req){

        if ($req->status == '1') {
            // Self Privateland Table Update
            $mAdvVehicle = AdvVehicle::find($req->applicationId);        // Application ID
            $mAdvVehicle->payment_status = $req->status;
            $pay_id=$mAdvVehicle->payment_id = "Cash-$req->applicationId/".time();
            // $mAdvCheckDtls->remarks = $req->remarks;
            $mAdvVehicle->payment_date = Carbon::now();
            $mAdvVehicle->payment_details = "By Cash";
            $mAdvVehicle->save();
            $renewal_id = $mAdvVehicle->last_renewal_id;

            // Privateland Renewal Table Updation
            $mAdvVehicleRenewal = AdvVehicleRenewal::find($renewal_id);
            $mAdvVehicleRenewal->payment_status = 1;
            $mAdvVehicleRenewal->payment_id =  $pay_id;
            $mAdvVehicleRenewal->payment_date = Carbon::now();
            $mAdvVehicleRenewal->payment_details = "By Cash";
            return $mAdvVehicleRenewal->save();
        }
    }


}
