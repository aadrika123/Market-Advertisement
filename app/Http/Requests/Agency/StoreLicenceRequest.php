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
            'district' => 'required|string',
            'city' => 'required|string',
            'wardId' => 'required|integer',
            'zoneId' => 'required|integer',
            'permitNo' => 'required|string',
            'roadStreetAddress' => 'required|string',
            'dateGranted' => 'required|date',
            'permitDateIssue' => 'required|date',
            'permitExpiredIssue' => 'required|date',
            'applicationNo' => 'required|string',
            'accountNo' => 'required|numeric',
            'bankName' => 'required|string',
            'ifscCode' => 'required|string',
            'totalCharge' => 'required|numeric',
            // 'applicantName' => 'required|string',
            // 'directorName' => 'required|string',
            // 'registrationNo' => 'required|string',
            // 'omdId' => 'required|string',
            // 'applicantEmail' => 'required|email',
            // 'applicantCity' => 'required|string',
            // 'applicantState' => 'required|string',
            // 'applicantMobileNo' => 'required|numeric',
            // 'applicantPermanentAddress' => 'required|string',
            // 'applicantPermanentCity' => 'required|integer',
            // 'applicantPermanentState' => 'required|integer',
            // 'applicantPincode' => 'required|numeric|digits:6',
            'propertyType' => 'required|string',
            'propertyOwnerName' => 'required|string',
            'propertyOwnerAddress' => 'required|string',
            'propertyOwnerCity' => 'required|string',
            'propertyOwnerPincode' => 'required|numeric|digits:6',
            'propertyOwnerMobileNo' => 'required|numeric|digits:10',
            'displayArea' => 'required|string',
            'displayLocation' => 'required|string',
            'displayStreet' => 'required|string',
            'displayLandMark' => 'required|string',
            'heigth' => 'required|numeric',
            'length' => 'required|numeric',
            'size' => 'required|numeric',
            'material' => 'required|string',
            'illumination' => 'required|boolean',
            'indicateFacing' => 'required|string',
            'typology' => 'required|integer',

            /*-------------Documents---------------*/
            // 'directorInformation' => 'required|mimes:png,jpeg,pdf,jpg',
            // 'buildingPropertyTax' => 'required|mimes:png,jpeg,pdf,jpg',
            // 'panNo' => 'required|mimes:png,jpeg,pdf,jpg',
            // 'serviceTaxNo' => 'required|mimes:png,jpeg,pdf,jpg',
            // 'certificateStructuralEngineerOwnershipDetails' => 'required|mimes:png,jpeg,pdf,jpg',
            // 'aggrementBuildingAndAgency' => 'required|mimes:png,jpeg,pdf,jpg',
            // 'sitePhotograph' => 'required|mimes:png,jpeg,pdf,jpg',
            // 'sketchPlanOfSite' => 'required|mimes:png,jpeg,pdf,jpg',
            // 'pendingDues' => 'required|mimes:png,jpeg,pdf,jpg',
            // 'architecturalDrawings' => 'required|mimes:png,jpeg,pdf,jpg',
            // 'coordinateOfOmdWithGpsLocatoion' => 'required|mimes:png,jpeg,pdf,jpg',

            'documents' => 'required|array',
            'documents.*.id' => 'required|integer',
            'documents.*.image' => 'required|mimes:png,jpeg,pdf,jpg',
            'documents.*.relativeName' => 'required|string'
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
