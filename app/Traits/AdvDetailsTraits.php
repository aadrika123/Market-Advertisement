<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;

/**
 * | Trait Created for Gettting Dynamic Saf Details
 */
trait AdvDetailsTraits
{
    /**
     * | Get Basic Details
     */
    public function generateBasicDetails($data)
    {
        return new Collection([
            ['displayString' => 'Permanent Ward No', 'key' => 'permanentWardNo', 'value' => $data['permanent_ward_no']],
            ['displayString' => 'Entity Ward No', 'key' => 'entityWardNo', 'value' => $data['entity_ward_no']],
            ['displayString' => 'ULB Name', 'key' => 'ulbName', 'value' => $data['ulb_name']],
            ['displayString' => 'Entity Name', 'key' => 'entityName', 'value' => $data['entity_name']],
            ['displayString' => 'Entity Address', 'key' => 'entityAddress', 'value' => $data['entity_address']],
            ['displayString' => 'Residence Address', 'key' => 'residenceAddress', 'value' => $data['residence_address']],
            ['displayString' => 'Licence Year', 'key' => 'licenceYear', 'value' => $data['license_year']],
            ['displayString' => 'Father', 'key' => 'father', 'value' => $data['father']],
            ['displayString' => 'Email', 'key' => 'email', 'value' => $data['email']],
            ['displayString' => 'Ward ID', 'key' => 'wardId', 'value' => $data['ward_id']],
            ['displayString' => 'Mobile No', 'key' => 'moibileNo', 'value' => $data['mobile_no']],
            ['displayString' => 'Aadhar No', 'key' => 'aadharNo', 'value' => $data['aadhar_no']],
            ['displayString' => 'Trade Licence No', 'key' => 'tradeLicenseNo', 'value' => $data['trade_license_no']],
            ['displayString' => 'Holding No', 'key' => 'holdingNo', 'value' => $data['holding_no']],
            ['displayString' => 'GST No', 'key' => 'gstNo', 'value' => $data['gst_no']],
            ['displayString' => 'Longitude', 'key' => 'longitude', 'value' => $data['longitude']],
            ['displayString' => 'Latitude', 'key' => 'Latitude', 'value' => $data['latitude']],
            ['displayString' => 'Display Area', 'key' => 'displayArea', 'value' => $data['display_area']],
            ['displayString' => 'Brand Display Name', 'key' => 'brandDisplayName', 'value' => $data['brand_display_name']],
            ['displayString' => 'M Display Name', 'key' => 'mDisplayType', 'value' => $data['m_display_type']],
            ['displayString' => 'M Installation Location', 'key' => 'mInstallationLocation', 'value' => $data['m_installation_location']]
            // ['displayString' => 'M Current Role', 'key' => 'mCurrentRole', 'value' => $data['m_current_role']]
        ]);
    }



    /**
     * | Get Card Details
     */
    public function generateCardDetails($data)
    {
        return new Collection([
            ['displayString' => 'Applicant Name', 'key' => 'applicantName', 'value' => $data['applicant']],
            ['displayString' => 'Appication No', 'key' => 'appicationNo', 'value' => $data['application_no']],
            ['displayString' => 'Entity Address', 'key' => 'entityAddress', 'value' => $data['entity_address']],
            ['displayString' => 'Entity Name', 'key' => 'entityName', 'value' => $data['entity_name']],
            ['displayString' => 'Trade Licence No', 'key' => 'tradeLicenseNo', 'value' => $data['trade_license_no']],
            ['displayString' => 'Holding No', 'key' => 'holdingNo', 'value' => $data['holding_no']],
        ]);
    }

    /**
     * | Generate Owner Details
     */
    public function generateUploadDocDetails($documentUploads)
    {
        return collect($documentUploads)->map(function ($documentUpload, $key) {
            return new Collection([
                $key + 1,
                $documentUpload['document_name'],
                $documentUpload['verified_by'],
                $documentUpload['verified_on'],
                $documentUpload['document_path']
            ]);
        });
    }


    /**
     * | Generate License Details
     */
    public function generateLicenseDetails($data)
    {
        return new Collection([
            ['displayString' => 'Appication No', 'key' => 'applicantNo', 'value' => $data->application_no],
            ['displayString' => 'Appication Date', 'key' => 'appicationDate', 'value' => $data->application_date],
            ['displayString' => 'Licence No', 'key' => 'licenseNo', 'value' => $data->license_no],
            ['displayString' => 'Valid From', 'key' => 'validFrom', 'value' => $data->valid_from],
            ['displayString' => 'Valid Upto', 'key' => 'validUpto', 'value' => $data->valid_upto],
            ['displayString' => 'Years', 'key' => 'licenceForYears', 'value' => $data->licence_for_years],
            ['displayString' => 'Firm Name', 'key' => 'firmName', 'value' => $data->firm_name],
            ['displayString' => 'Owner Name', 'key' => 'premisesOwnerName', 'value' => $data->premises_owner_name],
            ['displayString' => 'Address', 'key' => 'address', 'value' => $data->address],
            ['displayString' => 'Landmark', 'key' => 'landmark', 'value' => $data->landmark],
            ['displayString' => 'Pin Code', 'key' => 'pinCOde', 'value' => $data->pin_code],
        ]);
    }


