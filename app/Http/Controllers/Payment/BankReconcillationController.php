<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Param\AdvMarTransaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class BankReconcillationController extends Controller
{

    /**
     * |
     */
    public function searchTransaction(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fromDate' => 'required',
                'toDate' => 'required',
                'moduleId' => 'required'
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
            $selfAdvertisementworkflow = Config::get('workflow-constants.SELF-ADVERTISEMENT');
            $movablevehicleWorkflow = Config::get('workflow-constants.MOVABLE-VEHICLE');
            $privateLandWorkflow = Config::get('workflow-constants.PRIVATE-LAND');
            $agencyWorkflow = Config::get('workflow-constants.AGENCY');
            $mAdvMarTransaction = new AdvMarTransaction();
            // $mTradeTransaction = new TradeTransaction();
            // $mWaterTran = new WaterTran();

            if ($moduleId == $selfAdvertisementworkflow) {
                $chequeTranDtl  = $mAdvMarTransaction->chequeTranDtl($ulbId);
                if ($request->verificationType != "bounce") {
                    $chequeTranDtl = $chequeTranDtl->where("prop_transactions.status", 1);
                }
                if ($request->chequeNo) {
                    $data =  $chequeTranDtl
                        ->where('cheque_no', $request->chequeNo)
                        ->get();
                }
                if (!isset($data)) {
                    $data = $chequeTranDtl
                        ->whereBetween('tran_date', [$fromDate, $toDate])
                        ->get();
                }
            }

            if ($moduleId == $movablevehicleWorkflow) {

                $chequeTranDtl  = $mAdvMarTransaction->chequeTranDtl($ulbId);
                if ($request->verificationType != "bounce") {
                    $chequeTranDtl = $chequeTranDtl->where("water_trans.status", 1);
                }

                if ($request->chequeNo) {
                    $data =  $chequeTranDtl
                        ->where('cheque_no', $request->chequeNo)
                        ->get();
                }
                if (!isset($data)) {
                    $data = $chequeTranDtl
                        ->whereBetween('tran_date', [$fromDate, $toDate])
                        ->get();
                }
            }

            if ($moduleId == $privateLandWorkflow) {
                $chequeTranDtl  = $mAdvMarTransaction->chequeTranDtl($ulbId);

                if ($request->chequeNo) {
                    $data =  $chequeTranDtl
                        ->where('cheque_no', $request->chequeNo)
                        ->get();
                }
                if (!isset($data)) {
                    $data = $chequeTranDtl
                        ->whereBetween('tran_date', [$fromDate, $toDate])
                        ->get();
                }
            }
            if ($moduleId == $agencyWorkflow) {
                $chequeTranDtl  = $mAdvMarTransaction->chequeTranDtl($ulbId);

                if ($request->chequeNo) {
                    $data =  $chequeTranDtl
                        ->where('cheque_no', $request->chequeNo)
                        ->get();
                }
                if (!isset($data)) {
                    $data = $chequeTranDtl
                        ->whereBetween('tran_date', [$fromDate, $toDate])
                        ->get();
                }
            }

            if ($paymentMode == 'DD') {
                $a =  collect($data)->where('payment_mode', 'DD');
                $data = (array_values(objtoarray($a)));
            }

            if ($paymentMode == 'CHEQUE') {
                $a =  collect($data)->where('payment_mode', 'CHEQUE');
                $data = (array_values(objtoarray($a)));
            }

            if ($paymentMode == 'NEFT') {
                $a =  collect($data)->where('payment_mode', 'NEFT');
                $data = (array_values(objtoarray($a)));
            }

            //search with verification status
            if ($verifyStatus == 'pending') {
                $a =  collect($data)->where('status', '2');
                $data = (array_values(objtoarray($a)));
            }

            if ($verifyStatus == 'clear') {
                $a =  collect($data)->where('status', '1');
                $data = (array_values(objtoarray($a)));
            }

            if ($verifyStatus == 'bounce') {
                $a =  collect($data)->where('status', '3');
                $data = (array_values(objtoarray($a)));
            }

            if (collect($data)->isNotEmpty()) {
                return responseMsgs(true, "Data Acording to request!", $data, '010801', '01', '382ms-547ms', 'Post', '');
            }
            return responseMsg(false, "data not found!", "");
        } catch (Exception $error) {
            return responseMsg(false, "ERROR!", $error->getMessage());
        }
    }
}
