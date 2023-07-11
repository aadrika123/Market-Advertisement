<?php

namespace App\Http\Controllers\Rentals;

use App\BLL\Market\ShopPaymentBll;
use App\Http\Controllers\Controller;
use App\Models\Rentals\Shop;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Shop\ShopRequest;
use Illuminate\Support\Facades\Config;
use App\MicroServices\DocumentUpload;

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

    // Add records
    public function store(ShopRequest $req)
    {

        try {
            $docUpload = new DocumentUpload;
            $relativePath = config::get('constants.SHOP_PATH');



            if (isset($req->photo1Path)) {
                $image = $req->file('photo1Path');
                $refImageName = 'Shop-Photo-1' . '-' . $req->allottee;
                $imageName1 = $docUpload->upload($refImageName, $image, $relativePath);
                $absolutePath = public_path($relativePath);
                $imageName1Absolute = $absolutePath . '-' . $imageName1;
            }



            if (isset($req->photo2Path)) {
                $image = $req->file('photo2Path');
                $refImageName = 'Shop-Photo-2' . '-' . $req->allottee;
                $imageName2 = $docUpload->upload($refImageName, $image, $relativePath);
                $absolutePath = public_path($relativePath);
                $imageName2Absolute = $absolutePath . '-' . $imageName2;
            }


            $metaReqs = [
                'circle_id' => $req->circleId,
                'market' => $req->market,
                'allottee' => $req->allottee,
                'shop_no' => $req->shopNo,
                'address' => $req->address,
                'rate' => $req->rate,
                'arrear' => $req->arrear,
                'allotted_length' => $req->allottedLength,
                'allotted_breadth' => $req->allottedBreadth,
                'allotted_height' => $req->allottedHeight,
                'area' => $req->area,
                'present_length' => $req->presentLength,
                'present_breadth' => $req->presentBreadth,
                'present_height' => $req->presentHeight,
                'no_of_floors' => $req->noOfFloors,
                'present_occupier' => $req->presentOccupier,
                'trade_license' => $req->tradeLicense,
                'construction' => $req->construction,
                'electricity' => $req->electricity,
                'water' => $req->water,
                'sale_purchase' => $req->salePurchase,
                'contact_no' => $req->contactNo,
                'longitude' => $req->longitude,
                'latitude' => $req->latitude,
                'photo1_path' => $imageName1 ?? "",
                'photo1_path_absolute' => $imageName1Absolute ?? "",
                'photo2_path' => $imageName2 ?? "",
                'photo2_path_absolute' => $imageName2Absolute ?? "",
                'remarks' => $req->remarks,
                'last_tran_id' => $req->lastTranId,
                'user_id' => $req->userId,
                'ulb_id' => $req->ulbId,
            ];

            $this->_mShops->create($metaReqs);

            return responseMsgs(true, "Successfully Saved", [$metaReqs], "050202", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {

            return responseMsgs(false, $e->getMessage(), [], "050202", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }


    // Edit records
    public function edit(ShopRequest $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|integer',
            'status' => 'nullable|bool'
        ]);

        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $docUpload = new DocumentUpload;
            $relativePath = config::get('constants.SHOP_PATH');

            if (isset($req->photo1Path)) {
                $image = $req->file('photo1Path');
                $refImageName = 'Shop-Photo-1' . '-' . $req->allottee;
                $imageName1 = $docUpload->upload($refImageName, $image, $relativePath);
                $absolutePath = public_path($relativePath);
                $imageName1Absolute = $absolutePath . '-' . $imageName1;
            }



            if (isset($req->photo2Path)) {
                $image = $req->file('photo2Path');
                $refImageName = 'Shop-Photo-2' . '-' . $req->allottee;
                $imageName2 = $docUpload->upload($refImageName, $image, $relativePath);
                $absolutePath = public_path($relativePath);
                $imageName2Absolute = $absolutePath . '-' . $imageName2;
            }

            $metaReqs = [
                'circle' => $req->circle,
                'market' => $req->market,
                'allottee' => $req->allottee,
                'shop_no' => $req->shopNo,
                'address' => $req->address,
                'rate' => $req->rate,
                'arrear' => $req->arrear,
                'allotted_length' => $req->allottedLength,
                'allotted_breadth' => $req->allottedBreadth,
                'allotted_height' => $req->allottedHeight,
                'area' => $req->area,
                'present_length' => $req->presentLength,
                'present_breadth' => $req->presentBreadth,
                'present_height' => $req->presentHeight,
                'no_of_floors' => $req->noOfFloors,
                'present_occupier' => $req->presentOccupier,
                'trade_license' => $req->tradeLicense,
                'construction' => $req->construction,
                'electricity' => $req->electricity,
                'water' => $req->water,
                'sale_purchase' => $req->salePurchase,
                'contact_no' => $req->contactNo,
                'longitude' => $req->longitude,
                'latitude' => $req->latitude,
                'photo1_path' => $imageName1 ?? "",
                'photo1_path_absolute' => $imageName1Absolute ?? "",
                'photo2_path' => $imageName2 ?? "",
                'photo2_path_absolute' => $imageName2Absolute ?? "",
                'remarks' => $req->remarks,
                'last_tran_id' => $req->lastTranId,
                'user_id' => $req->userId,
                'ulb_id' => $req->ulbId,
            ];

            if (isset($req->status)) {                  // In Case of Deactivation or Activation
                $status = $req->status == false ? 0 : 1;
                $metaReqs = array_merge($metaReqs, ['status', $status]);
            }

            $Shops = $this->_mShops::findOrFail($req->id);

            $Shops->update($metaReqs);
            return responseMsgs(true, "Successfully Updated", [], "050203", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {

            return responseMsgs(false, $e->getMessage(), [], 050203, "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    //View by id
    public function show(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|integer'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $Shops = $this->_mShops->getGroupById($req->id);

            if (collect($Shops)->isEmpty())
                throw new Exception("Shop Does Not Exists");
            return responseMsgs(true, "", $Shops, 050204, "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], 050204, "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    //View All Shop Data
    public function retrieve(Request $req)
    {
        try {
            $shops = $this->_mShops->retrieveAll();
            return responseMsgs(true, "", $shops, 050205, "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], 050205, "1.0", responseTime(), "POST", $req->deviceId);
        }
    }


    //View All Active Shops
    public function retrieveAllActive(Request $req)
    {
        try {
            $shops = $this->_mShops->retrieveActive();
            return responseMsgs(true, "", $shops, 050206, "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], 050206, "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    //delete
    public function delete(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'status' => 'required|bool'
        ]);

        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors(), []);
        }
        try {
            if (isset($req->status)) { // In Case of Deactivation or Activation
                $status = $req->status == false ? 0 : 1;
                $metaReqs = [
                    'status' => $status
                ];
            }
            $Shops = $this->_mShops::findOrFail($req->id);
            $Shops->update($metaReqs);
            return responseMsgs(true, "Status Updated Successfully", [], 050207, "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], 050207, "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
}
