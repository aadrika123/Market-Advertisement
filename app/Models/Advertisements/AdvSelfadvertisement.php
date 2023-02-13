<?php

namespace App\Models\Advertisements;

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
    public function approvedList($citizenId, $userType)
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
    public function jskApprovedList($userId)
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
    public function detailsForPayments($id)
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
}
