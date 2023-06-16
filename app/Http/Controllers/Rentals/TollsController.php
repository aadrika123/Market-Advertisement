<?php

namespace App\Http\Controllers\Rentals;

use App\Http\Controllers\Controller;
use App\Models\Rentals\MarToll;
use App\Models\Rentals\MarTollPayment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TollsController extends Controller
{
    private $_mToll;
    /**
     * | Created On-14-06-2023 
     * | Author - Anshu Kumar
     */
    public function __construct()
    {
        $this->_mToll = new MarToll();
    }

    public function tollPayments(Request $req)
    {
        $validator = Validator::make($req->all(), [
            "tollId" => "required|integer",
            "dateUpto" => "required|date|date_format:Y-m-d",
            "dateFrom" => "required|date|date_format:Y-m-d|before_or_equal:$req->dateUpto",
            "remarks" => "nullable|string"
        ]);

        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), [], 055101, "1.0", responseTime(), "POST", $req->deviceId);

        try {
            // Variable Assignments
            $todayDate = Carbon::now();
            $mTollPayment = new MarTollPayment();

            $toll = $this->_mToll::find($req->tollId);
            if (collect($toll)->isEmpty())
                throw new Exception("Toll Not Available for this ID");
            $dateFrom = Carbon::parse($req->dateFrom);
            $dateUpto = Carbon::parse($req->dateUpto);
            // Calculation
            $diffInDays = $dateFrom->diffInDays($dateUpto);
            $noOfDays = $diffInDays + 1;
            $rate = $toll->rate;
            $payableAmt = $noOfDays * $rate;
            if ($payableAmt < 1)
                throw new Exception("Dues Not Available");
            // Payment
            $reqTollPayment = [
                'toll_id' => $toll->id,
                'from_date' => $req->fromDate,
                'to_date' => $req->toDate,
                'amount' => $payableAmt,
                'rate' => $rate,
                'days' => $noOfDays,
                'payment_date' => $todayDate,
                'user_id' => $req->auth->id ?? 0,
                'ulb_id' => $toll->ulb_id,
                'remarks' => $req->remarks
            ];
            $createdTran = $mTollPayment->create($reqTollPayment);
            $toll->update([
                'last_payment_date' => $todayDate,
                'last_amount' => $payableAmt,
                'last_tran_id' => $createdTran->id
            ]);
            return responseMsgs(true, "Payment Successfully Done", [], 055101, "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], 055101, "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
}
