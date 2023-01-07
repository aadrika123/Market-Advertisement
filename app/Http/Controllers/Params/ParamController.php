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
    /**
     * | String Parameters values
     * | @param request $req
     */
    public function paramStrings(Request $req)
    {
        try {
            $mUlbId = $req->ulbId;
            $data = json_decode(Cache::get('adv_param_strings' . $mUlbId));       // Get Value from Redis Cache Memory
            $bearer = $req->bearerToken();
            if (!$data) {                                                         // If Cache Memory is not available
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
}
