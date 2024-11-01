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
        return AdvRejectedSelfadvertisement::select(
                'adv_rejected_selfadvertisements.id',
                'adv_rejected_selfadvertisements.application_no',
                DB::raw("TO_CHAR(adv_rejected_selfadvertisements.application_date, 'DD-MM-YYYY') as application_date"),
                'adv_rejected_selfadvertisements.applicant',
                'adv_rejected_selfadvertisements.application_type',
                'adv_rejected_selfadvertisements.entity_name',
                'adv_rejected_selfadvertisements.entity_address',
                'adv_rejected_selfadvertisements.payment_status',
                'adv_rejected_selfadvertisements.rejected_date',
                'um.ulb_name as ulb_name',
                'workflow_tracks.message as reason'
            )
            ->join('ulb_masters as um', 'um.id', '=', 'adv_rejected_selfadvertisements.ulb_id')
            ->leftJoin('workflow_tracks', function ($join) {
                $join->on('workflow_tracks.ref_table_id_value', '=', 'adv_rejected_selfadvertisements.id')
                    ->where('workflow_tracks.verification_status', 0)
                    ->where('workflow_tracks.message', "<>", null);
            })
            ->where('adv_rejected_selfadvertisements.citizen_id', $citizenId)
            ->orderByDesc('adv_rejected_selfadvertisements.id')
            ->get();
    }

    /**
     * | Get Application Reject List by Login JSK
     */
    public function listJskRejectedApplication()
    {
        return AdvRejectedSelfadvertisement::select(
            'adv_rejected_selfadvertisements.id',
            'application_no',
            DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"),
            'applicant',
            'entity_name',
            'entity_address',
            'payment_status',
            'rejected_date',
            'mobile_no',
            'wr.role_name as rejected_by',
            'remarks as reason',
            DB::raw("CASE WHEN user_id IS NOT NULL THEN 'jsk' ELSE 'citizen' END AS applied_by")
        )
            ->join('wf_roles as wr', 'wr.id', '=', 'adv_rejected_selfadvertisements.current_role_id')
            ->orderByDesc('adv_rejected_selfadvertisements.id');
        //->get();
    }

    /**
     * | Get Application Reject List by Role Ids
     */
    public function rejectedApplication()
    {
        return AdvRejectedSelfadvertisement::select(
            'id',
            'application_no',
            DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"),
            'applicant',
            'entity_name',
            'ulb_id',
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
            DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"),
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
        return AdvRejectedSelfadvertisement::select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', 'ulb_id', 'license_year', 'display_type','entity_name', DB::raw("'Reject' as application_status"));
    }
}
