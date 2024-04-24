<?php

namespace App\Models\Rentals;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopRazorpayResponse extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function store($req)
    {
        return ShopRazorpayResponse::create($req);
    }
}
