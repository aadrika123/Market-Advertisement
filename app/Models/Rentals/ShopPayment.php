<?php

namespace App\Models\Rentals;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShopPayment extends Model
{
  use HasFactory;
  protected $guarded = [];
  protected $table = 'mar_shop_payments';

  /**
   * | Get Payment List
   */
  public function paymentList($ulbId)
  {
    return self::select(
      'mar_shop_payments.*',
      'ms.shop_no',
      'mc.circle_name',
      'mm.market_name',
      DB::raw("TO_CHAR(mar_shop_payments.payment_date, 'DD-MM-YYYY') as payment_date"),
      DB::raw("TO_CHAR(mar_shop_payments.paid_from, 'DD-MM-YYYY') as paid_from"),
      DB::raw("TO_CHAR(mar_shop_payments.paid_to, 'DD-MM-YYYY') as paid_to"),
    )
      ->join('mar_shops as ms', 'ms.id', '=', 'mar_shop_payments.shop_id')
      ->join('m_circle as mc', 'mc.id', '=', 'ms.circle_id')
      ->join('m_market as mm', 'mm.id', '=', 'ms.market_id')
      ->where('mar_shop_payments.ulb_id', $ulbId);
  }

  /**
   * | Get TOday Shop Collection
   */
  public function todayShopCollection($ulbId)
  {
    return self::select('amount')
      ->where('ulb_id', $ulbId);
    // ->where('payment_date', $date);
    //  ->sum('amount');
  }

  /**
   * | Get List of Tc Collection
   */
  public function paymentListForTcCollection($ulbId)
  {
    return self::select('user_id', 'payment_date', 'amount')->where('ulb_id', $ulbId);
  }

  /**
   * | Payment Accept By Admin
   */
  public function addPaymentByAdmin($req, $shopId)
  {
    $metaReqs = $this->metaReqs($req, $shopId);
    return self::create($metaReqs)->id;
  }

  /**
   * | Make Payment Data For Payment By Admin  
   */
  public function metaReqs($req, $shopId)
  {
    return [
      "shop_id" => $shopId,
      "paid_from" => $req->fromDate,
      "paid_to" => $req->fromDate,
      "amount" => $req->amount,
      "pmt_mode" => "CASH",
      "rate" => $req->rate,
      "payment_date" => $req->paymentDate,
      "remarks" => $req->remarks,
      "is_active" => '1',
      "ulb_id" => $req->auth['ulb_id'],
      "collected_by" => $req->collectedBy,
      "reciepts" => $req->reciepts,
      "absolute_path" => $req->absolutePath,
      "months" => $req->months,
      "demand" => $req->months * $req->rate,
    ];
  }
  /**
   * | Entry Check or DD
   */
  public function entryCheckDD($req)
  {
    // Get Amount For Payment
    $amount = DB::table('mar_shop_demands')
      ->where('shop_id', $req->shopId)
      ->where('payment_status', 0)
      ->where('monthly', '<=', $req->month)
      ->orderBy('monthly', 'ASC')
      ->sum('amount');
    if ($amount < 1)
      throw new Exception("No Any Due Amount !!!");
    $shopDetails = DB::table('mar_shops')->select('*')->where('id', $req->shopId)->first();                                       // Get Shop Details For Payment

    $month = DB::table('mar_shop_demands')                                                                                // Get First Financial Year where Payment start
      ->where('shop_id', $req->shopId)
      ->where('payment_status', 0)
      ->where('amount', '>', '0')
      ->where('monthly', '<=', $req->month)
      ->orderBy('monthly', 'ASC')
      ->first('monthly');

    // Make payment Records for insert in pyment Table
    $paymentReqs = [
      'shop_id' => $req->shopId,
      'amount' => $amount,
      'paid_from' => $month->monthly,
      'paid_to' => $req->month,
      'cheque_date' => $req->chequeDdDate,
      'payment_date' => Carbon::now()->format('Y-m-d'),
      'bank_name' => $req->bankName,
      'branch_name' => $req->branchName,
      'cheque_no' => $req->chequeNo,
      'dd_no' => $req->ddNo,
      'user_id' => $req->auth['id'] ?? 0,
      'ulb_id' => $shopDetails->ulb_id,
      'remarks' => $req->remarks,
      'payment_status' => 2,
      'pmt_mode' => $req->paymentMode,
      'transaction_no' => time() . $shopDetails->ulb_id . $req->shopId,     // Transaction id is a combination of time funcation in PHP and ULB ID and Shop ID
      'photo_path_absolute' => $req->photo_path_absolute,
      'photo_path' => $req->photo_path,
    ];
    $createdPayment = ShopPayment::create($paymentReqs);

    // update shop table with payment transaction ID
    $mshop = Shop::find($createdPayment->shop_id);
    $mshop->last_tran_id = $createdPayment->id;
    $mshop->save();

    // Get All Demand for cheque Payment
    $UpdateDetails = MarShopDemand::where('shop_id',  $req->shopId)
      ->where('monthly', '>=', $month->monthly)
      ->where('monthly', '<=',  $req->month)
      ->where('amount', '>', 0)
      ->orderBy('monthly', 'ASC')
      ->get();
    // Update All Demand for cheque Payment
    foreach ($UpdateDetails as $updateData) {
      $updateRow = MarShopDemand::find($updateData->id);
      $updateRow->payment_date = Carbon::now()->format('Y-m-d');
      $updateRow->payment_status = 1;
      $updateRow->tran_id = $createdPayment->id;
      $updateRow->save();
    }
    $shop['createdPayment'] = $createdPayment;
    $shop['shopDetails'] = $mshop;
    $shop['amount'] = $amount;
    $shop['lastTranId'] = $createdPayment->id;
    return $shop;
  }

