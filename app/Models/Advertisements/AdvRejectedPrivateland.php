<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvRejectedPrivateland extends Model
{
    use HasFactory;

     /**
     * | Get Application Reject List by Role Ids
     */
    public function rejectedList($citizenId)
    {
        return AdvRejectedPrivateland::where('citizen_id', $citizenId)
            ->select(
                'id',
                'temp_id',
                'application_no',
                'application_date',
                // 'entity_address',
                // 'old_application_no',
                // 'payment_status',
                'rejected_date',
            )
            ->orderByDesc('temp_id')
            ->get();
    }
    
     /**
     * | Get Application Reject List by Login JSK
     */
    public function jskRejectedList($userId)
    {
        return AdvRejectedPrivateland::where('user_id', $userId)
            ->select(
                'id',
                'temp_id',
                'application_no',
                'application_date',
                // 'entity_address',
                // 'old_application_no',
                // 'payment_status',
                'rejected_date',
            )
            ->orderByDesc('temp_id')
            ->get();
    }
}
