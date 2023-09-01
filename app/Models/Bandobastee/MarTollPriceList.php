<?php

namespace App\Models\Bandobastee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarTollPriceList extends Model
{
    use HasFactory;

    /**
     * | Get Market Toll price List From Model
     */
    public function getTollPriceList($ulbId){
        return self::select('id','toll_type','rate')->where('ulb_id'.$ulbId)->where('status','1')->get();
    }
}
