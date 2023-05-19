<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdvRejectedVehicle extends Model
{
    use HasFactory;

    /**
     * | Get Application Reject List by Role Ids
     */
    public function listRejected($citizenId)
    {
        return AdvRejectedVehicle::where('citizen_id', $citizenId)
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
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
        return AdvRejectedVehicle::where('user_id', $userId)
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
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
        return AdvRejectedVehicle::select(
            'id',
            'application_no',
            'application_date',
            'applicant',
            'entity_name',
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
        return AdvRejectedVehicle::select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'ulb_id', DB::raw("'Reject' as application_status"));
    }
}
