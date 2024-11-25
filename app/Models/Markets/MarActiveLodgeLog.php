<?php

namespace App\Models\Markets;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MarActiveLodgeLog extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $_applicationDate;
    /**
     * | Get Application Details For Update 
     */
    public function getEditedApplicationDetails($appId)
    {
        return MarActiveLodgeLog::select(
            'mar_active_lodge_logs.*',
            'mar_active_lodge_logs.lodge_type as lodge_type_id',
            'mar_active_lodge_logs.organization_type as organization_type_id',
            'mar_active_lodge_logs.land_deed_type as land_deed_type_id',
            'mar_active_lodge_logs.mess_type as mess_type_id',
            'mar_active_lodge_logs.water_supply_type as water_supply_type_id',
            'mar_active_lodge_logs.electricity_type as electricity_type_id',
            'mar_active_lodge_logs.security_type as security_type_id',
            'mar_active_lodge_logs.father as father',
            'mar_active_lodge_logs.residential_address as residential_address',
            'mar_active_lodge_logs.permanent_address as permanent_address',
            'mar_active_lodge_logs.email',
            'mar_active_lodge_logs.application_no',
            'mar_active_lodge_logs.applicant',
            'mar_active_lodge_logs.application_date',
            'mar_active_lodge_logs.application_no',
            'mar_active_lodge_logs.entity_address',
            'mar_active_lodge_logs.mobile as mobile_no',
            'mar_active_lodge_logs.application_type',
            'mar_active_lodge_logs.holding_no',
            'mar_active_lodge_logs.aadhar_card',
            'mar_active_lodge_logs.four_wheelers_parking',
            'mar_active_lodge_logs.two_wheelers_parking',
            'mar_active_lodge_logs.entry_gate',
            'mar_active_lodge_logs.fire_extinguisher',
            'mar_active_lodge_logs.exit_gate',
            'mar_active_lodge_logs.cctv_camera',
            'mar_active_lodge_logs.no_of_beds as noOfBeds',
            'mar_active_lodge_logs.exit_gate',
            'mar_active_lodge_logs.no_of_beds',
            'mar_active_lodge_logs.no_of_rooms',
            'mar_active_lodge_logs.no_of_rooms as noOfRooms',
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
            ->leftJoin('ref_adv_paramstrings as ly', 'ly.id', '=', DB::raw('mar_active_lodge_logs.license_year::int'))
            ->leftJoin('ulb_ward_masters as rw', 'rw.id', '=', DB::raw('mar_active_lodge_logs.residential_ward_id::int'))
            ->leftJoin('ref_adv_paramstrings as lt', 'lt.id', '=', DB::raw('mar_active_lodge_logs.lodge_type::int'))
            ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', DB::raw('mar_active_lodge_logs.organization_type::int'))
            ->leftJoin('ref_adv_paramstrings as ldt', 'ldt.id', '=', DB::raw('mar_active_lodge_logs.land_deed_type::int'))
            ->leftJoin('ref_adv_paramstrings as mt', 'mt.id', '=', DB::raw('mar_active_lodge_logs.mess_type::int'))
            ->leftJoin('ref_adv_paramstrings as wt', 'wt.id', '=', DB::raw('mar_active_lodge_logs.water_supply_type::int'))
            ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', DB::raw('mar_active_lodge_logs.electricity_type::int'))
            ->leftJoin('ref_adv_paramstrings as st', 'st.id', '=', DB::raw('mar_active_lodge_logs.security_type::int'))
            ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'mar_active_lodge_logs.entity_ward_id')
            ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'mar_active_lodge_logs.permanent_ward_id')
            ->leftJoin('ulb_masters as ulb', 'ulb.id', '=', 'mar_active_lodge_logs.ulb_id')
            ->leftJoin('wf_roles as wfr', 'wfr.id', '=', 'mar_active_lodge_logs.current_role_id')
            ->where('mar_active_lodge_logs.application_id', $appId);
    }
}
