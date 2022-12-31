<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;

class VehicleAdvetController extends Controller
{
    /**
     * | Created On-31-12-2022 
     * | Created By-Anshu Kumar
     * | Created for the Movable Vehicles Operations
     */
    public function store(Request $req)
    {
        try {
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040301", "1.0", "", "POST", $req->deviceId ?? "");
        }
    }
}
