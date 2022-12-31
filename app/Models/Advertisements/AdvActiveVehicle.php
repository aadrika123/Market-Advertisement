<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvActiveVehicle extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * | Meta Data Uses to Store data in DB
     */
    public function metaReqs($req)
    {
        $metaReqs = [
            'applicant' => $req->applicant,
            'father' => $req->father,
            'email' => $req->email,
            'residence_address' => $req->residenceAddress,
            'ward_id' => $req->wardId,
            'permanent_address' => $req->permanentAddress,
            'permanent_ward_id' => $req->permanentWardId,
            'mobile_no' => $req->mobile,
            'aadhar_no' => $req->aadharNo,
            'license_from' => $req->licenseFrom,
            'license_to' => $req->licenseTo,
            'entity_name' => $req->entityName,
            'trade_license_no' => $req->tradeLicenseNo,
            'gst_no' => $req->gstNo,
            'vehicle_no' => $req->vehicleNo,
            'vehicle_type' => $req->vehicleType,
            'brand_display' => $req->brandDisplayed,
            'front_area' => $req->frontArea,
            'rear_area' => $req->rearArea,
            'side_area' => $req->sideArea,
            'top_area' => $req->topArea,
            'display_type' => $req->displayType
        ];
        return $metaReqs;
    }

    /**
     * | Store function to apply 
     * | @param request 
     */
    public function store($req)
    {
        $metaReqs = $this->metaReqs($req);
        $applicationNo = ['application_no' => "VEHICLE-" . $req->applicant];
        $metaReqs = array_merge($metaReqs, $applicationNo);
        return AdvActiveVehicle::create($metaReqs)->application_no;
    }
}
