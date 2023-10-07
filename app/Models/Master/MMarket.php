<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MMarket extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'm_market';


    public function getMarketNameByCircleId($marketName, $circleId)
    {
        return MMarket::select('*')
            ->where('circle_id', $circleId)
            // ->where('market_name', $marketName)
            ->whereRaw('LOWER(market_name) = (?)', [strtolower($marketName)])
            ->get();
    }

    public function getMarketByCircleId($circleId)
    {
        return MMarket::select('*')
            ->where('circle_id', $circleId)
            ->get();
    }

    /**
     * | Get All Market By ULB
     */
    public function getAllActive($ulbId)
    {
        return MMarket::select('m_market.id', 'm_market.circle_id', 'm_market.market_name', 'mc.circle_name')
            ->leftjoin('m_circle as mc', 'mc.id', '=', 'm_market.circle_id')
            ->where('m_market.is_active', 1)
            ->where('m_market.ulb_id', $ulbId);
        // ->get();
    }

    /**
     * | Get Market Details By Id
     */
    public function getDetailsByMarketId($id)
    {
        return MMarket::select('m_market.id', 'm_market.circle_id', 'm_market.market_name', 'mc.circle_name')
            ->leftjoin('m_circle as mc', 'mc.id', '=', 'm_market.circle_id')
            // ->where('m_market.is_active', 1)
            ->where('m_market.id', $id)
            ->first();
    }
}
