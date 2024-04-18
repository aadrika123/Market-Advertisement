<?php

namespace App\Http\Controllers\Rentals;

use App\BLL\Market\ShopPaymentBll;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ShopRequest;
use App\MicroServices\DocumentUpload;
use App\Models\Master\MCircle;
use App\Models\Master\MMarket;
use App\Models\Rentals\MarShopDemand;
use App\Models\Rentals\MarTollPayment;
use App\Models\Rentals\Shop;
use App\Models\Rentals\ShopConstruction;
use App\Models\Rentals\ShopPayment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;


use App\Traits\ShopDetailsTraits;
use Carbon\Carbon;

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
            "paymentMode" => 'required|string',
            "month" => 'required|string',
        ]);
        // $validator->sometimes("paymentFrom", "required|date|date_format:Y-m-d|before_or_equal:$req->paymentTo", function ($input) use ($shopPmtBll) {
        //     $shopPmtBll->_shopDetails = $this->_mShops::findOrFail($input->shopId);
        //     $shopPmtBll->_tranId = $shopPmtBll->_shopDetails->last_tran_id;
        //     return !isset($shopPmtBll->_tranId);
        // });

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
     * | Entry Cheque or DD For Payment
     * | API - 14
     * | Function - 14
     */
    public function entryCheckOrDD(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'shopId' => 'required|integer',
            'bankName' => 'required|string',
            'branchName' => 'required|string',
            'chequeNo' => $req->ddNo == NULL ? 'required|' : 'nullable|',
            'ddNo' => $req->chequeNo == NULL ? 'required|numeric' : 'nullable|numeric',
            "month" => 'required|string',
            "paymentMode" => 'required|string',
            "chequeDdDate" => 'required|date_format:Y-m-d|after_or_equal:' . Carbon::now()->subMonth(3)->format('d-m-Y'),
            'photo'  =>   'nullable|image|mimes:jpg,jpeg,png',
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors()->first(), [], "055014", "1.0", responseTime(), "POST", $req->deviceId);
        }
        try {
            $docUpload = new DocumentUpload;
            $relativePath = Config::get('constants.SHOP_PATH');
            if (isset($req->photo)) {
                $image = $req->file('photo');
                $refImageName = 'Shop-cheque-1' . $req->allottee;
                $imageName1 = $docUpload->upload($refImageName, $image, $relativePath);
                $imageName1Absolute = $relativePath;
            }
            $req->merge(['photo_path' => $imageName1 ?? ""]);
            $req->merge(['photo_path_absolute' => $imageName1Absolute ?? ""]);
            $mMarShopPayment = new ShopPayment();
            DB::beginTransaction();
            $res = $mMarShopPayment->entryCheckDD($req);                                                            // Store Cheque or DD Details in Shop Payment Table
            DB::commit();
            return responseMsgs(true, "Cheque or DD Entry Successfully", ['details' => $res['createdPayment']], "055014", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "055014", "1.0", responseTime(), "POST", $req->deviceId);
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
            $list['todayCollection'] = $mShopPayment->todayShopCollection($req->auth['ulb_id'])->whereBetween('payment_date', [$fromDate, $toDate])->get()->sum('amount');
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
            $mShop = new Shop();
            $reciept = $mShop->getShopReciept($req->shopId);
            if (!$reciept)
                throw new Exception("Reciept Data Not Fetched !!!");
            $reciept->inWords = getIndianCurrency($reciept->last_payment_amount) . " Only /-";
            return responseMsgs(true, "Reciept Data Fetch Successfully !!!",  $reciept, "055017", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "055017", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * |Shop demand generation 
     * |Function - 18 
     * |API - 18
     */
    public function generateShopDemand(Request $request)
    {
        $shopPmtBll = new ShopPaymentBll();
        $validator = Validator::make($request->all(), [
            "shopId" => "required|integer",
        ]);
        if ($validator->fails())
            return $validator->errors();
        // Business Logics
        try {
            $shopPmtBll->shopDemand($request);
            $shopDetails = Shop::find($request->shopId);
            DB::commit();
            return responseMsgs(true, "Demand Generate Successfully", ['shopNo' => $shopDetails->shop_no], "055001", "1.0", responseTime(), "POST", $request->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "055001", "1.0", responseTime(), "POST", $request->deviceId);
        }
    }
    /**
     * |Shop demand generation 
     * |Function - 18 
     * |API - 18
     */
    public function generateAllShopDemand(Request $request)
    {
        $shopPmtBll = new ShopPaymentBll();
        try {
            $shopIds = Shop::pluck('id')->toArray();
            $getShop = collect($shopIds);
            foreach ($shopIds as $shopId) {
                $req = new \Illuminate\Http\Request();
                $req->replace(['shopId' => $shopId]); // Set the shopId in the request

                $validator = Validator::make($req->all(), [
                    "shopId" => "required|integer",
                ]);

                if ($validator->fails()) {
                    return $validator->errors();
                }
                $shopPmtBll->allShopDemand($req);

                DB::commit();
                return responseMsgs(true, "Demand Generate Successfully", ['shopNo' => $shopIds->shop_no], "055001", "1.0", responseTime(), "POST", $request->deviceId);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "055001", "1.0", responseTime(), "POST", $request->deviceId);
        }
    }

    /**
     * | Generate Demand Reciept Details Before Payment
     */
    public function generateShopDemandBill(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'shopId' => 'required|integer',
            'month' => 'required|string',
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $mMarShopDemand = new MarShopDemand();
            $shopDemand = $mMarShopDemand->payBeforeDemand($req->shopId, $req->month);                            // Demand Details Before Payment 
            $demands['shopDemand'] = $shopDemand;
            $demands['totalAmount'] = round($shopDemand->pluck('amount')->sum());
            if ($demands['totalAmount'] > 0)
                $demands['amountinWords'] = getIndianCurrency($demands['totalAmount']) . "Only /-";
            $shopDetails = $this->_mShops->getShopDetailById($req->shopId);                                               // Get Shop Details By Shop Id
            $ulbDetails = DB::table('ulb_masters')->where('id', $shopDetails->ulb_id)->first();
            $demands['shopNo'] = $shopDetails->shop_no;
            // $demands['amcShopNo'] = $shopDetails->amc_shop_no;
            $demands['allottee'] = $shopDetails->allottee;
            $demands['market'] = $shopDetails->market_name;
            $demands['shopType'] = $shopDetails->shop_type;
            $demands['ulbName'] = $ulbDetails->ulb_name;
            $demands['tollFreeNo'] = $ulbDetails->toll_free_no;
            $demands['website'] = $ulbDetails->current_website;
            $demands['rentType'] =  $shopDetails->rent_type;
            $demands['aggrementEndDate'] =  $shopDetails->alloted_upto;
            return responseMsgs(true, "", $demands, "055030", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055030", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Calculate Shop rate monthly Wise
     */
    public function calculateShopRateMonhtlyWise(Request $req)
    {
        $shopPmtBll = new ShopPaymentBll();
        $validator = Validator::make($req->all(), [
            "shopId" => "required|integer",
            "month" => 'required|string',
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        // Business Logics
        try {
            $amount = $shopPmtBll->calculateShopRateMonhtly($req);                                        // Calculate amount according to Financial Year wise
            return responseMsgs(true, "Amount Fetch Successfully", ['amount' => $amount], "055013", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055013", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
    /**
     * | Get Shop Payment Reciept By Demand ID 
     */
    public function shopPaymentRecieptBluetoothPrint(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'tranId' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return  $validator->errors();
        }
        try {
            $data = ShopPayment::select('mar_shop_payments.*', 'users.name as receiver_name', 'users.mobile as receiver_mobile')
                ->leftjoin('users', 'users.id', 'mar_shop_payments.user_id')
                ->where('mar_shop_payments.id', $req->tranId)
                ->first();
            if (!$data)
                throw new Exception("Transaction Id Not Valid !!!");
            $shopDetails = $this->_mShops->getShopDetailById($data->shop_id);                                               // Get Shop Details By Shop Id
            $ulbDetails = DB::table('ulb_masters')->where('id', $shopDetails->ulb_id)->first();
            $reciept = array();
            $reciept['shopNo'] = $shopDetails->shop_no;
            $reciept['amcShopNo'] = $shopDetails->amc_shop_no;
            $reciept['paidFrom'] = $data->paid_from;
            $reciept['paidTo'] = $data->paid_to;
            $reciept['amount'] = $data->amount;
            $reciept['paymentDate'] =  Carbon::createFromFormat('Y-m-d', $data->payment_date)->format('d-m-Y');
            $reciept['paymentMode'] = $data->pmt_mode;
            $reciept['transactionNo'] = $data->transaction_id;
            $reciept['allottee'] = $shopDetails->allottee;
            $reciept['market'] = $shopDetails->market_name;
            $reciept['shopType'] = $shopDetails->shop_type;
            $reciept['ulbName'] = $ulbDetails->ulb_name;
            $reciept['tollFreeNo'] = $ulbDetails->toll_free_no;
            $reciept['website'] = $ulbDetails->current_website;
            // $reciept['ulbLogo'] =  $this->_ulbLogoUrl . $ulbDetails->logo;
            // $reciept['ulbLogo'] =  $this->_ulbLogoUrl . "Uploads/Icon/akolall.png";
            $reciept['receiverName'] =  $data->receiver_name;
            $reciept['receiverMobile'] =  $data->receiver_mobile;
            $reciept['paymentStatus'] = $data->payment_status == 1 ? "Success" : ($data->payment_status == 2 ? "Payment Made By " . strtolower($data->pmt_mode) . " are considered provisional until they are successfully cleared." : ($data->payment_status == 3 ? "Cheque Bounse" : "No Any Payment"));
            $reciept['amountInWords'] = getIndianCurrency($data->amount) . "Only /-";
            $reciept['aggrementEndDate'] =  $shopDetails->alloted_upto;
            $reciept['ownerName']   = $shopDetails->shop_owner_name;

            // If Payment By Cheque then Cheque Details is Added Here
            $reciept['chequeDetails'] = array();
            if (strtoupper($data->pmt_mode) == 'CHEQUE') {
                $reciept['chequeDetails']['cheque_date'] = Carbon::createFromFormat('Y-m-d', $data->cheque_date)->format('d-m-Y');;
                $reciept['chequeDetails']['cheque_no'] = $data->cheque_no;
                $reciept['chequeDetails']['bank_name'] = $data->bank_name;
                $reciept['chequeDetails']['branch_name'] = $data->branch_name;
            }
            // If Payment By DD then DD Details is Added Here
            $reciept['ddDetails'] = array();
            if (strtoupper($data->pmt_mode) == 'DD') {
                $reciept['ddDetails']['cheque_date'] = Carbon::createFromFormat('Y-m-d', $data->cheque_date)->format('d-m-Y');;
                $reciept['ddDetails']['dd_no'] = $data->dd_no;
                $reciept['ddDetails']['bank_name'] = $data->bank_name;
                $reciept['ddDetails']['branch_name'] = $data->branch_name;
            }
            return responseMsgs(true, "Shop Reciept Fetch Successfully !!!", $reciept, "055033", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055033", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
    /**
     * | Clear or Bounce Cheque or DD (i.e. After Bank Reconsile )
     * | API - 16
     * | Function - 16
     */
    public function clearOrBounceChequeOrDD(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'chequeId' => 'required|integer',
            'status' => 'required|integer',
            'date' => 'required|date_format:Y-m-d',
            'remarks' => $req->status == 3 ? 'required|string' : 'nullable|string',
            'amount' => $req->status == 3 ? 'nullable|numeric' : 'nullable',
            'bounceReason' => $req->status == 3 ? 'required|string' : 'nullable|string',
        ]);
        if ($validator->fails()) {
            return  $validator->errors();
        }
        try {
            DB::beginTransaction();
            $shopPayment = $mMarShopPayment = ShopPayment::find($req->chequeId);                                    // Get Entry Cheque Details                        
            // $mMarShopPayment->payment_date = Carbon::now()->format('Y-m-d');
            $mMarShopPayment->payment_status = $req->status;
            $mMarShopPayment->is_verified    = $req->status;
            $mMarShopPayment->bounce_amount  = $req->amount;
            $mMarShopPayment->bounce_reason  = $req->bounceReason;
            $mMarShopPayment->clear_or_bounce_date = $req->date;
            $mMarShopPayment->save();
            if ($req->status == 3) {
                // if cheque is bounced then demand is again generated
                $UpdateDetails = MarShopDemand::where('shop_id',  $shopPayment->shop_id)                             // Get Data For Again Demand Generate
                    ->where('monthly', '>=', $shopPayment->paid_from)
                    ->where('monthly', '<=', $shopPayment->paid_to)
                    ->where('amount', '>', 0)
                    ->orderBy('monthly', 'ASC')
                    ->get();
                foreach ($UpdateDetails as $updateData) {                                                           // Update Demand Table With Demand Generate 
                    $updateRow = MarShopDemand::find($updateData->id);
                    $updateRow->payment_date = Carbon::now()->format('Y-m-d');
                    $updateRow->payment_status = 0;
                    $updateRow->payment_date = NULL;
                    $updateRow->tran_id = NULL;
                    $updateRow->save();
                }
            }
            DB::commit();
            if ($req->status == 1) {
                $msg = $shopPayment->pmt_mode . " Cleared Successfully !!!";
                $shop = Shop::find($shopPayment->shop_id);

                return responseMsgs(true, $msg, '', "055016", "1.0", responseTime(), "POST", $req->deviceId);
            } else {
                $msg = $shopPayment->pmt_mode . " Has Been Bounced !!!";
                return responseMsgs(true, $msg, '', "055016", "1.0", responseTime(), "POST", $req->deviceId);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "055016", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | List Cash Verification userwise
     */
    public function listCashVerification(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'date' => 'nullable|date_format:Y-m-d',
            'reportType' => 'nullable|integer|in:0,1',        // 0 - Not Verified, 1 - Verified
            'shopType' => 'nullable|integer|in:1,2,3',
            'market' => 'nullable|integer',
            'circle' => 'nullable|integer',                    // Circle i.e. Zone
            'userId' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors()->first(), [], "055026", "1.0", responseTime(), "POST", $req->deviceId);
        }
        try {
            $mMarShopPayment = new ShopPayment();
            $data = $mMarShopPayment->getListOfPayment();
            if ($req->date != NULL)
                $data = $data->where("mar_shop_payments.payment_date", $req->date);
            if ($req->reportType != NULL)
                $data = $data->where("mar_shop_payments.is_verified", $req->reportType);
            // if ($req->shopType != NULL)
            //     $data = $data->where("t1.shop_category_id", $req->shopType);
            if ($req->market != NULL)
                $data = $data->where("t1.market_id", $req->market);
            if ($req->circle != NULL)
                $data = $data->where("t1.circle_id", $req->circle);
            if ($req->userId != NULL)
                $data = $data->where("user.id", $req->userId);
            $data = $data->groupBy('mar_shop_payments.user_id', 'user.name', 'user.mobile', 'circle_id', 'market_id',);
            $list = paginator($data, $req);
            return responseMsgs(true, "List of Cash Verification", $list, "055026", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055026", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | List Cash Verification Details by TC or Userwise
     * | API - 27
     * | Function - 27
     */
    public function listDetailCashVerification(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'date' => 'required|date_format:Y-m-d',
            'reportType' => 'nullable|integer|in:0,1',          // 0 - Not Verified, 1 - Verified
            'shopType' => 'nullable|integer|in:1,2,3',          // 1 - BOT Shop, 2 - City Shop, 3 - GP (Gram Panchyat Shop) Shop
            'market' => 'nullable|integer',
            'circle' => 'nullable|integer',                     // Circle i.e. Zone
            'userId' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors()->first(), [], "055027", "1.0", responseTime(), "POST", $req->deviceId);
        }
        try {
            $mMarShopPayment = new ShopPayment();
            $data = $mMarShopPayment->getListOfPaymentDetails();
            if ($req->date != NULL)
                $data = $data->where("mar_shop_payments.payment_date", $req->date);
            if ($req->reportType != NULL)
                $data = $data->where("mar_shop_payments.is_verified", $req->reportType);
            // if ($req->shopType != NULL)
            //     $data = $data->where("t1.shop_category_id", $req->shopType);
            if ($req->market != NULL)
                $data = $data->where("t1.market_id", $req->market);
            if ($req->circle != NULL)
                $data = $data->where("t1.circle_id", $req->circle);
            if ($req->userId != NULL)
                $data = $data->where("user.id", $req->userId);
            $list = $data->get();
            $cash = $cheque = $dd = 0;
            foreach ($list as $record) {
                if ($record->payment_mode == 'CASH') {
                    $cash += $record->amount;                                                       // Add Cash Amount in cash Variable
                }
                if ($record->payment_mode == 'CHEQUE') {
                    $cheque += $record->amount;                                                     // Add Cheque Amount in cheque Variable
                }
                if ($record->payment_mode == 'DD') {
                    $dd += $record->amount;                                                         // Add DD Amount in DD Variable
                }
            }
            $f_data['data'] = $list;
            $f_data['userDetails']['collector_name'] = $list[0]->collector_name;
            $f_data['userDetails']['total_amount'] = $data->sum('amount');
            $f_data['userDetails']['transactionDate'] = Carbon::createFromFormat('Y-m-d', $req->date)->format('d-m-Y');
            $f_data['userDetails']['no_of_transaction'] = count($list);
            $f_data['userDetails']['cash'] = $cash;
            $f_data['userDetails']['cheque'] = $cheque;
            $f_data['userDetails']['dd'] = $dd;
            return responseMsgs(true, "List of Cash Verification", $f_data, "055027", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055027", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | List cheque or DD For Clearance
     */
    public function listEntryCheckorDD(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fromDate' => 'nullable|date_format:Y-m-d',
            'toDate' => $req->fromDate != NULL ? 'required|date_format:Y-m-d|after_or_equal:fromDate' : 'nullable|date_format:Y-m-d',
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors()->first(), [], "055014", "1.0", responseTime(), "POST", $req->deviceId);
        }
        try {
            $mMarShopPayment = new ShopPayment();
            $data = $mMarShopPayment->listUnclearedCheckDD($req);                                                   // Get List of Cheque or DD
            if ($req->fromDate != NULL) {
                $data = $data->whereBetween('mar_shop_payments.payment_date', [$req->fromDate, $req->toDate]);
            }
            $list = paginator($data, $req);
            return responseMsgs(true, "List Uncleared Check Or DD", $list, "055015", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055015", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Verified Payment one or more than one
     */
    public function verifiedCashPayment(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'ids' => 'required|array',
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors()->first(), [], "055025", "1.0", responseTime(), "POST", $req->deviceId);
        }
        try {
            ShopPayment::whereIn('id', $req->ids)->update(['is_verified' => '1']);
            return responseMsgs(true, "Payment Verified Successfully !!!",  '', "055025", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055025", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Search Shop For Payment
     */
    public function searchShopForPayment(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'shopConstructionId' => 'required|integer',
            'circleId' => 'required|integer',
            'marketId' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return  $validator->errors();
        }
        try {
            $mShop = new Shop();
            // $list = $mShop->searchShopForPayment($req->shopCategoryId, $req->circleId, $req->marketId);
            DB::enableQueryLog();
            $list = $mShop->searchShopForPayment($req->shopConstructionId, $req->marketId);                                       // Get List Shop FOr Payment
            if ($req->key)
                $list = searchShopRentalFilter($list, $req);
            $list = paginator($list, $req);
            // return [dd(DB::getQueryLog())];
            return responseMsgs(true, "Shop List Fetch Successfully !!!",  $list, "055012", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055012", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
    /**
     * | Transaction Deactivation 
     */
    public function transactionDeactivation(Request $req)
    {
        $validator = Validator::make($req->all(), [
            "tranId" => "required|integer",
            "deactiveReason" => "required|string",
            "module" => "required|in:Shop",
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            if ($req->module == 'Shop') {
                $mMarShopPayment = new ShopPayment();
                DB::beginTransaction();
                $status = $mMarShopPayment->deActiveTransaction($req);
                DB::commit();
            }
            // $list = paginator($listShop, $req);
            return responseMsgs(true, "Transaction De-Active Successfully !!!", $status, "055044", "1.0", responseTime(), "POST");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "055044", "1.0", responseTime(), "POST");
        }
    }
    /**
     * | get Shop Master Data
     * | API - 11
     * | Function - 11
     */
    public function shopMaster(Request $req)
    {
        try {
            $mMCircle = new MCircle();
            $mShopConstruction = new ShopConstruction();                                                    
            $list['circleList'] = $mMCircle->getCircleByUlbId($req->auth['ulb_id']);                                // Get Circle / Zone by ULB Id
            $list['listConstruction'] = $mShopConstruction->listConstruction();                                     // Get List of Building Type
            $fYear = FyListdescForShop();                                                                           // Get Financial Year
            $f_y = array();
            foreach ($fYear as $key => $fy) {
                $f_y[$key]['id'] = $fy;
                $f_y[$key]['financialYear'] = $fy;
            }
            $list['fYear'] = $f_y;
            return responseMsgs(true, "Shop Type List !!!", $list, "055011", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055011", "1.0", responseTime(), "POST", $req->deviceId);
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
