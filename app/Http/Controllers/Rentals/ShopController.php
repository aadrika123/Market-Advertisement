<?php

namespace App\Http\Controllers\Rentals;

use App\BLL\Market\ShopPaymentBll;
use App\Http\Controllers\Controller;
use App\Models\Rentals\Shop;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    private $_mShops;
    private $_tranId;

    public function __construct()
    {
        $this->_mShops = new Shop();
    }
    /**
     * | Shop Payments
     */
    public function shopPayment(Request $req)
    {
        $shopPmtBll = new ShopPaymentBll;
        $validator = Validator::make($req->all(), [
            "shopId" => "required|integer",
            "paymentTo" => "required|date|date_format:Y-m",
            "amount" => 'required|numeric'
        ]);

        $validator->sometimes("paymentFrom", "required|date|date_format:Y-m|before_or_equal:$req->paymentTo", function ($input) use ($shopPmtBll) {
            $shopPmtBll->_shopDetails = $this->_mShops::findOrFail($input->shopId);
            $shopPmtBll->_tranId = $shopPmtBll->_shopDetails->last_tran_id;
            return !isset($this->_tranId);
        });

        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), [], 055001, "1.0", responseTime(), "POST", $req->deviceId);

        // Business Logics
        try {
            $shopPmtBll->shopPayment($req);
            DB::commit();
            return responseMsgs(true, "Payment Done Successfully", [], 055001, "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], 055001, "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
}
