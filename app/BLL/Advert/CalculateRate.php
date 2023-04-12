<?php 

namespace App\BLL\Advert;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

/**
 * | Calculate Price On Advertisement & Market
 * | Created By- Bikash Kumar
 * | Created On 12-04-2023 
 * | Status - Open
 */

 
class CalculateRate
{
    protected $_baseUrl;
    public function __construct()
    {
        $this->_baseUrl = Config::get('constants.BASE_URL');
    }

    public function generateId($token,$paramId,$ulbId){
         // Generate Application No
         $reqData = [
            "paramId" => $paramId,
            'ulbId' => $ulbId
        ];
        $refResponse = Http::withToken($token)
            ->post($this->_baseUrl . 'api/id-generator', $reqData);
        $idGenerateData = json_decode($refResponse);
        return $idGenerateData->data;
    }

    public function getAdvertisementPayment($displayArea)
    {
        return $displayArea * 10;   // Rs. 10  per Square ft.
    }
}
  