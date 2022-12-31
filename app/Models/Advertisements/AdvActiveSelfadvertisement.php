<?php

namespace App\Models\Advertisements;

use App\MicroServices\DocumentUpload;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class AdvActiveSelfadvertisement extends Model
{
    //
    protected $guarded = [];
    protected $_applicationDate;

    public function __construct()
    {
        $this->_applicationDate = Carbon::now()->format('Y-m-d');
    }

    // helper meta reqs
    public function metaReqs($req)
    {
        return [
            'ulb_id' => $req->ulbId,
            'citizen_id' => $req->citizenId,
            'application_date' => $this->_applicationDate,
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

    // Store Self Advertisements(1)
    public function store($req)
    {
        $mRelativePath = Config::get('constants.SELF_ADVET.RELATIVE_PATH');
        $mDocRelPathReq = ['doc_relative_path' => $mRelativePath];
        $metaReqs = array_merge($this->metaReqs($req), $mDocRelPathReq);
        $metaReqs = $this->uploadDocument($req, $metaReqs);
        AdvActiveSelfadvertisement::create($metaReqs);
    }

    /**
     * | Document Upload (1.1)
     * | @param request $req
     * | @param metaReqs more Fileds Required For Meta Reqs
     * */
    public function uploadDocument($req, $metaReqs)
    {
        $mDocUpload = new DocumentUpload();
        $mRelativePath = Config::get('constants.SELF_ADVET.RELATIVE_PATH');
        $mDocSuffix = $this->_applicationDate . '-' . $req->citizenId;
        // Document Upload
        if ($req->aadharDoc) {          // Aadhar Document
            $mRefDocName = Config::get('constants.AADHAR_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->aadharDoc, $mRelativePath);          // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['aadhar_path' => $docName]);
        }

        // Trade License
        if ($req->tradeLicenseDoc) {
            $mRefDocName = Config::get('constants.TRADE_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->tradeLicenseDoc, $mRelativePath);     // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['trade_license_path' => $docName]);
        }

        // Holding No Photo
        if ($req->holdingDoc) {
            $mRefDocName = Config::get('constants.HOLDING_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->holdingDoc, $mRelativePath);         // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['holding_no_path' => $docName]);
        }

        // Gps Photo
        if ($req->gpsDoc) {
            $mRefDocName = Config::get('constants.GPS_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->gpsDoc, $mRelativePath);             // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['gps_path' => $docName]);
        }

        // GST Photo
        if ($req->gstDoc) {
            $mRefDocName = Config::get('constants.GST_RELATIVE_NAME') . '-' . $mDocSuffix;
            $docName = $mDocUpload->upload($mRefDocName, $req->gstDoc, $mRelativePath);             // Micro Service for Uploading Document
            $metaReqs = array_merge($metaReqs, ['gst_path' => $docName]);
        }

        return $metaReqs;
    }
}
