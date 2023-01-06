<?php

namespace App\Models\Advertisements;

use App\MicroServices\DocumentUpload;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class AdvActivePrivateland extends Model
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
            'mobile_no' => $req->mobileNo,
            'aadhar_no' => $req->aadharNo,
            'license_from' => $req->licenseFrom,
            'license_to' => $req->licenseTo,
            'holding_no' => $req->holdingNo,
            'trade_license_no' => $req->tradeLicenseNo,
            'gst_no' => $req->gstNo,
            'entity_name' => $req->entityName,
            'entity_address' => $req->entityAddress,
            'entity_ward_id' => $req->entityWardId,
            'brand_display_name' => $req->brandDisplayName,
            'brand_display_address' => $req->brandDisplayAddress,
            'display_area' => $req->displayArea,
            'display_type' => $req->displayType,
            'no_of_hoardings' => $req->noOfHoardings,
            'longitude' => $req->longitude,
            'latitude' => $req->latitude,
            'installation_location' => $req->installationLocation,
            'citizen_id' => $req->citizenId,
            'ulb_id' => $req->ulbId
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
        $mRelativePath = Config::get('constants.LAND_ADVET.RELATIVE_PATH');
        $mDocRelPathReq = ['doc_relative_path' => $mRelativePath];
        $mClientIpAddress = ['ip_address' => getClientIpAddress()];
        $applicationNo = ['application_no' => "LAND-" . random_int(100000, 999999)];
        $metaReqs = array_merge($metaReqs, $applicationNo, $mDocRelPathReq, $mClientIpAddress);     // Final Merged Meta Requests
        $metaReqs = $this->uploadDocument($req, $metaReqs);             // Current Objection function to Upload Document
        return AdvActivePrivateland::create($metaReqs)->application_no;
    }


    /**
     * | Document Upload(1.1)
     * | @param Client User Requested Data
     * | @param metaReqs More Added Filtered Data
     */
    public function uploadDocument($req, $metaReqs)
    {
        $mDocUpload = new DocumentUpload();
        $mRelativePath = Config::get('constants.LAND_ADVET.RELATIVE_PATH');
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
        // Gps Document
        if ($req->gpsDoc) {
            $mRefDocName = Config::get('constants.GPS_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->gpsDoc, $mRelativePath);          // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['gps_path' => $docName]);
        }
        // Holding Document
        if ($req->holdingDoc) {
            $mRefDocName = Config::get('constants.HOLDING_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->holdingDoc, $mRelativePath);          // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['holding_path' => $docName]);
        }
        // GST Document
        if ($req->gstDoc) {
            $mRefDocName = Config::get('constants.GST_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->gstDoc, $mRelativePath);           // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['gst_path' => $docName]);
        }
        // Brand Display Path
        if ($req->brandDisplayDoc) {
            $mRefDocName = Config::get('constants.BRAND_DISPLAY_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->brandDisplayDoc, $mRelativePath);           // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['brand_display_path' => $docName]);
        }

        return $metaReqs;
    }
}
