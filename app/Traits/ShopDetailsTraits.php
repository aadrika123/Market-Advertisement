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
            // ['displayString' => 'Shop Type', 'key' => 'shopType', 'value' => $data['shop_type']],
        ]);
    }
}
