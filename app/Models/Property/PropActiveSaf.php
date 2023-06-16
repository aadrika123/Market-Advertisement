<?php

namespace App\Models\Property;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PropActiveSaf extends Model
{
    use HasFactory;

    /**
     * | Get Saf Details by Saf No
     * | @param SafNo
     */
    public function getSafDtlBySaf()
    {
        return DB::table('prop_active_safs as s')
            ->select(
                's.*',
                'u.ward_name as old_ward_no',
                'u1.ward_name as new_ward_no',
            )
            ->join('ulb_ward_masters as u', 's.ward_mstr_id', '=', 'u.id')
            ->leftJoin('ulb_ward_masters as u1', 's.new_ward_mstr_id', '=', 'u1.id')
            ->where('s.status', 1);
    }
}
