<?php

namespace App\Models\Property;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PropProperty extends Model
{
    use HasFactory;

    /**
     * | Get property details by provided key
     * | @param 
     */
    public function getPropDtls()
    {
        return DB::table('prop_properties')
            ->select(
                'prop_properties.*',
                DB::raw("REPLACE(prop_properties.holding_type, '_', ' ') AS holding_type"),
                'prop_properties.status as active_status',
                'prop_properties.assessment_type as assessment',
                'w.ward_name as old_ward_no',
                'nw.ward_name as new_ward_no',
                // 'o.ownership_type',
                // 'ref_prop_types.property_type',
                // 'r.road_type',
                // 'a.apartment_name',
                // 'a.apt_code as apartment_code'
            )
            ->join('ulb_ward_masters as w', 'w.id', '=', 'prop_properties.ward_mstr_id')
            ->leftJoin('ulb_ward_masters as nw', 'nw.id', '=', 'prop_properties.new_ward_mstr_id');
        // ->leftJoin('ref_prop_ownership_types as o', 'o.id', '=', 'prop_properties.ownership_type_mstr_id')
        // ->leftJoin('ref_prop_types', 'ref_prop_types.id', '=', 'prop_properties.prop_type_mstr_id')
        // ->leftJoin('ref_prop_road_types as r', 'r.id', '=', 'prop_properties.road_type_mstr_id')
        // ->leftJoin('prop_apartment_dtls as a', 'a.id', '=', 'prop_properties.apartment_details_id');
    }
}
