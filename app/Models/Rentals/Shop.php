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
  public function getShop($assetId)
  {
    return Shop::select(
      'mar_shops.*',
      // 'mc.circle_name',
      // 'mm.market_name',
      DB::raw("TO_CHAR(msp.payment_date, 'DD-MM-YYYY') as last_payment_date"),
      DB::raw("CASE WHEN demand_genrated.shop_id IS NULL THEN TRUE ELSE FALSE END AS can_generate_demand"),
      'msp.amount as last_payment_amount'
    )
      // ->join('m_circle as mc', 'mar_shops.circle_id', '=', 'mc.id')
      // ->join('m_market as mm', 'mar_shops.market_id', '=', 'mm.id')
      ->leftJoin('mar_shop_payments as msp', 'mar_shops.last_tran_id', '=', 'msp.id')
      ->leftJoin(DB::raw("
        (SELECT DISTINCT shop_id
         FROM mar_shop_demands
         WHERE status = 1
         AND TO_CHAR(cast(monthly as date),'YYYY-MM') = TO_CHAR(CURRENT_DATE,'YYYY-MM')
        ) AS demand_genrated"), 'demand_genrated.shop_id', '=', 'mar_shops.id')
      ->where('mar_shops.asset_id', $assetId)
      ->where('mar_shops.status', '1')
      ->orderByDesc('mar_shops.id');
  }
  /**
   * | Get Shop List Market Wise
   */
  public function getShopv1($marketId)
  {
    return Shop::select(
      'mar_shops.*',
      'mc.circle_name',
      'mm.market_name',
      DB::raw("TO_CHAR(msp.payment_date, 'DD-MM-YYYY') as last_payment_date"),
      DB::raw("CASE WHEN demand_genrated.shop_id IS NULL THEN TRUE ELSE FALSE END AS can_generate_demand"),
      'msp.amount as last_payment_amount'
    )
      ->join('m_circle as mc', 'mar_shops.circle_id', '=', 'mc.id')
      ->join('m_market as mm', 'mar_shops.market_id', '=', 'mm.id')
      ->leftJoin('mar_shop_payments as msp', 'mar_shops.last_tran_id', '=', 'msp.id')
      ->leftJoin(DB::raw("
        (SELECT DISTINCT shop_id
         FROM mar_shop_demands
         WHERE status = 1
         AND TO_CHAR(cast(monthly as date),'YYYY-MM') = TO_CHAR(CURRENT_DATE,'YYYY-MM')
        ) AS demand_genrated"), 'demand_genrated.shop_id', '=', 'mar_shops.id')
      ->where('mar_shops.market_id', $marketId)
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
      'mar_shops.circle_id',
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
  public function getShopReciept($tranId)
  {
    return Shop::select(
      'mar_shops.*',
      'mc.circle_name',
      'mm.market_name',
      'sc.construction_type',
      // DB::raw("(msp.payment_date) as last_payment_date"),
      DB::raw("TO_CHAR(msp.payment_date::date, 'DD-MM-YYYY') as last_payment_date"),
      'msp.amount as last_payment_amount',
      'msp.pmt_mode as payment_mode',
      'msp.transaction_no',
      // DB::raw("(msp.paid_to) as payment_upto"),
      DB::raw("TO_CHAR(msp.paid_to::date, 'DD-MM-YYYY') as payment_upto"),
      'usr.mobile as reciever_mobile',
      'usr.name as reciever_name',
      'ulb.ulb_name',
      'ulb.toll_free_no',
      'ulb.current_website as website',
      'ulb.address as ulb_address',
      'msp.cheque_date',
      'msp.cheque_no',
      'msp.bank_name',
      'msp.branch_name'
    )
      ->leftjoin('m_circle as mc', 'mar_shops.circle_id', '=', 'mc.id')
      ->join('m_market as mm', 'mar_shops.market_id', '=', 'mm.id')
      ->join('shop_constructions as sc', 'mar_shops.construction', '=', 'sc.id')
      ->leftjoin('mar_shop_payments as msp', 'mar_shops.id', '=', 'msp.shop_id')
      ->join('users as usr', 'msp.user_id', '=', 'usr.id')
      ->join('ulb_masters as ulb', 'mar_shops.ulb_id', '=', 'ulb.id')
      ->where('msp.id', $tranId)
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

  /**
   * | Search Shop for Payment
   */
  public function searchShopForPaymentv1()
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
      // ->where(['mar_shops.construction' => $shopConstructionId, 'mar_shops.market_id' => $marketId])
      ->orderByDesc('mar_shops.id');
    // ->get();
  }
  /**
   * | Get Application details by Id
   */
  public function getApplicationDtls($appId)
  {

    return Shop::select('*')
      ->where('id', $appId)
      ->first();
  }

  /**
   * | List of shop collection between two given date
   */
  public function listShop($user)
  {
    return DB::table('mar_shops')
      ->select(
        'mar_shops.shop_no as shop_no',
        'mar_shops.allottee',
        'mar_shops.market_id',
        'mar_shops.allottee as ownerName',
        // 'mst.shop_type',
        'mkt.market_name',
        'mc.circle_name',
        'mar_shops.rate',
        'mar_shops.area',
        'mar_shops.address',
        'mar_shops.contact_no',

      )
      // ->leftjoin('mar_shop_types as mst', 'mst.id', '=', 't2.shop_category_id')
      ->leftjoin('m_circle as mc', 'mc.id', '=', 'mar_shops.circle_id')
      ->leftjoin('m_market as mkt', 'mkt.id', '=', 'mar_shops.market_id')
      ->where('mar_shops.status', 1)
      ->where('mar_shops.ulb_id', $user->ulb_id)
      ->orderBy('mar_shops', 'Desc');
  }

// public static function getDashboardStats($ulbId)
// {
//     return Shop::selectRaw('
//             COUNT(mar_shops.id) AS total_shops,
//             COALESCE(SUM(CASE WHEN mar_shop_demands.payment_status = 0 THEN mar_shop_demands.amount ELSE 0 END), 0) AS total_demand,
//             COALESCE(SUM(CASE WHEN mar_shop_demands.payment_status = 1 THEN mar_shop_demands.amount ELSE 0 END), 0) AS total_collection,
//             COALESCE(SUM(CASE WHEN mar_shop_demands.payment_status = 0 THEN mar_shop_demands.amount ELSE 0 END), 0) AS balance_pending
//         ')
//         ->leftJoin('mar_shop_demands', 'mar_shops.id', '=', 'mar_shop_demands.shop_id')
//         ->where('mar_shops.ulb_id', $ulbId)
//         ->where('mar_shops.status', 1)
//         ->first();
// }

  public static function getDashboardStats($ulbId)
  {
      return Shop::from('mar_shops as s')
          ->leftJoin('mar_shop_demands as d', 's.id', '=', 'd.shop_id')
          ->selectRaw('
              COUNT(DISTINCT s.id) AS total_shops,
              COALESCE(SUM(CASE 
                  WHEN d.payment_status = 0 THEN d.amount 
                  ELSE 0 END), 0) AS total_demand,
              COALESCE(SUM(CASE 
                  WHEN d.payment_status = 1 THEN d.amount 
                  ELSE 0 END), 0) AS total_collection,
              (
                  COALESCE(SUM(CASE 
                      WHEN d.payment_status = 0 THEN d.amount 
                      ELSE 0 END), 0)
                  -
                  COALESCE(SUM(CASE 
                      WHEN d.payment_status = 1 THEN d.amount 
                      ELSE 0 END), 0)
              ) AS balance_pending
          ')
          ->where('s.ulb_id', $ulbId)
          ->where('s.status', 1)
          ->first();
  }




}
