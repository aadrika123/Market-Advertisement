<?php

namespace App\Http\Controllers\Bandobastee;

use App\BLL\Advert\CalculateRate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bandobastee\StoreRequest;
use App\Models\Bandobastee\BdBanquetHall;
use App\Models\Bandobastee\BdBazar;
use App\Models\Bandobastee\BdMaster;
use App\Models\Bandobastee\BdPanalty;
use App\Models\Bandobastee\BdPanaltyMaster;
use App\Models\Bandobastee\BdParking;
use App\Models\Bandobastee\BdPayment;
use App\Models\Bandobastee\BdPenaltyMaster;
use App\Models\Bandobastee\BdSettler;
use App\Models\Bandobastee\BdSettlerTransaction;
use App\Models\Bandobastee\BdStand;
use App\Models\Bandobastee\BdStandCategory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class BandobasteeController extends Controller
{
    protected $_gstAmt;
    protected $_tcsAmt;
    //Constructor
    public function __construct()
    {
        $this->_gstAmt = Config::get('constants.GST_AMT');
        $this->_tcsAmt = Config::get('constants.TCS_AMT');
    }

    public function bandobasteeMaster(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mUlbId = authUser()->ulb_id;
            if ($mUlbId == '')
                throw new Exception("You Are Not Authorished !!!");                                                    // If Cache Memory is not available
            $data = array();
            $mBdstand = new Bdstand();
            $strings = $mBdstand->masters($mUlbId);
            $data['bandobasteeCategories'] = remove_null($strings->groupBy('stand_category')->toArray());

            $mBdStandCategory = new BdStandCategory();
            $listCategory = $mBdStandCategory->listCategory();                  // Get Topology List
            $data['bandobasteeCategories']['Stand'] = $listCategory;


            $mBdMaster = new BdMaster();
            $listMaster = $mBdMaster->listMaster();                  // Get Bandobastee List
            $data['bandobasteeCategories']['BandobasteeType'] = $listMaster;





            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Param Strings", $data, "050201", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    /**
     * | String Parameters values
     * | @param request $req
     */
    public function getStandCategory(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mBdStandCategory = new BdStandCategory();
            $listCategory = $mBdStandCategory->listCategory();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Category List", $listCategory, "050201", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function getStands(Request $req)
    {
        $mUlbId = authUser()->ulb_id;
        if ($mUlbId == '')
            throw new Exception("You Are Not Authorished !!!");

        $validator = Validator::make($req->all(), [
            'categoryId' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mBdStand = new BdStand();
            $listStands = $mBdStand->listStands($req->categoryId, $mUlbId);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Stand List", $listStands, "050201", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function addNew(StoreRequest $req)
    {
        if (authUser()->ulb_id == '')
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050834", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = authUser()->ulb_id;

        try {
            // Variable initialization
            $startTime = microtime(true);
            $mBdSettler = new BdSettler();
            $mCalculateRate = new CalculateRate;
            $gst = $mCalculateRate->calculateAmount($req->baseAmount, $this->_gstAmt);   // Calculate GST Amount From BLL
            $gstAmt = ['gstAmt' => $gst];
            $req->merge($gstAmt);

            $ulbId = ['ulbId' => $ulbId];
            $req->merge($ulbId);


            $tcs = $mCalculateRate->calculateAmount($req->baseAmount, $this->_tcsAmt);     // Calculate TCS Amount From BLL
            $tcsAmt = ['tcsAmt' => $tcs];
            $req->merge($tcsAmt);

            $totalAmount = ['totalAmount' => ($tcs + $gst + $req->baseAmount)];               // Calculate Total Amount
            $req->merge($totalAmount);

            DB::beginTransaction();
            $mBdSettler->addNew($req);       //<--------------- Model function to store 
            DB::commit();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Successfully Accepted !!", '', "051101", "1.0", "$executionTime Sec", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "051101", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    public function listPenalty(Request $req)
    {
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mBdPenaltyMaster = new BdPenaltyMaster();
            $listPanalty = $mBdPenaltyMaster->listPenalty();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Category List", $listPanalty, "050201", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function listSettler(Request $req)
    {
        if (authUser()->ulb_id == '')
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050834", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = authUser()->ulb_id;
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mBdSettler = new BdSettler();
            $listSettler = $mBdSettler->listSettler($ulbId);
            $listSettler = $listSettler->where('ulb_id', $ulbId);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Category List", $listSettler, "050201", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function installmentPayment(Request $req)
    {
        $validator = Validator::make($req->all(), [
            // 'ulbId' => 'required|integer',
            'settlerId' => 'required|integer',
            'paymentAmount' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }

        if (authUser()->ulb_id == '')
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050834", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = authUser()->ulb_id;
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mBdPayment = new BdPayment();
            $mBdStand = BdSettler::find($req->settlerId);
            $ulbId = ['ulbId' => $mBdStand->ulb_id];
            $req->request->add($ulbId);

            $paymentDate = ['paymentDate' => Carbon::now()->format('Y-m-d')];
            $req->request->add($paymentDate);
            // return $req;
            $listSettler = $mBdPayment->installmentPayment($req);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Payment Successfully", '', "050201", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function listInstallmentPayment(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'settlerId' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }

        if (authUser()->ulb_id == '')
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050834", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = authUser()->ulb_id;
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mBdPayment = new BdPayment();

            $listInstallment = $mBdPayment->listInstallmentPayment($req->settlerId)->map(function ($val) {
                $val->payment_date = Carbon::parse($val->payment_date)->format('d-m-Y');
                return $val;
            });
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Payment Successfully", $listInstallment, "050201", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function addPenaltyOrPerformanceSecurity(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'settlerId' => 'required|integer',
            'amount' => 'required|numeric',
            'isPenalty' => 'required|boolean',
            'remarks' => 'required|string',
            'penaltyType' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mBdSettlerTransaction = new BdSettlerTransaction();

            $mBdStand = BdSettler::find($req->settlerId);
            $ulbId = ['ulbId' => $mBdStand->ulb_id];
            $req->request->add($ulbId);

            $res = $mBdSettlerTransaction->addTransaction($req);

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Added Successfully", "", "050201", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function listSettlerTransaction(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'settlerId' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mBdSettlerTransaction = new BdSettlerTransaction();

            // $mBdStand = BdSettler::find($req->settlerId);
            // $ulbId = ['ulbId' => $mBdStand->ulb_id];
            // $req->request->add($ulbId);
            $credit = 0;
            $debit = 0;
            $list = $mBdSettlerTransaction->listSettlerTransaction($req->settlerId);
            foreach ($list as $l) {
                if ($l['is_penalty'] == NULL) {
                    $credit += $l['amount'];
                } else {
                    $debit += $l['amount'];
                }
            }
            $availableBalance = $credit - $debit;
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Data Fetch Successfully", ['transactionHistory' => $list, 'availableBalance' => $availableBalance], "050201", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function listParking(Request $req)
    {
        if (authUser()->ulb_id == '')
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050834", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = authUser()->ulb_id;
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mBdParking = new BdParking();

            $list = $mBdParking->listParking($ulbId);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Data Fetch Successfully", $list, "050201", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function listParkingSettler(Request $req)
    {
        if (authUser()->ulb_id == '')
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050834", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = authUser()->ulb_id;
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mBdSettler = new BdSettler();

            $list = $mBdSettler->listParkingSettler($ulbId);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Data Fetch Successfully", $list, "050201", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    public function listBazar(Request $req)
    {
        if (authUser()->ulb_id == '')
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050834", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = authUser()->ulb_id;
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mBdBazar = new BdBazar();

            $list = $mBdBazar->listBazar($ulbId);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Data Fetch Successfully", $list, "050201", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }


    public function listBazarSettler(Request $req)
    {
        if (authUser()->ulb_id == '')
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050834", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = authUser()->ulb_id;
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mBdSettler = new BdSettler();

            $list = $mBdSettler->listBazarSettler($ulbId);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Data Fetch Successfully", $list, "050201", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    public function listBanquetHall(Request $req)
    {
        if (authUser()->ulb_id == '')
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050834", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = authUser()->ulb_id;
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mBdBanquetHall = new BdBanquetHall();

            $list = $mBdBanquetHall->listBanquetHall($ulbId);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Data Fetch Successfully", $list, "050201", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    
    public function listBanquetHallSettler(Request $req)
    {
        if (authUser()->ulb_id == '')
            return responseMsgs(false, "Not Allowed", 'You Are Not Authorized !!', "050834", 1.0, "271ms", "POST", "", "");
        else
            $ulbId = authUser()->ulb_id;
        try {
            // Variable initialization
            $startTime = microtime(true);
            $mBdSettler = new BdSettler();

            $list = $mBdSettler->listBanquetHallSettler($ulbId);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            return responseMsgs(true, "Data Fetch Successfully", $list, "050201", "1.0", "$executionTime Sec", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }
}
