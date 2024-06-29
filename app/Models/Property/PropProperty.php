<?php

namespace App\Models\Property;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PropProperty extends Model
{
    use HasFactory;
    protected $connection = 'pgsql_property';

    /**
     * | Get property details by provided key
     * | @param 
     */
    public function getPropDtls()
    {
        return PropProperty::select(
            'prop_properties.*',
            DB::raw("REPLACE(prop_properties.holding_type, '_', ' ') AS holding_type"),
            'prop_properties.status as active_status',
            'prop_properties.assessment_type as assessment',
            'w.ward_name as old_ward_no',
            'nw.ward_name as new_ward_no',
        )
            ->join('ulb_ward_masters as w', 'w.id', '=', 'prop_properties.ward_mstr_id')
            ->leftJoin('ulb_ward_masters as nw', 'nw.id', '=', 'prop_properties.new_ward_mstr_id');
    }

    public function getPropDtlsv2($fiYear,$currentfyStartDate)
    {
        return PropProperty::select(
            'prop_properties.id',
            'prop_properties.saf_id',
            'prop_properties.prop_address',
            'prop_properties.area_of_plot',
            'w.ward_name as old_ward_no',
            'nw.ward_name as new_ward_no',
            'prop_saf_geotag_uploads.latitude',
            'prop_saf_geotag_uploads.longitude',
            // DB::raw(" (CASE WHEN demands.arrear_demand IS NULL THEN '0.00' ELSE demands.arrear_demand END) AS arrear_demand")
            DB::raw("(CASE WHEN demands.prop_arrear_demand IS NULL THEN '0.00' ELSE demands.prop_arrear_demand END) AS arrear_demand")
        )
            ->join('ulb_ward_masters as w', 'w.id', '=', 'prop_properties.ward_mstr_id')
            ->leftjoin('prop_saf_geotag_uploads', 'prop_saf_geotag_uploads.saf_id', '=', 'prop_properties.saf_id')
            // ->LEFTJOIN(
            //     DB::RAW("(
            //     SELECT 
            //         property_id, 
            //         SUM(CASE WHEN fyear < '$fiYear' THEN amount ELSE 0 END) AS arrear_demand
            //     FROM prop_demands 
            //     WHERE status=1 AND paid_status IN (0,1)  
            //     GROUP BY property_id
            //     )AS demands
            //     "),
            //     function ($join) {
            //         $join->on("demands.property_id", "=", "prop_properties.id");
            //     }
            // )
            ->LEFTJOIN(
                DB::RAW("(
                SELECT 
                    property_id, 
                    SUM(
                                    CASE WHEN prop_demands.due_date < '$currentfyStartDate' then COALESCE(prop_demands.amount,0) -COALESCE(prop_demands.adjust_amt,0)
                                        ELSE 0
                                        END
                                    ) AS prop_arrear_demand
                FROM prop_demands 
                WHERE status=1 
                GROUP BY property_id
                )AS demands
                "),
                function ($join) {
                    $join->on("demands.property_id", "=", "prop_properties.id");
                }
            )
            //->join('prop_demands', 'prop_demands.property_id', '=', 'prop_properties.id')
            ->leftJoin('ulb_ward_masters as nw', 'nw.id', '=', 'prop_properties.new_ward_mstr_id');
            //->groupby('prop_properties.id');
    }


    /**
     * | Get Proprty Details By Holding No
     */
    public function getPropByHolding($holdingNo, $ulbId)
    {
        $oldHolding = PropProperty::select(
            'prop_properties.id',
            'prop_properties.holding_no',
            'prop_properties.new_holding_no',
            'prop_properties.ward_mstr_id',
            'prop_properties.new_ward_mstr_id',
            'prop_properties.prop_pin_code',
            'prop_properties.corr_pin_code',
            'prop_properties.prop_address',
            'prop_properties.corr_address',
            'prop_properties.apartment_details_id',
            'prop_properties.area_of_plot as total_area_in_desimal',
            'prop_properties.prop_type_mstr_id',
            'ulb_ward_masters.ward_name as old_ward_no',
            'u.ward_name as new_ward_no',
        )
            ->join('ulb_ward_masters', 'ulb_ward_masters.id', '=', 'prop_properties.ward_mstr_id')
            ->leftJoin('ulb_ward_masters as u', 'u.id', '=', 'prop_properties.new_ward_mstr_id')
            ->where('prop_properties.holding_no', $holdingNo)
            ->where('prop_properties.ulb_id', $ulbId)
            ->where('prop_properties.status', 1)
            ->first();

        if ($oldHolding) {
            return $oldHolding;
        }

        $newHolding = PropProperty::select(
            'prop_properties.id',
            'prop_properties.holding_no',
            'prop_properties.new_holding_no',
            'prop_properties.ward_mstr_id',
            'prop_properties.new_ward_mstr_id',
            'prop_properties.elect_consumer_no',
            'prop_properties.elect_acc_no',
            'prop_properties.elect_bind_book_no',
            'prop_properties.elect_cons_category',
            'prop_properties.prop_pin_code',
            'prop_properties.corr_pin_code',
            'prop_properties.prop_address',
            'prop_properties.corr_address',
            'prop_properties.apartment_details_id',
            'prop_properties.area_of_plot as total_area_in_desimal',
            'prop_properties.prop_type_mstr_id',
            'ulb_ward_masters.ward_name as old_ward_no',
            'u.ward_name as new_ward_no',
        )
            ->join('ulb_ward_masters', 'ulb_ward_masters.id', '=', 'prop_properties.ward_mstr_id')
            ->leftJoin('ulb_ward_masters as u', 'u.id', '=', 'prop_properties.new_ward_mstr_id')
            ->where('prop_properties.new_holding_no', $holdingNo)
            ->where('prop_properties.ulb_id', $ulbId)
            ->first();
        return $newHolding;
    }


    /**
     * | Get citizen holdings
     */
    public function getCitizenHoldings($citizenId, $ulbId)
    {
        return PropProperty::select('id', 'new_holding_no', 'citizen_id', 'holding_no')
            ->where('ulb_id', $ulbId)
            ->where('citizen_id', $citizenId)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * | get New Holding
     */
    public function getNewholding($propertyId)
    {
        return PropProperty::select('id', 'new_holding_no', 'citizen_id')
            ->whereIn('id', $propertyId)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * | Get Proprty Details By Holding No
     */
    public function getHoldingDetails($holdingNo, $ulbId)
    {
        $oldHolding = PropProperty::select(
            'prop_properties.id',
            'prop_properties.holding_no',
            'prop_properties.new_holding_no',
            'prop_properties.ward_mstr_id',
            'prop_properties.new_ward_mstr_id',
            'prop_properties.prop_pin_code',
            'prop_properties.corr_pin_code',
            'prop_properties.prop_address',
            'prop_properties.corr_address',
            'prop_properties.apartment_details_id',
            'prop_properties.area_of_plot as total_area_in_desimal',
            'prop_properties.prop_type_mstr_id',
            'ulb_ward_masters.ward_name as old_ward_no',
            'u.ward_name as new_ward_no',
        )
            ->join('ulb_ward_masters', 'ulb_ward_masters.id', '=', 'prop_properties.ward_mstr_id')
            ->leftJoin('ulb_ward_masters as u', 'u.id', '=', 'prop_properties.new_ward_mstr_id')
            ->where('prop_properties.holding_no', $holdingNo)
            ->where('prop_properties.ulb_id', $ulbId)
            ->where('prop_properties.status', 1)
            ->first();
    }
}
