<?php

namespace App\Models\Markets;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MarRejectedLodge extends Model
{
    use HasFactory;

    /**
     * | Get Application Reject List by Role Ids
     */
    public function listRejected($citizenId)
    {
        return MarRejectedLodge::where('mar_rejected_lodges.citizen_id', $citizenId)
            ->select(
                'mar_rejected_lodges.id',
                'mar_rejected_lodges.application_no',
                'mar_rejected_lodges.application_date',
                'mar_rejected_lodges.entity_address',
                'mar_rejected_lodges.rejected_date',
                'mar_rejected_lodges.citizen_id',
                'um.ulb_name as ulb_name',
                'workflow_tracks.message as remarks',
                'mar_rejected_lodges.entity_name',
                'mar_rejected_lodges.entity_address'
            )
            ->join('ulb_masters as um', 'um.id', '=', 'mar_rejected_lodges.ulb_id')
            ->leftJoin('workflow_tracks', function ($join) use ($citizenId) {
                $join->on('workflow_tracks.ref_table_id_value', 'mar_rejected_lodges.id')
                    ->where('workflow_tracks.status', true)
                    ->where('workflow_tracks.message', '<>', null)
                    ->where('workflow_tracks.verification_status', 3)
                    ->where('workflow_tracks.workflow_id', 25)
                    ->where('workflow_tracks.module_id', 5)
                    ->where('workflow_tracks.citizen_id', $citizenId);
            })
            ->orderByDesc('mar_rejected_lodges.id')
            ->get();
    }

    /**
     * | Get All Application Reject List
     */
    public function rejectedApplication()
    {
        return MarRejectedLodge::select(
            'id',
            'application_no',
            'application_date',
            'entity_address',
            'rejected_date',
            'citizen_id',
            'ulb_id',
        )
            ->orderByDesc('id')
            ->get();
    }

    /**
     * | Rejected List For Report
     */
    public function rejectedListForReport()
    {
        return MarRejectedLodge::select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'lodge_type', 'license_year', 'ulb_id', DB::raw("'Reject' as application_status"));
    }

    public function listjskRejectedApplication()
    {
        return MarRejectedLodge::select(
            'mar_rejected_lodges.id',
            'application_no',
            'applicant',
            DB::raw("TO_CHAR(mar_rejected_lodges.application_date, 'DD-MM-YYYY') as application_date"),
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
        ->join('wf_roles as wr', 'wr.id', '=', 'mar_rejected_lodges.current_role_id');
    }
}
