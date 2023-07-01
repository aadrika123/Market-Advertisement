<?php

namespace App\Models\Pet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetRazorPayRequest extends Model
{
    use HasFactory;

    /**
     * | Get details for checking the payment
     */
    public function getRazorpayRequest($req)
    {
        return PetRazorPayRequest::where("order_id", $req->orderId)
            ->where("related_id", $req->id)
            ->where("status", 2)
            ->first();
    }
}
