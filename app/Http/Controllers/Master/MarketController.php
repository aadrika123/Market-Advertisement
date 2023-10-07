<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Master\MMarket;
use App\Models\Rentals\ShopConstruction;
use Illuminate\Support\Facades\Validator;
use Exception;

class MarketController extends Controller
{
    private $_mMarket;

    public function __construct()
    {
        $this->_mMarket = new MMarket();
    }

    /**
     * | Add Market Records
     * | Function - 01
     * | API - 01
     */
    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'circleId' => 'required|integer',
            'marketName' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $exists = $this->_mMarket->getMarketNameByCircleId($req->marketName, $req->circleId);
            if (collect($exists)->isNotEmpty())
                throw new Exception("Market According To Circle Id Already Existing");

            $metaReqs = [
                'circle_id' => $req->circleId,
                'market_name' => $req->marketName,
                'ulb_id' => $req->auth['ulb_id'],
            ];

            $this->_mMarket->create($metaReqs);

            return responseMsgs(true, "Successfully Saved", [$metaReqs], "055301", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {

            return responseMsgs(false, $e->getMessage(), [], "055301", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Update Market Records
     * | Function - 02
     * | API - 02
     */
    public function edit(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id'         => 'required|integer',
            'circleId'   => 'required|integer',
            'marketName' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {

            $exists = $this->_mMarket->getMarketNameByCircleId($req->marketName, $req->circleId);
            if (collect($exists)->where('id', '!=', $req->id)->isNotEmpty())
                throw new Exception("Market According To Circle Id Already Existing");

            $metaReqs = [
                'circle_id'   => $req->circleId,
                'market_name' => $req->marketName
            ];

            $market = $this->_mMarket->findOrFail($req->id);
            $market->update($metaReqs);

            return responseMsgs(true, "Successfully Saved", [$metaReqs], "055302", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {

            return responseMsgs(false, $e->getMessage(), [], "055302", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Update Market Records
     * | Function - 03
     * | API - 03
     */
    public function getMarketByCircleId(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'circleId' => 'required|integer'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $Market = $this->_mMarket->getMarketByCircleId($req->circleId);
            if (collect($Market)->isEmpty())
                throw new Exception("Market According To Circle Id Does Not Exist");
            return responseMsgs(true, "", $Market, "055303", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055303", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Update Market Records
     * | Function - 04
     * | API - 04
     */
    public function listAllMarket(Request $req)
    {
        try {
            $market = $this->_mMarket->getAllActive($req->auth['ulb_id']);
            if (collect($market)->isEmpty())
                throw new Exception("No Data Found");
            $market = searchMarketFilter($market, $req);
            $market = paginator($market, $req);
            return responseMsgs(true, "", $market, "055304", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055304", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Update Market Records
     * | Function - 05
     * | API - 05
     */
    public function delete(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id'  => 'required|integer',
            'isActive' => 'required|bool'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {
            if (isset($req->isActive)) {
                $isActive = $req->isActive == false ? 0 : 1;
                $metaReqs = [
                    'is_active' => $isActive
                ];
            }
            $Shops = $this->_mMarket::findOrFail($req->id);
            $Shops->update($metaReqs);
            return responseMsgs(true, "Status Updated Successfully", [], "055305", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055305", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Get Market Records By ID
     * | Function - 06
     * | API - 06
     */
    public function getDetailByMarketId(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'marketId'  => 'required|integer',
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []); //getDetailsByMarketId
        try {
            $market = $this->_mMarket->getDetailsByMarketId($req->marketId);
            return responseMsgs(true, "Details Fetch Successfully", $market, "055306", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055306", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
    /**
     * | Update Market Records
     * | Function - 07
     * | API - 07
     */
    public function listConstruction(Request $req)
    {
        try {
            $mShopConstruction = new ShopConstruction();
            $list = $mShopConstruction->listConstruction();
            return responseMsgs(true, "Construction Fetch Successfully", $list, "055307", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055307", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
}
