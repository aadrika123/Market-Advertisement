<?php

namespace App\Http\Requests\Hostel;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRequest extends FormRequest
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
            'organizationType' => 'required|integer',
            'landDeedType' => 'required|integer',
            'waterSupplyType' => 'required|integer',
            'electricityType' => 'required|integer',
            'securityType' => 'required|integer',
            'cctvCamera' => 'required|integer',
            'noOfBeds' => 'required|integer',
            'noOfRooms' => 'required|integer',
            'fireExtinguisher' => 'required|integer',
            'entryGate' => 'required|integer',
            'exitGate' => 'required|integer',
            'twoWheelersParking' => 'required|integer',
            'fourWheelersParking' => 'required|integer',
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
