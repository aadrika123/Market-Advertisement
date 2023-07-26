<?php

namespace App\Models\Rentals;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarTollPayment extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function paymentList($ulbId)
    {
        return self::where('ulb_id',$ulbId);
    }

    public function paymentListForTcCollection($ulbId){
        return self::select('user_id','payment_date','amount')->where('ulb_id',$ulbId);
    }

    
    public function todayTallCollection($ulbId,$date){
        return self::select('amount')
                    ->where('ulb_id', $ulbId)
                    ->where('payment_date', $date);
            //  ->sum('amount');
    }

}
