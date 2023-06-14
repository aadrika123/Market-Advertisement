<?php

namespace App\Http\Controllers\Rentals;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TollsController extends Controller
{
    /**
     * | Created On-14-06-2023 
     * | Author - Anshu Kumar
     */
    public function tollPayments(Request $req)
    {
        $validator = Validator::make($req->all(), [
            "tollId" => "required|integer",
            "dateUpto" => "required|date|date_format:Y-m-d",
            "dateFrom" => "required|date|date_format:Y-m-d|before:$req->dateUpto",
        ]);

        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), [], 055101, "1.0", responseTime(), "POST", $req->deviceId);

        try {
            $dateFrom = Carbon::parse($req->dateFrom);
            $dateUpto = Carbon::parse($req->dateUpto);
            $diffInDays = $dateFrom->diffInDays($dateUpto);
            return $diffInDays + 1;
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], 055101, "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
}
