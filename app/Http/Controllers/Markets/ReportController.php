<?php

namespace App\Http\Controllers\Markets;

use App\Http\Controllers\Controller;
use App\Models\Markets\MarActiveBanquteHall;
use App\Models\Markets\MarBanquteHall;
use App\Models\Markets\MarDharamshala;
use App\Models\Markets\MarHostel;
use App\Models\Markets\MarLodge;
use App\Models\Markets\MarRejectedBanquteHall;
use App\Models\Property\PropFloor;
use App\Models\Property\PropOwner;
use App\Models\Property\PropProperty;
use App\Models\TradeLicence;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function finacialYearWiseApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reportType' => 'required|in:Banquet/Marriage Hall,Hostel,Lodge,Dharmshala',
            'wardNo' => 'nullable',
            'applicationType' => 'nullable|in:New Apply,Renew',
            'fyear' => 'nullable'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'validation error',
                'errors'  => $validator->errors()
            ], 200);
        }
        try {
            $banquet = new MarBanquteHall();
            $hostel = new MarHostel();
            $lodge = new MarLodge();
            $dharamshala = new MarDharamshala();
            $user = Auth()->user();
            $ulbId = $user->ulb_id ?? null;
            $response = [];
            $perPage = $request->perPage ?: 10;
            if ($request->reportType == 'Banquet/Marriage Hall') {
                $response = $banquet->getApplicationFinancialYearWise($request);
                $response['user_name'] = $user->name;
            }
            if ($request->reportType == 'Hostel') {
                $response = $hostel->getApplicationFinancialYearWise($request);
                $response['user_name'] = $user->name;
            }
            if ($request->reportType == 'Lodge') {
                $response = $lodge->getApplicationFinancialYearWise($request);
                $response['user_name'] = $user->name;
            }
            if ($request->reportType == 'Dharmshala') {
                $response = $dharamshala->getApplicationFinancialYearWise($request);
                $response['user_name'] = $user->name;
            }
            if ($response) {
                return responseMsgs(true, "Market Application List Fetch Succefully !!!", $response, "055017", "1.0", responseTime(), "POST", $request->deviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055017", "1.0", responseTime(), "POST", $request->deviceId);
        }
    }

    public function paymentCollection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reportType' => 'required|in:Banquet/Marriage Hall,Hostel,Lodge,Dharmshala',
            'wardNo' => 'nullable',
            'applicationType' => 'nullable|in:New Apply,Renew',
            'dateFrom' => 'nullable|date_format:Y-m-d',
            'dateUpto' => 'nullable|date_format:Y-m-d',
            'payMode' => 'nullable|in:All,Online,Cash,Cheque/DD',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'validation error',
                'errors'  => $validator->errors()
            ], 200);
        }
        try {
            $banquet = new MarBanquteHall();
            $hostel = new MarHostel();
            $lodge = new MarLodge();
            $dharamshala = new MarDharamshala();
            $user = Auth()->user();
            $ulbId = $user->ulb_id ?? null;
            $response = [];
            $perPage = $request->perPage ?: 10;
            if ($request->reportType == 'Banquet/Marriage Hall') {
                $response = $banquet->payCollection($request);
                $response['user_name'] = $user->name;
            }
            if ($request->reportType == 'Hostel') {
                $response = $hostel->payCollection($request);
                $response['user_name'] = $user->name;
            }
            if ($request->reportType == 'Lodge') {
                $response = $lodge->payCollection($request);
                $response['user_name'] = $user->name;
            }
            if ($request->reportType == 'Dharmshala') {
                $response = $dharamshala->payCollection($request);
                $response['user_name'] = $user->name;
            }
            if ($response) {
                return responseMsgs(true, "Market Payment List Fetch Succefully !!!", $response, "055017", "1.0", responseTime(), "POST", $request->deviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055017", "1.0", responseTime(), "POST", $request->deviceId);
        }
    }

    public function applicationStatusWiseApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reportType' => 'required|in:Banquet/Marriage Hall,Hostel,Lodge,Dharmshala',
            'wardNo' => 'nullable',
            'applicationType' => 'nullable|in:New Apply,Renew',
            'applicationStatus' => 'nullable|in:Approved,Reject',
            'dateFrom' => 'nullable|date_format:Y-m-d',
            'dateUpto' => 'nullable|date_format:Y-m-d',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'validation error',
                'errors'  => $validator->errors()
            ], 200);
        }
        try {
            $banquet = new MarBanquteHall();
            $hostel = new MarHostel();
            $lodge = new MarLodge();
            $dharamshala = new MarDharamshala();
            $user = Auth()->user();
            $ulbId = $user->ulb_id ?? null;
            $response = [];
            $perPage = $request->perPage ?: 10;
            if ($request->reportType == 'Banquet/Marriage Hall') {
                $response = $banquet->getApplicationWithStatus($request);
                $response['user_name'] = $user->name;
            }
            if ($request->reportType == 'Hostel') {
                $response = $hostel->getApplicationWithStatus($request);
                $response['user_name'] = $user->name;
            }
            if ($request->reportType == 'Lodge') {
                $response = $lodge->getApplicationWithStatus($request);
                $response['user_name'] = $user->name;
            }
            if ($request->reportType == 'Dharmshala') {
                $response = $dharamshala->getApplicationWithStatus($request);
                $response['user_name'] = $user->name;
            }
            if ($response) {
                return responseMsgs(true, "Market Application List Fetch Succefully !!!", $response, "055017", "1.0", responseTime(), "POST", $request->deviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055017", "1.0", responseTime(), "POST", $request->deviceId);
        }
    }

    public function ruleWiseApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reportType' => 'required|in:Banquet/Marriage Hall,Hostel,Lodge,Dharmshala',
            'wardNo' => 'nullable',
            'applicationType' => 'nullable|in:New Apply,Renew',
            'applicationStatus' => 'nullable|in:Approved,Reject',
            'dateFrom' => 'nullable|date_format:Y-m-d',
            'dateUpto' => 'nullable|date_format:Y-m-d',
            'ruleType' => 'nullable|in:All,New Rule,Old Rule'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'validation error',
                'errors'  => $validator->errors()
            ], 200);
        }
        try {
            $banquet = new MarBanquteHall();
            $hostel = new MarHostel();
            $lodge = new MarLodge();
            $dharamshala = new MarDharamshala();
            $user = Auth()->user();
            $ulbId = $user->ulb_id ?? null;
            $response = [];
            $perPage = $request->perPage ?: 10;
            if ($request->reportType == 'Banquet/Marriage Hall') {
                $response = $banquet->getApplicationWithRule($request);
                $response['user_name'] = $user->name;
            }
            if ($request->reportType == 'Hostel') {
                $response = $hostel->getApplicationWithRule($request);
                $response['user_name'] = $user->name;
            }
            if ($request->reportType == 'Lodge') {
                $response = $lodge->getApplicationWithRule($request);
                $response['user_name'] = $user->name;
            }
            if ($request->reportType == 'Dharmshala') {
                $response = $dharamshala->getApplicationWithRule($request);
                $response['user_name'] = $user->name;
            }
            if ($response) {
                return responseMsgs(true, "Market Application List Fetch Succefully !!!", $response, "055017", "1.0", responseTime(), "POST", $request->deviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055017", "1.0", responseTime(), "POST", $request->deviceId);
        }
    }

    public function hallTypeWiseApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reportType' => 'required|in:Banquet/Marriage Hall',
            'wardNo' => 'nullable',
            'applicationType' => 'nullable|in:New Apply,Renew',
            'applicationStatus' => 'nullable|in:Approved,Reject',
            'dateFrom' => 'nullable|date_format:Y-m-d',
            'dateUpto' => 'nullable|date_format:Y-m-d',
            'ruleType' => 'nullable|in:All,New Rule,Old Rule',
            //'hallType' => 'nullable|in:MARRIAGE HALL,BANQUET HALL,BANQUET+MARRIAGE HALL'
            'hallType' => 'nullable|integer'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'validation error',
                'errors'  => $validator->errors()
            ], 200);
        }
        try {
            $banquet = new MarBanquteHall();
            $user = Auth()->user();
            $ulbId = $user->ulb_id ?? null;
            $response = [];
            $perPage = $request->perPage ?: 10;
            if ($request->reportType == 'Banquet/Marriage Hall') {
                $response = $banquet->getHallTypeApplication($request);
                $response['user_name'] = $user->name;
            }
            if ($response) {
                return responseMsgs(true, "Market Application List Fetch Succefully !!!", $response, "055017", "1.0", responseTime(), "POST", $request->deviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055017", "1.0", responseTime(), "POST", $request->deviceId);
        }
    }

    public function organizationTypeWiseApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reportType' => 'required|in:Banquet/Marriage Hall,Dharmshala',
            'wardNo' => 'nullable',
            'applicationType' => 'nullable|in:New Apply,Renew',
            'applicationStatus' => 'nullable|in:Approved,Reject',
            'dateFrom' => 'nullable|date_format:Y-m-d',
            'dateUpto' => 'nullable|date_format:Y-m-d',
            'ruleType' => 'nullable|in:All,New Rule,Old Rule',
            // 'organizationType' => 'nullable|in:PRIVATE,GOVERNMENT'
            'organizationType' => 'nullable|integer'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'validation error',
                'errors'  => $validator->errors()
            ], 200);
        }
        try {
            $banquet = new MarBanquteHall();
            $dharamshala = new MarDharamshala();
            $user = Auth()->user();
            $ulbId = $user->ulb_id ?? null;
            $response = [];
            $perPage = $request->perPage ?: 10;
            if ($request->reportType == 'Banquet/Marriage Hall') {
                $response = $banquet->getOrganizationTypeApplication($request);
                $response['user_name'] = $user->name;
            }
            if ($request->reportType == 'Dharmshala') {
                $response = $dharamshala->getOrganizationTypeApplication($request);
                $response['user_name'] = $user->name;
            }
            if ($response) {
                return responseMsgs(true, "Market Application List Fetch Succefully !!!", $response, "055017", "1.0", responseTime(), "POST", $request->deviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055017", "1.0", responseTime(), "POST", $request->deviceId);
        }
    }

    public function hostelTypeWiseApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reportType' => 'required|in:Hostel',
            'wardNo' => 'nullable',
            'applicationType' => 'nullable|in:New Apply,Renew',
            'applicationStatus' => 'nullable|in:Approved,Reject',
            'dateFrom' => 'nullable|date_format:Y-m-d',
            'dateUpto' => 'nullable|date_format:Y-m-d',
            'ruleType' => 'nullable|in:All,New Rule,Old Rule',
            // 'hostelType' => 'nullable|in:BOYS,GIRLS'
            'hostelType' => 'nullable|integer'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'validation error',
                'errors'  => $validator->errors()
            ], 200);
        }
        try {
            $hostel = new MarHostel();
            $user = Auth()->user();
            $ulbId = $user->ulb_id ?? null;
            $response = [];
            $perPage = $request->perPage ?: 10;
            if ($request->reportType == 'Hostel') {
                $response = $hostel->getApplicationWithHostelType($request);
                $response['user_name'] = $user->name;
            }
            if ($response) {
                return responseMsgs(true, "Market Application List Fetch Succefully !!!", $response, "055017", "1.0", responseTime(), "POST", $request->deviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055017", "1.0", responseTime(), "POST", $request->deviceId);
        }
    }

    public function lodgeTypeWiseApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reportType' => 'required|in:Lodge',
            'wardNo' => 'nullable',
            'applicationType' => 'nullable|in:New Apply,Renew',
            'applicationStatus' => 'nullable|in:Approved,Reject',
            'dateFrom' => 'nullable|date_format:Y-m-d',
            'dateUpto' => 'nullable|date_format:Y-m-d',
            'ruleType' => 'nullable|in:All,New Rule,Old Rule',
            // 'lodgeType' => 'nullable|in:BOYS,GIRLS'
            'lodgeType' => 'nullable|integer'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'validation error',
                'errors'  => $validator->errors()
            ], 200);
        }
        try {
            $lodge = new MarLodge();
            $user = Auth()->user();
            $ulbId = $user->ulb_id ?? null;
            $response = [];
            $perPage = $request->perPage ?: 10;
            if ($request->reportType == 'Lodge') {
                $response = $lodge->getApplicationWithLodgeType($request);
                $response['user_name'] = $user->name;
            }
            if ($response) {
                return responseMsgs(true, "Market Application List Fetch Succefully !!!", $response, "055017", "1.0", responseTime(), "POST", $request->deviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055017", "1.0", responseTime(), "POST", $request->deviceId);
        }
    }

    public function holdingDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'holdingNo' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validator->errors()
            ], 200);
        }

        try {
            $mProperties = new PropProperty();
            $mPropFloors = new PropFloor();
            $mPropOwners = new PropOwner();
            //$mTrade = new TradeLicence();
            $fiYear = getFY();
            list($currentfyStartDate, $currentfyEndDate) = explode('-', $fiYear);
            $currentfyStartDate = $currentfyStartDate . "-04-01";
            $currentfyEndDate = $currentfyEndDate . "-03-31";
            if ($request->holdingNo) {
                $properties = $mProperties->getPropDtlsv2($fiYear, $currentfyStartDate)
                    ->where('prop_properties.holding_no', $request->holdingNo)
                    ->first();
                if (!$properties) {
                    throw new Exception("Property Not Found");
                }

                if ($properties->arrear_demand != 0) {
                    throw new Exception("Demand against this holding is not clear, Please pay your demand first");
                }

               // $mTrade = $mTrade->getLicenceByHoldingNo($properties->holding_no);

                $floors = $mPropFloors->getPropFloorsV2($properties->id)->get();
                $owners = $mPropOwners->getOwnerByPropIdV2($properties->id);

                $propertyDtl = collect($properties);
                $propertyDtl['floors'] = $floors;
                $propertyDtl['owners'] = $owners;

                return responseMsgs(true, "Holding No. Validated Successfully", remove_null($propertyDtl), "010116", "1.0", "", "POST", $request->deviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055017", "1.0", responseTime(), "POST", $request->deviceId);
        }
    }
}
