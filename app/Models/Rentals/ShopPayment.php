<?php

namespace App\Models\Rentals;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShopPayment extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'mar_shop_payments';

    public function paymentList($ulbId)
    {
        return self::where('ulb_id',$ulbId);
    }

    
    public function todayShopCollection($ulbId,$date){
        return self::select('amount')
                    ->where('ulb_id', $ulbId)
                    ->where('payment_date', $date);
            //  ->sum('amount');
    }

    public function paymentListForTcCollection($ulbId){
        return self::select('user_id','payment_date','amount')->where('ulb_id',$ulbId);
    }

}
