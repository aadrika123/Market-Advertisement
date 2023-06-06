<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdvRejectedHoarding extends Model
{
    use HasFactory;

    /**
     * | Get Application Reject List by Role Ids
     */
    public function listRejected($citizenId)
    {
        return AdvRejectedHoarding::where('citizen_id', $citizenId)
            ->select(
                'id',
                'application_no',
                'license_no',
                'application_date',
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
        return AdvRejectedHoarding::where('user_id', $userId)
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
     * | Get Application Reject List by Role Ids
     */
    public function rejectedApplication()
    {
        return AdvRejectedHoarding::select(
            'id',
            'application_no',
            'license_no',
            'application_date',
            'rejected_date',
        )
            ->orderByDesc('id')
            ->get();
    }

    /**
     * | Reject List For Report
     */
    public function rejectListForReport()
    {
        return AdvRejectedHoarding::select('id', 'application_no', 'application_date', 'application_type', 'license_year', 'ulb_id', DB::raw("'Reject' as application_status"));
    }
}
