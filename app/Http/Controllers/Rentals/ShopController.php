<?php

namespace App\Http\Controllers\Rentals;

use App\BLL\Market\ShopPaymentBll;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ShopRequest;
use App\MicroServices\DocumentUpload;
use App\MicroServices\IdGeneration;
use App\MicroServices\IdGenerator\PrefixIdGenerator;
use App\Models\Advertisements\WfActiveDocument;
use App\Models\Master\MCircle;
use App\Models\Master\MMarket;
use App\Models\Rentals\MarDailycollection;
use App\Models\Rentals\MarDailycollectiondetail;
use App\Models\Rentals\MarShopDemand;
use App\Models\Rentals\MarShopLog;
use App\Models\Rentals\MarTollPayment;
use App\Models\Rentals\Shop;
use App\Models\Rentals\ShopConstruction;
use App\Models\Rentals\ShopPayment;
use App\Models\Rentals\ShopRazorpayRequest;
use App\Models\Rentals\ShopRazorpayResponse;
use App\Pipelines\Rentals\SearchByShopAlottee;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;


use App\Traits\ShopDetailsTraits;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;

class ShopController extends Controller
{
    use ShopDetailsTraits;
    /**
     * | Created On-14-06-2023 
     * | Created By - Anshu Kumar
     * | Change By - Bikash Kumar
     * | Change By - Arshad Hussain
     */
    private $_mShops;
    private $_tranId;
    private $_mShopDemand;

