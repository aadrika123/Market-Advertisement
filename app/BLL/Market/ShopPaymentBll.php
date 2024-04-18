<?php

namespace App\BLL\Market;

use App\Models\Rentals\MarShopDemand;
use App\Models\Rentals\MarToll;
use App\Models\Rentals\MarTollDemand;
use App\Models\Rentals\Shop;
use App\Models\Rentals\ShopPayment;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * | Created On-14-06-2023 
 * | Author-Anshu Kumar
 * | Status-Open
 */
class ShopPaymentBll
{
    private $_mShopPayments;
    private $_mShopDemand;
    public $_shopDetails;
    public $_tranId;
    private $_shopLastDemand;
    private $_tollLastDemand;
    private $_mTollDemand;
    private $_now;

    public function __construct()
    {
        $this->_mShopPayments = new ShopPayment();
        $this->_mShopDemand   = new MarShopDemand();
        $this->_now           = Carbon::now();
        $this->_mTollDemand   = new MarTollDemand();
    }

    /**
     * | Shop Payments
     * | @param Request $req
     */
    public function shopPaymentOld($req)
    {
        // Business Logics
        $paymentTo = Carbon::parse($req->paymentTo);
        if (!isset($this->_tranId))                                 // If Last Transaction Not Found
        {
            $paymentFrom = Carbon::parse($req->paymentFrom);
            $diffInMonths = $paymentFrom->diffInMonths($paymentTo);
            $totalMonths = $diffInMonths + 1;
        }

        if (isset($this->_tranId)) {                                // If Last Transaction ID is Available
            $shopLastPayment = $this->_mShopPayments::findOrFail($this->_tranId);
            $paymentFrom = Carbon::parse($shopLastPayment->paid_to);
            $diffInMonths = $paymentFrom->diffInMonths($paymentTo);
            $totalMonths = $diffInMonths + 1;
        }

        // $payableAmt = ($this->_shopDetails->rate * $totalMonths) + $this->_shopDetails->arrear;
        // $amount = $req->amount;
        $payableAmt = ($this->_shopDetails->rate * $totalMonths);
        $amount = $payableAmt;
        $arrear = $payableAmt - $amount;
        // $count=ShopPayment::where('shop_id',$req->shopId)->where('paid_to',$paymentTo)->count();
        if ($payableAmt < 1)
            throw new Exception("Dues Not Available");
        // Insert Payment 
        $paymentReqs = [
            'shop_id' => $req->shopId,
            'paid_from' => $paymentFrom,
            'paid_to' => $paymentTo,
            'demand' => $payableAmt,
            'amount' => $amount,
            'rate' => $this->_shopDetails->rate,
            'months' => $totalMonths,
            'payment_date' => Carbon::now(),
            'user_id' => $req->auth['id'] ?? 0,
            'pmt_mode' => $req->paymentMode,
            'ulb_id' => $this->_shopDetails->ulb_id,
            'transaction_no' => "TRAN-" . time() . $this->_shopDetails->ulb_id . $req->shopId,
            'remarks' => $req->remarks
        ];
        DB::beginTransaction();
        $createdPayment = $this->_mShopPayments::create($paymentReqs);
        $this->_shopDetails->update([
            'last_tran_id' => $createdPayment->id,
            'arrear' => $arrear
        ]);
    }
    /* | Shop Payments
    * | @param Request $req
    */
    public function shopPayment($req)
    {
        // Calculate Amount For Payment
        $amount = DB::table('mar_shop_demands')
            ->where('shop_id', $req->shopId)
            ->where('payment_status', 0)
            ->where('monthly', '<=', $req->month)
            ->orderBy('monthly', 'ASC')
            ->sum('amount');
        if ($amount < 1)
            throw new Exception("No Any Due Amount !!!");
        $shopDetails = DB::table('mar_shops')->select('*')->where('id', $req->shopId)->first();                                     // Get Shop Details
        // Get All Financial Year For Payment
        $month = DB::table('mar_shop_demands')
            ->where('shop_id', $req->shopId)
            ->where('payment_status', 0)
            ->where('amount', '>', '0')
            ->where('monthly', '<=', $req->month)
            ->orderBy('monthly', 'ASC')
            ->first('monthly');

        // Insert Payment Records 
        $paymentReqs = [
            'shop_id' => $req->shopId,
            'amount' => $amount,
            'paid_from' => $month->monthly,
            'paid_to' => $req->month,
            'payment_date' => Carbon::now(),
            'payment_status' => '1',
            'user_id' => $req->auth['id'] ?? 0,
            'ulb_id' => $shopDetails->ulb_id,
            'remarks' => $req->remarks,
            'pmt_mode' => $req->paymentMode,
            'transaction_no' => time() . $shopDetails->ulb_id . $req->shopId,                   // Transaction id is a combination of time funcation in PHP and ULB ID and Shop ID
        ];
        DB::beginTransaction();
        $createdPayment = $this->_mShopPayments::create($paymentReqs);                          // Insert Payment Records in Payment Table
        $mshop = Shop::find($req->shopId);
        $tranId = $mshop->last_tran_id = $createdPayment->id;
        $mshop->save();

        $UpdateDetails = MarShopDemand::where('shop_id', $req->shopId)                         // Get All demand of Selected financial Year After Payment Success
            ->where('monthly', '>=', $month->monthly)
            ->where('monthly', '=', $req->month)
            ->orderBy('monthly', 'ASC')
            ->where('amount', '>', '0')
            ->get();

        // Update All Payment Demand After Payment Success
        foreach ($UpdateDetails as $updateData) {
            // return $updateData->id; die;
            $updateRow = MarShopDemand::find($updateData->id);
            $updateRow->payment_date = Carbon::now()->format('Y-m-d');
            $updateRow->payment_status = 1;
            $updateRow->tran_id = $createdPayment->id;
            $updateRow->save();
        }
        $mShop = Shop::find($req->shopId);
        $ret['shopDetails'] = $mShop;
        $ret['amount'] = $amount;
        $ret['paymentDate'] = Carbon::now()->format('d-m-Y');
        $ret['allottee'] = $mShop->allottee;
        $ret['mobile'] = $mShop->contact_no;
        $ret['tranId'] = $tranId;
        return $ret;
    }


