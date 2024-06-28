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
     * |self adverrtisement search chque transactions 
     * |  
     */

    private function handleSelfAdvertisement($ulbId, $request, $fromDate, $toDate, $workflowID)
    {
        $mAdvMarTransaction = new AdvMarTransaction();
        $chequeTranDtl = $mAdvMarTransaction->chequeTranDtl($ulbId);
        $chequeTranDtl = $chequeTranDtl->where('adv_mar_transactions.workflow_id', $workflowID);
        if ($request->verificationType != "bounce") {
            $chequeTranDtl = $chequeTranDtl->where("adv_mar_transactions.status", 1);
        }
        if ($request->chequeNo) {
            return $chequeTranDtl->where('cheque_no', $request->chequeNo)->get();
        }
        return $chequeTranDtl->whereBetween('transaction_date', [$fromDate, $toDate])->get();
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
            $advchequedtls   = new AdvChequeDtl();


            switch ($moduleId) {
                    //Property
                case ($propertyModuleId):
                    $data = $advchequedtls->chequeDtlById($request);
                    break;

                    //Water
                case ($waterModuleId):
                    $data = $advchequedtls->chequeDtlById($request);
                    break;

                    //Trade
                case ($tradeModuleId):
                    $data = $advchequedtls->chequeDtlById($request);
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

            switch ($workflowID) {
                case Config::get('workflow-constants.SELF-ADVERTISEMENT'):
                    $data = $this->handleSelfAdvertisement($ulbId, $request, $fromDate, $toDate, $workflowID);
                    break;
                case Config::get('workflow-constants.MOVABLE-VEHICLE'):
                    $data = $this->handleMovableVehicle($ulbId, $request, $fromDate, $toDate);
                    break;
                case Config::get('workflow-constants.PRIVATE-LAND'):
                    $data = $this->handlePrivateLand($ulbId, $request, $fromDate, $toDate);
                    break;
                case Config::get('workflow-constants.AGENCY'):
                    $data = $this->handleAgency($ulbId, $request, $fromDate, $toDate);
                    break;
                default:
                    return responseMsg(false, "Invalid workflow ID", "");
            }

            $data = $this->filterDataByPaymentMode($data, $paymentMode);
            $data = $this->filterDataByVerificationStatus($data, $verifyStatus);

            if (collect($data)->isNotEmpty()) {
                return responseMsgs(true, "Data Acording to request!", $data, '010801', '01', '382ms-547ms', 'Post', '');
            }
            return responseMsg(false, "data not found!", "");
        } catch (Exception $error) {
            return responseMsg(false, "ERROR!", $error->getMessage());
        }
    }


    private function filterDataByPaymentMode($data, $paymentMode)
    {
        if ($paymentMode == 'DD') {
            $filteredData = collect($data)->where('payment_mode', 'DD');
            return array_values(objtoarray($filteredData));
        }
        if ($paymentMode == 'CHEQUE') {
            $filteredData = collect($data)->where('payment_mode', 'CHEQUE');
            return array_values(objtoarray($filteredData));
        }
        if ($paymentMode == 'NEFT') {
            $filteredData = collect($data)->where('payment_mode', 'NEFT');
            return array_values(objtoarray($filteredData));
        }
        return $data;
    }

    private function filterDataByVerificationStatus($data, $verifyStatus)
    {
        if ($verifyStatus == 'pending') {
            $filteredData = collect($data)->where('status', '2');
            return array_values(objtoarray($filteredData));
        }
        if ($verifyStatus == 'clear') {
            $filteredData = collect($data)->where('status', '1');
            return array_values(objtoarray($filteredData));
        }
        if ($verifyStatus == 'bounce') {
            $filteredData = collect($data)->where('status', '3');
            return array_values(objtoarray($filteredData));
        }
        return $data;
    }
}