    public function __construct()
    {
        $this->_mShops = new Shop();
        $this->_mShopDemand   = new MarShopDemand();
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
            $shop = $shopPmtBll->shopPayment($req);
            $shopDetails = Shop::find($req->shopId);
            DB::commit();
            // $tranId = isset($response['TranId']) ? $response['TranId'] : null;
            return responseMsgs(true, "Payment Done Successfully", ['tranId' => $shop['tranId'], 'tranNo' => $shop['tranNo']], "055001", "1.0", responseTime(), "POST", $req->deviceId);
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
            // Initialize models
            $mCircle           = new MCircle();
            $mMarket           = new MMarket();
            $mWfActiveDocument = new WfActiveDocument();
            $mMarShopDemand    = new MarShopDemand();
            $docUpload         = new DocumentUpload();

            $relativePath = Config::get('constants.SHOP_PATH');
            $currentMonth = Carbon::now()->startOfMonth();

            // Step 1: Create Circle and Market
            $checkCircle = $mCircle->getCircleNameByUlbId($req->circleName, $req->auth['ulb_id'] ?? 0);

            if ($checkCircle == null || $checkCircle->isEmpty()) {
                // Circle does not exist, create new one
                $circleDetails = $mCircle->createCirlce($req);
            } else {
                // Circle already exists, use existing details
                $circleDetails = $checkCircle->first();
            }

            $checkMarket = $mMarket->getMarketNameByCircleId($req->marketName, $circleDetails->id);
            if ($checkMarket == null || $checkMarket->isEmpty()) {
                // Market does not exist, create new one
                $marketDetails = $mMarket->createMarket($req, $circleDetails);
            } else {
                // Market already exists, use existing details
                $marketDetails = $checkMarket->first();
            }
            $existingShop = $this->_mShops
                ->where('circle_id', $circleDetails->id)
                ->where('ulb_id', $req->auth['ulb_id'] ?? 0)
                ->where('market_id', $marketDetails->id)
                ->where('shop_no', $req->shopNo)
                ->first();

            if ($existingShop) {
                throw new Exception("A shop with the same Circle, Market, ULB, and Shop No already exists!");
            }

            // Step 2: Prepare Shop data
            $metaReqs = [
                'circle_id'           => $circleDetails->id,
                'market_id'           => $marketDetails->id,
                'asset_id'            => $req->assetId,
                'floor_id'            => $req->floorId,
                'floor_name'          => $req->floorName,
                'asset_name'          => $req->assetName,
                'allottee'            => $req->allottee,
                'shop_no'             => $req->shopNo,
                'address'             => $req->address,
                'rate'                => $req->rate,
                'arrear'              => $req->arrear,
                'allotted_length'     => $req->allottedLength,
                'allotted_breadth'    => $req->allottedBreadth,
                'allotted_height'     => $req->allottedHeight,
                'area'                => $req->area,
                'present_length'      => $req->presentLength,
                'present_breadth'     => $req->presentBreadth,
                'present_height'      => $req->presentHeight,
                'no_of_floors'        => $req->noOfFloors,
                'present_occupier'    => $req->presentOccupier,
                'trade_license'       => $req->tradeLicense,
                'construction'        => $req->construction,
                'electricity'         => $req->electricity,
                'water'               => $req->water,
                'sale_purchase'       => $req->salePurchase,
                'contact_no'          => $req->contactNo,
                'longitude'           => $req->longitude,
                'latitude'            => $req->latitude,
                'photo1_path'         => $imageName1 ?? "",
                'photo1_path_absolute' => $imageName1Absolute ?? "",
                'photo2_path'         => $imageName2 ?? "",
                'photo2_path_absolute' => $imageName2Absolute ?? "",
                'remarks'             => $req->remarks,
                'last_tran_id'        => $req->lastTranId,
                'electricity_no'      => $req->electricityNo,
                'water_consumer_no'   => $req->waterConsumerNo,
                'user_id'             => $req->auth['id'] ?? 0,
                'ulb_id'              => $req->auth['ulb_id'] ?? 0,
                'ward_no'             => $req->wardNo,
                'last_payment_date'   => $req->lastPayDt,
                'last_payment_amount' => $req->lastPayAmt,
                'apply_date'          => Carbon::now(),
            ];

            // Step 3: Save Shop
            $shop = $this->_mShops->create($metaReqs);
            $shopId = $shop->id;

            // Step 4: If arrear exists, create demand entry
            if (!empty($req->arrear)) {
                $demandReqs = [
                    'shop_id'      => $shopId,
                    'amount'       => $req->arrear,
                    'monthly'      => $currentMonth->format('Y-m-d'),
                    'payment_date' => Carbon::now(),
                    'user_id'      => $req->auth['id'] ?? 0,
                    'ulb_id'       => $req->auth['ulb_id'] ?? 0,
                ];

                $mMarShopDemand::create($demandReqs);
            }

            // Step 5: Upload Documents (if provided)
            if (!empty($req->documents)) {
                $this->uploadDocument($shopId, $req->documents, $req->auth);
            }

            // return responseMsgs(true, "Successfully Saved", ['shopNo' => $req->shopNo], "055002", "1.0", responseTime(), "POST", $req->deviceId ?? "");
            return responseMsgs(true, "Successfully Saved", ['shopNo' => $req->shopNo], "055002", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {

            return responseMsgs(false, $e->getMessage(), [], "055002", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }



    // public function store(ShopRequest $req)
    // {
    //     try {
    //         $docUpload = new DocumentUpload;
    //         $mWfActiveDocument = new WfActiveDocument();
    //         $mMarShopDemand    = new MarShopDemand();
    //         $relativePath = Config::get('constants.SHOP_PATH');
    //         $currentMonth = Carbon::now()->startOfMonth();

    //         // if (isset($req->photo1Path)) {
    //         //     $image = $req->file('photo1Path');
    //         //     $refImageName = 'Shop-Photo-1' . '-' . $req->allottee;
    //         //     $newRequest = new Request([
    //         //         'document' => $image
    //         //     ]);
    //         //     $imageName1 = $docUpload->upload($newRequest);
    //         //     // $absolutePath = $relativePath;
    //         //     $imageName1Absolute = $relativePath;
    //         // }

    //         // if (isset($req->photo2Path)) {
    //         //     $image = $req->file('photo2Path');
    //         //     $refImageName = 'Shop-Photo-2' . '-' . $req->allottee;
    //         //     $newRequest = new Request([
    //         //         'document' => $image
    //         //     ]);
    //         //     $imageName2 = $docUpload->upload($newRequest);
    //         //     // $absolutePath = $relativePath;
    //         //     $imageName2Absolute = $relativePath;
    //         // }

    //         // $shopNo = $this->shopIdGeneration($req->marketId);
    //         $Mcirlcle = new MCircle();
    //         $circleDetails = $Mcirlcle->createCirlce($req);

    //         $metaReqs = [
    //             // 'circle_id' => $req->assetId,
    //             // 'market_id' => $req->floorId,
    //             'asset_id' => $req->assetId,
    //             'floor_id' => $req->floorId,
    //             'floor_name' => $req->floorName,
    //             'asset_name' => $req->assetName,

    //             'allottee' => $req->allottee,
    //             // 'shop_no' => $shopNo,
    //             'shop_no' => $req->shopNo,
    //             'address' => $req->address,
    //             'rate' => $req->rate,
    //             'arrear' => $req->arrear,
    //             'allotted_length' => $req->allottedLength,
    //             'allotted_breadth' => $req->allottedBreadth,
    //             'allotted_height' => $req->allottedHeight,
    //             'area' => $req->area,
    //             'present_length' => $req->presentLength,
    //             'present_breadth' => $req->presentBreadth,
    //             'present_height' => $req->presentHeight,
    //             'no_of_floors' => $req->noOfFloors,
    //             'present_occupier' => $req->presentOccupier,
    //             'trade_license' => $req->tradeLicense,
    //             'construction' => $req->construction,
    //             'electricity' => $req->electricity,
    //             'water' => $req->water,
    //             'sale_purchase' => $req->salePurchase,
    //             'contact_no' => $req->contactNo,
    //             'longitude' => $req->longitude,
    //             'latitude' => $req->latitude,
    //             'photo1_path' => $imageName1 ?? "",
    //             'photo1_path_absolute' => $imageName1Absolute ?? "",
    //             'photo2_path' => $imageName2 ?? "",
    //             'photo2_path_absolute' => $imageName2Absolute ?? "",
    //             'remarks' => $req->remarks,
    //             'last_tran_id' => $req->lastTranId,
    //             'electricity_no' => $req->electricityNo,
    //             'water_consumer_no' => $req->waterConsumerNo,
    //             'user_id' => $req->auth['id'] ?? 0,
    //             'ulb_id' => $req->auth['ulb_id'] ?? 0,
    //             'ward_no' => $req->wardNo,
    //             'last_payment_date' => $req->lastPayDt,
    //             'last_payment_amount' => $req->lastPayAmt,
    //             'apply_date' => Carbon::now(),
    //         ];


    //         // return $metaReqs;
    //         $tempId = $this->_mShops->create($metaReqs)->id;
    //         $month = $currentMonth->format('Y-m-d');
    //         if ($req->arrear != null) {
    //             $demandReqs = [
    //                 'shop_id' => $tempId,
    //                 'amount' => $req->arrear,
    //                 'monthly' => $month,
    //                 'payment_date' => Carbon::now(),
    //                 'user_id' => $req->auth['id'] ?? 0,
    //                 'ulb_id' => $req->auth['ulb_id'] ?? 0,
    //             ];
    //             $this->_mShopDemand::create($demandReqs);
    //         }
    //         $mDocuments = $req->documents;
    //         $this->uploadDocument($tempId, $mDocuments, $req->auth);

    //         return responseMsgs(true, "Successfully Saved", ['shopNo' => $req->shopNo], "055002", "1.0", responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {

    //         return responseMsgs(false, $e->getMessage(), [], "055002", "1.0", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }
    public function uploadDocument($tempId, $documents, $auth)
    {
        $docUpload = new DocumentUpload;
        $mWfActiveDocument = new WfActiveDocument();
        $mMarShop = new Shop();
        // $relativePath = Config::get('constants.LODGE.RELATIVE_PATH');
        $relativePath = Config::get('constants.SHOP_PATH');

        collect($documents)->map(function ($doc) use ($tempId, $docUpload, $mWfActiveDocument, $mMarShop, $relativePath, $auth) {
            $metaReqs = array();
            $getApplicationDtls = $mMarShop->getApplicationDtls($tempId);
            $refImageName = $doc['docCode'];
            $refImageName = $getApplicationDtls->id . '-' . $refImageName;
            $documentImg = $doc['image'];
            //$imageName = $docUpload->upload($refImageName, $documentImg, $relativePath);
            $newRequest = new Request([
                'document' => $documentImg
            ]);
            $imageName = $docUpload->upload($newRequest);

            $metaReqs['moduleId'] = Config::get('workflow-constants.MARKET_MODULE_ID');
            $metaReqs['activeId'] = $getApplicationDtls->id;
            $metaReqs['workflowId'] = 0;
            $metaReqs['ulbId'] = $getApplicationDtls->ulb_id;
            $metaReqs['relativePath'] = $relativePath;
            $metaReqs['document'] = $imageName;
            $metaReqs['docCode'] = $doc['docCode'];
            $metaReqs['ownerDtlId'] = $doc['ownerDtlId'] ?? null;
            $metaReqs['uniqueId'] = $imageName['data']['uniqueId'];
            $metaReqs['referenceNo'] = $imageName['data']['ReferenceNo'];
            $a = new Request($metaReqs);
            // $mWfActiveDocument->postDocuments($a,$auth);
            $metaReqs =  $mWfActiveDocument->metaReqs($metaReqs);
            $mWfActiveDocument->create($metaReqs);
            // foreach($metaReqs as $key=>$val)
            // {
            //     $mWfActiveDocument->$key = $val;
            // }
            // $mWfActiveDocument->save();
        });
    }
    /**
     * |view uploaded documents
     */
    public function viewShopDocuments(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'applicationId' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors(), "", "050710", "1.0", "", "POST", $req->deviceId ?? "");
        }
        $workflowId = shop::find($req->applicationId);
        $mWfActiveDocument = new WfActiveDocument();
        $data = array();
        if ($req->applicationId) {
            $data = $mWfActiveDocument->uploadDocumentsShopViewById($req->applicationId);
        } else {
            throw new Exception("Required Application Id And Application Type");
        }
        $data = (new DocumentUpload())->getDocUrl($data);
        return responseMsgs(true, "Data Fetched", remove_null($data), "050118", "1.0", responseTime(), "POST", "");
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
            $mMarShopDemand = new MarShopDemand();
            $demands = $mMarShopDemand->getDemandByShopId($req->id);                                            // Get List of Generated All Demands against SHop
            $total = $demands->pluck('amount')->sum();
            $financialYear = $demands->where('payment_status', '0')->where('amount', '>', '0')->pluck('financial_year');
            $f_y = array();
            // foreach ($financialYear as $key => $fy) {
            //     $f_y[$key]['id'] = $fy;
            //     $f_y[$key]['financialYear'] = $fy;
            // }
            // $shop['fYear'] = $f_y;
            // $shop['demands'] = $demands;
            $shop['total'] =  round($total, 2);
            $mMarShopPayment = new ShopPayment(); // DB::enableQueryLog();
            $payments = $mMarShopPayment->getPaidListByShopId($req->id);                                        // Get Paid Demand Against Shop
            $totalPaid = $payments->pluck('amount')->sum();
            // $shop['payments'] = $payments;
            $shop['totalPaid'] =   round($totalPaid, 2);
            $shop['pendingAmount'] =  round(($total - $totalPaid), 2);
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
            // 'marketId' => 'required|integer'
            'assetId' => 'required|integer',
            'floorId' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return  $validator->errors();
        }
        try {
            $mShop = new Shop();
            $list = $mShop->getShop($req->assetId);
            if ($req->floorId) {
                $list->where('floor_id', $req->floorId);
            }

            if ($req->key)
                $list = searchShopRentalFilter($list, $req);
            $list = paginator($list, $req);
            return responseMsgs(true, "Shop List Fetch Successfully !!!", $list, "055010", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055010", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
    /**
     * | Get Shop list by Market Id
     * | Function - 10
     * | API - 10
     */
    public function listShopByMarketIdv1(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'marketId' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return  $validator->errors();
        }
        try {
            $mShop = new Shop();
            $list = $mShop->getShopv1($req->marketId);
            if ($req->floorId) {
                $list->where('floor_id', $req->floorId);
            }

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
            'empId' => 'nullable',
            'perPage' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $authUrl = Config::get('constants.AUTH_URL');
            $fromDate = $req->fromDate ?? date('Y-m-d');
            $toDate = $req->toDate ?? date('Y-m-d');
            $perPage = $req->perPage ?? 15;
            $page = $req->page ?? 1;

            $mShopPayment = new ShopPayment();
            $shopPaymentQuery = $mShopPayment->paymentListForTcCollection($req->auth['ulb_id'], $req->empId)
                ->whereBetween('payment_date', [$fromDate, $toDate]);
            if (!empty($req->empId)) {
                $shopPaymentQuery->where('user_id', $req->empId);
            }
            // $shopPayments = $shopPaymentQuery->get();
            $ShopPayment = $shopPaymentQuery->sum('amount');

            $mMarTollPayment = new MarTollPayment();
            $tollPaymentQuery = $mMarTollPayment->paymentListForTcCollection($req->auth['ulb_id'], $req->empId)
                ->whereBetween('payment_date', [$fromDate, $toDate])

                ->union($shopPaymentQuery);

            if (!empty($req->empId)) {
                $tollPaymentQuery->where('user_id', $req->empId);
            }

            // $tollPayments = $tollPaymentQuery->get();

            $TollPayment = $tollPaymentQuery->sum('amount');

            // Merge results
            // $totalCollection = $shopPaymentdtl->merge($tollPaymentdtl);
            $allPayments =  $tollPaymentQuery;

            // Paginate the merged results
            $paginator = $allPayments->paginate($perPage);
            $list = [
                "current_page" => $paginator->currentPage(),
                "last_page" => $paginator->lastPage(),
                "data" => $paginator->items(),
                "total" => $paginator->total(),
                'totalCollection' => $ShopPayment + $TollPayment,
                'summary' => [
                    'shop_payment_total' => $ShopPayment,
                    'toll_payment_total' => $TollPayment,
                ]
            ];

            return responseMsgs(true, "TC Collection Fetch Successfully !!!", $list, "055014", "1.0", responseTime(), "POST", $req->deviceId);
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
            "tranId" => "required|integer",
        ]);
        if ($validator->fails())
            return $validator->errors();
        // Business Logics
        try {
            $mShop = new Shop();
            $reciept = $mShop->getShopReciept($req->tranId);
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
     * |Funtion - 18
     * | API - 18
     * | made by Arshad 
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
     * | Function - 19
     * | API - 19
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
     * | Function - 20
     * | API - 20
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
     * | Funtcion -21
     * | API - 21 
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
     * | Funtcion - 22
     * | API - 22
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
     * | API - 23
     * | Function - 23
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
     * | Function - 24
     * | API - 24
     */
    public function listCashVerification(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'date' => 'required|date_format:Y-m-d',
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
            $perPage = $req->perPage ? $req->perPage : 5;
            $page = $req->page && $req->page > 0 ? $req->page : 1;
            $mMarShopPayment = new ShopPayment();
            $ulbId = $req->auth['ulb_id'];
            $data = $mMarShopPayment->getListOfPayment($ulbId);
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

            $paginator = $data->paginate($perPage);
            // Convert items to array and add date to each item
            $items = $paginator->items();
            $modifiedItems = array_map(function ($item) use ($req) {
                // Convert stdClass object to array and add date
                $itemArray = (array) $item;
                $itemArray['date'] = $req->date;
                return $itemArray;
            }, $items);

            $list = [
                "current_page" => $paginator->currentPage(),
                "last_page" => $paginator->lastPage(),
                "data" => $modifiedItems,
                "total" => $paginator->total(),
                "date" => $req->date,
            ];
            return responseMsgs(true, "List of Cash Verification", $list, "055026", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055026", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | List Cash Verification Details by TC or Userwise
     * | API - 25
     * | Function - 25
     */
    public function listDetailCashVerification(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'date' => 'nullable|date_format:Y-m-d',
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
     * | Function - 26 
     * | API - 26
     */
    public function listEntryCheckorDD(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fromDate' => 'nullable|date_format:Y-m-d',
            'toDate' => $req->fromDate != NULL ? 'required|date_format:Y-m-d|after_or_equal:fromDate' : 'nullable|date_format:Y-m-d',
            'verificationType ' => 'nullable|in:1,2,3',
            'paymentMode'   => 'nullable|in:CHEQUE,DD,NEFT'
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors()->first(), [], "055014", "1.0", responseTime(), "POST", $req->deviceId);
        }
        try {
            $mMarShopPayment = new ShopPayment();
            $ulbId = $req->auth['ulb_id'];
            $data = $mMarShopPayment->listUnclearedCheckDD($req, $ulbId);                                                   // Get List of Cheque or DD
            if ($req->fromDate != NULL) {
                $data = $data->whereBetween('mar_shop_payments.payment_date', [$req->fromDate, $req->toDate]);
            }
            if ($req->verificationType != NULL) {
                $data = $data->where('mar_shop_payments.payment_status', $req->verificationType);
            }
            if ($req->paymentMode != NULL) {
                $data = $data->where('mar_shop_payments.pmt_mode', $req->paymentMode);
            }
            $list = paginator($data, $req);
            return responseMsgs(true, "List Uncleared Check Or DD", $list, "055015", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055015", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Verified Payment one or more than one
     * | Function - 27
     * | API - 27
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
     * | Function - 28
     * | API - 28
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
     * | Function - 29
     * | API - 29
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
     * | API - 30
     * | Function - 30
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

    /**
     * | Edit Shop Data For Contact Number
     * | API - 31
     * | Function - 31
     */
    public function editShopData(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'shopId' => 'required|numeric',
            'contactNo' => 'nullable|numeric|digits:10',
            'rentType' => 'nullable|string',
            'remarks' => 'nullable|string',
            'amcShopNo' => 'nullable|string',
            'circleId' => 'nullable|integer',                                                               // Circle i.e. Zone
            'image' => 'nullable|image|mimes:jpg,jpeg,png',
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {
            $docUpload = new DocumentUpload;
            $relativePath = Config::get('constants.SHOP_PATH');
            if (isset($req->image)) {
                $image = $req->file('image');
                $refImageName = 'Shop-Photo-1';
                $imageName1 = $docUpload->upload($refImageName, $image, $relativePath);
                $imageName1Absolute = $relativePath;
            }
            $shopDetails = Shop::find($req->shopId);
            $shopDetails->contact_no = $req->contactNo;
            // $shopDetails->rent_type = $req->rentType;
            $shopDetails->circle_id = $req->circleId;
            $shopDetails->remarks = $req->remarks;
            if (isset($req->image)) {
                $shopDetails->photo1_path = $imageName1 ?? "";
                $shopDetails->photo1_path_absolute = $imageName1Absolute ?? "";
            }
            $shopDetails->save();
            // Generate Edit Logs
            $logData = [
                'shop_id' => $req->shopId,
                'user_id' => $req->auth['id'],
                'change_data' => json_encode($req->all()),
                'date' => Carbon::now()->format('Y-m-d'),
            ];
            MarShopLog::create($logData);
            return responseMsgs(true, "Update Shop Successfully !!!", '', "055018", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055018", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * |list shop demand 
     * | Function - 32
     * | API - 32
     */
    public function listShopDemand(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shopId' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            # initialise variable
            $mShop  = new Shop();
            $mShopDemand = new MarShopDemand();

            $shopDemand = $mShopDemand->getShopDemand($request->shopId);
            if ($shopDemand->isEmpty()) {
                throw new Exception('Shop demand not found');
            }
            return responseMsgs(true, "Shop Deamand !!!",  $shopDemand, "055018", "1.0", responseTime(), "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055018", "1.0", responseTime(), "POST", $request->deviceId);
        }
    }

    /**
     * | Initiate Online Payment
         razor pay request store pending
     */
    public function generatePaymentOrderId(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required',
            'month' => 'required'
        ]);
        if ($validator->fails()) {
            return $validator->errors();
        }
        try {
            // Variable initialization
            $mMarShopDemand   = new MarShopDemand();
            $mShopRazorpayRequest = new ShopRazorpayRequest();
            $getDemandDetails = $mMarShopDemand->demandDtls($req->id, $req->month);
            if (!$getDemandDetails) {
                throw new Exception('Demand Not Found');
            }
            // if()
            // $totalAmount      = round($getDemandDetails->pluck('amount')->sum());
            $reqData = [
                "id" => $getDemandDetails->id,
                'amount' => $getDemandDetails->amount,
                'workflowId' => $getDemandDetails->worklflow_id ?? 0,
                'ulbId' => $getDemandDetails->ulb_id ?? 2,
                'departmentId' => Config::get('workflow-constants.MARKET_MODULE_ID'),
                'auth' => $req->auth,
            ];
            $paymentUrl = Config::get('constants.PAYMENT_URL');
            $refResponse = Http::withHeaders([
                "api-key" => "eff41ef6-d430-4887-aa55-9fcf46c72c99"
            ])
                ->withToken($req->bearerToken())
                ->post($paymentUrl . 'api/payment/generate-orderid', $reqData);

            $orderData = json_decode($refResponse);
            if ($orderData->status == false) {
                throw new Exception(collect($orderData->message)->first());
            }

            $refPaymentRequest = [
                "order_id"       => $orderData->data->orderId,
                "application_id" => $orderData->data->applicationId,
                // "workflow_id"    => $orderData->data->workflowId,
                "department_id"  => $orderData->data->departmentId,
                "total_amount"   => $orderData->data->amount,
                "amount"         => $mMarShopDemand->amount,
                "penalty_amount" => $mMarShopDemand->amount,
            ];
            $mShopRazorpayRequest->store($refPaymentRequest);
            // return $data;

            return responseMsgs(true, "Payment OrderId Generated Successfully !!!", $orderData->data, "050421", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050421", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    /**
     * | End Online Payment
         razor pay response store pending
     */
    public function storeTransactionDtl(Request $req)
    {
        try {
            $mShopPayment = new ShopPayment();
            $mMarShopDemand   = new MarShopDemand();
            $mShopRazorpayRequest = new ShopRazorpayRequest();
            $mShopRazorpayResponse = new ShopRazorpayResponse();

            $getDemandDetails = $mMarShopDemand->demandDtls($req->id, $req->month);
            if (!$getDemandDetails) {
                throw new Exception('Demand Not Found');
            }
            if (!$mMarShopDemand)
                throw new Exception("Application Not Found");

            if ($mMarShopDemand->payment_status == 1)
                throw new Exception("Payment Already Done");

            $RazorPayRequest = $mShopRazorpayRequest->getRazorpayRequest($req);
            if (!$RazorPayRequest) {
                throw new Exception("Payment request data Not Found!");
            }

            $razorpayReqs = [
                "razorpay_request_id" => $RazorPayRequest->id,
                "order_id"            => $req->orderId,
                "payment_id"          => $req->paymentId,
                "application_id"      => $req->id,
                "amount"              => $req->amount,
                "workflow_id"         => $req->workflowId,
                "transaction_no"      => $req->transactionNo,
                "citizen_id"          => $req->userId,
                "ulb_id"              => $req->ulbId,
                "tran_date"           => date("Y-m-d", $req->tranDate),
                "gateway_type"        => $req->gatewayType,
                "department_id"       => $req->departmentId,
            ];

            $transanctionReqs = [
                "shop_id"           => $req->id,
                "payment_date"      => date("Y-m-d", $req->tranDate),
                "transaction_no"    => $req->transactionNo,
                "amount"            => $req->amount,
                "pmt_mode"          => $req->paymentMode,
                "workflow_id"       => $req->workflowId,
                "amount"            => $mMarShopDemand->payment_amount,
                "penalty_amount"    => $mMarShopDemand->penalty_amount,
                "ulb_id"            => $req->ulbId,
                "citizen_id"        => $req->userId,
                "status"            => 1,
            ];

            DB::beginTransaction();
            $mShopRazorpayResponse->store($razorpayReqs);
            $mShopPayment->store($transanctionReqs);
            $mMarShopDemand->update(["payment_status" => 1]);
            $RazorPayRequest->update(["status" => 1]);
            DB::commit();

            return responseMsgs(true, "Data Received", "", 100117, 01, responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", 100117, 01, responseTime(), $req->getMethod(), $req->deviceId);
        }
    }



    /**
     * |shop recipt list by Toll Id
     * | Function - 34
     * | API - 34
     */
    public function shopRecieptList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shopId' => 'required'
        ]);
        if ($validator->fails()) {
            return $validator->errors();
        }
        try {
            $mMarShopPayment = new shopPayment();
            $mMarShop = Shop::find($request->shopId);
            if (!$mMarShop) {
                throw new Exception("Data Not Found!");
            }
            $shopId = $mMarShop->id;
            $data = $mMarShopPayment->getshopPayment($shopId);
            if (collect($data)->isEmpty()) {
                return responseMsgs(false, "Payment Receipt not Found !!!", [], "050421", "1.0", responseTime(), "POST", $request->deviceId ?? "");
            }
            return responseMsgs(true, "Payment List !!!", $data, "050421", "1.0", responseTime(), "POST", $request->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "050421", "1.0", "", 'POST', $request->deviceId ?? "");
        }
    }
    /**
     * | List shop Collection between two dates
     * | API - 35
     * | Function - 35
     */
    public function listShopCollection(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'shopCategoryId' => 'nullable|integer',
            'marketId' => 'nullable|integer',
            'dateFrom' => 'nullable|date_format:Y-m-d',
            'dateTo' => 'nullable|date_format:Y-m-d|after_or_equal:fromDate',
            'paymentMode'  => 'nullable',
            "userId"  => 'nullable'
        ]);
        if ($validator->fails()) {
            return  $validator->errors();
        }
        // return $req->all();
        try {
            $paymentMode = null;
            $user = authUser($req);
            $userName = $user->name;
            if (!isset($req->dateFrom))
                $fromDate = Carbon::now()->format('Y-m-d');                                                 // if date Is not pass then From Date take current Date
            else
                $fromDate = $req->dateFrom;
            if (!isset($req->dateTo))
                $toDate = Carbon::now()->format('Y-m-d');                                                  // if date Is not pass then to Date take current Date
            else
                $toDate = $req->dateTo;

            if ($req->paymentMode) {
                $paymentMode = $req->paymentMode;
            }
            $mMarShopPayment = new ShopPayment();
            $ulbId = $req->auth['ulb_id'];
            $data = $mMarShopPayment->listShopCollection($fromDate, $toDate, $ulbId);                              // Get Shop Payment collection between givrn two dates
            if ($req->shopCategoryId != 0)
                $data = $data->where('t2.construction', $req->shopCategoryId);
            if ($req->paymentMode != 0)
                $data = $data->where('mar_shop_payments.pmt_mode', $req->paymentMode);
            if ($req->userId != 0)
                $data = $data->where('mar_shop_payments.user_id', $req->userId);
            if ($req->marketId != 0)
                $data = $data->where('t2.market_id', $req->marketId);
            if ($req->auth['user_type'] == 'JSK' || $req->auth['user_type'] == 'TC')
                $data = $data->where('mar_shop_payments.user_id', $req->auth['id']);
            // Calculate counts
            $cashCount = $data->clone()->where('mar_shop_payments.pmt_mode', 'CASH')->count();
            // $cashCount = $data->clone()->where('mar_shop_payments.pmt_mode', 'CASH')->where('')->count();
            $onlineCount = $data->clone()->where('mar_shop_payments.pmt_mode', 'ONLINE')->count();
            $chequeCount = $data->clone()->where('mar_shop_payments.pmt_mode', 'CHEQUE')->count();
            $cashAmount = $data->clone()->where('mar_shop_payments.pmt_mode', 'CASH')->sum('amount');
            $onlineAmount = $data->clone()->where('mar_shop_payments.pmt_mode', 'ONLINE')->sum('amount');
            $chequeAmount = $data->clone()->where('mar_shop_payments.pmt_mode', 'CHEQUE')->sum('amount');

            $list = paginator($data, $req);
            $list['collectAmount'] = $data->sum('amount');
            $list['cashCount'] = $cashCount;
            $list['onlineCount'] = $onlineCount;
            $list['chequeCount'] = $chequeCount;
            $list['cashAmount'] = $cashAmount;
            $list['onlineAmount'] = $onlineAmount;
            $list['chequeAmount'] = $chequeAmount;
            $list['userName'] = $userName;
            return responseMsgs(true, "Shop Collection List Fetch Succefully !!!", $list, "055017", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055017", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
    /**
     * | Entry Cheque or DD For Payment
     * | API - 36
     * | Function - 36
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
            return responseMsgs(true, "Cheque or DD Entry Successfully", ['tranId' => $res['lastTranId'], 'tranNo' => $res['tranNo']], "055014", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), [], "055014", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * |search for shop payment
     */
    public function searchShopPipeline(Request $request)
    {
        // Define validation rules
        $validated = Validator::make($request->all(), [
            'allottee'  => 'nullable',
            'shopNo'     => 'nullable',
            'pages'     => 'nullable',
            'wardId'    => 'nullable',
            'zoneId'    => 'nullable'
        ]);

        // Handle validation errors
        if ($validated->fails()) {
            return $this->validationError($validated);
        }
        try {
            $refNo = 0;
            $mShop = new Shop();
            // Create a pipeline to process the search
            $result = $mShop->searchShopForPaymentv1();
            // $result = HoardingMaster::where('status',1);
            // $mobile =  $this->_modelObj->getByItsDetailsV2($request, $key, $refNo, $request->auth['email']);
            $result = app(Pipeline::class)
                ->send($result)
                ->through([
                    SearchByShopAlottee::class,
                ])
                ->thenReturn()
                ->first();
            return responseMsgs(true, "Data According To Parameter!", remove_null($result), "", "01", "652 ms", "POST", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }




    /**================================================= Support Function ============================== */

    /**
     * | ID Generation For Shop
     * | Function - 37
     */
    public function shopIdGeneration($marketId)
    {
        $idDetails = DB::table('m_market')->select('shop_counter', 'market_name')->where('id', $marketId)->first();
        $market = strtoupper(substr($idDetails->market_name, 0, 3));
        $counter = $idDetails->shop_counter + 1;
        DB::table('m_market')->where('id', $marketId)->update(['shop_counter' => $counter]);
        return $id = "SHOP-" . $market . "-" . (1000 + $idDetails->shop_counter);
    }

    /**
     * | List shop Collection between two dates
     * | API - 35
     * | Function - 35
     */
    public function listShops(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'shopCategoryId' => 'nullable|integer',
            'marketId' => 'nullable|integer',
            'dateFrom' => 'nullable|date_format:Y-m-d',
            'dateTo' => 'nullable|date_format:Y-m-d|after_or_equal:fromDate',
            'paymentMode'  => 'nullable',
            "userId"  => 'nullable',
            "zoneId" => 'nullable'
        ]);
        if ($validator->fails()) {
            return  $validator->errors();
        }
        // return $req->all();
        try {
            $user = authUser($req);
            $perPage = $req->perPage ? $req->perPage : 5;
            $paymentMode = null;
            if (!isset($req->dateFrom))
                $fromDate = Carbon::now()->format('Y-m-d');                                                 // if date Is not pass then From Date take current Date
            else
                $fromDate = $req->dateFrom;
            if (!isset($req->dateTo))
                $toDate = Carbon::now()->format('Y-m-d');                                                  // if date Is not pass then to Date take current Date
            else
                $toDate = $req->dateTo;

            if ($req->paymentMode) {
                $paymentMode = $req->paymentMode;
            }
            $mMarShops = new shop();
            $data = $mMarShops->listShop($user);                              // Get Shop Payment collection between givrn two dates
            if ($req->shopCategoryId != 0)
                $data = $data->where('mar_shops.construction', $req->shopCategoryId);
            if ($req->marketId != 0)
                $data = $data->where('mar_shops.market_id', $req->marketId);
            if ($req->zoneId != 0)
                $data = $data->where('mar_shops.circle_id', $req->zoneId);
            if ($req->dateFrom != null && $req->dateTo != null)
                $data = $data->whereBetween('mar_shops.created_at', [$fromDate, $toDate]);
            $paginator = $data->paginate($perPage);
            $list = [
                "current_page" => $paginator->currentPage(),
                "last_page" => $paginator->lastPage(),
                "data" => $paginator->items(),
                "total" => $paginator->total(),

            ];
            return responseMsgs(true, "Shop List Fetch Succefully !!!", $list, "055017", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055017", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
}
