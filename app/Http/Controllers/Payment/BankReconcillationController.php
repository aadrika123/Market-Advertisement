<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Advertisements\AdvChequeDtl;
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
                'workflowID' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validator->errors()
                ]);
            }
            $ulbId = authUser($request)->ulb_id;
            $workflowID = $request->workflowID;
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
            if ($workflowID == $selfAdvertisementworkflow) {
                $chequeTranDtl  = $mAdvMarTransaction->chequeTranDtl($ulbId);
                if ($request->verificationType != "bounce") {
                    $chequeTranDtl = $chequeTranDtl->where("adv_mar_transactions.status", 1);
                }
                if ($request->chequeNo) {
                    $data =  $chequeTranDtl
                        ->where('cheque_no', $request->chequeNo)
                        ->get();
                }
                if (!isset($data)) {
                    $data = $chequeTranDtl
                        ->whereBetween('transaction_date', [$fromDate, $toDate])
                        ->get();
                }
            }

            if ($workflowID == $movablevehicleWorkflow) {

                $chequeTranDtl  = $mAdvMarTransaction->chequeTranDtl($ulbId);
                if ($request->verificationType != "bounce") {
                    $chequeTranDtl = $chequeTranDtl->where("adv_mar_transactions.status", 1);
                }

                if ($request->chequeNo) {
                    $data =  $chequeTranDtl
                        ->where('cheque_no', $request->chequeNo)
                        ->get();
                }
                if (!isset($data)) {
                    $data = $chequeTranDtl
                        ->whereBetween('transaction_date', [$fromDate, $toDate])
                        ->get();
                }
            }

            if ($workflowID == $privateLandWorkflow) {
                $chequeTranDtl  = $mAdvMarTransaction->chequeTranDtl($ulbId);

                if ($request->chequeNo) {
                    $data =  $chequeTranDtl
                        ->where('cheque_no', $request->chequeNo)
                        ->get();
                }
                if (!isset($data)) {
                    $data = $chequeTranDtl
                        ->whereBetween('transaction_date', [$fromDate, $toDate])
                        ->get();
                }
            }
            if ($workflowID == $agencyWorkflow) {
                $chequeTranDtl  = $mAdvMarTransaction->chequeTranDtl($ulbId);

                if ($request->chequeNo) {
                    $data =  $chequeTranDtl
                        ->where('cheque_no', $request->chequeNo)
                        ->get();
                }
                if (!isset($data)) {
                    $data = $chequeTranDtl
                        ->whereBetween('transaction_date', [$fromDate, $toDate])
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


    /**
     * | 2
     */
    public function chequeDtlById(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'moduleId' => 'required',
                'chequeId' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => False, 'msg' => $validator()->errors()]);
            }

            $moduleId = $request->moduleId;
            $propertyModuleId = Config::get('module-constants.PROPERTY_MODULE_ID');
            $waterModuleId = Config::get('module-constants.WATER_MODULE_ID');
            $tradeModuleId = Config::get('module-constants.TRADE_MODULE_ID');
            $mAdvChequDtl = new AdvChequeDtl();
            // $mTradeChequeDtl = new TradeChequeDtl();
            // $mWaterChequeDtl = new WaterChequeDtl();


            switch ($moduleId) {
                    //Property
                case ($propertyModuleId):
                    $data = $mAdvChequDtl->chequeDtlById($request);
                    break;

                    //Water
                case ($waterModuleId):
                    $data = $mAdvChequDtl->chequeDtlById($request);
                    break;

                    //Trade
                case ($tradeModuleId):
                    $data = $mAdvChequDtl->chequeDtlById($request);
                    break;
            }

            if ($data)
                return responseMsg(true, "data found", $data);
            else
                return responseMsg(false, "data not found!", "");
        } catch (Exception $error) {
            return responseMsg(false, "ERROR!", $error->getMessage());
        }
    }
}
