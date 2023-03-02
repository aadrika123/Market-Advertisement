<?php

namespace App\Http\Requests\Agency;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreLicenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            // 'district' => 'required|string',
            // 'city' => 'required|string',
            // 'wardId' => 'required|integer',
            // 'zoneId' => 'required|integer',
            // 'permitNo' => 'required|string',
            // 'roadStreetAddress' => 'required|string',
            // 'dateGranted' => 'required|date',
            // 'permitDateIssue' => 'required|date',
            // 'permitExpiredIssue' => 'required|date',
            // 'applicationNo' => 'required|string',
            // 'accountNo' => 'required|numeric',
            // 'bankName' => 'required|string',
            // 'ifscCode' => 'required|string',
            // 'totalCharge' => 'required|numeric',
            // 'propertyType' => 'required|string',
            // 'propertyOwnerName' => 'required|string',
            // 'propertyOwnerAddress' => 'required|string',
            // 'propertyOwnerCity' => 'required|string',
            // 'propertyOwnerPincode' => 'required|numeric|digits:6',
            // 'propertyOwnerMobileNo' => 'required|numeric|digits:10',
            // 'displayArea' => 'required|string',
            // 'displayLocation' => 'required|string',
            // 'displayStreet' => 'required|string',
            // 'displayLandMark' => 'required|string',
            // 'heigth' => 'required|numeric',
            // 'length' => 'required|numeric',
            // 'size' => 'required|numeric',
            // 'material' => 'required|string',
            // 'illumination' => 'required|boolean',
            // 'indicateFacing' => 'required|string',
            // 'typology' => 'required|integer',
            
            'zoneId' => 'required|integer',
            'licenseYear' => 'required|string',   
            'HordingType' => 'required|integer',
            'displayLocation' => 'required|string',     
            'width' => 'required|numeric',
            'length' => 'required|numeric',
            'displayArea' => 'required|string',
            'longitude' => 'required|numeric',   
            'latitude' => 'required|numeric',    
            'material' => 'required|string',
            'illumination' => 'required|boolean',
            'indicateFacing' => 'required|string',
            'propertyType' => 'required|string',
            'displayLandMark' => 'required|string',
            'propertyOwnerName' => 'nullable|string',
            'propertyOwnerAddress' => 'nullable|string',
            'propertyOwnerCity' => 'nullable|string',
            'propertyOwnerWhatsappNo' => 'nullable|numeric|digits:10',    
            'propertyOwnerMobileNo' => 'nullable|numeric|digits:10',


            // 'city' => 'required|string',
            // 'wardId' => 'required|integer',
            // 'permitNo' => 'required|string',
            // 'roadStreetAddress' => 'required|string',
            // 'dateGranted' => 'required|date',
            // 'permitDateIssue' => 'required|date',
            // 'permitExpiredIssue' => 'required|date',
            // 'applicationNo' => 'required|string',
            // 'accountNo' => 'required|numeric',
            // 'bankName' => 'required|string',
            // 'ifscCode' => 'required|string',
            // 'totalCharge' => 'required|numeric',
            // 'displayArea' => 'required|string',
            // 'displayLocation' => 'required|string',
            // 'displayStreet' => 'required|string',
            // 'heigth' => 'required|numeric',
            // 'length' => 'required|numeric',
            // 'size' => 'required|numeric',
            // 'typology' => 'required|integer',

            'documents' => 'required|array',
            'documents.*.image' => 'required|mimes:png,jpeg,pdf,jpg',
            'documents.*.docCode' => 'required|string',
            'documents.*.ownerDtlId' => 'nullable|integer'
        ];
    }


    /**
     * | Error Message
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ], 422),);
    }
}
