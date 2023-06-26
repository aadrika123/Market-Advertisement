<?php

namespace App\Models\Pet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetTran extends Model
{
    use HasFactory;

    /**
     * | Get transaction details accoring to related Id and transaction type
     */
    public function getTranDetails($relatedId, $tranType)
    {
        return PetTran::where('related_id', $relatedId)
            ->where('tran_type_id', $tranType)
            ->where('status', 1)
            ->orderByDesc('id');
    }
}
