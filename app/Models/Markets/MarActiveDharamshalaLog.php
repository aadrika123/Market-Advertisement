<?php

namespace App\Models\Markets;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MarActiveDharamshalaLog extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $_applicationDate;

    public function getEditedApplicationDetails($appId)
    {
        return MarActiveDharamshalaLog::select(
            'mar_active_dharamshala_logs.*',
            'mar_active_dharamshala_logs.organization_type as organization_type_id',
            'mar_active_dharamshala_logs.land_deed_type as land_deed_type_id',
            'mar_active_dharamshala_logs.water_supply_type as water_supply_type_id',
            'mar_active_dharamshala_logs.electricity_type as electricity_type_id',
            'mar_active_dharamshala_logs.security_type as security_type_id',
            'mar_active_dharamshala_logs.no_of_rooms as noOfRooms',
            'mar_active_dharamshala_logs.no_of_beds as noOfBeds',
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
            ->leftJoin('ref_adv_paramstrings as ly', 'ly.id', '=', DB::raw('mar_active_dharamshala_logs.license_year::int'))
            ->leftJoin('ulb_ward_masters as rw', 'rw.id', '=', DB::raw('mar_active_dharamshala_logs.residential_ward_id::int'))
            ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', DB::raw('mar_active_dharamshala_logs.organization_type::int'))
            ->leftJoin('ref_adv_paramstrings as ldt', 'ldt.id', '=', DB::raw('mar_active_dharamshala_logs.land_deed_type::int'))
            ->leftJoin('ref_adv_paramstrings as wt', 'wt.id', '=', DB::raw('mar_active_dharamshala_logs.water_supply_type::int'))
            ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', DB::raw('mar_active_dharamshala_logs.electricity_type::int'))
            ->leftJoin('ref_adv_paramstrings as st', 'st.id', '=', DB::raw('mar_active_dharamshala_logs.security_type::int'))
            ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'mar_active_dharamshala_logs.entity_ward_id')
            ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'mar_active_dharamshala_logs.permanent_ward_id')
            ->leftJoin('ulb_masters as ulb', 'ulb.id', '=', 'mar_active_dharamshala_logs.ulb_id')
            ->where('mar_active_dharamshala_logs.application_id', $appId)->first();
    }
}
