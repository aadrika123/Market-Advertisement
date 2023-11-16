<?php

namespace App\Models\Rentals;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MarTollPayment extends Model
{
  use HasFactory;
  protected $guarded = [];

  /**
   * | Get All Payment List ULB Wise
   */
  public function paymentList($ulbId)
  {
    return self::select(
      'mar_toll_payments.*',
      'mt.toll_no',
      'mc.circle_name',
      'mm.market_name',
      DB::raw("TO_CHAR(mar_toll_payments.payment_date, 'DD-MM-YYYY') as payment_date"),
    )
      ->join('mar_tolls as mt', 'mt.id', '=', 'mar_toll_payments.toll_id')
      ->join('m_circle as mc', 'mc.id', '=', 'mt.circle_id')
      ->join('m_market as mm', 'mm.id', '=', 'mt.market_id')
      ->where('mar_toll_payments.ulb_id', $ulbId);
  }

  /**
   * | Get Payment List For TC Collection
   */
  public function paymentListForTcCollection($ulbId)
  {
    return self::select('user_id', 'payment_date', 'amount')->where('ulb_id', $ulbId);
  }

  /**
   * | Get Current Date Collection List
   */
  public function todayTallCollection($ulbId)
  {
    return self::select('amount')
      ->where('ulb_id', $ulbId);
      // ->where('payment_date', $date);
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
   * | Make Payment Data For Insert By Admin
   */
  public function metaReqs($req, $tollId)
  {
    return [
      "toll_id" => $tollId,
      "from_date" => $req->fromDate,
      "to_date" => $req->fromDate,
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
    ];
  }
}
