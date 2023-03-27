<?php

namespace App\Models\Markets;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarRejectedHostel extends Model
{
    use HasFactory;

        
     /**
     * | Get Application Reject List by Role Ids
     */
    public function listRejected($citizenId)
    {
        return MarRejectedHostel::where('citizen_id', $citizenId)
            ->select(
                'id',
                'application_no',
                'application_date',
                'entity_address',
                'entity_name',
                'applicant',
                // 'old_application_no',
                // 'payment_status',
                'rejected_date',
                'citizen_id',
            )
            ->orderByDesc('id')
            ->get();
    }


            
     /**
     * | Get All Application Reject List
     */
    public function rejectedApplication()
    {
        return MarRejectedHostel::select(
                'id',
                'application_no',
                'application_date',
                'entity_address',
                'entity_name',
                'applicant',
                'rejected_date',
                'citizen_id',
            )
            ->orderByDesc('id')
            ->get();
    }
}
