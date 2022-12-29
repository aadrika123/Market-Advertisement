<?php

namespace App\Models\Advertisements;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AdvActiveSelfadvertisement extends Model
{
    //
    protected $guarded = [];

    // helper meta reqs
    public function metaReqs($req)
    {
        return [
            'ulb_id' => $req->ulbId,
            'citizen_id' => $req->citizenId,
            'application_date' => Carbon::now()->format('Y-m-d'),
            'applicant' => $req->applicantName,
            'license_year' => $req->licenseYear,
            'father' => $req->fatherName,
            'email' => $req->email,
            'residence_address' => $req->residenceAddress,
            'ward_id' => $req->wardId,
            'permanent_address' => $req->permanentAddress,
            'permanent_ward_id' => $req->permanentWardId,
            'entity_name' => $req->entityName,
            'entity_address' => $req->entityAddress,
            'entity_ward_id' => $req->entityWardId,
            'mobile_no' => $req->mobileNo,
            'aadhar_no' => $req->aadharNo,
            'trade_license_no' => $req->tradeLicenseNo,
            'holding_no' => $req->holdingNo,
            'gst_no' => $req->gstNo,
            'longitude' => $req->longitude,
            'latitude' => $req->latitude,
            'display_area' => $req->displayArea,
            'display_type' => $req->displayType,
            'installation_location' => $req->installationLocation,
            'brand_display_name' => $req->brandDisplayName
        ];
    }

    // Store Self Advertisements
    public function store($req)
    {
        $metaReqs = $this->metaReqs($req);
        AdvActiveSelfadvertisement::create($metaReqs);
    }
}
