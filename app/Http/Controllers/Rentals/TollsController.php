<?php

namespace App\Http\Controllers\Rentals;

use App\Http\Controllers\Controller;
use App\Http\Requests\Toll\TollValidationRequest;
use App\Models\Rentals\MarToll;
use App\Models\Rentals\MarTollPayment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;
use Illuminate\Support\Facades\Config;
use App\MicroServices\DocumentUpload;
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
    //------------crud started from here-------------
    //-------------insert
    public function store(TollValidationRequest $request)
    {
        try {
            $docUpload = new DocumentUpload;
            $relativePath = config::get('constants.TOLL_PATH');
            if (isset($request->photograph1)) {
                $image = $request->file('photograph1');
                $refImageName = 'Toll-Photo-1'. '-'.$request->vendorName ;
                $imageName1 = $docUpload->upload($refImageName, $image, $relativePath);
                $absolutePath = public_path($relativePath);
                $imageName1Absolute = $refImageName . $absolutePath;
                }

            if (isset($request->photograph2)) {
                $image = $request->file('photograph2');
                $refImageName = 'Toll-Photo-2'. '-'.$request->vendorName ;
                $imageName2 = $docUpload->upload($refImageName, $image, $relativePath);
                $absolutePath = public_path($relativePath);
                $imageName2Absolute = $refImageName . $absolutePath;
                }
       
            $marToll = [
                'area_name'               => $request->areaName,
                'toll_no'                 => $request->tollNo,
                'toll_type'               => $request->tollType,
                'vendor_name'             => $request->vendorName,
                'address'                 => $request->address,
                'rate'                    => $request->rate,
                'last_payment_date'       => $request->lastPaymentDate,
                'last_amount'             => $request->lastAmount,
                'location'                => $request->location,
                'present_length'          => $request->presentLength,
                'present_breadth'         => $request->presentBreadth,
                'present_height'          => $request->presentHeight,
                'no_of_floors'            => $request->noOfFloors,
                'trade_license'           => $request->tradeLicense,
                'construction'            => $request->construction,
                'utility'                 => $request->utility,
                'mobile'                  => $request->mobile,
                'remarks'                 => $request->remarks,
                'photograph1'             => $imageName1 ?? null,
                'photo1_absolute_path'    => $imageName1Absolute ?? null,
                'photograph2'             => $imageName2 ?? null,
                'photo2_absolute_path'    => $imageName2Absolute ?? null,
                'longitude'               => $request->longitude,
                'latitude'                => $request->latitude,
                'user_id'                 => $request->userId,
                'ulb_id'                  => $request->ulbId,
                'last_tran_id'            => $request->lastTranId,
            ];
            $this->_mToll->create($marToll);
            return responseMsgs(true, "Successfully Saved", $marToll, 055102, "1.0", responseTime(), "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], 055102, "1.0", responseTime(), "POST", $request->deviceId);
        }
    }
    //-------------update toll details-----------------
    public function edit(TollValidationRequest $request) //upadte
    {
        $validator = Validator::make($request->all(), [
            "id" => 'required|numeric',
            "status" => 'nullable|bool'
        ]);
        if ($validator->fails()) 
            return responseMsgs(false, $validator->errors(), [], 055103, "1.0", responseTime(), "POST", $request->deviceId);

        try {
            $relativePath = config::get('constants.TOLL_PATH');
            $docUpload = new DocumentUpload;

            if (isset($request->photograph1)) {
                $image = $request->file('photograph1');
                $refImageName = 'Toll-Photo-1'. '-'.$request->vendorName ;
                $imageName1 = $docUpload->upload($refImageName, $image, $relativePath);
                $absolutePath = public_path($relativePath);
                $imageName1Absolute = $absolutePath . '-' . $imageName1;
                }

            if (isset($request->photograph2)) {
                $image = $request->file('photograph2');
                $refImageName = 'Toll-Photo-2'. '-'.$request->vendorName ;
                $imageName2 = $docUpload->upload($refImageName, $image, $relativePath);
                $absolutePath = public_path($relativePath);
                $imageName2Absolute = $absolutePath . '-' . $imageName2;
                }
            $marToll = [
                'area_name' => $request->areaName,
                'toll_no' => $request->tollNo,
                'toll_type' => $request->tollType,
                'vendor_name' => $request->vendorName,
                'address' => $request->address,
                'rate' => $request->rate,
                'last_payment_date' => $request->lastPaymentDate,
                'last_amount' => $request->lastAmount,
                'location' => $request->location,
                'present_length' => $request->presentLength,
                'present_breadth' => $request->presentBreadth,
                'present_height' => $request->presentHeight,
                'no_of_floors' => $request->noOfFloors,
                'trade_license' => $request->tradeLicense,
                'construction' => $request->construction,
                'utility' => $request->utility,
                'mobile' => $request->mobile,
                'remarks' => $request->remarks,
                'photograph1' => $imageName1 ?? null,
                'photo1_absolute_path' => $imageName1Absolute ?? null,
                'photograph2' => $imageName2 ?? null,
                'photo2_absolute_path' => $imageName2Absolute ?? null,
                'longitude' => $request->longitude,
                'latitude' => $request->latitude,
                'user_id' => $request->userId,
                'ulb_id' => $request->ulbId,
                'last_tran_id' => $request->lastTranId,
            ];
            if (isset($request->status)) {                  // In Case of Deactivation or Activation
                $status = $request->status == false ? 0 : 1;
                $marToll=array_merge($marToll,['status',$status]);
            }

            $toll = $this->_mToll::findOrFail($request->id);
            $toll->update($marToll);
            return responseMsgs(true, "update Successfully ",  [], 055104, "1.0", responseTime(), "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], 055104, "1.0", responseTime(), "POST", $request->deviceId);
        }
    }




    //------------------------get toll by id----------------------------
    public function show(Request $request)
    {
        $validator = validator::make($request->all(), [
            'id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors(), []);
        }
        try {

            $toll = $this->_mToll::findOrFail($request->id);

            if (collect($toll)->isEmpty())
                throw new Exception("Toll not Exist");
            return responseMsgs(true, "record found", $toll, 055105, "1.0", responseTime(), "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], 055105, "1.0", responseTime(), "POST", $request->deviceId);
        }
    }

    //-----------------------show all tolls----------------
    public function retrieve(Request $request) {
        try {
            $mtoll = $this->_mToll->retrieveAll();
            return responseMsgs(true, "", $mtoll, 55106, "1.0", responseTime(), "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], 055106, "1.0", responseTime(), "POST", $request->deviceId);
        }
    }

    //---------------------show active tolls-------------------
    public function retrieveActive(Request $request)
    {
        try {
            $mtoll = $this->_mToll->retrieveActive();
            return responseMsgs(true, "", $mtoll, 55107, "1.0", responseTime(), "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], 055106, "1.0", responseTime(), "POST", $request->deviceId);
        }
    }

    //-----------soft delete---------------------

    public function delete(Request $request)
    {

        $validator = validator::make($request->all(), [
            'status' => 'required|bool'
        ]);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors(), []);
        }
        try {
            if (isset($request->status)) {
                $status = $request->status == false ? 0 : 1;
                $metaReqs = [
                    'status' => $status
                ];
            }
            $marToll = $this->_mToll::findOrFail($request->id);
            $marToll->update($metaReqs);
            return responseMsgs(true, "Status Updated Successfully", [], 55108, "1.0", responseTime(), "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], 055107, "1.0", responseTime(), "POST", $request->deviceId);
        }
    }
}
