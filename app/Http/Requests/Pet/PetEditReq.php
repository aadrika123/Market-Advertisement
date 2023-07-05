<?php

namespace App\Http\Requests\Pet;

use Illuminate\Foundation\Http\FormRequest;

class PetEditReq extends FormRequest
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
        $rules['id']                    = 'required|int';
        $rules['breed']                 = 'required|';
        $rules['color']                 = 'required|';
        $rules['dateOfLepVaccine']      = 'required|date|date_format:Y-m-d';
        $rules['dateOfRabies']          = 'required|date|date_format:Y-m-d';
        $rules['doctorName']            = 'required|';
        $rules['doctorRegNo']           = 'required|';
        $rules['petBirthDate']          = 'required|date|date_format:Y-m-d';
        $rules['petFrom']               = 'required|';
        $rules['petGender']             = 'required|int|in:1,2';
        $rules['petIdentity']           = 'required|';
        $rules['petName']               = 'required|';
        return $rules;
    }
}
