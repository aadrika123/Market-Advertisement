<?php

namespace App\Http\Requests\Pet;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class PetPaymentReq extends FormRequest
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
        $refDate = Carbon::now()->format('Y-m-d');
        $offlinePaymentModes = Config::get('pet.PAYMENT_MODE');

        $rules['applicationId'] = 'required';
        $rules['paymentMode'] = 'required|in:ONLINE,NETBANKING,CASH,CHEQUE,DD,NEFT';
        if (isset($this['paymentMode']) &&  in_array($this['paymentMode'], $offlinePaymentModes) && $this['paymentMode'] != $offlinePaymentModes['3']) {
            $rules['chequeDate']    = "required|date|date_format:Y-m-d";
            $rules['bankName']      = "required";
            $rules['branchName']    = "required";
            $rules['chequeNo']      = "required";
            if (isset($this['chequeDate']) && $this['chequeDate'] > $refDate) {
                # throw error
            }
        }
        return $rules;
    }
}
