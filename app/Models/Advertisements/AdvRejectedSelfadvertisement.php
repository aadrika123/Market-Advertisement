<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvRejectedSelfadvertisement extends Model
{
    use HasFactory;

     /**
     * | Get Application Reject List by Role Ids
     */
    public function listRejected($citizenId)
    {
        return AdvRejectedSelfadvertisement::where('citizen_id', $citizenId)
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
                'payment_status',
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
        return AdvRejectedSelfadvertisement::where('user_id', $userId)
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
                'payment_status',
                'rejected_date',
            )
            ->orderByDesc('id')
            ->get();
    }
}
