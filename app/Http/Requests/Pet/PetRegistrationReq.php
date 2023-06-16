<?php

namespace App\Http\Requests\Pet;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Config;

class PetRegistrationReq extends FormRequest
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
        $rules = [
            'address'               => 'required|',
            'applyThrough'          => 'required|in:1,2',
            'breed'                 => 'required|',
            'categoryApplication'   => 'required|in:1,0',
            'color'                 => 'required|alpha_num',
            'dateOfLepVaccine'      => 'required|date',
            'dateOfRabies'          => 'required|date',
            'doctorName'            => 'required|alpha_num',
            'doctorRegNo'           => 'required|',
            'petBirthDate'          => 'required|date',
            'petFrom'               => 'required|int|',
            'petGender'             => 'required|in:1,2',
            'petIdentity'           => 'required|alpha_num',
            'petName'               => 'required|alpha_num',
            'petType'               => 'required|alpha_num',
            'ulb'                   => 'required|int',
            'ward'                  => 'required|int',
            'tenant'                => 'sometimes|required|array',
            'owner'                 => 'sometimes|required|array'
        ];
        if (isset($this->tenant) && $this->tenant) {
            $rules = $this->getApplicantRules();
        }
        if (isset($this->owner) && $this->owner) {
            $rules = $this->getApplicantRules();
        }
        if (isset($this->applyThrough) && $this->applyThrough) {
            $rules['propertyNo'] = 'required|';
        }
        return $rules;
    }
    public function getApplicantRules()
    {
        $rules = [
            "tenant.*.applicantName"    => "required|alpha_num",
            "tenant.*.mobileNo"         => "required|digits:10|regex:/[0-9]{10}/",
            "tenant.*.email"            => "nullable|email",
            "tenant.*.panNo"            => "required|",
        ];
        return $rules;
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'    => false,
            'message'   => "Validation Error!",
            'error'     => $validator->errors()
        ], 422));
    }
}
