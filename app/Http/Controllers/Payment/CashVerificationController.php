<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\MicroServices\DocumentUpload;
use App\MicroServices\IdGenerator\PrefixIdGenerator;
use App\Models\Markets\MarLodge;
use App\Models\Param\AdvMarTransaction;
use App\Models\Payment\RevDailycollection;
use App\Models\Payment\RevDailycollectiondetail;
use App\Models\Payment\TempTransaction;
use App\Models\TransactionDeactivateDtl;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CashVerificationController extends Controller
{
    /**
     * Self-Advertisement Cash Verification List
     */
    public function selfAdvertCashVerificationList(Request $request)
    {
        $selfAdvertisementworkflow = Config::get('workflow-constants.SELF-ADVERTISEMENT');
        return $this->cashVerificationListByWorkflow($request, $selfAdvertisementworkflow);
    }
    /**
     * Movable Vehicle Cash Verification List
     */
    public function movableVehicleCashVerificationList(Request $request)
    {
        $movablevehicleWorkflow = Config::get('workflow-constants.MOVABLE-VEHICLE');
        return $this->cashVerificationListByWorkflow($request, $movablevehicleWorkflow);
    }

    /**
     * Private Land Cash Verification List
     */
    public function privateLandCashVerificationList(Request $request)
    {
        $privateLandWorkflow = Config::get('workflow-constants.PRIVATE-LAND');
        return $this->cashVerificationListByWorkflow($request,  $privateLandWorkflow);
    }

    /**
     * Agency Cash Verification List
     */
    public function agencyCashVerificationList(Request $request)
    {
        $agencyWorkflow = Config::get('workflow-constants.AGENCY');
        return $this->cashVerificationListByWorkflow($request,  $agencyWorkflow);
    }



    /**
     * Helper function to get cash verification list by workflow type
     */
    private function cashVerificationListByWorkflow(Request $request, $workflowType)
    {
        try {
            $ulbId = authUser($request)->ulb_id;
            $userId = $request->id;
            $date = date('Y-m-d', strtotime($request->date));
            // $workflowId = Config::get('workflow-constants.' . $workflowType);
            $mTempTransaction = new TempTransaction();
            $zoneId = $request->zone;
            $wardId = $request->wardId;

            $data = $mTempTransaction->transactionDtl($date, $ulbId);
            if ($userId) {
                $data = $data->where('user_id', $userId);
            }
            if ($zoneId) {
                $data = $data->where('ulb_ward_masters.zone', $zoneId);
            }
            if ($wardId) {
                $data = $data->where('ulb_ward_masters.id', $wardId);
            }
            $data = $data->where('workflow_id', $workflowType)->get();

            $collection = collect($data->groupBy("id")->all());

            $data = $collection->map(function ($val) use ($date, $workflowType) {
                $total = $val->sum('amount');
                return [
                    "id" => $val[0]['id'],
                    "user_name" => $val[0]['name'],
                    "amount" => $total,
                    "date" => Carbon::parse($date)->format('d-m-Y'),
                ];
            });

            $data = (array_values(objtoarray($data)));

            return responseMsgs(true, "List cash Verification for $workflowType", $data, "010201", "1.0", "", "POST", $request->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010201", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }


    /**
     * | THIS FUNCTION FOR SELF ADVERTISEMENT 
     */

    public function selfAdvertisementCollection(Request $request)
    {
        $selfAdvertisementworkflow = Config::get('workflow-constants.SELF-ADVERTISEMENT');
        return $this->getCollectionByWorkflow($request, $selfAdvertisementworkflow);
    }

    /**
     * | THIS FUNCTION FOR MOVABLE VEHCLE 
     */
    public function movableVehicleCollection(Request $request)
    {
        $movablevehicleWorkflow = Config::get('workflow-constants.MOVABLE-VEHICLE');
        return $this->getCollectionByWorkflow($request, $movablevehicleWorkflow);
    }
    /**
     * | THIS FUNCTION FOR PRIVATE LAND
     */
    public function privateLandCollection(Request $request)
    {
        $privateLandWorkflow = Config::get('workflow-constants.PRIVATE-LAND');
        return $this->getCollectionByWorkflow($request, $privateLandWorkflow);
    }

    /**
     * | THIS FUNCTION FOR AGENCY
     */
    public function agencyCollection(Request $request)
    {
        $agencyWorkflow = Config::get('workflow-constants.AGENCY');
        return $this->getCollectionByWorkflow($request, $agencyWorkflow);
    }

    /**
     * | THIS FUNCTION TO GET TRANSACTON DETAIL 
     * | BY TC OR JSK USER ID 
     */
    private function getCollectionByWorkflow(Request $request, $workflowType)
    {
        try {
            $request->validate([
                "date" => "required|date",
                "userId" => "required|numeric",
            ]);

            $userId = $request->userId;
            $ulbId = authUser($request)->ulb_id;
            $date = date('Y-m-d', strtotime($request->date));
            // $workflowId = Config::get('workflow-constants.' . $workflowType);
            $mTempTransaction = new TempTransaction();
            $details = $mTempTransaction->transactionList($date, $userId, $ulbId);

            if ($details->isEmpty()) {
                throw new Exception("No Application Found for this id");
            }

            $data = [
                'workflowType' => $workflowType,
                'transactions' => collect($details)->where('workflow_id', $workflowType)->values(),
                'cash' => collect($details)->where('payment_mode', 'CASH')->sum('amount'),
                'cheque' => collect($details)->where('payment_mode', 'CHEQUE')->sum('amount'),
                'dd' => collect($details)->where('payment_mode', 'DD')->sum('amount'),
                'totalAmount' => $details->sum('amount'),
                'numberOfTransaction' => $details->count(),
                'collectorName' => collect($details)[0]->user_name,
                'date' => Carbon::parse($date)->format('d-m-Y'),
                'verifyStatus' => false,
            ];

            return responseMsgs(true, "$workflowType Collection", remove_null($data), "010201", "1.0", "", "POST", $request->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010201", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }




    #========================================================= Verify Cash Transaction End Process =============================================#

    /**
     * |SELF SDVERTISEMENT
     */
    public function selfAdvertisementCashVerify(Request $request)
    {
    
        return $this->verifyCash($request, 'selfAdvert');
    }

    /**
     * |MOVACLE VEHICLE
     */
    public function movableVehicleCashVerify(Request $request)
    {
        return $this->verifyCash($request, 'movableVehicle');
    }
    /**
     * |PRIVATE LAND
     */
    public function privateLandCashVerify(Request $request)
    {
        return $this->verifyCash($request, 'privatLand');
    }
    /**
     * |AGENCY
     */
    public function agencyCashVerify(Request $request)
    {
       
        return $this->verifyCash($request, 'agency');
    }


    private function verifyCash(Request $request, $type)
    {
        try {
            $user = authUser($request);
            $userId = $user->id;
            $ulbId = $user->ulb_id;
            $transactions = $request->$type;
            $mRevDailycollection = new RevDailycollection();
            $cashParamId = Config::get('workflow-constants.CASH_VERIFICATION_PARAM_ID');

            DB::beginTransaction();
            DB::connection('pgsql_masters')->beginTransaction();

            $idGeneration = new PrefixIdGenerator($cashParamId, $ulbId);
            $tranNo = $idGeneration->generate();

            if ($transactions) {
                $tempTranDtl = TempTransaction::find($transactions[0]);
                $tranDate = $tempTranDtl['tran_date'];
                $tcId = $tempTranDtl['user_id'];
                $mReqs = new Request([
                    "tran_no" => $tranNo,
                    "user_id" => $userId,
                    "demand_date" => $tranDate,
                    "deposit_date" => Carbon::now(),
                    "ulb_id" => $ulbId,
                    "tc_id" => $tcId,
                ]);
                $collectionId = $mRevDailycollection->store($mReqs);

                foreach ($transactions as $item) {
                    $tempDtl = TempTransaction::find($item);
                    $tranId = $tempDtl->transaction_id;

                    AdvMarTransaction::where('id', $tranId)
                        ->update([
                            'verify_status' => 1,
                            'verify_date' => Carbon::now(),
                            'verified_by' => $userId
                        ]);

                    $this->dailyCollectionDtl($tempDtl, $collectionId);

                    if (!$tempDtl) {
                        throw new Exception("No Transaction Found for this id");
                    }

                    $logTrans = $tempDtl->replicate();
                    $logTrans->setTable('log_temp_transactions');
                    $logTrans->id = $tempDtl->id;
                    $logTrans->save();
                    $tempDtl->delete();
                }
            }

            DB::commit();
            DB::connection('pgsql_masters')->commit();
            return responseMsgs(true, ucfirst($type) . " Cash Verified", '', "010201", "1.0", "", "POST", $request->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            DB::connection('pgsql_masters')->rollBack();
            return responseMsgs(false, $e->getMessage(), "", "010201", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }
    /**
     * | serial : 5.1
     */
    public function dailyCollectionDtl($tranDtl, $collectionId)
    {
        $RevDailycollectiondetail = new RevDailycollectiondetail();
        $mReqs = new Request([
            "collection_id" => $collectionId,
            "module_id" => $tranDtl['module_id'],
            "demand" => $tranDtl['amount'],
            "deposit_amount" => $tranDtl['amount'],
            "cheq_dd_no" => $tranDtl['cheque_dd_no'],
            "bank_name" => $tranDtl['bank_name'],
            "deposit_mode" => strtoupper($tranDtl['payment_mode']),
            "application_no" => $tranDtl['application_no'],
            "transaction_id" => $tranDtl['id'],
            "workflow_id"    => $tranDtl['workflow_id']
        ]);
        $RevDailycollectiondetail->store($mReqs);
    }

    #================================================================ Market =======================================#
    #============================== Lodge ========= #
    /**
     * lodge Cash Verification
     */
    public function lodgeCashVerificationList(Request $request)
    {
        $lodgewWorkflow = Config::get('workflow-constants.LODGE_WORKFLOWS');
        return $this->cashVerificationListByWorkflow($request, $lodgewWorkflow);
    }

    public function  lodgeCollection(Request $request)
    {
        $selfAdvertisementworkflow = Config::get('workflow-constants.LODGE_WORKFLOWS');
        return $this->getCollectionByWorkflow($request, $selfAdvertisementworkflow);
    }
    /**
     * |
     */
    public function lodgeCashVerify(Request $request)
    {
        return $this->verifyCash($request, 'lodge');
    }
    #================ Banquet ======================#  
    /**
     * | 
     */
    public function banquetCashVerificationList(Request $request)
    {
        $lodgewWorkflow = Config::get('workflow-constants.BANQUTE_MARRIGE_HALL_WORKFLOWS');
        return $this->cashVerificationListByWorkflow($request, $lodgewWorkflow);
    }
    /**
     * |
     */
    public function  banquetCollection(Request $request)
    {
        $selfAdvertisementworkflow = Config::get('workflow-constants.BANQUTE_MARRIGE_HALL_WORKFLOWS');
        return $this->getCollectionByWorkflow($request, $selfAdvertisementworkflow);
    }
    /**
     * |
     */
    public function banquetCashVerify(Request $request)
    {
        return $this->verifyCash($request, 'banquet');
    }
    #================ DharamShala ======================#  
    /**
     * | 
     */
    public function dharamCashVerificationList(Request $request)

    {
        $lodgewWorkflow = Config::get('workflow-constants.DHARAMSHALA_WORKFLOWS');
        return $this->cashVerificationListByWorkflow($request, $lodgewWorkflow);
    }
    /**
     * |
     */
    public function  dharamCollection(Request $request)
    {
        $selfAdvertisementworkflow = Config::get('workflow-constants.DHARAMSHALA_WORKFLOWS');
        return $this->getCollectionByWorkflow($request, $selfAdvertisementworkflow);
    }
    /**
     * |
     */
    public function dharamCashVerify(Request $request)
    {
        return $this->verifyCash($request, 'dharamshala');
    }
    #================ Hostel ======================#  
    /**
     * | 
     */
    public function hostelCashVerificationList(Request $request)
    {
        $lodgewWorkflow = Config::get('workflow-constants.HOSTEL_WORKFLOWS');
        return $this->cashVerificationListByWorkflow($request, $lodgewWorkflow);
    }
    /**
     * |
     */
    public function  hostelCollection(Request $request)
    {
        $selfAdvertisementworkflow = Config::get('workflow-constants.HOSTEL_WORKFLOWS');
        return $this->getCollectionByWorkflow($request, $selfAdvertisementworkflow);
    }
    /**
     * |
     */
    public function hostelCashVerify(Request $request)
    {
        return $this->verifyCash($request, 'hostel');
    }
    #================================================================== End ============================================================================# 
    /**
     * | For Verification of cash
     * | serial : 5
     */
    public function cashVerify(Request $request)
    {
        try {
            $user = authUser($request);
            $userId = $user->id;
            $ulbId = $user->ulb_id;
            $selfAdvert        =  $request->selfAdvert;
            $movableVehicle    =  $request->movableVehicle;
            $privatLand    =  $request->privatLand;
            $agency    =  $request->agency;
            $mRevDailycollection = new RevDailycollection();
            $cashParamId = Config::get('workflow-constants.CASH_VERIFICATION_PARAM_ID');

            DB::beginTransaction();
            DB::connection('pgsql_masters')->beginTransaction();
            DB::connection('pgsql_masters')->beginTransaction();
            DB::connection('pgsql_masters')->beginTransaction();
            $idGeneration = new PrefixIdGenerator($cashParamId, $ulbId);
            $tranNo = $idGeneration->generate();

            if ($selfAdvert) {
                $tempTranDtl = TempTransaction::find($selfAdvert[0]);
                $tranDate = $tempTranDtl['tran_date'];
                $tcId = $tempTranDtl['user_id'];
                $mReqs = new Request([
                    "tran_no" => $tranNo,
                    "user_id" => $userId,
                    "demand_date" => $tranDate,
                    "deposit_date" => Carbon::now(),
                    "ulb_id" => $ulbId,
                    "tc_id" => $tcId,
                ]);
                $collectionId =  $mRevDailycollection->store($mReqs);

                foreach ($selfAdvert as $item) {

                    $tempDtl = TempTransaction::find($item);
                    $tranId =  $tempDtl->transaction_id;

                    AdvMarTransaction::where('id', $tranId)
                        ->update(
                            [
                                'verify_status' => 1,
                                'verify_date' => Carbon::now(),
                                'verified_by' => $userId
                            ]
                        );
                    $this->dailyCollectionDtl($tempDtl, $collectionId);
                    if (!$tempDtl)
                        throw new Exception("No Transaction Found for this id");

                    $logTrans = $tempDtl->replicate();
                    $logTrans->setTable('log_temp_transactions');
                    $logTrans->id = $tempDtl->id;
                    $logTrans->save();
                    $tempDtl->delete();
                }
            }


            if ($movableVehicle) {
                $tempTranDtl = TempTransaction::find($movableVehicle[0]);
                $tranDate = $tempTranDtl['tran_date'];
                $tcId = $tempTranDtl['user_id'];
                $mReqs = new Request([
                    "tran_no" => $tranNo,
                    "user_id" => $userId,
                    "demand_date" => $tranDate,
                    "deposit_date" => Carbon::now(),
                    "ulb_id" => $ulbId,
                    "tc_id" => $tcId,
                ]);
                $collectionId =  $mRevDailycollection->store($mReqs);

                foreach ($movableVehicle as $item) {

                    $tempDtl = TempTransaction::find($item);
                    $tranId =  $tempDtl->transaction_id;

                    AdvMarTransaction::where('id', $tranId)
                        ->update(
                            [
                                'verify_status' => 1,
                                'verified_date' => Carbon::now(),
                                'verified_by' => $userId
                            ]
                        );
                    $this->dailyCollectionDtl($tempDtl, $collectionId);
                    if (!$tempDtl)
                        throw new Exception("No Transaction Found for this id");

                    $logTrans = $tempDtl->replicate();
                    $logTrans->setTable('log_temp_transactions');
                    $logTrans->id = $tempDtl->id;
                    $logTrans->save();
                    $tempDtl->delete();
                }
            }
            if ($privatLand) {
                $tempTranDtl = TempTransaction::find($privatLand[0]);
                $tranDate = $tempTranDtl['tran_date'];
                $tcId = $tempTranDtl['user_id'];
                $mReqs = new Request([
                    "tran_no" => $tranNo,
                    "user_id" => $userId,
                    "demand_date" => $tranDate,
                    "deposit_date" => Carbon::now(),
                    "ulb_id" => $ulbId,
                    "tc_id" => $tcId,
                ]);
                $collectionId =  $mRevDailycollection->store($mReqs);

                foreach ($privatLand as $item) {

                    $tempDtl = TempTransaction::find($item);
                    $tranId =  $tempDtl->transaction_id;

                    AdvMarTransaction::where('id', $tranId)
                        ->update(
                            [
                                'is_verified' => 1,
                                'verify_date' => Carbon::now(),
                                'verified_by' => $userId
                            ]
                        );
                    $this->dailyCollectionDtl($tempDtl, $collectionId);
                    if (!$tempDtl)
                        throw new Exception("No Transaction Found for this id");

                    $logTrans = $tempDtl->replicate();
                    $logTrans->setTable('log_temp_transactions');
                    $logTrans->id = $tempDtl->id;
                    $logTrans->save();
                    $tempDtl->delete();
                }
            }
            if ($agency) {
                $tempTranDtl = TempTransaction::find($agency[0]);
                $tranDate = $tempTranDtl['tran_date'];
                $tcId = $tempTranDtl['user_id'];
                $mReqs = new Request([
                    "tran_no" => $tranNo,
                    "user_id" => $userId,
                    "demand_date" => $tranDate,
                    "deposit_date" => Carbon::now(),
                    "ulb_id" => $ulbId,
                    "tc_id" => $tcId,
                ]);
                $collectionId =  $mRevDailycollection->store($mReqs);
                foreach ($agency as $item) {
                    $tempDtl = TempTransaction::find($item);
                    $tranId =  $tempDtl->transaction_id;
                    AdvMarTransaction::where('id', $tranId)
                        ->update(
                            [
                                'verify_status' => 1,
                                'verify_date' => Carbon::now(),
                                'verified_by' => $userId
                            ]
                        );
                    $this->dailyCollectionDtl($tempDtl, $collectionId);
                    if (!$tempDtl)
                        throw new Exception("No Transaction Found for this id");

                    $logTrans = $tempDtl->replicate();
                    $logTrans->setTable('log_temp_transactions');
                    $logTrans->id = $tempDtl->id;
                    $logTrans->save();
                    $tempDtl->delete();
                }
            }

            DB::commit();
            DB::connection('pgsql_masters')->commit();
            DB::connection('pgsql_masters')->commit();
            DB::connection('pgsql_masters')->commit();
            return responseMsgs(true, "Cash Verified", '', "010201", "1.0", "", "POST", $request->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            DB::connection('pgsql_masters')->rollBack();
            DB::connection('pgsql_masters')->rollBack();
            DB::connection('pgsql_masters')->rollBack();
            return responseMsgs(false, $e->getMessage(), "", "010201", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }
    /**
     * | Unverified Cash Verification List
     * | Serial : 1
     * | 
     */
    public function cashVerificationList(Request $request)
    {
        try {
            $ulbId =  authUser($request)->ulb_id;
            $userId =  $request->id;
            $date = date('Y-m-d', strtotime($request->date));
            $selfAdvertisementworkflow = Config::get('workflow-constants.SELF-ADVERTISEMENT');
            $movablevehicleWorkflow = Config::get('workflow-constants.MOVABLE-VEHICLE');
            $privateLandWorkflow = Config::get('workflow-constants.PRIVATE-LAND');
            $agencyWorkflow = Config::get('workflow-constants.AGENCY');
            $mTempTransaction =  new TempTransaction();
            $zoneId = $request->zone;
            $wardId = $request->wardId;

            $data = $mTempTransaction->transactionDtl($date, $ulbId);
            if ($userId) {
                $data = $data->where('user_id', $userId);
            }
            if ($zoneId) {
                $data = $data->where('ulb_ward_masters.zone', $zoneId);
            }
            if ($wardId) {
                $data = $data->where('ulb_ward_masters.id', $wardId);
            }
            $data = $data->get();

            $collection = collect($data->groupBy("id")->all());

            $data = $collection->map(function ($val) use ($date, $selfAdvertisementworkflow, $movablevehicleWorkflow, $privateLandWorkflow, $agencyWorkflow) {
                $total =  $val->sum('amount');
                $selfAdv  = $val->where("workflow_id", $selfAdvertisementworkflow)->sum('amount');
                $movableVehicle = $val->where("workflow_id", $movablevehicleWorkflow)->sum('amount');
                $trade = $val->where("workflow_id", $privateLandWorkflow)->sum('amount');
                $agency = $val->where("workflow_id", $agencyWorkflow)->sum('amount');
                return [
                    "id" => $val[0]['id'],
                    "user_name" => $val[0]['name'],
                    "selfAdvert" => $selfAdv,
                    "movableVehicle" => $movableVehicle,
                    "privatLand" => $trade,
                    "agency" => $agency,
                    "total" => $total,
                    "date" => Carbon::parse($date)->format('d-m-Y'),
                    // "verified_amount" => 0,
                ];
            });

            $data = (array_values(objtoarray($data)));

            return responseMsgs(true, "List cash Verification", $data, "010201", "1.0", "", "POST", $request->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010201", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }

    public function tcCollectionDtl(Request $request)
    {
        try {
            $request->validate([
                "date" => "required|date",
                "userId" => "required|numeric",

            ]);
            $userId =  $request->userId;
            $ulbId =  authUser($request)->ulb_id;
            $date = date('Y-m-d', strtotime($request->date));
            $selfAdvertisementworkflow = Config::get('workflow-constants.SELF-ADVERTISEMENT');
            $movablevehicleWorkflow = Config::get('workflow-constants.MOVABLE-VEHICLE');
            $privateLandWorkflow = Config::get('workflow-constants.PRIVATE-LAND');
            $agencyWorkflow = Config::get('workflow-constants.AGENCY');
            $mTempTransaction = new TempTransaction();
            $details = $mTempTransaction->transactionList($date, $userId, $ulbId);
            if ($details->isEmpty())
                throw new Exception("No Application Found for this id");

            $data['selfAdvert'] = collect($details)->where('workflow_id', $selfAdvertisementworkflow)->values();
            $data['movableVehicle'] = collect($details)->where('workflow_id', $movablevehicleWorkflow)->values();
            $data['privatLand'] = collect($details)->where('workflow_id', $privateLandWorkflow)->values();
            $data['agency'] = collect($details)->where('workflow_id', $agencyWorkflow)->values();
            $data['Cash'] = collect($details)->where('payment_mode', '=', 'CASH')->sum('amount');
            $data['Cheque'] = collect($details)->where('payment_mode', '=', 'CHEQUE')->sum('amount');
            $data['DD'] = collect($details)->where('payment_mode', '=', 'DD')->sum('amount');
            // $data['Neft'] = collect($details)->where('payment_mode', '=', 'Neft')->first()->amount;
            // $data['RTGS'] = collect($details)->where('payment_mode', '=', 'RTGS')->first()->amount;
            $data['totalAmount'] =  $details->sum('amount');
            $data['numberOfTransaction'] =  $details->count();
            $data['collectorName'] =  collect($details)[0]->user_name;
            $data['date'] = Carbon::parse($date)->format('d-m-Y');
            $data['verifyStatus'] = false;

            return responseMsgs(true, "TC Collection", remove_null($data), "010201", "1.0", "", "POST", $request->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010201", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }

    public function searchTransactionNo(Request $req)
    {
        $validator = Validator::make($req->all(), [
            "transactionNo" => "required"
        ]);

        if ($validator->fails())
            return validationError($validator);
        try {
            $lodgewWorkflow = Config::get('workflow-constants.LODGE_WORKFLOWS');
            $mTransaction = new AdvMarTransaction();
            $transactionDtl = $mTransaction->getTransByTranNo($req->transactionNo, $lodgewWorkflow);
            return responseMsgs(true, "Transaction No is", $transactionDtl, "", 01, responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", 01, responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    public function deactivateTransaction(Request $req)
    {
        $validator = Validator::make($req->all(), [
            "TranId" => "required", // Transaction ID
            "moduleId" => "required",
            "workflowId" => "required",
            "remarks" => "required|string",
            "document" => 'required|mimes:png,jpg,jpeg,gif,pdf'
        ]);

        if ($validator->fails()) {
            return validationError($validator);
        }

        try {
            $transactionId = $req->TranId;
            $moduleId = $req->moduleId;
            $workflowId = $req->workflowId;
            $document = new DocumentUpload();
            $document = $document->severalDoc($req);
            $document = $document->original["data"];
            $refImageName = $req->id . "_" . $req->moduleId . "_" . (Carbon::now()->format("Y-m-d"));
            $user = Auth()->user();

            DB::beginTransaction();
            DB::connection('pgsql_masters')->beginTransaction();

            $imageName = "";
            $deactivationArr = [
                "tran_id" => $transactionId,
                "deactivated_by" => $user->id,
                "reason" => $req->remarks,
                "file_path" => $imageName,
                "module_id" => $moduleId,
                "workflow_id" => $workflowId,
                "unique_id" => $document["document"]["data"]["uniqueId"],
                "reference_no" => $document["document"]["data"]["ReferenceNo"],
                "deactive_date" => $req->deactiveDate ?? Carbon::now()->format("Y-m-d"),
            ];

            $mTransaction = new AdvMarTransaction();
            $transaction = $mTransaction->find($transactionId);

            if (!$transaction) {
                throw new Exception("Transaction not found");
            }

            $mTransaction->deactivateTransaction($transactionId);
            $applicationId = $transaction->application_id;
            $TranDeativetion = new TransactionDeactivateDtl();
            $TranDeativetion->create($deactivationArr);

            TempTransaction::where('transaction_id', $transactionId)
                ->where('module_id', $moduleId)
                ->where('workflow_id', $workflowId)
                ->update(['status' => 0]);

            MarLodge::where('id', $applicationId)
                ->update(['payment_status' => 0]);

            DB::commit();
            DB::connection('pgsql_masters')->commit();

            return responseMsgs(true, "Transaction Deactivated", "", "", 01, responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            DB::connection('pgsql_masters')->rollBack();
            return responseMsgs(false, $e->getMessage(), "", "", 01, responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    public function deactivatedTransactionList(Request $req)
    {
        $validator = Validator::make($req->all(), [
            "fromDate" => "nullable|date|date_format:Y-m-d",
            "uptoDate" => "nullable|date|date_format:Y-m-d",
            'paymentMode' => 'nullable|in:CASH,CHEQUE,DD,NEFT,ALL',
            'transactionNo' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return validationError($validator);
        }

        try {
            $fromDate = $req->fromDate ?? Carbon::now()->format("Y-m-d");
            $uptoDate = $req->uptoDate ?? Carbon::now()->format("Y-m-d");
            $paymentMode = $req->paymentMode ?? null;
            $transactionNo = $req->transactionNo ?? null;
            $lodgewWorkflow = Config::get('workflow-constants.LODGE_WORKFLOWS');
            $mTransaction = new AdvMarTransaction();
            $transactionDeactivationDtl = $mTransaction->getDeactivatedTran($lodgewWorkflow)
                ->whereBetween('transaction_deactivate_dtls.deactive_date', [$fromDate, $uptoDate]);

            if ($paymentMode && $paymentMode != 'ALL') {
                $transactionDeactivationDtl->where('adv_mar_transactions.payment_mode', $paymentMode);
            }
            if ($transactionNo) {
                $transactionDeactivationDtl->where('adv_mar_transactions.transaction_no', $transactionNo);
            }

            $perPage = $req->perPage ?? 10;
            $page = $req->input('page', 1);

            // Paginate the results
            $paginatedData = $transactionDeactivationDtl->paginate($perPage, ['*'], 'page', $page);

            $list = [
                "current_page" => $paginatedData->currentPage(),
                "last_page" => $paginatedData->lastPage(),
                "data" => $paginatedData->items(),
                "total" => $paginatedData->total(),
            ];

            return responseMsgs(true, "Deactivated Transaction List", $list, "", 01, responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", 01, responseTime(), $req->getMethod(), $req->deviceId);
        }
    }
}
