<?php

namespace App\Models\Rentals;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopRazorpayRequest extends Model
{
    use HasFactory;
    protected $guarded = [];
    /**
     * | To Save Data
     */
    public function store($req)
    {
        return ShopRazorpayRequest::create($req);
    }

    /**
     * |Get Razor Pay Request 
     */
    public function  getRazorpayRequest($req)
    {
        return ShopRazorpayRequest::where('order_id', $req->orderId)
            ->where('application_id', $req->id)
            ->where('status', 2)
            ->first();
    }
}
