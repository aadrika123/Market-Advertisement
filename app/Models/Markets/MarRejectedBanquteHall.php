<?php

namespace App\Models\Markets;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    /**
     * | Get Rejected application list
     */
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

    /**
     * | Reject List For Report
     */
    public function rejectListForReport(){
        return MarRejectedBanquteHall::select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', 'rule','hall_type', 'ulb_id','license_year','organization_type',DB::raw("'Reject' as application_status"));
    }
}
