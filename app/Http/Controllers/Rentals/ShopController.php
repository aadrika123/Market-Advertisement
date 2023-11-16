<?php

namespace App\Http\Controllers\Rentals;

use App\BLL\Market\ShopPaymentBll;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ShopRequest;
use App\MicroServices\DocumentUpload;
use App\Models\Master\MCircle;
use App\Models\Master\MMarket;
use App\Models\Rentals\MarTollPayment;
use App\Models\Rentals\Shop;
use App\Models\Rentals\ShopPayment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;


use App\Traits\ShopDetailsTraits;

class ShopController extends Controller
{
    use ShopDetailsTraits;
    /**
     * | Created On-14-06-2023 
     * | Created By - Anshu Kumar
     * | Change By - Bikash Kumar
     */
    private $_mShops;
    private $_tranId;

    public function __construct()
    {
        $this->_mShops = new Shop();
    }

    /**
     * | Shop Payments
     * | Function - 01
     * | API - 01
     */
    public function shopPayment(Request $req)
    {
        $shopPmtBll = new ShopPaymentBll();
        $validator = Validator::make($req->all(), [
            "shopId" => "required|integer",
            "paymentTo" => "required|date|date_format:Y-m-d",
        ]);
        $validator->sometimes("paymentFrom", "required|date|date_format:Y-m-d|before_or_equal:$req->paymentTo", function ($input) use ($shopPmtBll) {
            $shopPmtBll->_shopDetails = $this->_mShops::findOrFail($input->shopId);
            $shopPmtBll->_tranId = $shopPmtBll->_shopDetails->last_tran_id;
            return !isset($shopPmtBll->_tranId);
        });

        if ($validator->fails())
            return $validator->errors();
        // Business Logics
        try {
            $shopPmtBll->shopPayment($req);
            $shopDetails = Shop::find($req->shopId);
            DB::commit();
            return responseMsgs(true, "Payment Done Successfully", ['shopNo' => $shopDetails->shop_no], "055001", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "055001", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Add Shop Records
     * | Function - 02
     * | API - 02
     */
    public function store(ShopRequest $req)
    {
        try {
            $docUpload = new DocumentUpload;
            $relativePath = Config::get('constants.SHOP_PATH');

            if (isset($req->photo1Path)) {
                $image = $req->file('photo1Path');
                $refImageName = 'Shop-Photo-1' . '-' . $req->allottee;
                $imageName1 = $docUpload->upload($refImageName, $image, $relativePath);
                // $absolutePath = $relativePath;
                $imageName1Absolute = $relativePath;
            }

            if (isset($req->photo2Path)) {
                $image = $req->file('photo2Path');
                $refImageName = 'Shop-Photo-2' . '-' . $req->allottee;
                $imageName2 = $docUpload->upload($refImageName, $image, $relativePath);
                // $absolutePath = $relativePath;
                $imageName2Absolute = $relativePath;
            }
            $shopNo = $this->shopIdGeneration($req->marketId);
            $metaReqs = [
                'circle_id' => $req->circleId,
                'market_id' => $req->marketId,
                'allottee' => $req->allottee,
                'shop_no' => $shopNo,
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
                'electricity_no' => $req->electricityNo,
                'water_consumer_no' => $req->waterConsumerNo,
                'user_id' => $req->auth['id'],
                'ulb_id' => $req->auth['ulb_id']
            ];
            // return $metaReqs;
            $this->_mShops->create($metaReqs);

            return responseMsgs(true, "Successfully Saved", ['shopNo' => $shopNo], "055002", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {

            return responseMsgs(false, $e->getMessage(), [], "055002", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Edit shop Records
     * | Function - 03
     * | API - 03
     */
    public function edit(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|numeric',
            'status' => 'nullable|bool'
        ]);
        if ($validator->fails())
            return $validator->errors();

        try {
            $docUpload = new DocumentUpload;
            $relativePath = Config::get('constants.SHOP_PATH');
            if (isset($req->photo1Path)) {
                $image = $req->file('photo1Path');
                $refImageName = 'Shop-Photo-1' . '-' . $req->allottee;
                $imageName1 = $docUpload->upload($refImageName, $image, $relativePath);
                // $absolutePath = $relativePath;
                $imageName1Absolute = $relativePath;
            }

            if (isset($req->photo2Path)) {
                $image = $req->file('photo2Path');
                $refImageName = 'Shop-Photo-2' . '-' . $req->allottee;
                $imageName2 = $docUpload->upload($refImageName, $image, $relativePath);
                // $absolutePath = $relativePath;
                $imageName2Absolute = $relativePath;
            }

            $metaReqs = [
                'circle_id' => $req->circleId,
                'market_id' => $req->marketId,
                'allottee' => $req->allottee,
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
                // 'photo1_path' => $imageName1 ?? "",
                // 'photo1_path_absolute' => $imageName1Absolute ?? "",
                // 'photo2_path' => $imageName2 ?? "",
                // 'photo2_path_absolute' => $imageName2Absolute ?? "",
                'remarks' => $req->remarks,
                'last_tran_id' => $req->lastTranId,
                'user_id' => $req->auth['id'],
                'ulb_id' => $req->auth['ulb_id']
            ];

            if (isset($req->status)) {                  // In Case of Deactivation or Activation
                $status = $req->status == false ? 0 : 1;
                $metaReqs = array_merge($metaReqs, ['status', $status]);
            }
            if (isset($req->photograph1)) {
                $metaReqs = array_merge($metaReqs, ['photo1_path', $imageName1]);
                $metaReqs = array_merge($metaReqs, ['photo1_path_absolute', $imageName1Absolute]);
            }

            if (isset($req->photograph2)) {
                $metaReqs = array_merge($metaReqs, ['photo2_path', $imageName2]);
                $metaReqs = array_merge($metaReqs, ['photo2_path_absolute', $imageName2Absolute]);
            }

            $Shops = $this->_mShops::findOrFail($req->id);

            $Shops->update($metaReqs);
            return responseMsgs(true, "Successfully Updated", [], "055003", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055003", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Get Shop Details By Id
     * | Function - 04
     * | API - 04
     */
    public function show(Request $req)
    {
        // $validator = Validator::make($req->all(), [
        //     'id' => 'required|numeric'
        // ]);
        // if ($validator->fails())
        //     return responseMsgs(false, $validator->errors(), []);
        // try {
        //     $Shops = $this->_mShops->getShopDetailById($req->id);

        //     if (collect($Shops)->isEmpty())
        //         throw new Exception("Shop Does Not Exists");
        //     return responseMsgs(true, "", $Shops, "055004", "1.0", responseTime(), "POST", $req->deviceId);
        // } catch (Exception $e) {
        //     return responseMsgs(false, $e->getMessage(), [], "055004", "1.0", responseTime(), "POST", $req->deviceId);
        // }
        $validator = Validator::make($req->all(), [
            'id' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $details = $this->_mShops->getShopDetailById($req->id);                                             // Get Shop Details By ID
            if (collect($details)->isEmpty())
                throw new Exception("Shop Does Not Exists");
            // Basic Details
            $basicDetails = $this->generateBasicDetails($details);                                              // Generate Basic Details of Shop
            $shop['shopDetails'] = $basicDetails;
            return responseMsgs(true, "", $shop, "055004", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055004", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | View All Shop Data
     * | Function - 05
     * | API - 05
     */
    public function retrieve(Request $req)
    {
        try {
            $shops = $this->_mShops->retrieveAll();
            return responseMsgs(true, "", $shops, "055005", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055005", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | View All Active Shops
     * | Function - 06
     * | API - 06
     */
    public function retrieveAllActive(Request $req)
    {
        try {
            $shops = $this->_mShops->retrieveActive();
            return responseMsgs(true, "", $shops, "055006", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055006", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Delete Shop by Id
     * | Function - 07
     * | API - 07
     */
    public function delete(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|integer',
            'status' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors(), []);
        }
        try {
            if (isset($req->status)) {                                                          // In Case of Deactivation or Activation
                $status = $req->status == '0' ? 0 : 1;
                $metaReqs = [
                    'status' => $status
                ];
            }
            if ($req->status == '0') {
                $message = "Shop De-Activated Successfully !!!";
            } else {
                $message = "Shop Activated Successfully !!!";
            }
            $Shops = $this->_mShops::findOrFail($req->id);
            $Shops->update($metaReqs);
            return responseMsgs(true, $message, [], "055007", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055007", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | List Ulb Wise Circle
     * | Function - 08
     * | API - 08
     */
    public function listUlbWiseCircle(Request $req)
    {
        try {
            $mMCircle = new MCircle();
            $list = $mMCircle->getCircleByUlbId($req->auth['ulb_id']);
            return responseMsgs(true, "Circle List Featch Successfully !!!", $list, "055008", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055008", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Get Market list Circle wise
     * | Function - 09
     * | API - 09
     */
    public function listCircleWiseMarket(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'circleId' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return  $validator->errors();
        }
        try {
            $mMMarket = new MMarket();
            $list = $mMMarket->getMarketByCircleId($req->circleId);
            return responseMsgs(true, "Market List Featch Successfully !!!", $list, "055009", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055009", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }


    /**
     * | Get Shop list by Market Id
     * | Function - 10
     * | API - 10
     */
    public function listShopByMarketId(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'marketId' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return  $validator->errors();
        }
        try {
            $mShop = new Shop();
            $list = $mShop->getShop($req->marketId);
            if ($req->key)
                $list = searchShopRentalFilter($list, $req);
            $list = paginator($list, $req);
            return responseMsgs(true, "Shop List Fetch Successfully !!!", $list, "055010", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055010", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Get list Shop 
     * | Function - 11
     * | API - 11
     */
    public function listShop(Request $req)
    {
        try {
            $ulbId = $req->auth['ulb_id'];
            $mShop = new Shop();
            $list = $mShop->getAllShopUlbWise($ulbId);
            if ($req->key)
                $list = searchShopRentalFilter($list, $req);
            $list = paginator($list, $req);
            return responseMsgs(true, "Shop List Fetch Successfully !!!", $list, "055011", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055011", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Get Shop Details By ID
     * | Function - 12
     * | API - 12
     */
    public function getShopDetailtId(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'shopId' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return  $validator->errors();
        }
        try {
            $mShop = new Shop();
            $list = $mShop->getShopDetailById($req->shopId);
            return responseMsgs(true, "Shop Details Fetch Successfully !!!", $list, "055012", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055012", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }


    /**
     * | Get Shop Collection Summery
     * | Function - 13
     * | API - 13
     */
    public function getShopCollectionSummary(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fromDate' => 'nullable|date_format:Y-m-d',
            'toDate' => $req->fromDate == NULL ? 'nullable|date_format:Y-m-d' : 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return  $validator->errors();
        }
        try {
            if ($req->fromDate == NULL) {
                $fromDate = date('Y-m-d');
                $toDate = date('Y-m-d');
            } else {
                $fromDate = $req->fromDate;
                $toDate = $req->toDate;
            }
            $mShopPayment = new ShopPayment();
            $list = $mShopPayment->paymentList($req->auth['ulb_id'])->whereBetween('payment_date', [$fromDate, $toDate]);
            $list = paginator($list, $req);
            $list['todayCollection'] = $mShopPayment->todayShopCollection($req->auth['ulb_id'], date('Y-m-d'))->get()->sum('amount');
            return responseMsgs(true, "Shop Summary Fetch Successfully !!!", $list, "055013", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055013", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Get TC Collection Datewise 
     * | Function - 14
     * | API - 14
     */
    public function getTcCollection(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fromDate' => 'nullable|date_format:Y-m-d',
            'toDate' => $req->fromDate == NULL ? 'nullable|date_format:Y-m-d' : 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return  $validator->errors();
        }
        try {
            $authUrl = Config::get('constants.AUTH_URL');
            if ($req->fromDate == NULL) {
                $fromDate = date('Y-m-d');
                $toDate = date('Y-m-d');
            } else {
                $fromDate = $req->fromDate;
                $toDate = $req->toDate;
            }
            $mShopPayment = new ShopPayment();
            $shopPayment = $mShopPayment->paymentListForTcCollection($req->auth['ulb_id'])->whereBetween('payment_date', [$fromDate, $toDate])->get();
            // $todayShopPayment = $mShopPayment->paymentListForTcCollection($req->auth['ulb_id'])->where('payment_date', date('Y-m-d'))->sum('amount');
            $todayShopPayment = $mShopPayment->paymentListForTcCollection($req->auth['ulb_id'])->whereBetween('payment_date', [$fromDate, $toDate])->sum('amount');
            $mMarTollPayment = new MarTollPayment();
            $tollPayment = $mMarTollPayment->paymentListForTcCollection($req->auth['ulb_id'])->whereBetween('payment_date', [$fromDate, $toDate])->get();
            // $todayTollPayment = $mMarTollPayment->paymentListForTcCollection($req->auth['ulb_id'])->where('payment_date', date('Y-m-d'))->sum('amount');
            $todayTollPayment = $mMarTollPayment->paymentListForTcCollection($req->auth['ulb_id'])->whereBetween('payment_date', [$fromDate, $toDate])->sum('amount');
            $totalCollection = collect($shopPayment)->merge($tollPayment);
            $refValues = collect($totalCollection)->pluck('user_id')->unique();
            $ids['ids'] = $refValues;
            $userDetails = Http::withToken($req->token)
                ->post($authUrl . 'api/user-managment/v1/crud/multiple-user/list', $ids);

            $userDetails = json_decode($userDetails);
            $list = collect($refValues)->map(function ($values) use ($totalCollection, $userDetails) {
                $ref['totalAmount'] = $totalCollection->where('user_id', $values)->sum('amount');
                $ref['userId'] = $values;
                $ref['tcName'] = collect($userDetails->data)->where('id', $values)->pluck('name')->first();
                return $ref;
            });
            $list1['list'] = $list->values();
            $list1['todayPayments'] = $todayTollPayment + $todayShopPayment;
            return responseMsgs(true, "TC Collection Fetch Successfully !!!", $list1, "055014", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055014", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Shop Payment By Admin
     * | Function - 15
     * | API - 15
     */
    public function shopPaymentByAdmin(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fromDate' => 'required|date_format:Y-m-d',
            'toDate' => 'required|date_format:Y-m-d',
            'shopNo' => 'required|string',
            'amount' => 'required|numeric',
            'due'    => 'required|numeric',
            'rate' =>   'required|numeric',
            'paymentDate' => 'required|date_format:Y-m-d',
            'collectedBy' => 'required|string',
            'remarks' => "required|string",
            'image'   => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        if ($validator->fails()) {
            return  $validator->errors();
        }
        try {
            $docUpload = new DocumentUpload;
            $relativePath = Config::get('constants.SHOP_PATH');
            if (isset($req->image)) {
                $image = $req->file('image');
                $refImageName = 'reciept' . '-' . time();
                $imageName1 = $docUpload->upload($refImageName, $image, $relativePath);
                // $absolutePath = $relativePath;
                $imageName1Absolute = $relativePath;
                $req->merge(['reciepts' => $imageName1]);
                $req->merge(['absolutePath' => $imageName1Absolute]);
            }
            $mShopPayment = new ShopPayment();
            $details = DB::table('mar_shops')->select('*')->where('shop_no', $req->shopNo)->first();
            if (!$details)
                throw new Exception("Shop Not Found !!!");
            $shopId = $details->id;
            $months = monthDiff($req->toDate, $req->fromDate) + 1;
            $req->merge(['months' => $months]);
            $paymentId = $mShopPayment->addPaymentByAdmin($req, $shopId);
            $mshop = new Shop();
            $mshopDetails = $mshop->find($shopId);
            $mshopDetails->last_tran_id = $paymentId;
            $mshopDetails->arrear = $req->due;
            $mshopDetails->save();
            return responseMsgs(true, "Payment Accept Successfully !!!", '', "055015", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055015", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Shop Payment By Admin
     * | Function - 16
     * | API - 16
     */
    public function calculateShopPrice(Request $req)
    {
        $shopPmtBll = new ShopPaymentBll();
        $validator = Validator::make($req->all(), [
            "shopId" => "required|integer",
            "paymentTo" => "required|date|date_format:Y-m-d",
        ]);
        $validator->sometimes("paymentFrom", "required|date|date_format:Y-m-d|before_or_equal:$req->paymentTo", function ($input) use ($shopPmtBll) {
            $shopPmtBll->_shopDetails = $this->_mShops::findOrFail($input->shopId);
            $shopPmtBll->_tranId = $shopPmtBll->_shopDetails->last_tran_id;
            return !isset($shopPmtBll->_tranId);
        });

        if ($validator->fails())
            return $validator->errors();
        // Business Logics
        try {
            $amount = $shopPmtBll->calculateShopPayment($req);
            return responseMsgs(true, "Payable Amount - $amount", ['amount' => $amount], "055016", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "055016", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Shop Payment By Admin
     * | Function - 17
     * | API - 17
     */
    public function shopReciept(Request $req)
    {
        $validator = Validator::make($req->all(), [
            "shopId" => "required|integer",
        ]);
        if ($validator->fails())
            return $validator->errors();
        // Business Logics
        try {
            $mShop=new Shop();
            $reciept = $mShop->getShopReciept($req->shopId);
            if(!$reciept)
                throw new Exception("Reciept Data Not Fetched !!!");
            $reciept->inWords=getIndianCurrency($reciept->last_payment_amount)." Only /-";
            return responseMsgs(true, "Reciept Data Fetch Successfully !!!",  $reciept, "055017", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "055017", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**================================================= Support Function ============================== */

    /**
     * | ID Generation For Shop
     * | Function - 18
     */
    public function shopIdGeneration($marketId)
    {
        $idDetails = DB::table('m_market')->select('shop_counter', 'market_name')->where('id', $marketId)->first();
        $market = strtoupper(substr($idDetails->market_name, 0, 3));
        $counter = $idDetails->shop_counter + 1;
        DB::table('m_market')->where('id', $marketId)->update(['shop_counter' => $counter]);
        return $id = "SHOP-" . $market . "-" . (1000 + $idDetails->shop_counter);
    }
}
