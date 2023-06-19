<?php

namespace App\Http\Requests\Toll;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TollValidationRequest extends FormRequest
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
            'areaName'              =>   'required|string|max:255',
            'tollNo'                =>   'string|max:255',
            'tollType'              =>   'required|string|max:255',
            'vendorName'            =>   'required|string|max:255',
            'address'               =>   'required|string|max:255',
            'rate'                  =>   'required|numeric',
            'lastPaymentDate'       =>   'date',
            'lastAmount'            =>   'numeric',
            'location'              =>   'string|max:255',
            'presentLength'         =>   'string|max:255',
            'presentBreadth'        =>   'string|max:255',
            'presentHeight'         =>   'string|max:255',
            'noOfFloors'            =>   'string|max:255',
            'tradeLicense'          =>   'string|max:255',
            'construction'          =>   'string|max:255',
            'utility'               =>   'string|max:255',
            'mobile'                =>   'numeric|digits:10',
            'remarks'               =>   'string|max:255',
            'photograph1'           =>   'image|mimes:jpeg,png,jpg,gif',
            'photograph2'           =>   'image|mimes:jpeg,png,jpg,gif',
            'longitude'             =>   'string|max:255',
            'latitude'              =>   'string|max:255',
            'userId'                =>   'numeric',
            'ulbId'                 =>   'numeric',
            'lastTranId'            =>   'numeric',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ], 422),);
    }
}
