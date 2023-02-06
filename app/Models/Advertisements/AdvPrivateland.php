<?php

namespace App\Models\Advertisements;

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
        if ($userType == 'Citizen') {
            return  $allApproveList->where('citizen_id', $citizenId);
        }else{
            return $allApproveList;
        }
    }
    

    /**
     * | Get Application Approve List by Role Ids
     */
    public function jskApprovedList($userId)
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
    public function detailsForPayments($id)
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
}
