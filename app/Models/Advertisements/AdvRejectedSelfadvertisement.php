<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    /**
     * | Get Application Reject List by Role Ids
     */
    public function rejectedApplication()
    {
        return AdvRejectedSelfadvertisement::select(
            'id',
            'application_no',
            'application_date',
            'applicant',
            'entity_name',
            'entity_address',
            'payment_status',
            'rejected_date',
            'entity_ward_id',
            'application_type',
            DB::raw("'Rejected' as applicationStatus"),
        )
            ->orderByDesc('id')
            ->get();
    }

    /**
     * | Get Application Reject List by Role Ids
     */
    public function rejectedApplicationForReport()
    {
        return AdvRejectedSelfadvertisement::select(
            'id',
            'application_no',
            'application_date',
            'applicant',
            'entity_name',
            'entity_address',
            'payment_status',
            'rejected_date',
            'entity_ward_id',
            'application_type',
            DB::raw("'Rejected' as applicationStatus"),
        )
            ->orderByDesc('id')->get();
    }
    
    /**
     * | Reject List For Report
     */
    public function rejectListForReport()
    {
        return AdvRejectedSelfadvertisement::select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', 'ulb_id', 'license_year', 'display_type', DB::raw("'Reject' as application_status"));
    }
}
