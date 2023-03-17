<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvRejectedAgencyLicense extends Model
{
    use HasFactory;

         
     /**
     * | Get Application Reject List by Role Ids
     */
    public function listRejectedLicense($citizenId)
    {
        return AdvRejectedAgencyLicense::where('citizen_id', $citizenId)
            ->select(
                'id',
                'application_no',
                'application_date',
                'rejected_date',
            )
            ->orderByDesc('id')
            ->get();
    }
    
     /**
     * | Get Application Reject List by Login JSK
     */
    public function listJskRejectedLicenseApplication($userId)
    {
        return AdvRejectedAgencyLicense::where('user_id', $userId)
            ->select(
                'id',
                'application_no',
                'application_date',
                'rejected_date',
            )
            ->orderByDesc('id')
            ->get();
    }
}
