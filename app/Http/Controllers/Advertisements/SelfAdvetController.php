<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelfAdvets\StoreRequest;
use App\Models\Advertisements\AdvActiveSelfadvertisement;
use Exception;

/**
 * | Created On-14-12-2022 
 * | Created By-Anshu Kumar
 * | Created for Operations on Self Advertisements
 */

class SelfAdvetController extends Controller
{
    /**
     * | Apply for Self Advertisements 
     * | @param request 
     */
    public function store(StoreRequest $req)
    {
        try {
            $selfAdvets = new AdvActiveSelfadvertisement();
            $citizenId = ['citizenId' => authUser()->id];
            $req->request->add($citizenId);
            $selfAdvets->store($req);       //<--------------- Model function to store 
            return responseMsgs(true, "Successfully Submitted the application", "", "040101", "1.0", "260ms", 'POST', $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "040101", "1.0", "260ms", 'POST', $req->deviceId ?? "");
        }
    }
}
