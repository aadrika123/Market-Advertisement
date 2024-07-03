<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\Models\Pet\PetActiveDetail;
use App\Models\Pet\PetActiveRegistration;
use App\Models\Pet\PetApprovedRegistration;
use App\Models\Pet\PetRejectedRegistration;
use App\Models\Pet\PetRenewalRegistration;
use App\Models\Pet\PetTran;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function applicationReports(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reportType' => 'required|in:applicationReport,collectionReport,vaccinationReport',
            'applicationType' => 'required|in:Pending,Approved,Renewal,Expired,Rejected',
            'filterBy'  => 'nullable|in:mobileNo,applicantName,applicationNo,holdingNo,safNo',
            'parameter' => 'nullable',
            'dateFrom' => 'nullable|date_format:Y-m-d',
            'wardNo' => 'nullable',
            'dateUpto' => 'nullable|date_format:Y-m-d',
            'level' => 'nullable|in:BO,DA,SI'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'validation error',
                'errors'  => $validator->errors()
            ], 200);
        }
        try {
            $active = new PetActiveRegistration();
            $approved = new PetApprovedRegistration();
            $reject = new PetRejectedRegistration();
            $renew = new PetRenewalRegistration();
            $user = Auth()->user();
            $response = [];
            if ($request->reportType == 'applicationReport' && $request->applicationType == 'Pending') {
                $response = $active->pendingApplication($request);
                //$response['user_name'] = $user->name;
            }
            if ($request->reportType == 'applicationReport' && $request->applicationType == 'Approved') {
                $response = $approved->approvedApplication($request);
                //$response['user_name'] = $user->name;
            }
            if ($request->reportType == 'applicationReport' && $request->applicationType == 'Rejected') {
                $response = $reject->rejectedApplication($request);
                //$response['user_name'] = $user->name;
            }
            if ($request->reportType == 'applicationReport' && $request->applicationType == 'Renewal') {
                $response = $renew->renewApplication($request);
                //$response['user_name'] = $user->name;
            }
            if ($request->reportType == 'applicationReport' && $request->applicationType == 'Expired') {
                $response = $approved->expiredApplication($request);
                //$response['user_name'] = $user->name;
            }
            if ($request->applicationType == 'Pending' && $request->level == 'BO') {
                $response = $active->boApplication($request);
                //$response['user_name'] = $user->name;
            }
            if ($request->applicationType == 'Pending' && $request->level == 'DA') {
                $response = $active->daApplication($request);
                //$response['user_name'] = $user->name;
            }
            if ($request->applicationType == 'Pending' && $request->level == 'SI') {
                $response = $active->siApplication($request);
                //$response['user_name'] = $user->name;
            }
            if ($response) {
                return responseMsgs(true, "Pet Application List Fetch Succefully !!!", $response, "055017", "1.0", responseTime(), "POST", $request->deviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055017", "1.0", responseTime(), "POST", $request->deviceId);
        }
    }

    public function payCollectionReports(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reportType' => 'required|in:collectionReport',
            'dateFrom' => 'nullable|date_format:Y-m-d',
            'dateUpto' => 'nullable|date_format:Y-m-d',
            'wardNo' => 'nullable',
            'paymentMode'  => 'nullable|in:CASH,ONLINE',
            'collectionBy' => 'nullable|in:JSK,Citizen'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'validation error',
                'errors'  => $validator->errors()
            ], 200);
        }
        try {
            $tran = new PetTran();
            $response = [];
            $user = Auth()->user();
            if ($request->reportType == 'collectionReport') {
                $response = $tran->dailyCollection($request);
                //$response['user_name'] = $user->name;
            }
            if ($response) {
                //return response()->json(['status' => true, 'data' => $response, 'msg' => ''], 200);
                return responseMsgs(true, "Pet Collection List Fetch Succefully !!!", $response, "055017", "1.0", responseTime(), "POST", $request->deviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055017", "1.0", responseTime(), "POST", $request->deviceId);
        }
    }

    public function vaccinationReports(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reportType' => 'required|in:vaccinationReport',
            'vaccinationType' => 'required|in:rabies,leptospirosis',
            'dateFrom' => 'nullable|date_format:Y-m-d',
            'dateUpto' => 'nullable|date_format:Y-m-d',
            'wardNo' => 'nullable',
            'vaccinationPending' => 'nullable|in:less_than_1_year,1_to_3_years,3_to_6_years,more_than_6_years'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'validation error',
                'errors'  => $validator->errors()
            ], 200);
        }
        try {
            $active = new PetActiveDetail();
            $response = [];
            $user = Auth()->user();
            if ($request->reportType == 'vaccinationReport' && $request->vaccinationType == 'rabies') {
                $response = $active->getPendingRabiesVaccine($request);
            }
            if ($request->reportType == 'vaccinationReport' && $request->vaccinationType == 'leptospirosis') {
                $response = $active->getPendingLeptospirosisVaccine($request);
            }
            //$response['user_name'] = $user->name;
            if ($response) {
                //return response()->json(['status' => true, 'data' => $response, 'msg' => ''], 200);
                return responseMsgs(true, "Pet Collection List Fetch Succefully !!!", $response, "055017", "1.0", responseTime(), "POST", $request->deviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055017", "1.0", responseTime(), "POST", $request->deviceId);
        }
    }
}
