<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelfAdvets\StoreRequest;
use App\Models\Advertisements\AdvActiveSelfadvertisement;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * | Created On-14-12-2022 
 * | Created By-Anshu Kumar
 * | Created for Operations on Self Advertisements
 * | Workflow ID=129
 * | Ulb Workflow ID=245
 */

class SelfAdvetController extends Controller
{
    /**
     * | Apply for Self Advertisements 
     * | @param StoreRequest 
     */
    public function store(StoreRequest $req)
    {
        try {
            $selfAdvets = new AdvActiveSelfadvertisement();
            $citizenId = ['citizenId' => authUser()->id];
            $req->request->add($citizenId);
            DB::beginTransaction();
            $applicationNo = $selfAdvets->store($req);       //<--------------- Model function to store 
            DB::commit();
            return responseMsgs(
                true,
                "Successfully Submitted the application !!",
                [
                    'status' => true,
                    'ApplicationNo' => $applicationNo
                ],
                "040101",
                "1.0",
                "",
                'POST',
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "040101",
                "1.0",
                "",
                'POST',
                $req->deviceId ?? ""
            );
        }
    }

    /**
     * | Application Update 
     * | @param Request $req
     */
    public function edit(Request $req)
    {
        $documents = collect($req->documents)->first();
        if (empty($documents)) {
            return 'Collection is Empty';
        }
        return 'Not Empty';
    }

    /**
     * | Inbox List
     */
    public function inbox()
    {
    }

    /**
     * | Outbox List
     */
    public function outbox()
    {
    }

    /**
     * | Application Details
     */
    public function details(Request $req)
    {
        // Validation
        $validator = Validator::make($req->all(), [
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return responseMsgs(
                false,
                $validator->errors(),
                "",
                "040105",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        }
        try {
            $selfAdvets = new AdvActiveSelfadvertisement();
            $details = collect($selfAdvets->details($req->id));          // Model function to get Details of the application
            return responseMsgs(
                true,
                "Application Details",
                remove_null($details),
                "040105",
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
                "040105",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        }
    }

    /**
     * | Get Applied Applications by Logged In Citizen
     */
    public function getCitizenApplications(Request $req)
    {
        try {
            $citizenId = authUser()->id;
            $selfAdvets = new AdvActiveSelfadvertisement();
            $applications = $selfAdvets->getCitizenApplications($citizenId);
            return responseMsgs(
                true,
                "Applied Applications",
                remove_null($applications->toArray()),
                "040106",
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
                "040106",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        }
    }
}
