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
    public function listRejected($citizenId)
    {
        return AdvRejectedPrivateland::where('citizen_id', $citizenId)
            ->select(
                'id',
                'application_no',
                'application_date',
                // 'entity_address',
                // 'old_application_no',
                // 'payment_status',
                'rejected_date',
            )
            ->orderByDesc('id')
            ->get();
    }
    
     /**
     * | Get Application Reject List by Login JSK
     */
    public function listJskRejectedApplication($userId)
    {
        return AdvRejectedPrivateland::where('user_id', $userId)
            ->select(
                'id',
                'application_no',
                'application_date',
                // 'entity_address',
                // 'old_application_no',
                // 'payment_status',
                'rejected_date',
            )
            ->orderByDesc('id')
            ->get();
    }

     /**
     * | Get All Application Reject List
     */
    public function rejectedApplication()
    {
        return AdvRejectedPrivateland::select(
                'id',
                'application_no',
                'application_date',
                // 'entity_address',
                // 'old_application_no',
                // 'payment_status',
                'rejected_date',
            )
            ->orderByDesc('id')
            ->get();
    }
}
