<?php

namespace App\Models\Rentals;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarTollDemand extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'mar_toll_demands';

    # get consumer demand details 
    public function CheckConsumerDemand($req)
    {
        return self::where('toll_id', $req->tollId)
            ->where('status', 1)
            ->orderByDesc('id');
    }

    /**
     * | Get Generated Demand Details Pay Before
     */
    public function payBeforeDemand($shopId, $month)
    {
        return self::select('monthly', 'amount')->where('shop_id', $shopId)->where('monthly', '<=', $month)->where('payment_status', '0')->orderBy('monthly', 'ASC')->get();
    }
}
