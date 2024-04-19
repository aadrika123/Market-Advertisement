<?php

namespace App\Models\Rentals;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarShopDemand extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'mar_shop_demands';

    # get consumer demand details 
    public function CheckConsumerDemand($req)
    {
        return self::where('shop_id', $req->shopId)
            ->where('status', true)
            ->orderByDesc('id');
    }

    /**
     * | Get Generated Demand Details Pay Before
     */
    public function payBeforeDemand($shopId, $month)
    {
        return self::select('monthly', 'amount')->where('shop_id', $shopId)->where('monthly', '<=', $month)->where('payment_status', '0')->orderBy('monthly', 'ASC')->get();
    }

    # get shop demand 
    public function getShopDemand($shopId)
    {
        return self::where('shop_id', $shopId)
            ->where('status', true)
            ->where('payment_status', 0)
            ->get();
    }
}
