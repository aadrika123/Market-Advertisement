<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Advertisements\AdvAgency;
use App\Models\Advertisements\AdvChequeDtl;
use App\Models\Advertisements\AdvPrivateland;
use App\Models\Advertisements\AdvSelfadvertisement;
use App\Models\Advertisements\AdvVehicle;
use App\Models\Markets\MarBanquteHall;
use App\Models\Markets\MarDharamshala;
use App\Models\Markets\MarHostel;
use App\Models\Markets\MarLodge;
use App\Models\Param\AdvMarTransaction;
use App\Models\Payment\PaymentReconciliation;
use App\Models\Payment\TempTransaction;
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
     * |  Function = 111111111111111111111111111111111111111111111111cls
     * |
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

            $data = $this->CommonHandleTransaction($ulbId, $request, $fromDate, $toDate, $workflowID);                 // common funtion all workflows of ADVERTISEMENT & MARKET

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

    /**\
     * | This is common function for searching chewque details 
     * | Function = 2
     */

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

    /**
     * | Function = 3
     */
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

    /**
     * |Function = 4
     */
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
     * | Function =5 
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
     This for final cheque clear or Bounce of all workflow
     under Advertisemnet & Market 
     * |
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
                if ($mChequeDtl->status == 2) {
                    throw new Exception('Cheque Already Deactivated!');
                }

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
                    if ($mChequeDtl->status == 1) {
                        throw new Exception('Cheque Already Verified!');
                    }
                    $mChequeDtl =  AdvChequeDtl::find($request->chequeId);
                    $mChequeDtl->status = 2;
                    AdvMarTransaction::where('id', $mChequeDtl->transaction_id)
                        ->update(
                            [
                                'verify_status' => 2,
                                'status' => 0,
                                'verify_date' => Carbon::now(),
                                'verified_by' => $userId
                            ]
                        );
                    MarDharamshala::where('id', $applicationId)->update(['payment_status' => 0]);
                    TempTransaction::where('transaction_id', $mChequeDtl->transaction_id)
                        ->where('workflow_id', $mChequeDtl->workflow_id)
                        ->update(['status' => 0]);
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
                if ($mChequeDtl->status == 2) {
                    throw new Exception('Cheque Already Deactivated!');
                }

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

                    if ($mChequeDtl->status == 1) {
                        throw new Exception('Cheque Already Verified!');
                    }
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
                    TempTransaction::where('transaction_id', $mChequeDtl->transaction_id)
                        ->where('workflow_id', $mChequeDtl->workflow_id)
                        ->update(['status' => 0]);
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
                if ($mChequeDtl->status == 2) {
                    throw new Exception('Cheque Already Deactivated!');
                }

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
                    if ($mChequeDtl->status == 1) {
                        throw new Exception('Cheque Already Verified!');
                    }

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
                    TempTransaction::where('transaction_id', $mChequeDtl->transaction_id)
                        ->where('workflow_id', $mChequeDtl->workflow_id)
                        ->update(['status' => 0]);
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
            if ($workfowId == $privateLandWorkflow) {
                $mChequeDtl =  AdvChequeDtl::find($request->chequeId);
                if ($mChequeDtl->status == 2) {
                    throw new Exception('Cheque Already Deactivated!');
                }

                $mChequeDtl->status = $paymentStatus;
                $mChequeDtl->clear_bounce_date = $request->clearanceDate;
                $mChequeDtl->bounce_amount = $request->cancellationCharge;
                $mChequeDtl->remarks = $request->remarks;
                $mChequeDtl->save();

                $transaction = AdvMarTransaction::where('id', $mChequeDtl->transaction_id)
                    ->first();
                $applicationId = $transaction->application_id;

                if ($applicationId)
                    $wardId = AdvPrivateland::findorFail($applicationId)->ward_mstr_id;

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
                    if ($mChequeDtl->status == 1) {
                        throw new Exception('Cheque Already Verified!');
                    }
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
                    AdvPrivateland::where('id', $applicationId)->update(['payment_status' => 0]);
                    TempTransaction::where('transaction_id', $mChequeDtl->transaction_id)
                        ->where('workflow_id', $mChequeDtl->workflow_id)
                        ->update(['status' => 0]);
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
            if ($workfowId == $agencyWorkflow) {
                $mChequeDtl =  AdvChequeDtl::find($request->chequeId);
                if ($mChequeDtl->status == 2) {
                    throw new Exception('Cheque Already Deactivated!');
                }

                $mChequeDtl->status = $paymentStatus;
                $mChequeDtl->clear_bounce_date = $request->clearanceDate;
                $mChequeDtl->bounce_amount = $request->cancellationCharge;
                $mChequeDtl->remarks = $request->remarks;
                $mChequeDtl->save();

                $transaction = AdvMarTransaction::where('id', $mChequeDtl->transaction_id)
                    ->first();
                $applicationId = $transaction->application_id;

                if ($applicationId)
                    $wardId = AdvAgency::findorFail($applicationId)->ward_mstr_id;

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
                    if ($mChequeDtl->status == 1) {
                        throw new Exception('Cheque Already Verified!');
                    }

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
                    AdvAgency::where('id', $applicationId)->update(['payment_status' => 0]);
                    TempTransaction::where('transaction_id', $mChequeDtl->transaction_id)
                        ->where('workflow_id', $mChequeDtl->workflow_id)
                        ->update(['status' => 0]);
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
            if ($workfowId == $lodgeworkflow) {
                $mChequeDtl =  AdvChequeDtl::find($request->chequeId);
                if ($mChequeDtl->status == 2) {
                    throw new Exception('Cheque Already Deactivated!');
                }

                $mChequeDtl->status = $paymentStatus;
                $mChequeDtl->clear_bounce_date = $request->clearanceDate;
                $mChequeDtl->bounce_amount = $request->cancellationCharge;
                $mChequeDtl->remarks = $request->remarks;
                $mChequeDtl->save();

                $transaction = AdvMarTransaction::where('id', $mChequeDtl->transaction_id)
                    ->first();
                $applicationId = $transaction->application_id;

                if ($applicationId)
                    $wardId = MarLodge::findorFail($applicationId)->ward_mstr_id;

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
                    if ($mChequeDtl->status == 1) {
                        throw new Exception('Cheque Already Verified!');
                    }

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
                    MarLodge::where('id', $applicationId)->update(['payment_status' => 0]);
                    TempTransaction::where('transaction_id', $mChequeDtl->transaction_id)
                        ->where('workflow_id', $mChequeDtl->workflow_id)
                        ->update(['status' => 0]);
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
            if ($workfowId == $banquetHallWorkflow) {
                $mChequeDtl =  AdvChequeDtl::find($request->chequeId);
                if ($mChequeDtl->status == 2) {
                    throw new Exception('Cheque Already Deactivated!');
                }

                $mChequeDtl->status = $paymentStatus;
                $mChequeDtl->clear_bounce_date = $request->clearanceDate;
                $mChequeDtl->bounce_amount = $request->cancellationCharge;
                $mChequeDtl->remarks = $request->remarks;
                $mChequeDtl->save();

                $transaction = AdvMarTransaction::where('id', $mChequeDtl->transaction_id)
                    ->first();
                $applicationId = $transaction->application_id;

                if ($applicationId)
                    $wardId = MarBanquteHall::findorFail($applicationId)->ward_mstr_id;

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

                    if ($mChequeDtl->status == 1) {
                        throw new Exception('Cheque Already Verified!');
                    }
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
                    MarBanquteHall::where('id', $applicationId)->update(['payment_status' => 0]);
                    TempTransaction::where('transaction_id', $mChequeDtl->transaction_id)
                        ->where('workflow_id', $mChequeDtl->workflow_id)
                        ->update(['status' => 0]);
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
            if ($workfowId == $hostelWorkflow) {
                $mChequeDtl =  AdvChequeDtl::find($request->chequeId);
                if ($mChequeDtl->status == 2) {
                    throw new Exception('Cheque Already Deactivated!');
                }

                $mChequeDtl->status = $paymentStatus;
                $mChequeDtl->clear_bounce_date = $request->clearanceDate;
                $mChequeDtl->bounce_amount = $request->cancellationCharge;
                $mChequeDtl->remarks = $request->remarks;
                $mChequeDtl->save();

                $transaction = AdvMarTransaction::where('id', $mChequeDtl->transaction_id)
                    ->first();
                $applicationId = $transaction->application_id;

                if ($applicationId)
                    $wardId = MarHostel::findorFail($applicationId)->ward_mstr_id;

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
                    if ($mChequeDtl->status == 1) {
                        throw new Exception('Cheque Already Verified!');
                    }

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
                    MarHostel::where('id', $applicationId)->update(['payment_status' => 0]);
                    TempTransaction::where('transaction_id', $mChequeDtl->transaction_id)
                        ->where('workflow_id', $mChequeDtl->workflow_id)
                        ->update(['status' => 0]);
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