    /**
     * |-----------------------------------------------
     * |================ Bikash Kumar =================
     * |================ 19-01-2023 ===================
     * |================ Movable Vehicles =============
     * |-----------------------------------------------
     * */


    /**
     * | Get Vehicle Basic Details
     */
    public function generateVehicleBasicDetails($data)
    {
        return new Collection([
            ['displayString' => 'Permanent Ward No', 'key' => 'permanentWardNo', 'value' => $data['permanent_ward_no']],
            ['displayString' => 'Entity Ward No', 'key' => 'entityWardNo', 'value' => $data['entity_ward_no']],
            ['displayString' => 'ULB Name', 'key' => 'ulbName', 'value' => $data['ulb_name']],
            ['displayString' => 'Entity Name', 'key' => 'entityName', 'value' => $data['entity_name']],
            ['displayString' => 'Residence Address', 'key' => 'residenceAddress', 'value' => $data['residence_address']],
            ['displayString' => 'Father', 'key' => 'father', 'value' => $data['father']],
            ['displayString' => 'Email', 'key' => 'email', 'value' => $data['email']],
            ['displayString' => 'Ward ID', 'key' => 'wardId', 'value' => $data['ward_id']],
            ['displayString' => 'Mobile No', 'key' => 'moibileNo', 'value' => $data['mobile_no']],
            ['displayString' => 'Aadhar No', 'key' => 'aadharNo', 'value' => $data['aadhar_no']],
            ['displayString' => 'Trade Licence No', 'key' => 'tradeLicenseNo', 'value' => $data['trade_license_no']],
            ['displayString' => 'GST No', 'key' => 'gstNo', 'value' => $data['gst_no']],
            ['displayString' => 'M Display Name', 'key' => 'mDisplayType', 'value' => $data['m_display_type']],
            ['displayString' => 'Vehicle No', 'key' => 'vehicleNo', 'value' => $data['vehicle_no']],
            ['displayString' => 'Vehicle Name', 'key' => 'vehicleName', 'value' => $data['vehicle_name']],
            ['displayString' => 'Front Area', 'key' => 'frontArea', 'value' => $data['front_area']],
            ['displayString' => 'Rear Area', 'key' => 'rearArea', 'value' => $data['rear_area']],
            ['displayString' => 'Side Area', 'key' => 'sideArea', 'value' => $data['side_area']],
            ['displayString' => 'Top Area', 'key' => 'topArea', 'value' => $data['top_area']],
        ]);
    }


    /**
     * | Get Vehicle Card Details
     */
    public function generateVehicleCardDetails($data)
    {
        return new Collection([
            ['displayString' => 'Applicant Name', 'key' => 'applicantName', 'value' => $data['applicant']],
            ['displayString' => 'Appication No', 'key' => 'appicationNo', 'value' => $data['application_no']],
            ['displayString' => 'Entity Name', 'key' => 'entityName', 'value' => $data['entity_name']],
            ['displayString' => 'Trade Licence No', 'key' => 'tradeLicenseNo', 'value' => $data['trade_license_no']],
        ]);
    }



    /**
     * |-----------------------------------------------
     * |================ Bikash Kumar =================
     * |================ 21-01-2023 ===================
     * |================ Agency =======================
     * |-----------------------------------------------
     * */



    public function generateAgencyBasicDetails($data)
    {
        return new Collection([
            ['displayString' => 'Appication No', 'key' => 'appicationNo', 'value' => $data['application_no']],
            ['displayString' => 'Appication Date', 'key' => 'appicationDate', 'value' => $data['application_date']],
            ['displayString' => 'ULB Name', 'key' => 'ulbName', 'value' => $data['ulb_name']],
            ['displayString' => 'Entity Name', 'key' => 'entityName', 'value' => $data['entity_name']],
            ['displayString' => 'Address', 'key' => 'address', 'value' => $data['address']],
            ['displayString' => 'Email', 'key' => 'email', 'value' => $data['email']],
            ['displayString' => 'Mobile No', 'key' => 'moibileNo', 'value' => $data['mobile_no']],
            ['displayString' => 'Fax', 'key' => 'fax', 'value' => $data['fax']],
            ['displayString' => 'GST No', 'key' => 'gstNo', 'value' => $data['gst_no']],
            ['displayString' => 'Pan No', 'key' => 'panNo', 'value' => $data['pan_no']],
            ['displayString' => 'Blacklisted', 'key' => 'blacklisted', 'value' => $data['blacklisted'] == 0 ? "NO" : "YES"],
            ['displayString' => 'Pending Amount', 'key' => 'pendingAmount', 'value' => $data['pending_amount']],
            ['displayString' => 'pending Cour tCase', 'key' => 'pendingCourtCase', 'value' => $data['pending_court_case'] == 0 ? "NO" : "YES"],
        ]);
    }


    /**
     * | Get Agency Card Details
     */
    public function generateAgencyCardDetails($data)
    {
        return new Collection([
            ['displayString' => 'Appication No', 'key' => 'appicationNo', 'value' => $data['application_no']],
            ['displayString' => 'Appication Date', 'key' => 'appicationDate', 'value' => $data['application_date']],
            ['displayString' => 'Entity Name', 'key' => 'entityName', 'value' => $data['entity_name']],
            ['displayString' => 'Pending Amount', 'key' => 'pending_amount', 'value' => $data['pending_amount']],
        ]);
    }
}
