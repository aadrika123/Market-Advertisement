<?php

namespace App\Models\Bandobastee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BdPayment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function metaReqs($req)
    {
        return [
            'settler_id' => $req->settlerId,
            'ulb_id' => $req->ulbId,
            'payment_date' => $req->paymentDate,
            'payment_amount' => $req->paymentAmount,
        ];
    }

    public function installmentPayment($req)
    {
        $metaReqs = $this->metaReqs($req);
        return BdPayment::create($metaReqs);
    }

    public function listInstallmentPayment($settlerId)
    {
        return BdPayment::select('payment_amount', DB::raw('cast(payment_date as date) as payment_date'))
            ->where('settler_id', $settlerId)
            ->get();
    }
}
