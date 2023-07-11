<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Master\MMarket;
use Illuminate\Support\Facades\Validator;
use Exception;

class MarketController extends Controller
{
    private $_mMarket;

    public function __construct()
    {
        $this->_mMarket = new MMarket();
    }

    // Add records
    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'circleId' => 'required|integer',
            'marketName' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $metaReqs = [
                'circle_id' => $req->circleId,
                'market_name' => $req->marketName
            ];

            $this->_mMarket->create($metaReqs);

            return responseMsgs(true, "Successfully Saved", [$metaReqs], "055301", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {

            return responseMsgs(false, $e->getMessage(), [], "055301", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //find by Circle Id
    public function getMarketByCircleId(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'circleId' => 'required|integer'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $Market = $this->_mMarket->getGroupById($req->auth['circle_id']);
            if (collect($Market)->isEmpty())
                throw new Exception("Market Does Not Exist");
            return responseMsgs(true, "", $Market, "055302", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055302", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
}
