<?php

namespace App\Models\Property;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropActiveSafsFloor extends Model
{
    use HasFactory;

       /**
     * | Get Safs Floors By Saf Id
     */
    public function getSafFloors($safId)
    {
        return PropActiveSafsFloor::where('saf_id', $safId)
            ->where('status', 1);
    }
}
