<?php

namespace App\Repositories\SelfAdvets;

use App\Models\Advertisements\AdvActiveSelfadvertisement;
use App\Repositories\SelfAdvets\iSelfAdvetRepo;
use Carbon\Carbon;
use Exception;

/**
 * | Repository for the Self Advertisements
 * | Created On-15-12-2022 
 * | Created By-Anshu Kumar
 */

class SelfAdvetRepo implements iSelfAdvetRepo
{
    private $_todayDate;
    public function __construct()
    {
        $this->_todayDate = Carbon::now();
    }
    /**
     * | Store Self Advets in DB
     */
    public function store($req)
    {
        try {
            $selfAdvets = new AdvActiveSelfadvertisement();
            $selfAdvets->ulb_id = authUser()->ulb_id;
            $selfAdvets->citizen_id = authUser()->id;
            $selfAdvets->application_date = $this->_todayDate->format('Y-m-d');
            $selfAdvets->applicant = $req->applicantName;
            $selfAdvets->license_year = $req->licenseYear;
            $selfAdvets->father = $req->fatherName;
            $selfAdvets->email = $req->email;
            $selfAdvets->residence_address = $req->residenceAddress;
            $selfAdvets->ward_no = $req->wardNo;
            $selfAdvets->permanent_address = $req->permanentAddress;
            $selfAdvets->ward_no1 = $req->wardNo1;
            $selfAdvets->entity_name = $req->entityName;
            $selfAdvets->entity_address = $req->entityAddress;
            $selfAdvets->entity_ward = $req->entityWard;
            $selfAdvets->mobile_no = $req->mobileNo;
            $selfAdvets->aadhar_no = $req->aadharNo;
            $selfAdvets->trade_license_no = $req->tradeLicenseNo;
            $selfAdvets->holding_no = $req->holdingNo;
            $selfAdvets->gst_no = $req->gstNo;
            $selfAdvets->longitude = $req->longitude;
            $selfAdvets->latitude = $req->latitude;
            $selfAdvets->display_area = $req->displayArea;
            $selfAdvets->display_type = $req->displayType;
            $selfAdvets->installation_location = $req->installationLocation;
            $selfAdvets->brand_display_name = $req->brandDisplayName;
            $selfAdvets->zone_id = $req->zoneId;
            $selfAdvets->save();
            return responseMsgs(true, "Successfully Submitted the application", "");
        } catch (Exception $e) {
            return $e;
        }
    }
}