    /**
     * | Calculate Shop Payments
     * | @param Request $req
     */
    public function calculateShopPayment($req)
    {
        // Business Logics
        $paymentTo = Carbon::parse($req->paymentTo);
        if (!isset($this->_tranId))                                 // If Last Transaction Not Found
        {
            $paymentFrom = Carbon::parse($req->paymentFrom);
            $diffInMonths = $paymentFrom->diffInMonths($paymentTo);
            $totalMonths = $diffInMonths + 1;
        }

        if (isset($this->_tranId)) {                                // If Last Transaction ID is Available
            $shopLastPayment = $this->_mShopPayments::findOrFail($this->_tranId);
            $paymentFrom = Carbon::parse($shopLastPayment->paid_to);
            $diffInMonths = $paymentFrom->diffInMonths($paymentTo);
            $totalMonths = $diffInMonths + 1;
        }
        return ($this->_shopDetails->rate * $totalMonths);
    }
    /**
     * | Shop demand
     * | @param Request $req
     */
    public function shopDemand($req)
    {
        // Get the current month
        $currentMonth = Carbon::now()->startOfMonth();

        $shopDetails = Shop::find($req->shopId);
        #check shop last demand 
        $this->_shopLastDemand = $this->_mShopDemand->CheckConsumerDemand($req)->get()->sortByDesc("monthly")->first();;

        if ($this->_shopLastDemand) {
            $lastDemandMonth = Carbon::parse($this->_shopLastDemand->monthly)->startOfMonth();
            if ($lastDemandMonth->eq($currentMonth)) {
                throw new Exception("Demand is already generated for this month.");
            }
        }
        if ($this->_shopLastDemand) {
            $startDate          = Carbon::parse($this->_shopLastDemand->monthly);
            $endDate            = Carbon::parse($this->_now);
        }
        # If the demand is generated for the first time
        else {
            $endDate            = Carbon::parse($this->_now);
            $startDate          = Carbon::parse($shopDetails->created_at);
        }

        $demandFrom = Carbon::parse($startDate);
        $months = [];
        $currentMonth = $demandFrom->copy()->startOfMonth();
        while ($currentMonth->lte($endDate)) {
            $months[] = $currentMonth->format('Y-m-d');
            $currentMonth->addMonth();
        }
        DB::beginTransaction();
        foreach ($months as $month) {
            $amount = $shopDetails->rate;                                        // rate is fixed for each month
            $payableAmt = $amount;
            $arrear = $amount;
            // Insert demand
            $demandReqs = [
                'shop_id' => $req->shopId,
                'amount' => $amount,
                'monthly' => $month,
                'payment_date' => Carbon::now(),
                'user_id' => $req->auth['id'] ?? 0,
                'ulb_id' => $shopDetails->ulb_id,
            ];
            $this->_mShopDemand::create($demandReqs);
        }
    }
    /**
     * | Shop demand
     * | @param Request $req
     */
    public function allShopDemand($req)
    {
        // Get the current month
        $currentMonth = Carbon::now()->startOfMonth();

        $shopDetails = Shop::find($req->shopId);
        #check shop last demand 
        $this->_shopLastDemand = $this->_mShopDemand->CheckConsumerDemand($req)->get()->sortByDesc("monthly")->first();;

        if ($this->_shopLastDemand) {
            $lastDemandMonth = Carbon::parse($this->_shopLastDemand->monthly)->startOfMonth();
            if ($lastDemandMonth->eq($currentMonth)) {
                throw new Exception("Demand is already generated for this month.");
            }
        }
        if ($this->_shopLastDemand) {
            $startDate          = Carbon::parse($this->_shopLastDemand->monthly);
            $endDate            = Carbon::parse($this->_now);
        }
        # If the demand is generated for the first time
        else {
            $endDate            = Carbon::parse($this->_now);
            $startDate          = Carbon::parse($shopDetails->created_at);
        }

        $demandFrom = Carbon::parse($startDate);
        $months = [];
        $currentMonth = $demandFrom->copy()->startOfMonth();
        while ($currentMonth->lte($endDate)) {
            $months[] = $currentMonth->format('Y-m-d');
            $currentMonth->addMonth();
        }
        DB::beginTransaction();
        foreach ($months as $month) {
            $amount = $shopDetails->rate;                                        // rate is fixed for each month
            $payableAmt = $amount;
            $arrear = $amount;
            // Insert demand
            $demandReqs = [
                'shop_id' => $req->shopId,
                'amount' => $amount,
                'monthly' => $month,
                'payment_date' => Carbon::now(),
                'user_id' => $req->auth['id'] ?? 0,
                'ulb_id' => $shopDetails->ulb_id,
            ];
            $this->_mShopDemand::create($demandReqs);
        }
    }

