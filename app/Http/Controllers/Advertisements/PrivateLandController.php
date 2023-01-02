<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use App\Models\Advertisements\AdvActivePrivateland;
use Exception;
use Illuminate\Http\Request;

/**
 * | Created On-02-01-2022 
 * | Created By-Anshu Kumar
 * | Private Land Operations
 */

class PrivateLandController extends Controller
{
    /**
     * | Apply For Private Land Advertisement
     */
    public function store(Request $req)
    {
        try {
            $privateLand = new AdvActivePrivateland();
            $citizenId = ['citizenId' => authUser()->id];
            $req->request->add($citizenId);
            $applicationNo = $privateLand->store($req);       //<--------------- Model function to store 
            return responseMsgs(
                true,
                "Successfully Submitted the application !!",
                [
                    'status' => true,
                    'ApplicationNo' => $applicationNo
                ],
                "040401",
                "1.0",
                "260ms",
                'POST',
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "040401",
                "1.0",
                "200ms",
                "POST",
                $req->deviceId ?? ""
            );
        }
    }
}
