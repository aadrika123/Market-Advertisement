<?php

namespace App\Models\Markets;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MarRejectedBanquteHall extends Model
{
    use HasFactory;

    /**
     * | Get Application Reject List by Role Ids
     */
    public function listRejected($citizenId)
    {
        return MarRejectedBanquteHall::where('mar_rejected_banqute_halls.citizen_id', $citizenId)
            ->select(
                'mar_rejected_banqute_halls.id',
                'mar_rejected_banqute_halls.application_no',
                'mar_rejected_banqute_halls.application_date',
                'mar_rejected_banqute_halls.rejected_date',
                'um.ulb_name as ulb_name',
                'workflow_tracks.message as remarks',
                'mar_rejected_banqute_halls.entity_name',
                'mar_rejected_banqute_halls.entity_address',
            )
            ->join('ulb_masters as um', 'um.id', '=', 'mar_rejected_banqute_halls.ulb_id')
            ->leftJoin('workflow_tracks', function ($join) use ($citizenId) {
                $join->on('workflow_tracks.ref_table_id_value', 'mar_rejected_banqute_halls.id')
                    ->where('workflow_tracks.status', true)
                    ->where('workflow_tracks.message', '<>', null)
                    ->where('workflow_tracks.verification_status', 3)
                    ->where('workflow_tracks.workflow_id', 23)
                    ->where('workflow_tracks.module_id', 5)
                    ->where('workflow_tracks.citizen_id', $citizenId);
            })
            ->orderByDesc('mar_rejected_banqute_halls.id')
            ->get();
    }

    /**
     * | Get Rejected application list
     */
    public function rejectedApplication()
    {
        return MarRejectedBanquteHall::select(
            'id',
            'application_no',
            'application_date',
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
        return MarRejectedBanquteHall::select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', 'rule', 'hall_type', 'ulb_id', 'license_year', 'organization_type', DB::raw("'Reject' as application_status"));
    }

    public function listjskRejectedApplication()
    {
        return MarRejectedBanquteHall::select(
            'mar_rejected_banqute_halls.id',
            'application_no',
            'applicant',
            DB::raw("TO_CHAR(mar_rejected_banqute_halls.application_date, 'DD-MM-YYYY') as application_date"),
            'application_type',
            'entity_ward_id',
            'rule','entity_name',
            'license_year',
            'ulb_id',
            'mobile as mobile_no',
            DB::raw("TO_CHAR(rejected_date,'DD-MM-YYYY') as rejected_date"),
            DB::raw("CASE WHEN user_id IS NOT NULL THEN 'jsk' ELSE 'citizen' END AS applied_by"),
            'wr.role_name as rejected_by',
            'remarks as reason'
        )
        ->join('wf_roles as wr', 'wr.id', '=', 'mar_rejected_banqute_halls.current_role_id');
    }
}
