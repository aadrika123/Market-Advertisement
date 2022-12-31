<?php

namespace App\Models\Advertisements;

use App\MicroServices\DocumentUpload;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class AdvActiveVehicle extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $_applicationDate;

    public function __construct()
    {
        $this->_applicationDate = Carbon::now()->format('Y-m-d');
    }

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
            'display_type' => $req->displayType,
            'citizen_id' => $req->citizenId
        ];
        return $metaReqs;
    }

    /**
     * | Store function to apply(1)
     * | @param request 
     */
    public function store($req)
    {
        $metaReqs = $this->metaReqs($req);
        $mRelativePath = Config::get('constants.VEHICLE_ADVET.RELATIVE_PATH');
        $mDocRelPathReq = ['doc_relative_path' => $mRelativePath];
        $mClientIpAddress = ['ip_address' => getClientIpAddress()];
        $applicationNo = ['application_no' => "VEHICLE-" . $req->applicant];
        $metaReqs = array_merge($metaReqs, $applicationNo, $mDocRelPathReq, $mClientIpAddress);     // Final Merged Meta Requests
        $metaReqs = $this->uploadDocument($req, $metaReqs);             // Current Objection function to Upload Document
        return AdvActiveVehicle::create($metaReqs)->application_no;
    }

    /**
     * | Document Upload(1.1)
     */
    public function uploadDocument($req, $metaReqs)
    {
        $mDocUpload = new DocumentUpload();
        $mRelativePath = Config::get('constants.VEHICLE_ADVET.RELATIVE_PATH');
        $mDocSuffix = $this->_applicationDate . '-' . $req->citizenId;

        // Document Upload

        // Aadhar Document
        if ($req->aadharDoc) {
            $mRefDocName = Config::get('constants.AADHAR_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->aadharDoc, $mRelativePath);          // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['aadhar_path' => $docName]);
        }
        // Trade License Path
        if ($req->tradeDoc) {
            $mRefDocName = Config::get('constants.TRADE_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->tradeDoc, $mRelativePath);          // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['trade_license_path' => $docName]);
        }
        // Vehicle Photo Document
        if ($req->vehiclePhotoDoc) {
            $mRefDocName = Config::get('constants.VEHICLE_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->vehiclePhotoDoc, $mRelativePath);          // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['vehicle_photo_path' => $docName]);
        }
        // Owner Book Document
        if ($req->ownerBookDoc) {
            $mRefDocName = Config::get('constants.OWNER_BOOK_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->ownerBookDoc, $mRelativePath);          // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['owner_book_path' => $docName]);
        }
        // Driving License Document
        if ($req->drivingLicenseDoc) {
            $mRefDocName = Config::get('constants.DRIVING_LICENSE_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->drivingLicenseDoc, $mRelativePath);          // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['driving_license_path' => $docName]);
        }
        // Insurance Document
        if ($req->insuranceDoc) {
            $mRefDocName = Config::get('constants.INSURANCE_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->insuranceDoc, $mRelativePath);           // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['insurance_photo_path' => $docName]);
        }
        // GST Document
        if ($req->gstDoc) {
            $mRefDocName = Config::get('constants.GST_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->gstDoc, $mRelativePath);           // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['gst_photo_path' => $docName]);
        }

        return $metaReqs;
    }
}
