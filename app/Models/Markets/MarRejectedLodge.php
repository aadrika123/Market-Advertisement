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
            ->join('wf_roles as wr', 'wr.id', '=', 'mar_rejected_lodges.current_role_id');
    }

    public function getDetailsById($applicationId)
    {
        return MarRejectedLodge::select(
            'mar_rejected_lodges.*',
            'mar_rejected_lodges.lodge_type as lodge_type_id',
            'mar_rejected_lodges.organization_type as organization_type_id',
            'mar_rejected_lodges.land_deed_type as land_deed_type_id',
            'mar_rejected_lodges.mess_type as mess_type_id',
            'mar_rejected_lodges.water_supply_type as water_supply_type_id',
            'mar_rejected_lodges.electricity_type as electricity_type_id',
            'mar_rejected_lodges.security_type as security_type_id',
            'mar_rejected_lodges.father as father',
            'mar_rejected_lodges.residential_address as residential_address',
            'mar_rejected_lodges.permanent_address as permanent_address',
            'mar_rejected_lodges.email',
            'mar_rejected_lodges.application_no',
            'mar_rejected_lodges.applicant',
            'mar_rejected_lodges.application_date',
            'mar_rejected_lodges.application_no',
            'mar_rejected_lodges.entity_address',
            'mar_rejected_lodges.mobile as mobile_no',
            'mar_rejected_lodges.application_type',
            'mar_rejected_lodges.holding_no',
            'mar_rejected_lodges.aadhar_card',
            'mar_rejected_lodges.four_wheelers_parking',
            'mar_rejected_lodges.two_wheelers_parking',
            'mar_rejected_lodges.entry_gate',
            'mar_rejected_lodges.fire_extinguisher',
            'mar_rejected_lodges.exit_gate',
            'mar_rejected_lodges.cctv_camera',
            'mar_rejected_lodges.no_of_beds as noOfBeds',
            'mar_rejected_lodges.exit_gate',
            'mar_rejected_lodges.no_of_beds',
            'mar_rejected_lodges.no_of_rooms',
            'ly.string_parameter as license_year_name',
            'lt.string_parameter as lodge_type_name',
            'ot.string_parameter as organization_type_name',
            'ldt.string_parameter as land_deed_type_name',
            'mt.string_parameter as mess_type_name',
            'wt.string_parameter as water_supply_type_name',
            'et.string_parameter as electricity_type_name',
            'st.string_parameter as security_type_name',
            'pw.ward_name as permanent_ward_name',
            'ew.ward_name as entity_ward_name',
            'rw.ward_name as residential_ward_name',
            'wfr.role_name as current_role_name',
            'ulb.ulb_name',
            DB::raw("'Lodge' as headerTitle")
        )
            ->leftJoin('ref_adv_paramstrings as ly', 'ly.id', '=', DB::raw('mar_rejected_lodges.license_year::int'))
            ->leftJoin('ulb_ward_masters as rw', 'rw.id', '=', DB::raw('mar_rejected_lodges.residential_ward_id::int'))
            ->leftJoin('ref_adv_paramstrings as lt', 'lt.id', '=', DB::raw('mar_rejected_lodges.lodge_type::int'))
            ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', DB::raw('mar_rejected_lodges.organization_type::int'))
            ->leftJoin('ref_adv_paramstrings as ldt', 'ldt.id', '=', DB::raw('mar_rejected_lodges.land_deed_type::int'))
            ->leftJoin('ref_adv_paramstrings as mt', 'mt.id', '=', DB::raw('mar_rejected_lodges.mess_type::int'))
            ->leftJoin('ref_adv_paramstrings as wt', 'wt.id', '=', DB::raw('mar_rejected_lodges.water_supply_type::int'))
            ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', DB::raw('mar_rejected_lodges.electricity_type::int'))
            ->leftJoin('ref_adv_paramstrings as st', 'st.id', '=', DB::raw('mar_rejected_lodges.security_type::int'))
            ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'mar_rejected_lodges.entity_ward_id')
            ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'mar_rejected_lodges.permanent_ward_id')
            ->leftJoin('ulb_masters as ulb', 'ulb.id', '=', 'mar_rejected_lodges.ulb_id')
            ->leftJoin('wf_roles as wfr', 'wfr.id', '=', 'mar_rejected_lodges.current_role_id')
            ->where('mar_rejected_lodges.id', $applicationId)->first();
    }
}
