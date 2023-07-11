<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Master\MCircle;
use Illuminate\Support\Facades\Validator;
use Exception;

class CircleController extends Controller
{
    private $_mCircle;

    public function __construct()
    {
        $this->_mCircle = new MCircle();
    }

    // Add records
    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'circleName' => 'required|string',
            'ulbId' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $metaReqs = [
                'circle_name' => $req->circleName,
                'ulb_id' => $req->ulbId
            ];

            $this->_mCircle->create($metaReqs);

            return responseMsgs(true, "Successfully Saved", [$metaReqs], [], "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], [], "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //find by Ulb Id
    public function getCircleByUlb(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'ulbId' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $Circle = $this->_mCircle->getGroupById($req->ulbId);
            if (collect($Circle)->isEmpty())
                throw new Exception("Circle Does Not Exist");
            return responseMsgs(true, "", $Circle, [], "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], [], "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
}
