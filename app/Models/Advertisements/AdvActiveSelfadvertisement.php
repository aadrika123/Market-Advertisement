<?php

namespace App\Models\Advertisements;

use App\MicroServices\DocumentUpload;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
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
        $mClientIpAddress = ['ip_address' => getClientIpAddress()];
        $mApplicationNo = ['application_no' => 'SELF-' . random_int(100000, 999999)];              // Generate Application No
        $mDocuments = $req->documents;

        $metaReqs = array_merge($this->metaReqs($req), $mClientIpAddress, $mApplicationNo);  // Add Relative Path as Request and Client Ip Address
        $tempId = AdvActiveSelfadvertisement::create($metaReqs)->id;
        $this->uploadDocument($tempId, $mDocuments);

        return $mApplicationNo;
    }

    /**
     * | Document Upload (1.1)
     * | @param tempId Temprory Id
     * | @param documents Uploading Documents
     * */
    public function uploadDocument($tempId, $documents)
    {
        $mAdvDocument = new AdvActiveSelfadvetdocument();
        $mDocService = new DocumentUpload;
        $mRelativePath = Config::get('constants.SELF_ADVET.RELATIVE_PATH');

        collect($documents)->map(function ($document) use ($mAdvDocument, $tempId, $mDocService, $mRelativePath) {
            $mDocumentId = $document['id'];
            $mDocRelativeName = $document['relativeName'];
            $mImage = $document['image'];
            $mDocName = $mDocService->upload($mDocRelativeName, $mImage, $mRelativePath);

            $docUploadReqs = [
                'tempId' => $tempId,
                'docTypeCode' => 'Test-Code',
                'documentId' => $mDocumentId,
                'relativePath' => $mRelativePath,
                'docName' => $mDocName
            ];
            $docUploadReqs = new Request($docUploadReqs);

            $mAdvDocument->store($docUploadReqs);
        });
    }
}
