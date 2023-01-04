<?php

namespace App\Http\Controllers\Params;

use App\Http\Controllers\Controller;
use App\Models\Param\RefAdvParamstring;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class ParamController extends Controller
{
    // String Parameters
    public function paramStrings(Request $req)
    {
        try {
            $mUlbId = $req->ulbId;
            $data = json_decode(Cache::get('adv_param_strings' . $mUlbId));       // Get Value from Redis Cache
            $bearer = $req->bearerToken();
            if (!$data) {
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
                $data['wards'] = $mWards['data'];

                Cache::put('adv_param_strings' . $mUlbId, json_encode($data));  // Set Key on Param Strings
            }
            return responseMsgs(true, "Param Strings", $data, "040201", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }

    // Document Masters for Advertisements
    public function documentMstrs()
    {
        $documents = json_decode(file_get_contents(storage_path() . "/local-db/advDocumentMstrs.json", true));
        return $documents;
    }
}
