<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;use Illuminate\Support\Facades\Config;
use App\MicroServices\DocumentUpload;
use Illuminate\Support\Facades\DB;
use App\Traits\WorkflowTrait;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdvActiveAgencyLicense extends Model
{
    use HasFactory;
    
    use WorkflowTrait;
    protected $guarded = [];
    protected $_applicationDate;
    protected $_workflowId;

    // Initializing construction
    public function __construct()
    {
        $this->_applicationDate = Carbon::now()->format('Y-m-d');
        $this->_workflowId=Config::get('workflow-constants.AGENCY_HORDING');
    }


    public function licenceMetaReqs($req)
    {
        $metaReqs = [            
            // 'district' => $req->district,
            // 'city' => $req->city,
            // 'ward_id' => $req->wardId,
            // 'permit_no' => $req->permitNo,
            // 'road_street_address' => $req->roadStreetAddress,
            // 'date_granted' => $req->dateGranted,
            // 'permit_date_issue' => $req->permitDateIssue,
            // 'permit_expired_issue' => $req->permitExpiredIssue,
            // 'application_no' => $req->applicationNo,
            // 'account_no' => $req->accountNo,
            // 'bank_name' => $req->bankName,
            // 'ifsc_code' => $req->ifscCode,
            // 'total_charge' => $req->totalCharge,
            // 'applicant_name' => $req->applicantName,
            // 'director_name' => $req->directorName,
            // 'registration_no' => $req->registrationNo,
            // 'omd_id' => $req->omdId,
            // 'applicant_email' => $req->applicantEmail,
            // 'applicant_city' => $req->applicantCity,
            // 'applicant_state' => $req->applicantState,
            // 'applicant_mobile_no' => $req->applicantMobileNo,
            // 'applicant_permanent_address' => $req->applicantPermanentAddress,
            // 'applicant_permanent_city' => $req->applicantPermanentCity,
            // 'applicant_permanent_state' => $req->applicantPermanentState,
            // 'applicant_pincode' => $req->applicantPincode,
            // 'property_type' => $req->propertyType,
            // 'property_owner_name' => $req->propertyOwnerName,
            // 'property_owner_address' => $req->propertyOwnerAddress,
            // 'property_owner_city' => $req->propertyOwnerCity,
            // 'property_owner_pincode' => $req->propertyOwnerPincode,
            // 'property_owner_mobile_no' => $req->propertyOwnerMobileNo,
            // 'display_area' => $req->displayArea,
            // 'display_location' => $req->displayLocation,
            // 'display_street' => $req->displayStreet,
            // 'display_land_mark' => $req->displayLandMark,
            // 'heigth' => $req->heigth,
            // 'length' => $req->length,
            // 'size' => $req->size,
            // 'material' => $req->material,
            // 'illumination' => $req->illumination,
            // 'indicate_facing' => $req->indicateFacing,
            // 'user_id' => $req->userId,


            
            'zone_id' => $req->zoneId,
            'license_year' => $req->licenseYear,  
            'typology' => $req->HordingType,               // Hording Type is Convert Into typology
            'display_location' => $req->displayLocation, 
            'width' => $req->width,
            'length' => $req->length,  
            'display_area' => $req->displayArea,
            'longitude' => $req->longitude,   
            'latitude' => $req->latitude,
            'material' => $req->material,
            'illumination' => $req->illumination,
            'indicate_facing' => $req->indicateFacing,
            'property_type' => $req->propertyType,
            'display_land_mark' => $req->displayLandMark,
            'property_owner_name' => $req->propertyOwnerName,
            'property_owner_address' => $req->propertyOwnerAddress,
            'property_owner_city' => $req->propertyOwnerCity,
            'property_owner_whatsapp_no' => $req->propertyOwnerPincode,
            'property_owner_mobile_no' => $req->propertyOwnerMobileNo,
            'user_id' => $req->userId,
            

        ];
        return $metaReqs;
    } public function hordingRenewMetaReqs($req)
    {
        $metaReqs = [      
            'zone_id' => $req->zoneId,
            // 'license_no' => $req->licenseNo,
            'license_year' => $req->licenseYear,  
            'typology' => $req->HordingType,               // Hording Type is Convert Into typology
            'display_location' => $req->displayLocation, 
            'width' => $req->width,
            'length' => $req->length,  
            'display_area' => $req->displayArea,
            'longitude' => $req->longitude,   
            'latitude' => $req->latitude,
            'material' => $req->material,
            'illumination' => $req->illumination,
            'indicate_facing' => $req->indicateFacing,
            'property_type' => $req->propertyType,
            'display_land_mark' => $req->displayLandMark,
            'property_owner_name' => $req->propertyOwnerName,
            'property_owner_address' => $req->propertyOwnerAddress,
            'property_owner_city' => $req->propertyOwnerCity,
            'property_owner_whatsapp_no' => $req->propertyOwnerPincode,
            'property_owner_mobile_no' => $req->propertyOwnerMobileNo,
            'user_id' => $req->userId,
        ];
        return $metaReqs;
    }

    /**
     * | Store function to Hording apply
     * | @param request 
     */
    public function addNewLicense($req)
    {
        // Variable Initializing
        $bearerToken = $req->bearerToken();
        $LicencesMetaReqs = $this->licenceMetaReqs($req);
        $workflowId = $this->_workflowId;
        $ulbWorkflows = $this->getUlbWorkflowId($bearerToken, $req->ulbId, $workflowId);        // Workflow Trait Function
        $ipAddress = getClientIpAddress();
        $mLecenseNo = ['license_no' => 'LICENSE-' . random_int(100000, 999999)];                  // Generate Lecence No
        $ulbWorkflowReqs = [                                                                           // Workflow Meta Requests
            'workflow_id' => $ulbWorkflows['id'],
            'initiator_role_id' => $ulbWorkflows['initiator_role_id'],
            'last_role_id' => $ulbWorkflows['initiator_role_id'],
            'current_role_id' => $ulbWorkflows['initiator_role_id'],
            'finisher_role_id' => $ulbWorkflows['finisher_role_id'],
        ];

        // $LicencesMetaReqs=$this->uploadLicenseDocument($req,$LicencesMetaReqs);

        $LicencesMetaReqs = array_merge(
            [
                'ulb_id' => $req->ulbId,
                'citizen_id' => $req->citizenId,
                'application_date' => $this->_applicationDate,
                'ip_address' => $ipAddress
            ],
            $this->licenceMetaReqs($req),
            $mLecenseNo,
            $ulbWorkflowReqs
        );


        $licenceId=AdvActiveAgencyLicense::create($LicencesMetaReqs)->id;

        $mDocuments = $req->documents;
        $this->uploadDocument($licenceId, $mDocuments);

        return $mLecenseNo['license_no'];
    }


    
    /**
     * | Store function to Licence apply
     * | @param request 
     */
    public function renewalHording($req)
    {
        // Variable Initializing
        $bearerToken = $req->bearerToken();
        $LicencesMetaReqs = $this->hordingRenewMetaReqs($req);
        $workflowId = $this->_workflowId;
        $ulbWorkflows = $this->getUlbWorkflowId($bearerToken, $req->ulbId, $workflowId);        // Workflow Trait Function
        $ipAddress = getClientIpAddress();
        $mRenewNo = ['renew_no' => 'HORDING/REN-' . random_int(100000, 999999)];                  // Generate Lecence No
        $details=AdvAgency::find($req->applicationId);                              // Find Previous Application No
        $mLicenseNo=['license_no'=>$details->license_no];
        $ulbWorkflowReqs = [                                                                           // Workflow Meta Requests
            'workflow_id' => $ulbWorkflows['id'],
            'initiator_role_id' => $ulbWorkflows['initiator_role_id'],
            'last_role_id' => $ulbWorkflows['initiator_role_id'],
            'current_role_id' => $ulbWorkflows['initiator_role_id'],
            'finisher_role_id' => $ulbWorkflows['finisher_role_id'],
        ];

        // $LicencesMetaReqs=$this->uploadLicenseDocument($req,$LicencesMetaReqs);

        $LicencesMetaReqs = array_merge(
            [
                'ulb_id' => $req->ulbId,
                'citizen_id' => $req->citizenId,
                'application_date' => $this->_applicationDate,
                'ip_address' => $ipAddress
            ],
            $this->hordingRenewMetaReqs($req),
            $mRenewNo,
            $mLicenseNo,
            $ulbWorkflowReqs
        );


        $licenceId=AdvActiveAgencyLicense::create($LicencesMetaReqs)->id;

        $mDocuments = $req->documents;
        $this->uploadDocument($licenceId, $mDocuments);

        return $mRenewNo['renew_no'];
    }

        /**
     * | Document Upload(1.1)
     * | @param Client User Requested Data
     * | @param metaReqs More Added Filtered Data
     */
    // public function uploadLicenseDocument_old($req, $metaReqs)
    // {
    //     $mDocUpload = new DocumentUpload();
    //     $mRelativePath = Config::get('constants.VEHICLE_ADVET.RELATIVE_PATH');
    //     if($req->citizenId){
    //         $mDocSuffix = $this->_applicationDate . '-' . $req->citizenId;
    //     }else{
    //         $mDocSuffix = $this->_applicationDate . '-' . $req->userId;
    //     }

    //     // Document Upload

    //     // Director Information
    //     if ($req->directorInformation) {
    //         $mRefDocName = Config::get('constants.AADHAR_RELATIVE_NAME') . '-' . $mDocSuffix;
    //         $docName = $mDocUpload->upload($mRefDocName, $req->directorInformation, $mRelativePath);          // Micro Service for Uploading Document
    //         $metaReqs = array_merge($metaReqs, ['deirctor_information_path' => $docName]);
    //     }
    //     // Building Property Tax
    //     if ($req->buildingPropertyTax) {
    //         $mRefDocName = Config::get('constants.TRADE_RELATIVE_NAME') . '-' . $mDocSuffix;
    //         $docName = $mDocUpload->upload($mRefDocName, $req->buildingPropertyTax, $mRelativePath);          // Micro Service for Uploading Document
    //         $metaReqs = array_merge($metaReqs, ['building_property_path' => $docName]);
    //     }
    //     // Pan No Document
    //     if ($req->panNo) {
    //         $mRefDocName = Config::get('constants.VEHICLE_RELATIVE_NAME') . '-' . $mDocSuffix;
    //         $docName = $mDocUpload->upload($mRefDocName, $req->panNo, $mRelativePath);          // Micro Service for Uploading Document
    //         $metaReqs = array_merge($metaReqs, ['pan_no_path' => $docName]);
    //     }
    //     // Service Tax No Document
    //     if ($req->serviceTaxNo) {
    //         $mRefDocName = Config::get('constants.OWNER_BOOK_RELATIVE_NAME') . '-' . $mDocSuffix;
    //         $docName = $mDocUpload->upload($mRefDocName, $req->serviceTaxNo, $mRelativePath);          // Micro Service for Uploading Document
    //         $metaReqs = array_merge($metaReqs, ['service_tax_no_path' => $docName]);
    //     }
    //     // Certificate Structural Engineer Ownership Details Document
    //     if ($req->certificateStructuralEngineerOwnershipDetails) {
    //         $mRefDocName = Config::get('constants.DRIVING_LICENSE_RELATIVE_NAME') . '-' . $mDocSuffix;
    //         $docName = $mDocUpload->upload($mRefDocName, $req->certificateStructuralEngineerOwnershipDetails, $mRelativePath);          // Micro Service for Uploading Document
    //         $metaReqs = array_merge($metaReqs, ['certificateS_structural_engineer_wnership_details_path' => $docName]);
    //     }
    //     // Aggrement Building And Agency Document
    //     if ($req->aggrementBuildingAndAgency) {
    //         $mRefDocName = Config::get('constants.INSURANCE_RELATIVE_NAME') . '-' . $mDocSuffix;
    //         $docName = $mDocUpload->upload($mRefDocName, $req->aggrementBuildingAndAgency, $mRelativePath);           // Micro Service for Uploading Document
    //         $metaReqs = array_merge($metaReqs, ['aggrement_building_and_agency_path' => $docName]);
    //     }
    //     // Site Photograph Document
    //     if ($req->sitePhotograph) {
    //         $mRefDocName = Config::get('constants.GST_RELATIVE_NAME') . '-' . $mDocSuffix;
    //         $docName = $mDocUpload->upload($mRefDocName, $req->sitePhotograph, $mRelativePath);           // Micro Service for Uploading Document
    //         $metaReqs = array_merge($metaReqs, ['site_photograph_path' => $docName]);
    //     }
    //     // Sketch Plan Of Site Document
    //     if ($req->sketchPlanOfSite) {
    //         $mRefDocName = Config::get('constants.GST_RELATIVE_NAME') . '-' . $mDocSuffix;
    //         $docName = $mDocUpload->upload($mRefDocName, $req->sketchPlanOfSite, $mRelativePath);           // Micro Service for Uploading Document
    //         $metaReqs = array_merge($metaReqs, ['sketch_plan_of_site_path' => $docName]);
    //     }
    //     // Pending Dues Document
    //     if ($req->pendingDues) {
    //         $mRefDocName = Config::get('constants.GST_RELATIVE_NAME') . '-' . $mDocSuffix;
    //         $docName = $mDocUpload->upload($mRefDocName, $req->pendingDues, $mRelativePath);           // Micro Service for Uploading Document
    //         $metaReqs = array_merge($metaReqs, ['pending_dues_path' => $docName]);
    //     }
    //     // Architectural Drawings Document
    //     if ($req->architecturalDrawings) {
    //         $mRefDocName = Config::get('constants.GST_RELATIVE_NAME') . '-' . $mDocSuffix;
    //         $docName = $mDocUpload->upload($mRefDocName, $req->architecturalDrawings, $mRelativePath);           // Micro Service for Uploading Document
    //         $metaReqs = array_merge($metaReqs, ['architectural_drawings_path' => $docName]);
    //     }
    //     // coordinate Of OMD With GPS Locatoion Drawings Document
    //     if ($req->coordinateOfOmdWithGpsLocatoion) {
    //         $mRefDocName = Config::get('constants.GST_RELATIVE_NAME') . '-' . $mDocSuffix;
    //         $docName = $mDocUpload->upload($mRefDocName, $req->coordinateOfOmdWithGpsLocatoion, $mRelativePath);           // Micro Service for Uploading Document
    //         $metaReqs = array_merge($metaReqs, ['coordinate_of_omd_with_gps_locatoion_path' => $docName]);
    //     }

    //     return $metaReqs;
    // }


        /**
     * | Document Upload (1.1)
     * | @param tempId Temprory Id
     * | @param documents Uploading Documents
     * */
    // public function uploadDocument($tempId, $documents)
    // {
    //     $mAdvDocument = new AdvActiveSelfadvetdocument();
    //     $mDocService = new DocumentUpload;
    //     $mRelativePath = Config::get('constants.AGENCY_ADVET.RELATIVE_PATH');
    //     $workflowId = Config::get('workflow-constants.AGENCY_HORDING_WORKFLOWS');

    //     collect($documents)->map(function ($document) use ($mAdvDocument, $tempId, $mDocService, $mRelativePath,$workflowId) {
    //         $mDocumentId = $document['id'];
    //         $mDocRelativeName = $document['relativeName'];
    //         $mImage = $document['image'];
    //         $mDocName = $mDocService->upload($mDocRelativeName, $mImage, $mRelativePath);

    //         $docUploadReqs = [
    //             'tempId' => $tempId,
    //             'docTypeCode' => 'Test-Code',
    //             'documentId' => $mDocumentId,
    //             'relativePath' => $mRelativePath,
    //             'docName' => $mDocName,
    //             'workflowId' => $workflowId
    //         ];
    //         $docUploadReqs = new Request($docUploadReqs);

    //         $mAdvDocument->store($docUploadReqs);
    //     });
    // }

        /** 
     * upload Document
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadDocument($tempId, $documents)
    {
        collect($documents)->map(function ($doc) use ($tempId) {
            $metaReqs = array();
            $docUpload = new DocumentUpload;
            $mWfActiveDocument = new WfActiveDocument();
            $mAdvActiveAgencyLicense = new AdvActiveAgencyLicense();
            $relativePath = Config::get('constants.AGENCY_ADVET.RELATIVE_PATH');
            $getApplicationDtls = $mAdvActiveAgencyLicense->getAgencyLicenseDetails($tempId);
            $refImageName = $doc['docCode'];
            $refImageName = $getApplicationDtls->id . '-' . $refImageName;
            $documentImg = $doc['image'];
            $imageName = $docUpload->upload($refImageName, $documentImg, $relativePath);

            $metaReqs['moduleId'] = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
            $metaReqs['activeId'] = $getApplicationDtls->id;
            $metaReqs['workflowId'] = $getApplicationDtls->workflow_id;
            $metaReqs['ulbId'] = $getApplicationDtls->ulb_id;
            $metaReqs['relativePath'] = $relativePath;
            $metaReqs['document'] = $imageName;
            $metaReqs['docCode'] = $doc['docCode'];
            $metaReqs['ownerDtlId'] = $doc['ownerDtlId'];
            $a = new Request($metaReqs);
            $mWfActiveDocument->postDocuments($a);
        });
    }


    public function getAgencyLicenseDetails($appId)
    {
        return AdvActiveAgencyLicense::select('*')
            ->where('id', $appId)
            ->first();
    }



    
    /**
     * | Get Application License Details by id
     * | @param Agencies License id
     */
    public function getLicenseDetailsById($id,$type)
    {
        $details = array();
        if ($type == "Active" || $type==NULL) {
            $details = DB::table('adv_active_agency_licenses')
                ->select(
                    'adv_active_agency_licenses.*',
                    'u.ulb_name',
                    // 'ht.descriptions',
                // 'p.string_parameter as m_license_year',
                // 'w.ward_name as ward_no',
                // 'pw.ward_name as permanent_ward_no',
                // 'ew.ward_name as entity_ward_no',
                // 'dp.string_parameter as m_display_type',
                // 'il.string_parameter as m_installation_location',
                // 'r.role_name as m_current_role'
                )
                ->where('adv_active_agency_licenses.id', $id)
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_active_agency_licenses.ulb_id')
                // ->leftJoin('adv_typology_mstrs as ht', 'ht.id', '=', 'adv_active_agency_licenses.typology::int')
                    // ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'adv_active_agencies.license_year')
                    // ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_active_agencies.ward_id')
                    // ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_active_agencies.permanent_ward_id')
                    // ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'adv_active_agencies.entity_ward_id')
                    // ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', 'adv_active_agencies.display_type')
                    // ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', 'adv_active_agencies.installation_location')
                    // ->leftJoin('wf_roles as r', 'r.id', '=', 'adv_active_agencies.current_role_id')
                ->first();
        }elseif ($type == "Reject") {
            $details = DB::table('adv_active_agency_licenses')
                ->select(
                    'adv_active_agency_licenses.*',
                    'u.ulb_name',
                // 'p.string_parameter as m_license_year',
                // 'w.ward_name as ward_no',
                // 'pw.ward_name as permanent_ward_no',
                // 'ew.ward_name as entity_ward_no',
                // 'dp.string_parameter as m_display_type',
                // 'il.string_parameter as m_installation_location',
                // 'r.role_name as m_current_role'
                )
                ->where('adv_active_agency_licenses.id', $id)
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_active_agency_licenses.ulb_id')
                    // ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'adv_active_agencies.license_year')
                    // ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_active_agencies.ward_id')
                    // ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_active_agencies.permanent_ward_id')
                    // ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'adv_active_agencies.entity_ward_id')
                    // ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', 'adv_active_agencies.display_type')
                    // ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', 'adv_active_agencies.installation_location')
                    // ->leftJoin('wf_roles as r', 'r.id', '=', 'adv_active_agencies.current_role_id')
                ->first();
        }elseif ($type == "Approve") {
            $details = DB::table('adv_agency_licenses')
                ->select(
                    'adv_agency_licenses.*',
                    'u.ulb_name',
                // 'p.string_parameter as m_license_year',
                // 'w.ward_name as ward_no',
                // 'pw.ward_name as permanent_ward_no',
                // 'ew.ward_name as entity_ward_no',
                // 'dp.string_parameter as m_display_type',
                // 'il.string_parameter as m_installation_location',
                // 'r.role_name as m_current_role'
                )
                ->where('adv_agency_licenses.id', $id)
                ->leftJoin('ulb_masters as u', 'u.id', '=', 'adv_agency_licenses.ulb_id')
                    // ->leftJoin('ref_adv_paramstrings as p', 'p.id', '=', 'adv_agency_licenses.license_year')
                    // ->leftJoin('ulb_ward_masters as w', 'w.id', '=', 'adv_agency_licenses.ward_id')
                    // ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'adv_agency_licenses.permanent_ward_id')
                    // ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'adv_agency_licenses.entity_ward_id')
                    // ->leftJoin('ref_adv_paramstrings as dp', 'dp.id', '=', 'adv_agency_licenses.display_type')
                    // ->leftJoin('ref_adv_paramstrings as il', 'il.id', '=', 'adv_agency_licenses.installation_location')
                    // ->leftJoin('wf_roles as r', 'r.id', '=', 'adv_agency_licenses.current_role_id')
                ->first();
        }

        $details = json_decode(json_encode($details), true);            // Convert Std Class to Array
        
        return $details;
    }


        /**
     * | Get Application Inbox List by Role Ids
     * | @param roleIds $roleIds
     */
    public function listLicenseInbox($roleIds)
    {
        $inbox = DB::table('adv_active_agency_licenses')
            ->select(
                'id',
                'application_no',
                'license_no',
                'application_date',
                'license_no',
                'bank_name',
                'account_no',
                'ifsc_code',
                'total_charge'
            )
            ->orderByDesc('id')
            ->whereIn('current_role_id', $roleIds)
            ->get();
        return $inbox;
    }

    
    /**
     * | Get Citizen Applied applications
     * | @param citizenId
     */
    public function listLicenseAppliedApplications($citizenId)
    {
        return AdvActiveAgencyLicense::where('citizen_id', $citizenId)
            ->select(
                'id',
                'application_no',
                'license_no',
                'application_date',
                'license_no',
                'bank_name',
                'account_no',
                'ifsc_code',
                'total_charge',
                'doc_upload_status',
            )
            ->orderByDesc('id')
            ->get();
    }

    
    /**
     * | Get Application Outbox List by Role Ids
     */
    public function listLicenseOutbox($roleIds)
    {
        $outbox = DB::table('adv_active_agency_licenses')
            ->select(
                'id',
                'application_no',
                'license_no',
                'application_date',
                'license_no',
                'bank_name',
                'account_no',
                'ifsc_code',
                'total_charge'
            )
            ->orderByDesc('id')
            ->whereNotIn('current_role_id', $roleIds)
            ->get();
        return $outbox;
    }

    public function viewUploadedDocuments($id,$workflowId){
        // $documents = DB::table('adv_active_selfadvetdocuments')
        //     ->select(
        //         'adv_active_selfadvetdocuments.*',
        //         'd.document_name',
        //         DB::raw("CONCAT(adv_active_selfadvetdocuments.relative_path,'/',adv_active_selfadvetdocuments.doc_name) as document_path")
        //     )
        //     ->leftJoin('ref_adv_document_mstrs as d', 'd.id', '=', 'adv_active_selfadvetdocuments.document_id')
        //     ->where(array('adv_active_selfadvetdocuments.temp_id'=> $id,'adv_active_selfadvetdocuments.workflow_id'=>$workflowId))
        //     ->get();
        // $details['documents'] = remove_null($documents->toArray());
        // return $details;
        $documents = DB::table('adv_active_selfadvetdocuments')
        ->select(
            'adv_active_selfadvetdocuments.*',
            'd.document_name as doc_type',
            DB::raw("CONCAT(adv_active_selfadvetdocuments.relative_path,'/',adv_active_selfadvetdocuments.doc_name) as doc_path")
        )
        ->leftJoin('ref_adv_document_mstrs as d', 'd.id', '=', 'adv_active_selfadvetdocuments.document_id')
        ->where(array('adv_active_selfadvetdocuments.temp_id'=> $id,'adv_active_selfadvetdocuments.workflow_id'=>$workflowId))
        ->get();
    $details['documents'] = remove_null($documents->toArray());
    return $details;
    }

    
    
      /**
     * | Get Jsk Applied License  applications
     * | @param userId
     */
    public function getJskLicenseApplications($userId)
    {
        return AdvActiveAgencyLicense::where('user_id', $userId)
            ->select(
                'id',
                'application_no',
                'application_date'
            )
            ->orderByDesc('id')
            ->get();
    }

    
    public function getAgencyLicenseNo($appId)
    {
        return AdvActiveAgencyLicense::select('*')
            ->where('id', $appId)
            ->first();
    }


    
    public function getAgencyLicenseList($ulbId)
    {
        return AdvActiveAgencyLicense::select('*')
            ->where('adv_active_agency_licenses.ulb_id', $ulbId);
    }

     /**
     * | Reupload Documents
     */
    public function reuploadDocument($req){
        $docUpload = new DocumentUpload;
        $docDetails=WfActiveDocument::find($req->id);
        $relativePath = Config::get('constants.AGENCY_ADVET.RELATIVE_PATH');

        $refImageName = $docDetails['doc_code'];
        $refImageName = $docDetails['active_id'] . '-' . $refImageName;
        $documentImg = $req->image;
        $imageName = $docUpload->upload($refImageName, $documentImg, $relativePath);

        $metaReqs['moduleId'] = Config::get('workflow-constants.ADVERTISMENT_MODULE_ID');
        $metaReqs['activeId'] = $docDetails['active_id'];
        $metaReqs['workflowId'] = $docDetails['workflow_id'];
        $metaReqs['ulbId'] = $docDetails['ulb_id'];
        $metaReqs['relativePath'] = $relativePath;
        $metaReqs['document'] = $imageName;
        $metaReqs['docCode'] = $docDetails['doc_code'];
        $metaReqs['ownerDtlId'] = $docDetails['ownerDtlId'];
        $a = new Request($metaReqs);
        $mWfActiveDocument=new WfActiveDocument();
        $mWfActiveDocument->postDocuments($a);
        $docDetails->current_status='0';
        $docDetails->save();
        return $docDetails['active_id'];
    }



}