    /**
     * | Calculate rate between two financial year 
     */
    public function calculateShopRateMonhtly($req)
    {
        return  DB::table('mar_shop_demands')
            ->where('shop_id', $req->shopId)
            ->where('payment_status', 0)
            ->where('monthly', '<=', $req->month)
            ->sum('amount');
    }


    /**
     * | Shop demand
     * | @param Request $req
     */
    public function tollDemand($req)
    {
        $currentDate = Carbon::now()->startOfDay();

        $tollDetails = MarToll::find($req->tollId);
        #check shop last demand 
        $this->_tollLastDemand =  $this->_mTollDemand->CheckConsumerDemand($req)->get()->sortByDesc("daily")->first();;

        if ($this->_tollLastDemand) {
            $lastDemand = Carbon::parse($this->_tollLastDemand->daily);
            if ($lastDemand->eq($currentDate)) {
                throw new Exception("Demand is already generated for this date.");
            }
        }
        if ($this->_shopLastDemand) {
            $startDate          = Carbon::parse($this->_tollLastDemand->daily);
            $endDate            = Carbon::parse($this->_now);
        }
        # If the demand is generated for the first time
        else {
            $endDate            = Carbon::parse($this->_now);
            $startDate          = Carbon::parse($tollDetails->created_at);
        }

        $demandFrom = Carbon::parse($startDate);
        $days = [];
        $currentDay = $startDate->copy();
        while ($currentDay->lte($endDate)) {
            $days[] = $currentDay->format('Y-m-d');
            $currentDay->addDay();
        }

        DB::beginTransaction();

        // Insert demands for each day
        foreach ($days as $day) {
            $demandReqs = [
                'toll_id' => $req->tollId,
                'amount' => $tollDetails->rate,
                'daily' => $day,
                'payment_date' => Carbon::now(),
                'user_id' => $req->auth['id'] ?? 0,
                'ulb_id' => $tollDetails->ulb_id,
            ];
            $this->_mTollDemand->create($demandReqs);
        }
    }
}
