<?php

namespace App\Http\Controllers\Params;

use App\Http\Controllers\Controller;
use App\Models\Param\RefAdvParamstring;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ParamController extends Controller
{
    // String Parameters
    public function paramStrings(Request $req)
    {
        try {
            $redis = Redis::connection();
            $data = json_decode(Redis::get('adv_param_strings'));       // Get Value from Redis Cache
            if (!$data) {
                $mParamString = new RefAdvParamstring();
                $strings = $mParamString->masters();
                $data = $strings->groupBy('param_category');
                $data = remove_null($data->toArray());
                $redis->set('adv_param_strings', json_encode($data));   // Set Key on Param Strings
            }
            return responseMsgs(true, "Param Strings", $data, "040201", "1.0", "", "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040201", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }
}
