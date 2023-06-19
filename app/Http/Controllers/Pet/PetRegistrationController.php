<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pet\PetRegistrationReq;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Http\Request;

/**
 * | Created On-02-01-20222 
 * | Created By- Sam Kerketta
 * | Pet Registration Operations
 */

class PetRegistrationController extends Controller
{
    /**
     * | Class constructer 
     */
    public function __construct()
    {
    }


    /**
     * | Apply for the pet Registration
     * | Save form data 
     * | @param req
        | Serial No : 0
        | Under Construction
     */
    public function applyPetRegistration(PetRegistrationReq $req)
    {
        try {
            return $req->applicantName;
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", ".ms", "POST", $req->deviceId);
        }
    }
}
