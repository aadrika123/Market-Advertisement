<?php

namespace App\Http\Controllers\Params;

use App\Http\Controllers\Controller;
use App\MicroServices\DocumentUpload;
use App\Models\Param\RefAdvParamstring;
use App\Models\Advertisements\AdvActiveSelfadvetdocument;
use App\Models\Advertisements\AdvAgency;
use App\Models\Advertisements\AdvAgencyLicense;
use App\Models\Advertisements\AdvPrivateland;
use App\Models\Advertisements\AdvSelfadvertisement;
use App\Models\Advertisements\AdvVehicle;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ParamController extends Controller
{
    /**
     * | String Parameters values
     * | @param request $req
     */
    public function paramStrings(Request $req)
    {
        try {
            $mUlbId = $req->ulbId;
            $data = json_decode(Cache::get('adv_param_strings' . $mUlbId));      // Get Value from Redis Cache Memory
            $bearer = $req->bearerToken();
            if (!$data) {                                                        // If Cache Memory is not available
                $data = array();
                $baseUrl = Config::get('constants.BASE_URL');
                $mParamString = new RefAdvParamstring();
                $strings = $mParamString->masters($mUlbId);
                $data['paramCategories'] = remove_null($strings->groupBy('param_category')->toArray());
                // Get Wards By Ulb Id
                $mWards = Http::withHeaders([
                    "Authorization" => "Bearer $bearer",
                    "contentType" => "application/json"

                ])->post($baseUrl . 'api/workflow/getWardByUlb', [
                    "ulbId" => $mUlbId
                ]);

                if (!$mWards)
                    throw new Exception("Wards not found");

                $data['wards'] = $mWards['data'];

                Cache::put('adv_param_strings' . $mUlbId, json_encode($data));  // Set Key on Param Strings
            }
            return responseMsgs(
                true,
                "Param Strings",
                $data,
                "040201",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "040201",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        }
    }

    /**
     * | Get Document Masters from our localstorage db
     */
    public function documentMstrs()
    {
        $startTime = microtime(true);
        $documents = json_decode(file_get_contents(storage_path() . "/local-db/advDocumentMstrs.json", true));
        $documents = remove_null($documents);
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        return responseMsgs(
            true,
            "Document Masters",
            $documents,
            "040202",
            "1.0",
            $executionTime . " Sec",
            "POST"
        );
    }



    /**
     * | Get Document Masters from our localstorage db
     */
    public function districtMstrs()
    {
        $startTime = microtime(true);
        $districts = json_decode(file_get_contents(storage_path() . "/local-db/districtMstrs.json", true));
        $districts = remove_null($districts);
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        return responseMsgs(
            true,
            "District Masters",
            $districts,
            "040202",
            "1.0",
            $executionTime . " Sec",
            "POST"
        );
    }

    public function metaReqs($req)
    {
        $metaReqs = [
            'verified_by' => $req['roleId'],
            'verify_status' => $req['verifyStatus'],
            'remarks' => $req['remarks'],
            'verified_on' => Carbon::now()->format('Y-m-d')
        ];
        return $metaReqs;
    }




    /**
     * Summary of payment Success Failure of all Types of Advertisment 
     * @return void
     * @param request $req
     */
    public function paymentSuccessFailure(Request $req)
    {
        try {
            $startTime = microtime(true);
            DB::beginTransaction();
            $updateData = [
                'payment_date' => Carbon::now(),
                'payment_status' => 1,
                'payment_id' => $req->paymentId,
                'payment_details' => $req->all(),
            ];


            if ($req->workflowId == 245) { // Self Advertisement

                DB::table('adv_selfadvertisements')
                    ->where('id', $req->id)
                    ->update($updateData);

                $mAdvSelfadvertisement = AdvSelfadvertisement::find($req->id);

                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('adv_selfadvet_renewals')
                    ->where('id', $mAdvSelfadvertisement->last_renewal_id)
                    ->update($updateData);

            } elseif ($req->workflowId == 248) { // Movable Vechicles

                DB::table('adv_vehicles')
                    ->where('id', $req->id)
                    ->update($updateData);

                $mAdvVehicle = AdvVehicle::find($req->id);

                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('adv_vehicle_renewals')
                    ->where('id', $mAdvVehicle->last_renewal_id)
                    ->update($updateData);

            } elseif ($req->workflowId == 249) { // Agency Apply

                 DB::table('adv_agencies')
                    ->where('id', $req->id)
                    ->update($updateData);

                $mAdvVehicle = AdvAgency::find($req->id);

                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('adv_agency_renewals')
                    ->where('id', $mAdvVehicle->last_renewal_id)
                    ->update($updateData);

            } elseif ($req->workflowId == 250) { // Private Land

                DB::table('adv_privatelands')
                    ->where('id', $req->id)
                    ->update($updateData);

                $mAdvPrivateland = AdvPrivateland::find($req->id);

                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('adv_privateland_renewals')
                    ->where('id', $mAdvPrivateland->last_renewal_id)
                    ->update($updateData);

            } elseif ($req->workflowId == 251) { // Hording Apply
                
                DB::table('adv_agency_licenses')
                    ->where('id', $req->id)
                    ->update($updateData);

                $mAdvAgencyLicense = AdvAgencyLicense::find($req->id);

                $updateData['payment_amount'] = $req->amount;
                // update in Renewals Table
                DB::table('adv_agency_license_renewals')
                    ->where('id', $mAdvAgencyLicense->last_renewal_id)
                    ->update($updateData);

            }

            DB::commit();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            $msg = "Payment Accepted Successfully !!!";
            return responseMsgs(true, $msg, "", '050206', 01, "$executionTime Sec", 'Post', $req->deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", '050206', 01, "", 'Post', $req->deviceId);
        }
    }
}
