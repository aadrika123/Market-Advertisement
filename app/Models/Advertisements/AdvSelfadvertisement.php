<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class AdvSelfadvertisement extends Model
{
    use HasFactory;
    
   /**
     * | Get Application Approve List by Role Ids
     */
    public function approvedList($citizenId)
    {
        return AdvSelfadvertisement::where('citizen_id', $citizenId)
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
            )
            ->orderByDesc('temp_id')
            ->get();
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
            )
            ->orderByDesc('temp_id')
            ->get();
    }
}
