<?php

namespace App\Models\Marriage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarriageRazorpayResponse extends Model
{
    use HasFactory;

    public function store($req)
    {
        return MarriageRazorpayResponse::create($req);
    }
}
