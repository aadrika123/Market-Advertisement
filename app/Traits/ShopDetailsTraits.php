<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;

/**
 * | Created on: 05-10-2023
 * | Created by: Bikash Kumar
 * | Trait Created for Gettting Shop Details
 */
trait ShopDetailsTraits
{
    /**
     * | Get Basic Details
     */
    public function generateBasicDetails($data)
    {
        return new Collection([
            ['displayString' => 'Circle', 'key' => 'circle', 'value' => $data['circle_name']],
            ['displayString' => 'Market Name', 'key' => 'marketName', 'value' => $data['market_name']],
            ['displayString' => 'Allottee Name', 'key' => 'allotteeName', 'value' => $data['allottee']],
            ['displayString' => 'Contact No', 'key' => 'contactNo', 'value' => $data['contact_no']],
            ['displayString' => 'Shop No', 'key' => 'shopeNo', 'value' => $data['shop_no']],
            ['displayString' => 'Present Occupier', 'key' => 'presentOccupier', 'value' => $data['present_occupier']],
            ['displayString' => 'Circle Id', 'key' => 'id', 'value' => $data['circle_id']],
            ['displayString' => 'Ward No',   'key' => 'id', 'value' => $data['ward_no']],
            ['displayString' => 'Allottee Address',   'key' => 'id', 'value' => $data['address']],
            ['displayString' => 'Construction Type', 'key' => 'id', 'value' => $data['construction_type']],
            ['displayString' => 'Allotted Length', 'key' => 'id', 'value' => $data['allotted_length']],
            ['displayString' => 'Allotted Breadth', 'key' => 'id', 'value' => $data['allotted_breadth']],
            ['displayString' => 'Allotted Height', 'key' => 'id', 'value' => $data['allotted_height']],
            ['displayString' => 'Area', 'key' => 'id', 'value' => $data['area']],
            ['displayString' => 'Present Length', 'key' => 'id', 'value' => $data['present_length']],
            ['displayString' => 'Present Breadth', 'key' => 'id', 'value' => $data['present_breadth']],
            ['displayString' => 'No Of Floors', 'key' => 'id', 'value' => $data['no_of_floors']],
            ['displayString' => 'Trade License', 'key' => 'id', 'value' => $data['trade_license']],
            ['displayString' => 'Electricity No', 'key' => 'id', 'value' => $data['electricity_no']],
            ['displayString' => 'Rate', 'key' => 'id', 'value' => $data['rate']],
            ['displayString' => 'Last Payment Date', 'key' => 'id', 'value' => $data['last_payment_date']],
            ['displayString' => 'Last Payment Amount', 'key' => 'id', 'value' => $data['last_payment_amount']],
            ['displayString' => 'Remarks', 'key' => 'id', 'value' => $data['remarks']],
            // ['displayString' => 'Shop Type', 'key' => 'shopType', 'value' => $data['shop_type']],
        ]);
    }
}
