<?php

namespace App\Models\Rentals;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Shop extends Model
{
  use HasFactory;
  protected $guarded = [];
  protected $table = 'mar_shops';

  public function getGroupById($id)
  {
    return Shop::select(
      '*',
      DB::raw("
          CASE 
          WHEN status = '0' THEN 'Deactivated'  
          WHEN status = '1' THEN 'Active'
        END as status,
        TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(created_at,'HH12:MI:SS AM') as time
          ")
    )
      ->where('id', $id)
      ->orderBy('id', 'desc')
      ->first();
  }

  /**
   * | Get List of All Shop
   */
  public function retrieveAll()
  {
    return Shop::select(
      '*',
      DB::raw("
          CASE 
          WHEN status = '0' THEN 'Deactivated'  
          WHEN status = '1' THEN 'Active'
        END as status,
        TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(created_at,'HH12:MI:SS AM') as time
          ")
    )
      ->orderBy('id', 'desc')
      ->get();
  }

  /**
   * | Get List of All Active Shop
   */
  public function retrieveActive()
  {
    return Shop::select(
      '*',
      DB::raw("
          CASE 
          WHEN status = '0' THEN 'Deactivated'  
          WHEN status = '1' THEN 'Active'
        END as status,
        TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(created_at,'HH12:MI:SS AM') as time
          ")
    )
      ->where('status', 1)
      ->orderBy('id', 'desc')
      ->get();
  }

  /**
   * | Get List of All Shop ULB Wise
   */
  public function getAllShopUlbWise($ulbId)
  {
    return Shop::select(
      'mar_shops.*',
      'mc.circle_name',
      'mm.market_name',
    )
      ->leftjoin('m_circle as mc', 'mar_shops.circle_id', '=', 'mc.id')
      ->leftjoin('m_market as mm', 'mar_shops.market_id', '=', 'mm.id')
      ->where('mar_shops.ulb_id', $ulbId)
      // ->where('mar_shops.status', '1')
      ->orderByDesc('mar_shops.id');
  }

  /**
   * | Get Shop List Market Wise
   */
  public function getShop($marketid)
  {
    return Shop::select(
      'mar_shops.*',
      'mc.circle_name',
      'mm.market_name',
      DB::raw("TO_CHAR(msp.payment_date, 'DD-MM-YYYY') as last_payment_date"),
      'msp.amount as last_payment_amount'
    )
      ->join('m_circle as mc', 'mar_shops.circle_id', '=', 'mc.id')
      ->join('m_market as mm', 'mar_shops.market_id', '=', 'mm.id')
      ->leftjoin('mar_shop_payments as msp', 'mar_shops.last_tran_id', '=', 'msp.id')
      ->where('mar_shops.market_id', $marketid)
      ->where('mar_shops.status', '1')
      ->orderByDesc('mar_shops.id');
  }

  /**
   * | Get Shop Details By Market  Id
   */
  public function getShopDetailById($id)
  {
    return Shop::select(
      'mar_shops.*',
      'mc.circle_name',
      'mm.market_name',
      'sc.construction_type',
      'msp.amount as last_payment_amount',
    )
      ->leftjoin('m_circle as mc', 'mar_shops.circle_id', '=', 'mc.id')
      ->join('m_market as mm', 'mar_shops.market_id', '=', 'mm.id')
      ->join('shop_constructions as sc', 'mar_shops.construction', '=', 'sc.id')
      ->leftjoin('mar_shop_payments as msp', 'mar_shops.last_tran_id', '=', 'msp.id')
      ->where('mar_shops.id', $id)
      ->first();
  }

  /**
   * | Get Shop Reciept By Shop Id
   */
  public function getShopReciept($shopId)
  {
    return Shop::select(
      'mar_shops.*',
      'mc.circle_name',
      'mm.market_name',
      'sc.construction_type',
      DB::raw("(msp.payment_date, 'DD/MM/YYYY') as last_payment_date"),
      'msp.amount as last_payment_amount',
      'msp.pmt_mode as payment_mode',
      'msp.transaction_no',
      DB::raw("(msp.paid_to, 'DD/MM/YYYY') as payment_upto"),
      'usr.mobile as reciever_mobile',
      'usr.name as reciever_name',
      'ulb.ulb_name',
      'ulb.toll_free_no',
      'ulb.current_website as website',
    )
      ->join('m_circle as mc', 'mar_shops.circle_id', '=', 'mc.id')
      ->join('m_market as mm', 'mar_shops.market_id', '=', 'mm.id')
      ->join('shop_constructions as sc', 'mar_shops.construction', '=', 'sc.id')
      ->leftjoin('mar_shop_payments as msp', 'mar_shops.last_tran_id', '=', 'msp.id')
      ->join('users as usr', 'msp.user_id', '=', 'usr.id')
      ->join('ulb_masters as ulb', 'mar_shops.ulb_id', '=', 'ulb.id')
      ->where('mar_shops.id', $shopId)
      ->first();
  }

  # get shop details 
  public function getData($req)
  {
    return self::select('mamr_shops.*')
      ->where('mar_shops.id', $req->shopId)
      ->first();
  }

  /**
   * | Search Shop for Payment
   */
  public function searchShopForPayment($shopConstructionId, $marketId)
  {
    return Shop::select(
      'mar_shops.*',
      'mc.circle_name',
      'mm.market_name',
      'sc.construction_type',
      'msp.amount as last_payment_amount',
      // DB::raw('case when mar_shops.last_tran_id is NULL then 0 else 1 end as shop_payment_status')
    )
      ->join('m_circle as mc', 'mar_shops.circle_id', '=', 'mc.id')
      ->join('m_market as mm', 'mar_shops.market_id', '=', 'mm.id')
      ->join('shop_constructions as sc', 'mar_shops.construction', '=', 'sc.id')
      ->leftjoin('mar_shop_payments as msp', 'mar_shops.last_tran_id', '=', 'msp.id')
      // ->where(['mar_shops.shop_category_id' => $shopCategoryId, 'mar_shops.circle_id' => $circleId, 'mar_shops.market_id' => $marketId])
      ->where(['mar_shops.construction' => $shopConstructionId, 'mar_shops.market_id' => $marketId])
      ->orderByDesc('mar_shops.id');
    // ->get();
  }
}
