<?php

namespace App\Models\Markets;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MarRejectedDharamshala extends Model
{
    use HasFactory;

    /**
     * | Get Application Reject List by Role Ids
     */
    public function listRejected($citizenId)
    {
        return MarRejectedDharamshala::where('mar_rejected_dharamshalas.citizen_id', $citizenId)
            ->select(
                'mar_rejected_dharamshalas.id',
                'mar_rejected_dharamshalas.application_no',
                'mar_rejected_dharamshalas.application_date',
                'mar_rejected_dharamshalas.entity_address',
                'mar_rejected_dharamshalas.rejected_date',
                'mar_rejected_dharamshalas.citizen_id',
                'um.ulb_name as ulb_name',
                'workflow_tracks.message as remarks',
                'mar_rejected_dharamshalas.entity_name',
                'mar_rejected_dharamshalas.entity_address'
            )
            ->join('ulb_masters as um', 'um.id', '=', 'mar_rejected_dharamshalas.ulb_id')
            ->leftJoin('workflow_tracks', function ($join) use ($citizenId) {
                $join->on('workflow_tracks.ref_table_id_value', 'mar_rejected_dharamshalas.id')
                    ->where('workflow_tracks.status', true)
                    ->where('workflow_tracks.message', '<>', null)
                    ->where('workflow_tracks.verification_status', 3)
                    ->where('workflow_tracks.workflow_id', 26)
                    ->where('workflow_tracks.module_id', 5)
                    ->where('workflow_tracks.citizen_id', $citizenId);
            })
            ->orderByDesc('mar_rejected_dharamshalas.id')
            ->get();
    }

    /**
     * | Get All Application Reject List
     */
    public function rejectedApplication()
    {
        return MarRejectedDharamshala::select(
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
     * | Reject List For Report
     */
    public function rejectListForReport()
    {
        return MarRejectedDharamshala::select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'ulb_id', 'license_year', DB::raw("'Reject' as application_status"));
    }

    public function listjskRejectedApplication($ulbId)
    {
        return MarRejectedDharamshala::select(
            'mar_rejected_dharamshalas.id',
            'application_no',
            'applicant',
            DB::raw("TO_CHAR(mar_rejected_dharamshalas.application_date, 'DD-MM-YYYY') as application_date"),
            'application_type',
            'entity_ward_id',
            'rule',
            'entity_name',
            'license_year',
            'ulb_id',
            DB::raw("TO_CHAR(rejected_date,'DD-MM-YYYY') as rejected_date"),
            DB::raw("CASE WHEN user_id IS NOT NULL THEN 'jsk' ELSE 'citizen' END AS applied_by"),
            'wr.role_name as rejected_by',
            'remarks as reason'
        )
            ->where('mar_rejected_dharamshalas.ulb_id', $ulbId)
            ->join('wf_roles as wr', 'wr.id', '=', 'mar_rejected_dharamshalas.current_role_id');
    }

    public function getDetailsById($applicationId)
    {
        return MarRejectedDharamshala::select(
            'mar_rejected_dharamshalas.*',
            'mar_rejected_dharamshalas.organization_type as organization_type_id',
            'mar_rejected_dharamshalas.land_deed_type as land_deed_type_id',
            'mar_rejected_dharamshalas.water_supply_type as water_supply_type_id',
            'mar_rejected_dharamshalas.electricity_type as electricity_type_id',
            DB::raw("TO_CHAR(mar_rejected_dharamshalas.application_date, 'DD-MM-YYYY') as application_date"),
            DB::raw("TO_CHAR(rejected_date,'DD-MM-YYYY') as rejected_date"),
            DB::raw("CASE WHEN user_id IS NOT NULL THEN 'jsk' ELSE 'citizen' END AS applied_by"),
            'mar_rejected_dharamshalas.security_type as security_type_id',
            'mar_rejected_dharamshalas.no_of_rooms as noOfRooms',
            'mar_rejected_dharamshalas.no_of_beds as noOfBeds',
            'ly.string_parameter as license_year_name',
            'ot.string_parameter as organization_type_name',
            'ldt.string_parameter as land_deed_type_name',
            'wt.string_parameter as water_supply_type_name',
            'et.string_parameter as electricity_type_name',
            'st.string_parameter as security_type_name',
            'pw.ward_name as permanent_ward_name',
            'ew.ward_name as entity_ward_name',
            'rw.ward_name as residential_ward_name',
            'ulb.ulb_name',
            DB::raw("'Dharamshala' as headerTitle")
        )
            ->leftJoin('ref_adv_paramstrings as ly', 'ly.id', '=', DB::raw('mar_rejected_dharamshalas.license_year::int'))
            ->leftJoin('ulb_ward_masters as rw', 'rw.id', '=', DB::raw('mar_rejected_dharamshalas.residential_ward_id::int'))
            ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', DB::raw('mar_rejected_dharamshalas.organization_type::int'))
            ->leftJoin('ref_adv_paramstrings as ldt', 'ldt.id', '=', DB::raw('mar_rejected_dharamshalas.land_deed_type::int'))
            ->leftJoin('ref_adv_paramstrings as wt', 'wt.id', '=', DB::raw('mar_rejected_dharamshalas.water_supply_type::int'))
            ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', DB::raw('mar_rejected_dharamshalas.electricity_type::int'))
            ->leftJoin('ref_adv_paramstrings as st', 'st.id', '=', DB::raw('mar_rejected_dharamshalas.security_type::int'))
            ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'mar_rejected_dharamshalas.entity_ward_id')
            ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'mar_rejected_dharamshalas.permanent_ward_id')
            ->leftJoin('ulb_masters as ulb', 'ulb.id', '=', 'mar_rejected_dharamshalas.ulb_id')
            ->where('mar_rejected_dharamshalas.id', $applicationId);
    }
}
