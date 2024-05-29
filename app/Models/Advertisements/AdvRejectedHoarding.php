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
    public function listRejected($citizenId, $moduleId, $workflowId)
    {
        return AdvRejectedHoarding::select(
            'adv_rejected_hoardings.id',
            'adv_rejected_hoardings.application_no',
            'adv_rejected_hoardings.license_no',
            DB::raw("TO_CHAR(adv_rejected_hoardings.application_date, 'DD-MM-YYYY') as application_date"),
            'rejected_date',
            "workflow_tracks.message as reason",
            "workflow_tracks.workflow_id"
        )
            ->leftJoin('workflow_tracks', function ($join) use ($workflowId ,$moduleId) {
                $join->on('workflow_tracks.ref_table_id_value', 'adv_rejected_hoardings.id')
                    ->where('workflow_tracks.status', true)
                    ->where('workflow_tracks.message', '<>', null)
                    ->where('workflow_tracks.verification_status', 3)
                    ->where('workflow_tracks.workflow_id', $workflowId)
                    ->where('workflow_tracks.module_id', $moduleId);
                    
            })
            ->where('adv_rejected_hoardings.citizen_id', $citizenId)
            ->orderByDesc('id');
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
                DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"),
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
            DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"),
            'rejected_date',
            'ulb_id',
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

    /**
     * | Get Last 3 Rejected Application
     */
    public function lastThreeRejectRecord($citizenId)
    {
        return AdvRejectedHoarding::where('citizen_id', $citizenId)
            ->select(
                'id',
                'application_no',
                'license_no',
                DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"),
                'rejected_date',
            )
            ->orderByDesc('id')
            ->limit(3)
            ->get();
    }
}
