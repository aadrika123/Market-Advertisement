<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agency\StoreRequest;
use App\Models\Advertisements\AdvActiveAgency;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * | Created On-02-01-20222 
 * | Created By-Anshu Kumar
 * | Agency Operations
 */
class AgencyController extends Controller
{
    /**
     * | Store 
     * | @param StoreRequest Request
     */
    public function store(StoreRequest $req)
    {
        try {
            $agency = new AdvActiveAgency();
            $citizenId = ['citizenId' => authUser()->id];
            $req->request->add($citizenId);
            DB::beginTransaction();
            $applicationNo = $agency->store($req);       //<--------------- Model function to store 
            DB::commit();
            return responseMsgs(
                true,
                "Successfully Submitted the application !!",
                [
                    'status' => true,
                    'ApplicationNo' => $applicationNo
                ],
                "040501",
                "1.0",
                "",
                'POST',
                $req->deviceId ?? ""
            );
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(
                true,
                $e->getMessage(),
                "",
                "040501",
                "1.0",
                "",
                "POST",
                $req->deviceId ?? ""
            );
        }
    }
}
