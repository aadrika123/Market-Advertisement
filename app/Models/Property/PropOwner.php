<?php

namespace App\Models\Property;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropOwner extends Model
{
    use HasFactory;
    protected $connection = 'pgsql_property';

    /**
     * | Get The Owner by Property Id
     */
    public function getOwnerByPropId($propId)
    {
        return PropOwner::where('property_id', $propId)
            ->select(
                'id',
                'owner_name as ownerName',
                'mobile_no as mobileNo',
                'guardian_name as guardianName',
                'email',
                'gender',
                'is_armed_force',
                'is_specially_abled'
            )
            ->where('status', 1)
            ->orderBy('id')
            ->get();
    }

    public function getOwnerByPropIdV2($propId)
    {
        return PropOwner::where('property_id', $propId)
            ->select(
                'id','property_id',
                'owner_name as ownerName',
                'guardian_name as guardianName'
            )
            ->where('status', 1)
            ->orderBy('id')
            ->get();
    }
}
