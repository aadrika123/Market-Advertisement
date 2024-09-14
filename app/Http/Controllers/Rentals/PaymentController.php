<?php

namespace App\Http\Controllers\Rentals;

use App\Http\Controllers\Controller;
use App\MicroServices\IdGenerator\PrefixIdGenerator;
use App\Models\Rentals\MarDailycollection;
use App\Models\Rentals\MarDailycollectiondetail;
use App\Models\Rentals\MarTollDemand;
use App\Models\Rentals\MarTollPayment;
use App\Models\Rentals\ShopPayment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    // created by Arshad Hussain 

    /**
     * | Unverified Cash Verification List
     * | Its for shop or toll 
     */
    public function listCashVerificationDtl(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'date' => 'required|date',
            'userId' => 'nullable|int',
            'moduleId' => 'required|int|in:1,2'
        ]);

        if ($validator->fails()) {
            return validationError($validator);
        }

        try {
            $apiId = "0703";
            $version = "01";
            $user = authUser($req);
            $date = date('Y-m-d', strtotime($req->date));
            $userId = $req->userId;

            if ($req->moduleId == 1) {
                $mShopTrans = new ShopPayment();

                if (isset($userId)) {                                                     // if userId exist 
                    $data = $mShopTrans->cashDtl($date)
                        ->where('mar_shop_payments.ulb_id', $user->ulb_id)
                        ->where('user_id', $userId)
                        ->get();
                } else {
                    $data = $mShopTrans->cashDtl($date)                                   // search by Date  
                        ->where('mar_shop_payments.ulb_id', $user->ulb_id)
                        ->get();
                }

                $collection = collect($data->groupBy("user_id")->values());

                $data = $collection->map(function ($val) use ($date) {
                    $total = $val->sum('amount');

                    return [
                        "id" => $val[0]['id'],
                        "user_id" => $val[0]['user_id'],
                        "officer_name" => $val[0]['name'],
                        "mobile" => $val[0]['mobile'],
                        "amount" => $total,
                        "date" => Carbon::parse($date)->format('d-m-Y'),
                    ];
                });
            } else {
                $mTollPayments = new MarTollPayment();

                if (isset($userId)) {
                    $data = $mTollPayments->cashDtl($date)
                        ->where('mar_toll_payments.ulb_id', $user->ulb_id)
                        ->where('user_id', $userId)
                        ->get();
                } else {
                    $data = $mTollPayments->cashDtl($date)
                        ->where('mar_toll_payments.ulb_id', $user->ulb_id)
                        ->get();
                }

                $collection = collect($data->groupBy("user_id")->values());

                $data = $collection->map(function ($val) use ($date) {
                    $total = $val->sum('amount');

                    return [
                        "id" => $val[0]['id'],
                        "user_id" => $val[0]['user_id'],
                        "officer_name" => $val[0]['name'],
                        "mobile" => $val[0]['mobile'],
                        "amount" => $total,
                        "date" => Carbon::parse($date)->format('d-m-Y'),
                    ];
                });
                // $data = [];
            }

            return responseMsgs(true, "Cash Verification List", $data, $apiId, $version, responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", $apiId, $version, responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Details of Transactions
     * | Tc Collection Dtl
     */
    public function cashVerificationDtl(Request $req)
    {
        $validator = Validator::make($req->all(), [
            "date" => "required|date",
            "userId" => "required|int",
            'moduleId' => 'required|int|in:1,2'
        ]);
        if ($validator->fails())
            return validationError($validator);
        try {
            $apiId = "0704";
            $version = "01";
            if ($req->moduleId == 1) {
                $mShopPayments = new shopPayment();
                $userId =  $req->userId;
                $date = date('Y-m-d', strtotime($req->date));
                $details = $mShopPayments->cashDtl($date, $userId)
                    ->where('user_id', $userId)
                    ->get();
                // $details;

                if (collect($details)->isEmpty())
                    throw new Exception("No Application Found for this id");
            } else {
                $mTollPayments = new MarTollPayment();
                $userId =  $req->userId;
                $date = date('Y-m-d', strtotime($req->date));
                $details = $mTollPayments->cashDtl($date, $userId)
                    ->where('user_id', $userId)
                    ->get();
                // $details;

                if (collect($details)->isEmpty())
                    throw new Exception("No Application Found for this id");
            }
            $data['tranDtl'] = collect($details)->values();
            $data['Cash'] = collect($details)->where('pmt_mode', 'CASH')->sum('amount');
            $data['totalAmount'] =  $details->sum('amount');
            $data['numberOfTransaction'] =  $details->count();
            $data['date'] = Carbon::parse($date)->format('d-m-Y');
            $data['tcId'] = $userId;

            return responseMsgs(true, "Cash Verification Details", remove_null($data), $apiId, $version, responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", $apiId, $version, responseTime(), "POST", $req->deviceId);
        }
    }
    /**
     * | Verify Cash of Shop Or Toll
     */
    public function verifyCash(Request $req)
    {
        $validator = Validator::make($req->all(), [
            "date"          => "required|date",
            "tcId"          => "required|int",
            "id"            => "required|array",
            'moduleId'      => 'required|int|in:1,2'
        ]);

        if ($validator->fails()) {
            return validationError($validator);
        }

        try {
            $apiId = "0705";
            $version = "01";
            $user = authUser($req);
            $userId = $user->id;
            $ulbId = $user->ulb_id;

            $mShopTransaction = new ShopPayment();
            $mTollPayments = new MarTollPayment();
            $mShopDailycollection = new MarDailycollection();
            $mShopDailycollectiondetail = new MarDailycollectiondetail();
            $receiptIdParam = Config::get('constants.ID_GENERATION_PARAMS.CASH_VERIFICATION_ID');

            DB::beginTransaction();
            $idGeneration = new PrefixIdGenerator($receiptIdParam, $ulbId, 000, 0);
            $receiptNo = $idGeneration->generate();

            $totalAmount = 0;

            if ($req->moduleId == 1) {
                $totalAmount = $mShopTransaction->whereIn('id', $req->id)->sum('amount');
            } elseif ($req->moduleId == 2) {
                $totalAmount = $mTollPayments->whereIn('id', $req->id)->sum('amount');
            }

            $mReqs = [
                "receipt_no"     => $receiptNo,
                "user_id"        => $userId,
                "tran_date"      => Carbon::parse($req->date)->format('Y-m-d'),
                "deposit_date"   => Carbon::now(),
                "deposit_amount" => $totalAmount,
                "tc_id"          => $req->tcId,
            ];

            $collectionDtl = $mShopDailycollection->store($mReqs);

            foreach ($req->id as $id) {
                $collectionDtlsReqs = [
                    "collection_id"  => $collectionDtl->id,
                    "transaction_id" => $id,
                ];
                $mShopDailycollectiondetail->store($collectionDtlsReqs);
            }

            // Update the transaction table based on moduleId
            if ($req->moduleId == 1) {
                $mShopTransaction->whereIn('id', $req->id)->update(['is_verified' => 1]);
            } elseif ($req->moduleId == 2) {
                $mTollPayments->whereIn('id', $req->id)->update(['is_verified' => 1]);
            }

            DB::commit();
            return responseMsgs(true, "Cash Verified", ["receipt_no" => $receiptNo], $apiId, $version, responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", $apiId, $version, responseTime(), "POST", $req->deviceId);
        }
    }
    #========================================================= Bank Reconcillation ===========================================================#
    public function searchTransaction(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fromDate' => 'required',
                'toDate' => 'required',
                'moduleId' => 'required|int|in:1,2'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validator->errors()
                ]);
            }
            $ulbId = authUser($request)->ulb_id;
            $moduleId = $request->moduleId;
            $paymentMode = $request->paymentMode;
            $verifyStatus = $request->verificationType;
            $fromDate = Carbon::create($request->fromDate)->format('Y-m-d');
            $toDate = Carbon::create($request->toDate)->format('Y-m-d');

            $data = $this->CommonHandleTransaction($ulbId, $request, $fromDate, $toDate, $moduleId);                 // common funtion all workflows of ADVERTISEMENT & MARKET

            $data = $this->filterDataByPaymentMode($data, $paymentMode);
            $data = $this->filterDataByVerificationStatus($data, $verifyStatus);

            if (collect($data)->isNotEmpty()) {
                return responseMsgs(true, "Data Acording to request!", $data, '010801', '01', '382ms-547ms', 'Post', '');
            }
            return responseMsg(false, "data not found!", "");
        } catch (Exception $error) {
            return responseMsg(false, "ERROR!", $error->getMessage());
        }
    }

    /**\
     * | This is common function for searching chewque details 
     * | Function = 2
     */

    private function CommonHandleTransaction($ulbId, $request, $fromDate, $toDate, $moduleId)
    {
        $mShopPayments = new ShopPayment();
        $mTollPayment =  new MarTollPayment();
        if ($moduleId == 1) {
            $chequeTranDtl = $mShopPayments->chequeTranDtl($ulbId);
            $chequeTranDtl = $chequeTranDtl->where('mar_shop_payments.workflow_id', $moduleId);
            if ($request->verificationType != "bounce") {
                $chequeTranDtl = $chequeTranDtl->where("mar_shop_payments.status", 1);
            }
            if ($request->chequeNo) {
                return $chequeTranDtl->where('cheque_no', $request->chequeNo)->get();
            }
        } else {
            $chequeTranDtl = $mTollPayment->chequeTranDtl($ulbId);
            $chequeTranDtl = $chequeTranDtl->where('mar_shop_payments.workflow_id', $moduleId);
            if ($request->verificationType != "bounce") {
                $chequeTranDtl = $chequeTranDtl->where("mar_shop_payments.status", 1);
            }
            if ($request->chequeNo) {
                return $chequeTranDtl->where('cheque_no', $request->chequeNo)->get();
            }
        }

        return $chequeTranDtl->whereBetween('transaction_date', [$fromDate, $toDate])->get();
    }

    /**
     * | Function = 3
     */
    private function filterDataByPaymentMode($data, $paymentMode)
    {
        if ($paymentMode == 'DD') {
            $filteredData = collect($data)->where('payment_mode', 'DD');
            return array_values(objtoarray($filteredData));
        }
        if ($paymentMode == 'CHEQUE') {
            $filteredData = collect($data)->where('payment_mode', 'CHEQUE');
            return array_values(objtoarray($filteredData));
        }
        if ($paymentMode == 'NEFT') {
            $filteredData = collect($data)->where('payment_mode', 'NEFT');
            return array_values(objtoarray($filteredData));
        }
        return $data;
    }

    /**
     * |Function = 4
     */
    private function filterDataByVerificationStatus($data, $verifyStatus)
    {
        if ($verifyStatus == 'pending') {
            $filteredData = collect($data)->where('status', '2');
            return array_values(objtoarray($filteredData));
        }
        if ($verifyStatus == 'clear') {
            $filteredData = collect($data)->where('status', '1');
            return array_values(objtoarray($filteredData));
        }
        if ($verifyStatus == 'bounce') {
            $filteredData = collect($data)->where('status', '3');
            return array_values(objtoarray($filteredData));
        }
        return $data;
    }

    /**
     * |search transaction Number for deactivation 
     */
    public function searchTransactionNo(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'transactionNo' => 'required|'
        ]);
        if ($validator->fails()) {
            return  $validator->errors();
        }
        try {
            $mShopPayment = new ShopPayment();
            // $list = $mShop->searchShopForPayment($req->shopCategoryId, $req->circleId, $req->marketId);
            DB::enableQueryLog();
            $list = $mShopPayment->searchTranasction($req->transactionNo);                                       // Get List Shop FOr Payment
            $list = paginator($list, $req);
            // Add the 'module' key with value 'Shop' to each item in the data list
            foreach ($list['data'] as &$item) {
                $item['module'] = 'Shop';
            }
            // return [dd(DB::getQueryLog())];
            return responseMsgs(true, "Transaction Fecth Successfully !!!",  $list, "055012", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055012", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
    /**
     * |search transaction Number for deactivation 
     */
    public function transactionDeactList(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fromDate' => 'required|date_format:Y-m-d',
            'toDate' => $req->fromDate != NULL ? 'required|date_format:Y-m-d|after_or_equal:fromDate' : 'nullable|date_format:Y-m-d',
            'verificationType ' => 'nullable|in:1,2,3',
            'paymentMode'   => 'nullable|in:CHEQUE,DD,NEFT'
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors()->first(), [], "055014", "1.0", responseTime(), "POST", $req->deviceId);
        }
        try {
            $mMarShopPayment = new ShopPayment();
            $data = $mMarShopPayment->transactionDeactList($req);                                                   // Get List of Cheque or DD
            if ($req->fromDate != NULL) {
                $data = $data->whereBetween('mar_shop_payments.deactive_date', [$req->fromDate, $req->toDate]);
            }
            if ($req->verificationType != NULL) {
                $data = $data->where('mar_shop_payments.payment_status', $req->verificationType);
            }
            if ($req->paymentMode != NULL) {
                $data = $data->where('mar_shop_payments.pmt_mode', $req->paymentMode);
            }
            $list = paginator($data, $req);
            return responseMsgs(true, "List Of Deactivation Transaction", $list, "055015", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055015", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
}
