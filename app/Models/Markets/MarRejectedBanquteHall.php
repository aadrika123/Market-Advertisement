<?php

namespace App\Models\Markets;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarRejectedBanquteHall extends Model
{
    use HasFactory;

       
     /**
     * | Get Application Reject List by Role Ids
     */
    public function listRejected($citizenId)
    {
        return MarRejectedBanquteHall::where('citizen_id', $citizenId)
            ->select(
                'id',
                'temp_id',
                'application_no',
                'application_date',
                // 'entity_address',
                // 'old_application_no',
                // 'payment_status',
                'rejected_date',
            )
            ->orderByDesc('temp_id')
            ->get();
    }
}
