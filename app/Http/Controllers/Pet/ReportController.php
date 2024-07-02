<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\Models\Pet\PetActiveRegistration;
use App\Models\Pet\PetApprovedRegistration;
use App\Models\Pet\PetRejectedRegistration;
use App\Models\Pet\PetRenewalRegistration;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function allTypeReports(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reportType' => 'required|in:Pending,Approved,Rejected,Expired,Renewal',
            'filterBy'  => 'nullable|in:mobileNo,applicantName,applicationNo,holdingNo,safNo',
            'parameter' => 'nullable',
            'dateFrom' => 'nullable|date_format:Y-m-d',
            'dateUpto' => 'nullable|date_format:Y-m-d'
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
            if ($request->reportType == 'Pending') {
                $response = $active->pendingApplication($request);
                //$response['user_name'] = $user->name;
            }
            if ($request->reportType == 'Approved') {
                $response = $approved->approvedApplication($request);
                //$response['user_name'] = $user->name;
            }
            if ($request->reportType == 'Rejected') {
                $response = $reject->rejectedApplication($request);
                //$response['user_name'] = $user->name;
            }
            if ($request->reportType == 'Renewal') {
                $response = $renew->renewApplication($request);
                //$response['user_name'] = $user->name;
            }
            if ($request->reportType == 'Expired') {
                $response = $approved->expiredApplication($request);
                 //$response['user_name'] = $user->name;
            }
            if ($response) {
                return responseMsgs(true, "Pet Application List Fetch Succefully !!!", $response, "055017", "1.0", responseTime(), "POST", $request->deviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055017", "1.0", responseTime(), "POST", $request->deviceId);
        }
    }
}
