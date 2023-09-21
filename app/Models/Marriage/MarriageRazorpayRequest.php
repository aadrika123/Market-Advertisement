<?php

namespace App\Models\Marriage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarriageRazorpayRequest extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * | To Save Data
     */
    public function store($req)
    {
        return MarriageRazorpayRequest::create($req);
    }

    /**
     * | Get Razor Pay Request Data
     */
    public function  getRazorpayRequest($req)
    {
        return MarriageRazorpayRequest::where('order_id', $req->orderId)
            ->where('application_id', $req->id)
            ->where('workflow_id', $req->workflowId)
            ->where('status', 2)
            ->first();
    }
}
