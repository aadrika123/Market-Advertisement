<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvRejectedSelfadvertisement extends Model
{
    use HasFactory;

     /**
     * | Get Application Approve List by Role Ids
     */
    public function rejectedList($citizenId)
    {
        return AdvRejectedSelfadvertisement::where('citizen_id', $citizenId)
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
                'rejected_date',
            )
            ->orderByDesc('temp_id')
            ->get();
    }
    
     /**
     * | Get Application Approve List by Role Ids
     */
    public function jskRejectedList($userId)
    {
        return AdvRejectedSelfadvertisement::where('user_id', $userId)
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
                'rejected_date',
            )
            ->orderByDesc('temp_id')
            ->get();
    }
}
