<?php

namespace App\Models\Markets;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MarRejectedHostel extends Model
{
    use HasFactory;


    /**
     * | Get Application Reject List by Role Ids
     */
    public function listRejected($citizenId)
    {
        return MarRejectedHostel::where('mar_rejected_hostels.citizen_id', $citizenId)
            ->select(
                'mar_rejected_hostels.id',
                'mar_rejected_hostels.application_no',
                'mar_rejected_hostels.application_date',
                'mar_rejected_hostels.entity_address',
                'mar_rejected_hostels.entity_name',
                'mar_rejected_hostels.applicant',
                'mar_rejected_hostels.rejected_date',
                'mar_rejected_hostels.citizen_id',
                'um.ulb_name as ulb_name',
                'workflow_tracks.message as remarks'
            )
            ->join('ulb_masters as um', 'um.id', '=', 'mar_rejected_hostels.ulb_id')
            ->leftJoin('workflow_tracks', function ($join) use ($citizenId) {
                $join->on('workflow_tracks.ref_table_id_value', 'mar_rejected_hostels.id')
                    ->where('workflow_tracks.status', true)
                    ->where('workflow_tracks.message', '<>', null)
                    ->where('workflow_tracks.verification_status', 3)
                    ->where('workflow_tracks.workflow_id', 24)
                    ->where('workflow_tracks.module_id', 5)
                    ->where('workflow_tracks.citizen_id', $citizenId);
            })
            ->orderByDesc('mar_rejected_hostels.id')
            ->get();
    }


    /**
     * | Get All Application Reject List
     */
    public function rejectedApplication()
    {
        return MarRejectedHostel::select(
            'id',
            'application_no',
            'application_date',
            'entity_address',
            'entity_name',
            'applicant',
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
        return MarRejectedHostel::select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', 'rule', 'organization_type', 'hostel_type', 'ulb_id', 'license_year', DB::raw("'Reject' as application_status"));
    }

    public function listjskRejectedApplication($ulbId)
    {
        return MarRejectedHostel::select(
            'mar_rejected_hostels.id',
            'application_no',
            'applicant',
            DB::raw("TO_CHAR(mar_rejected_hostels.application_date, 'DD-MM-YYYY') as application_date"),
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
            ->where('mar_rejected_hostels.ulb_id', $ulbId)
            ->join('wf_roles as wr', 'wr.id', '=', 'mar_rejected_hostels.current_role_id');
    }

    public function getDetailsById($applicationId)
    {
        return MarRejectedHostel::select(
            'mar_rejected_hostels.*',
            'mar_rejected_hostels.hostel_type as hostel_type_id',
            'mar_rejected_hostels.organization_type as organization_type_id',
            'mar_rejected_hostels.land_deed_type as land_deed_type_id',
            'mar_rejected_hostels.mess_type as mess_type_id',
            'mar_rejected_hostels.water_supply_type as water_supply_type_id',
            'mar_rejected_hostels.electricity_type as electricity_type_id',
            'mar_rejected_hostels.security_type as security_type_id',
            'mar_rejected_hostels.no_of_rooms as noOfRooms',
            'mar_rejected_hostels.no_of_beds as noOfBeds',
            'ly.string_parameter as license_year_name',
            DB::raw("case when mar_rejected_hostels.is_approve_by_govt = true then 'Yes'
                        else 'No' end as is_approve_by_govt_name"),
            DB::raw("case when mar_rejected_hostels.is_approve_by_govt = true then 1
                        else 0 end as is_approve_by_govt_id"),
            'lt.string_parameter as hostel_type_name',
            'ot.string_parameter as organization_type_name',
            'ldt.string_parameter as land_deed_type_name',
            'mt.string_parameter as mess_type_name',
            'wt.string_parameter as water_supply_type_name',
            'et.string_parameter as electricity_type_name',
            'st.string_parameter as security_type_name',
            'pw.ward_name as permanent_ward_name',
            'ew.ward_name as entity_ward_name',
            'rw.ward_name as residential_ward_name',
            'ulb.ulb_name',
            DB::raw("'Hostel' as headerTitle")
        )
            ->leftJoin('ref_adv_paramstrings as ly', 'ly.id', '=', DB::raw('mar_rejected_hostels.license_year::int'))
            ->leftJoin('ulb_ward_masters as rw', 'rw.id', '=', DB::raw('mar_rejected_hostels.residential_ward_id::int'))
            ->leftJoin('ref_adv_paramstrings as lt', 'lt.id', '=', DB::raw('mar_rejected_hostels.hostel_type::int'))
            ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', DB::raw('mar_rejected_hostels.organization_type::int'))
            ->leftJoin('ref_adv_paramstrings as ldt', 'ldt.id', '=', DB::raw('mar_rejected_hostels.land_deed_type::int'))
            ->leftJoin('ref_adv_paramstrings as mt', 'mt.id', '=', DB::raw('mar_rejected_hostels.mess_type::int'))
            ->leftJoin('ref_adv_paramstrings as wt', 'wt.id', '=', DB::raw('mar_rejected_hostels.water_supply_type::int'))
            ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', DB::raw('mar_rejected_hostels.electricity_type::int'))
            ->leftJoin('ref_adv_paramstrings as st', 'st.id', '=', DB::raw('mar_rejected_hostels.security_type::int'))
            ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'mar_rejected_hostels.entity_ward_id')
            ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'mar_rejected_hostels.permanent_ward_id')
            ->leftJoin('ulb_masters as ulb', 'ulb.id', '=', 'mar_rejected_hostels.ulb_id')
            ->where('mar_rejected_hostels.id', $applicationId)->first();
    }
}