  /**
   * | List Uncleared cheque or DD
   */
  public function listUnclearedCheckDD($req)
  {
    return  DB::table('mar_shop_payments')
      ->select(
        'mar_shop_payments.id',
        'mar_shop_payments.payment_date',
        'mar_shop_payments.amount',
        'mar_shop_payments.paid_from',
        'mar_shop_payments.paid_to',
        'mar_shop_payments.cheque_no',
        //   'mar_shop_payments.cheque_date as recieve_date',
        DB::raw("TO_CHAR(mar_shop_payments.cheque_date, 'DD-MM-YYYY') as recieve_date"),
        'mar_shop_payments.bank_name',
        'mar_shop_payments.branch_name',
        't1.allottee',
        't1.contact_no'
      )
      ->join('mar_shops as t1', 'mar_shop_payments.shop_id', '=', 't1.id')
      ->where('payment_status', '2')
      ->where('cheque_date', '!=', NULL);
  }
  /** 
   * | Get Collection Report Tc Wise
   */
  public function getListOfPayment()
  {
    return  DB::table('mar_shop_payments')
      ->select(
        DB::raw('sum(mar_shop_payments.amount) as total_amount'),
        'mar_shop_payments.user_id as tc_id',
        'user.name as tc_name',
        'user.mobile as tc_mobile',
        't1.circle_id',
      )
      ->join('mar_shops as t1', 'mar_shop_payments.shop_id', '=', 't1.id')
      ->join('users as user', 'user.id', '=', 'mar_shop_payments.user_id')
      ->where('mar_shop_payments.pmt_mode', '=', "CASH")
      ->where('mar_shop_payments.deactive_date', '=', NULL)
      ->where('mar_shop_payments.is_verified', '=', "0");
  }
  /**
   * | Get List of All Payment
   */
  public function getListOfPaymentDetails()
  {
    return  DB::table('mar_shop_payments')
      ->select(
        'mar_shop_payments.id',
        'mar_shop_payments.payment_date',
        'mar_shop_payments.pmt_mode as payment_mode',
        'mar_shop_payments.amount',
        'mar_shop_payments.paid_from',
        'mar_shop_payments.paid_to',
        'mar_shop_payments.cheque_no',
        'mar_shop_payments.dd_no',
        'mar_shop_payments.bank_name',
        'mar_shop_payments.transaction_id as transaction_no',
        DB::raw("TO_CHAR(mar_shop_payments.cheque_date, 'DD-MM-YYYY') as recieve_date"),
        't1.shop_no',
        't1.allottee',
        't1.contact_no',
        'user.name as collector_name',
        'user.id as tc_id',
      )
      ->join('mar_shops as t1', 'mar_shop_payments.shop_id', '=', 't1.id')
      ->join('users as user', 'user.id', '=', 'mar_shop_payments.user_id')
      ->where('mar_shop_payments.pmt_mode', '!=', "ONLINE")
      ->where('mar_shop_payments.payment_status', '!=', "3");
  }
  /**
   * | Transaction De-activation
   */
  public function deActiveTransaction($req)
  {
    $tranDetails = $tran = Self::find($req->tranId);
    $tran->payment_status = 0;
    $tran->deactive_date = Carbon::now();
    $tran->deactive_reason = $req->deactiveReason;
    $tran->save();
    $demandids = MarShopDemand::select('id')->where('shop_id', $tranDetails->shop_id)->whereBetween('monthly', [$tranDetails->paid_from, $tranDetails->paid_to])->get();
    $updateData = [
      'payment_status' => '0',
      'payment_date' => NULL,
      'tran_id' => NULL
    ];

    return MarShopDemand::whereIn('id', $demandids)
      ->update($updateData);
  }
}
