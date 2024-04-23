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
}
