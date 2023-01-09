<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrivateLand\StoreRequest;
use App\Models\Advertisements\AdvActivePrivateland;
use Exception;

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
    public function store(StoreRequest $req)
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
                "",
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
                "",
                "POST",
                $req->deviceId ?? ""
            );
        }
    }
}
