<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicles\StoreRequest;
use App\Models\Advertisements\AdvActiveVehicle;
use Exception;

class VehicleAdvetController extends Controller
{
    /**
     * | Created On-31-12-2022 
     * | Created By-Anshu Kumar
     * | Created for the Movable Vehicles Operations
     */
    public function store(StoreRequest $req)
    {
        try {
            // dd($req->all());
            $advVehicle = new AdvActiveVehicle();
            $citizenId = ['citizenId' => authUser()->id];
            $req->request->add($citizenId);
            $applicationNo = $advVehicle->store($req);               // Store Vehicle 
            return responseMsgs(
                true,
                "Successfully Applied the Application !!",
                ["ApplicationNo" => $applicationNo],
                "040301",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040301", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }
}
