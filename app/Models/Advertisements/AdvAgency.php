<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvAgency extends Model
{
    use HasFactory;

    public function agencyDetails($id){
        return AdvAgency::where('citizen_id', $id)->first();
    }



    /**
     * Summary of allApproveList
     * @return void
     */
    public function allApproveList(){
        return AdvAgency::select(
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
    public function approvedList($citizenId,$userType)
    {
        $allApproveList = $this->allApproveList();
        if($userType=='Citizen'){
            return collect($allApproveList->where('citizen_id', $citizenId))->values();;
        }else{
            return collect($allApproveList)->values();;
        }
    }

       /**
     * | Get Application Approve List by Role Ids
     */
    public function jskApprovedList($userId)
    {
        return AdvAgency::where('user_id', $userId)
            ->select(
                'id',
                'temp_id',
                'application_no',
                'application_date',
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
        return AdvAgency::where('id', $id)
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
}
