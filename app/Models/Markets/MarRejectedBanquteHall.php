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
                'application_no',
                'application_date',
                // 'entity_address',
                // 'old_application_no',
                // 'payment_status',
                'rejected_date',
            )
            ->orderByDesc('id')
            ->get();
    }

    
    public function rejectedApplication()
    {
        return MarRejectedBanquteHall::select(
                'id',
                'application_no',
                'application_date',
                'rejected_date',
            )
            ->orderByDesc('id')
            ->get();
    }
}
