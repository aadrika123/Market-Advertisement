<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Advertisements\AdvChequeDtl;
use App\Models\Advertisements\AdvSelfadvertisement;
use App\Models\Advertisements\AdvVehicle;
use App\Models\Markets\MarDharamshala;
use App\Models\Param\AdvMarTransaction;
use App\Models\Payment\PaymentReconciliation;
use App\Models\TransactionDeactivateDtl;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BankReconcillationController extends Controller
{
    /**
     * | search chque transactions for Advertisement,Market,  
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
            $data = $this->CommonHandleTransaction($ulbId, $request, $fromDate, $toDate, $workflowID);

            // switch ($workflowID) {
            //     case Config::get('workflow-constants.SELF-ADVERTISEMENT'):

            //         break;
            //     case Config::get('workflow-constants.MOVABLE-VEHICLE'):
            //         $data = $this->handleMovableVehicle($ulbId, $request, $fromDate, $toDate, $workflowID);
            //         break;
            //     case Config::get('workflow-constants.PRIVATE-LAND'):
            //         $data = $this->handlePrivateLand($ulbId, $request, $fromDate, $toDate, $workflowID);
            //         break;
            //     case Config::get('workflow-constants.AGENCY'):
            //         $data = $this->handleAgency($ulbId, $request, $fromDate, $toDate, $workflowID);
            //         break;
            //     case Config::get('workflow-constants.BANQUTE_MARRIGE_HALL'):
            //         $data = $this->handleAgency($ulbId, $request, $fromDate, $toDate, $workflowID);
            //         break;
            //     case Config::get('workflow-constants.HOSTEL'):
            //         $data = $this->handleAgency($ulbId, $request, $fromDate, $toDate, $workflowID);
            //         break;
            //     case Config::get('workflow-constants.LODGE'):
            //         $data = $this->handleAgency($ulbId, $request, $fromDate, $toDate, $workflowID);
            //         break;
            //     case Config::get('workflow-constants.DHARAMSHALA'):
            //         $data = $this->handleAgency($ulbId, $request, $fromDate, $toDate, $workflowID);
            //         break;
            //     default:
            //         return responseMsg(false, "Invalid workflow ID", "");
            // }

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

    private function CommonHandleTransaction($ulbId, $request, $fromDate, $toDate, $workflowID)
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


    /**
     * | 2
     */
    public function chequeDtlById(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'workflowId' => 'required',
                'chequeId' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => False, 'msg' => $validator()->errors()]);
            }
            $advchequedtls   = new AdvChequeDtl();

            $data = $advchequedtls->chequeDtlById($request);
            if ($data)
                return responseMsg(true, "data found", $data);
            else
                return responseMsg(false, "data not found!", "");
        } catch (Exception $error) {
            return responseMsg(false, "ERROR!", $error->getMessage());
        }
    }

    /**
     * | 3
     */
    public function chequeClearance(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'workflowId'    => 'required',
                'chequeId'      => 'required',
                'status'        => 'required|in:clear,bounce',
                'clearanceDate' => 'required'
            ]);

            if ($validator->fails()) {
                return validationError($validator);
            }
            $user = authUser($request);
            $ulbId = $user->ulb_id;
            $userId = $user->id;
            $workfowId = $request->workflowId;
            $paymentStatus = 1;
            $applicationPaymentStatus = 1;
            $mPaymentReconciliation = new PaymentReconciliation();
            $selfAdvertisementworkflow = Config::get('workflow-constants.SELF-ADVERTISEMENT');
            $movablevehicleWorkflow = Config::get('workflow-constants.MOVABLE-VEHICLE');
            $privateLandWorkflow = Config::get('workflow-constants.PRIVATE-LAND');
            $agencyWorkflow = Config::get('workflow-constants.AGENCY');
            $lodgeworkflow = Config::get('workflow-constants.LODGE_WORKFLOWS');
            $dharamshalaWorkflow = Config::get('workflow-constants.DHARAMSHALA_WORKFLOWS');
            $banquetHallWorkflow = Config::get('workflow-constants.BANQUTE_MARRIGE_HALL_WORKFLOWS');
            $hostelWorkflow = Config::get('workflow-constants.HOSTEL_WORKFLOWS');

            if ($request->status == 'clear') {
                $applicationPaymentStatus = $paymentStatus = 1;
            }
            if ($request->status == 'bounce') {
                $paymentStatus = 2;
                $applicationPaymentStatus = 0;
            }

            DB::beginTransaction();
            DB::connection('pgsql_masters')->beginTransaction();

            if ($workfowId == $dharamshalaWorkflow) {
                $mChequeDtl =  AdvChequeDtl::find($request->chequeId);

                $mChequeDtl->status = $paymentStatus;
                $mChequeDtl->clear_bounce_date = $request->clearanceDate;
                $mChequeDtl->bounce_amount = $request->cancellationCharge;
                $mChequeDtl->remarks = $request->remarks;
                $mChequeDtl->save();

                $transaction = AdvMarTransaction::where('id', $mChequeDtl->transaction_id)
                    ->first();
                $applicationId = $transaction->application_id;

                if ($applicationId)
                    $wardId = MarDharamshala::findorFail($applicationId)->ward_mstr_id;

                AdvMarTransaction::where('id', $mChequeDtl->transaction_id)
                    ->update(
                        [
                            'verify_status' => $paymentStatus,
                            'verify_date' => Carbon::now(),
                            'verified_by' => $userId
                        ]
                    );
                $msg = 'Cheque Clear successfully';

                if ($applicationPaymentStatus == 0) {
                    $mChequeDtl =  AdvChequeDtl::find($request->chequeId);
                    $mChequeDtl->status = 2;
                    AdvMarTransaction::where('id', $mChequeDtl->transaction_id)
                        ->update(
                            [
                                'verify_status' => 2,
                                'verify_date' => Carbon::now(),
                                'verified_by' => $userId
                            ]
                        );
                    MarDharamshala::where('id', $applicationId)->update(['payment_status' => 0]);
                    $msg = 'Cheque Bounce successfully';
                }


                $request->merge([
                    'id' => $mChequeDtl->id,
                    'paymentMode' => $transaction->payment_mode,
                    'transactionNo' => $transaction->transaction_no,
                    'transactionAmount' => $transaction->amount,
                    'transactionDate' => $transaction->tran_date,
                    'wardId' => $wardId,
                    'chequeNo' => $mChequeDtl->cheque_no,
                    'branchName' => $mChequeDtl->branch_name,
                    'bankName' => $mChequeDtl->bank_name,
                    'clearanceDate' => $mChequeDtl->clear_bounce_date,
                    'bounceReason' => $mChequeDtl->remarks,
                    'chequeDate' => $mChequeDtl->cheque_date,
                    'moduleId' => 5,
                    'ulbId' => $ulbId,
                    'userId' => $userId,
                    'workflowId' => $mChequeDtl->workflow_id
                ]);

                // return $request;
                $mPaymentReconciliation->addReconcilation($request);
            }
            if ($workfowId == $selfAdvertisementworkflow) {
                $mChequeDtl =  AdvChequeDtl::find($request->chequeId);

                $mChequeDtl->status = $paymentStatus;
                $mChequeDtl->clear_bounce_date = $request->clearanceDate;
                $mChequeDtl->bounce_amount = $request->cancellationCharge;
                $mChequeDtl->remarks = $request->remarks;
                $mChequeDtl->save();

                $transaction = AdvMarTransaction::where('id', $mChequeDtl->transaction_id)
                    ->first();
                $applicationId = $transaction->application_id;

                if ($applicationId)
                    $wardId = AdvSelfadvertisement::findorFail($applicationId)->ward_mstr_id;

                AdvMarTransaction::where('id', $mChequeDtl->transaction_id)
                    ->update(
                        [
                            'verify_status' => $paymentStatus,
                            'verify_date' => Carbon::now(),
                            'verified_by' => $userId
                        ]
                    );
                $msg = 'Cheque Clear successfully!';

                if ($applicationPaymentStatus == 0) {


                    $mChequeDtl =  AdvChequeDtl::find($request->chequeId);
                    $mChequeDtl->status = 2;
                    AdvMarTransaction::where('id', $mChequeDtl->transaction_id)
                        ->update(
                            [
                                'verify_status' => 2,
                                'verify_date' => Carbon::now(),
                                'verified_by' => $userId
                            ]
                        );
                    AdvSelfadvertisement::where('id', $applicationId)->update(['payment_status' => 0]);
                    $msg = 'Cheque Bounce successfully';
                }


                $request->merge([
                    'id' => $mChequeDtl->id,
                    'paymentMode' => $transaction->payment_mode,
                    'transactionNo' => $transaction->transaction_no,
                    'transactionAmount' => $transaction->amount,
                    'transactionDate' => $transaction->tran_date,
                    'wardId' => $wardId,
                    'chequeNo' => $mChequeDtl->cheque_no,
                    'branchName' => $mChequeDtl->branch_name,
                    'bankName' => $mChequeDtl->bank_name,
                    'clearanceDate' => $mChequeDtl->clear_bounce_date,
                    'bounceReason' => $mChequeDtl->remarks,
                    'chequeDate' => $mChequeDtl->cheque_date,
                    'moduleId' => 5,
                    'ulbId' => $ulbId,
                    'userId' => $userId,
                    'workflowId' => $mChequeDtl->workflow_id
                ]);

                // return $request;
                $mPaymentReconciliation->addReconcilation($request);
            }
            if ($workfowId == $movablevehicleWorkflow) {
                $mChequeDtl =  AdvChequeDtl::find($request->chequeId);

                $mChequeDtl->status = $paymentStatus;
                $mChequeDtl->clear_bounce_date = $request->clearanceDate;
                $mChequeDtl->bounce_amount = $request->cancellationCharge;
                $mChequeDtl->remarks = $request->remarks;
                $mChequeDtl->save();

                $transaction = AdvMarTransaction::where('id', $mChequeDtl->transaction_id)
                    ->first();
                $applicationId = $transaction->application_id;

                if ($applicationId)
                    $wardId = AdvVehicle::findorFail($applicationId)->ward_mstr_id;

                AdvMarTransaction::where('id', $mChequeDtl->transaction_id)
                    ->update(
                        [
                            'verify_status' => $paymentStatus,
                            'verify_date' => Carbon::now(),
                            'verified_by' => $userId
                        ]
                    );
                $msg = 'Cheque Clear successfully!';

                if ($applicationPaymentStatus == 0) {


                    $mChequeDtl =  AdvChequeDtl::find($request->chequeId);
                    $mChequeDtl->status = 2;
                    AdvMarTransaction::where('id', $mChequeDtl->transaction_id)
                        ->update(
                            [
                                'verify_status' => 2,
                                'verify_date' => Carbon::now(),
                                'verified_by' => $userId
                            ]
                        );
                        AdvVehicle::where('id', $applicationId)->update(['payment_status' => 0]);
                    $msg = 'Cheque Bounce successfully';
                }


                $request->merge([
                    'id' => $mChequeDtl->id,
                    'paymentMode' => $transaction->payment_mode,
                    'transactionNo' => $transaction->transaction_no,
                    'transactionAmount' => $transaction->amount,
                    'transactionDate' => $transaction->tran_date,
                    'wardId' => $wardId,
                    'chequeNo' => $mChequeDtl->cheque_no,
                    'branchName' => $mChequeDtl->branch_name,
                    'bankName' => $mChequeDtl->bank_name,
                    'clearanceDate' => $mChequeDtl->clear_bounce_date,
                    'bounceReason' => $mChequeDtl->remarks,
                    'chequeDate' => $mChequeDtl->cheque_date,
                    'moduleId' => 5,
                    'ulbId' => $ulbId,
                    'userId' => $userId,
                    'workflowId' => $mChequeDtl->workflow_id
                ]);

                // return $request;
                $mPaymentReconciliation->addReconcilation($request);
            }


            DB::commit();
            DB::connection('pgsql_masters')->commit();
            return responseMsg(true, $msg, '');
        } catch (Exception $error) {
            DB::rollBack();
            DB::connection('pgsql_masters')->rollBack();
            return responseMsg(false, "ERROR!", $error->getMessage());
        }
    }
}
