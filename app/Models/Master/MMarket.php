<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MMarket extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'm_market';

    /**
     * | Get Market Name By Circle Id
     */
    public function getMarketNameByCircleId($marketName, $circleId)
    {
        return MMarket::select('*')
            ->where('circle_id', $circleId)
            ->whereRaw('LOWER(market_name) = (?)', [strtolower($marketName)])
            ->get();
    }

    /**
     * | Get List Market By CIrcle Id
     */
    public function getMarketByCircleId($circleId)
    {
        return MMarket::select('*')
            ->where('circle_id', $circleId)
            ->orderByDesc('id')
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
            ->where('m_market.ulb_id', $ulbId)
            ->orderByDesc('m_market.id');
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

    public function createMarket($req, $circleDetails)
    {
        $marketData = [
            'ulb_id' => $req->auth['ulb_id'] ?? 0,
            'circle_id' => $circleDetails->id,
            'market_name' => $req->marketName,
            // 'created_by' => auth()->user()->id,
        ];
        $createMarket = MMarket::create($marketData);
        return $createMarket;
    }
}
