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

    /**
     * | Add Circle Records
     * | Function - 01
     * | API - 01
     */
    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'circleName' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {
            $exists = $this->_mCircle->getCircleNameByUlbId($req->circleName, $req->auth['ulb_id']);
            if (collect($exists)->isNotEmpty())
                throw new Exception("Circle According To Ulb Already Existing");

            $metaReqs = [
                'circle_name' => $req->circleName,
                'ulb_id' => $req->auth['ulb_id']
            ];

            $this->_mCircle->create($metaReqs);
            return responseMsgs(true, "Successfully Saved", [$metaReqs], "055201", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055201", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Edit Circle Records
     * | Function - 02
     * | API - 02
     */
    public function edit(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id'         => 'required|integer',
            'circleName' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {
            $exists = $this->_mCircle->getCircleNameByUlbId($req->circleName, $req->auth['ulb_id']);
            if (collect($exists) && $exists->where('id', '!=', $req->id)->isNotEmpty())
                throw new Exception("Circle According To Ulb Id Already Existing");

            $metaReqs = [
                'circle_name' => $req->circleName,
                'ulb_id' => $req->auth['ulb_id']
            ];

            $circle = $this->_mCircle->findOrFail($req->id);
            $circle->update($metaReqs);
            return responseMsgs(true, "Successfully Saved", [$metaReqs], "055202", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055202", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Get Circle List By ULB Id
     * | Function - 03
     * | API - 03
     */
    public function getListCircle(Request $req)
    {
        try {
            $circle = $this->_mCircle->getListCircleByUlbId($req->auth['ulb_id']);
            if (collect($circle)->isEmpty())
                throw new Exception("No Data Found");
            if ($req->key)
                $circle = searchCircleFilter($circle, $req);
            $circle = paginator($circle, $req);
            return responseMsgs(true, "", $circle, "055203", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055203", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Delete Circle Records
     * | Function - 04
     * | API - 04
     */
    public function delete(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id'  => 'required|integer',
            'isActive' => 'required|bool'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {
            if (isset($req->isActive)) {
                $isActive = $req->isActive == false ? 0 : 1;
                $metaReqs = [
                    'is_active' => $isActive
                ];
            }
            $Shops = $this->_mCircle::findOrFail($req->id);
            $Shops->update($metaReqs);
            return responseMsgs(true, "Status Updated Successfully", [], "055204", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055204", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    /**
     * | Delete Circle Records
     * | Function - 05
     * | API - 05
     */
    public function getCircleDetailById(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'circleId'  => 'required|integer'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $mMCircle = new MCircle();
            $details = $mMCircle->getCircleDetails($req->circleId);
            return responseMsgs(true, "Fetch Details Successfully !!!", $details, "055205", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055205", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

   /**
     * | List All Circle Records
     * | Function - 06
     * | API - 06
     */
    public function listAllCircle(Request $req){
        try {
            $circle = $this->_mCircle->getListCircleByUlbId($req->auth['ulb_id'])->get();
            return responseMsgs(true, "", $circle, "055206", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "055206", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
}
