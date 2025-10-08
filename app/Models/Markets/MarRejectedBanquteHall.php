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

    public function listjskRejectedApplication($ulbId)
    {
        return MarRejectedBanquteHall::select(
            'mar_rejected_banqute_halls.id',
            'application_no',
            'applicant',
            DB::raw("TO_CHAR(mar_rejected_banqute_halls.application_date, 'DD-MM-YYYY') as application_date"),
            'application_type',
            'entity_ward_id',
            'rule',
            'entity_name',
            'license_year',
            'ulb_id',
            'mobile as mobile_no',
            DB::raw("TO_CHAR(rejected_date,'DD-MM-YYYY') as rejected_date"),
            DB::raw("CASE WHEN user_id IS NOT NULL THEN 'jsk' ELSE 'citizen' END AS applied_by"),
            'wr.role_name as rejected_by',
            'remarks as reason'
        )
            ->where('mar_rejected_banqute_halls.ulb_id', $ulbId)
            ->join('wf_roles as wr', 'wr.id', '=', 'mar_rejected_banqute_halls.current_role_id');
    }

    public function getDetailsById($applicationId)
    {
        return MarRejectedBanquteHall::select(
            'mar_rejected_banqute_halls.*',
            'mar_rejected_banqute_halls.organization_type as organization_type_id',
            'mar_rejected_banqute_halls.land_deed_type as land_deed_type_id',
            'mar_rejected_banqute_halls.water_supply_type as water_supply_type_id',
            'mar_rejected_banqute_halls.hall_type as hall_type_id',
            'mar_rejected_banqute_halls.electricity_type as electricity_type_id',
            'mar_rejected_banqute_halls.security_type as security_type_id',
            'ly.string_parameter as license_year_name',
            'rw.ward_name as resident_ward_name',
            'ew.ward_name as entity_ward_name',
            'ot.string_parameter as organization_type_name',
            'ldt.string_parameter as land_deed_type_name',
            'ldt.string_parameter as water_supply_type_name',
            'ht.string_parameter as hall_type_name',
            'et.string_parameter as electricity_type_name',
            'st.string_parameter as security_type_name',
            'pw.ward_name as permanent_ward_name',
            'ulb.ulb_name',
            DB::raw("'Banquet/Marriage Hall' as headerTitle")
        )
            ->leftJoin('ref_adv_paramstrings as ly', 'ly.id', '=', DB::raw('mar_rejected_banqute_halls.license_year::int'))
            ->leftJoin('ulb_ward_masters as rw', 'rw.id', '=', DB::raw('mar_rejected_banqute_halls.entity_ward_id::int'))
            ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', DB::raw('mar_rejected_banqute_halls.organization_type::int'))
            ->leftJoin('ref_adv_paramstrings as ldt', 'ldt.id', '=', DB::raw('mar_rejected_banqute_halls.land_deed_type::int'))
            ->leftJoin('ref_adv_paramstrings as ht', 'ht.id', '=', DB::raw('mar_rejected_banqute_halls.hall_type::int'))
            ->leftJoin('ref_adv_paramstrings as wt', 'wt.id', '=', DB::raw('mar_rejected_banqute_halls.water_supply_type::int'))
            ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', DB::raw('mar_rejected_banqute_halls.electricity_type::int'))
            ->leftJoin('ref_adv_paramstrings as st', 'st.id', '=', DB::raw('mar_rejected_banqute_halls.security_type::int'))
            ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'mar_rejected_banqute_halls.entity_ward_id')
            ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'mar_rejected_banqute_halls.permanent_ward_id')
            ->leftJoin('ulb_masters as ulb', 'ulb.id', '=', 'mar_rejected_banqute_halls.ulb_id')
            ->where('mar_rejected_banqute_halls.id', $applicationId);
    }
}
