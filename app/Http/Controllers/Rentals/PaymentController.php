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
    //

    /**
     * | Unverified Cash Verification List
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

                if (isset($userId)) {
                    $data = $mShopTrans->cashDtl($date)
                        ->where('mar_shop_payments.ulb_id', $user->ulb_id)
                        ->where('user_id', $userId)
                        ->get();
                } else {
                    $data = $mShopTrans->cashDtl($date)
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
}
