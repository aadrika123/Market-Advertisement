<?php

namespace App\Models\Markets;

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
}
