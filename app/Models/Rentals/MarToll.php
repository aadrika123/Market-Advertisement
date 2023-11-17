<?php

namespace App\Models\Rentals;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MarToll extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function retrieveAll()
    {
        return MarToll::select(
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
            ->orderBy('id', 'desc');
        // ->get();
    }

    /**
     * | get List of All Active Shop
     */
    public function retrieveActive()
    {
        return MarToll::select(
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

    public function getTollById($id)
    {
        return MarToll::select(
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
            ->first();
    }

    /**
     * | Get Ulb Wise Toll list
     */
    public function getUlbWiseToll($ulbId)
    {
        return MarToll::select(
            'mar_tolls.*',
            'mc.circle_name',
            'mm.market_name',
            DB::raw("TO_CHAR(msp.payment_date, 'DD-MM-YYYY') as last_payment_date"),
            'msp.amount as last_payment_amount'
        )
            ->join('m_circle as mc', 'mar_tolls.circle_id', '=', 'mc.id')
            ->join('m_market as mm', 'mar_tolls.market_id', '=', 'mm.id')
            ->leftjoin('mar_shop_payments as msp', 'mar_tolls.last_tran_id', '=', 'msp.id')
            ->orderByDesc('mar_tolls.id')
            ->where('mar_tolls.ulb_id', $ulbId);
        // ->where('mar_tolls.status', '1');
    }
    /**
     * | Get All Toll By Market Id 
     */
    public function getToll($marketid)
    {
        return MarToll::select(
            'mar_tolls.*',
            'mc.circle_name',
            'mm.market_name',
            DB::raw("TO_CHAR(msp.payment_date, 'DD-MM-YYYY') as last_payment_date"),
            'msp.amount as last_payment_amount'
        )
            ->join('m_circle as mc', 'mar_tolls.circle_id', '=', 'mc.id')
            ->join('m_market as mm', 'mar_tolls.market_id', '=', 'mm.id')
            ->leftjoin('mar_shop_payments as msp', 'mar_tolls.last_tran_id', '=', 'msp.id')
            ->where('mar_tolls.market_id', $marketid)
            ->where('mar_tolls.status', '1');
    }

    /**
     * | get Toll Details By Toll Id
     */
    public function getTallDetailById($id)
    {
        return MarToll::select(
            'mar_tolls.*',
            'mc.circle_name',
            'mm.market_name',
            DB::raw("TO_CHAR(mar_tolls.last_payment_date, 'DD-MM-YYYY') as last_payment_date"),
            DB::raw("TO_CHAR(mtp.to_date, 'DD-MM-YYYY') as payment_upto"),
            'mar_tolls.last_amount as last_payment_amount',
        )
            ->join('m_circle as mc', 'mar_tolls.circle_id', '=', 'mc.id')
            ->join('m_market as mm', 'mar_tolls.market_id', '=', 'mm.id')
            ->leftjoin('mar_toll_payments as mtp', 'mar_tolls.last_tran_id', '=', 'mtp.id')
            ->where('mar_tolls.id', $id)
            ->first();
    }

    /**
     * | get Toll Last Reciept By Toll Id
     */
    public function getTollReciept($id)
    {
        return MarToll::select(
            'mar_tolls.*',
            'mc.circle_name',
            'mm.market_name',
            DB::raw("TO_CHAR(mar_tolls.last_payment_date, 'DD-MM-YYYY') as last_payment_date"),
            DB::raw("TO_CHAR(mtp.to_date, 'DD-MM-YYYY') as payment_upto"),
            'mar_tolls.last_amount as last_payment_amount',
            'ulb.ulb_name',
            'ulb.toll_free_no',
            'ulb.current_website as website',
            'mtp.transaction_no',
            'mtp.pmt_mode as payment_mode',
            'user.name as reciever_name',
            'user.mobile as reciever_mobile',
        )
            ->join('m_circle as mc', 'mar_tolls.circle_id', '=', 'mc.id')
            ->join('m_market as mm', 'mar_tolls.market_id', '=', 'mm.id')
            ->leftjoin('mar_toll_payments as mtp', 'mar_tolls.last_tran_id', '=', 'mtp.id')
            ->join('ulb_masters as ulb', 'mar_tolls.ulb_id', '=', 'ulb.id')
            ->join('users as user', 'mtp.user_id', '=', 'user.id')
            ->where('mar_tolls.id', $id)
            ->first();
    }
}
