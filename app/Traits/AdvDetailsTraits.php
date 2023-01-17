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
            ['displayString' => 'M Installation Location', 'key' => 'mInstallationLocation', 'value' => $data['m_installation_location']],
            ['displayString' => 'M Current Role', 'key' => 'mCurrentRole', 'value' => $data['m_current_role']]
         ]);
    }


      /**
     * | Generate Owner Details
     */
    public function generateUploadDocDetails($documentUploads)
    {
        return collect($documentUploads)->map(function ($documentUpload, $key) {
            return [
                $key + 1,
                $documentUpload['document_name'],
                $documentUpload['verified_by'],
                $documentUpload['verified_on'],
                $documentUpload['document_path']
            ];
        });
    }

   
}
