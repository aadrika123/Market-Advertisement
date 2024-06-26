<?php

namespace App\Http\Requests\Agency;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RenewalHordingRequest extends FormRequest
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
            'zoneId' => 'required|integer',
            'applicationId' => 'required|integer',
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
            'status'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ], 200),);
    }
}
