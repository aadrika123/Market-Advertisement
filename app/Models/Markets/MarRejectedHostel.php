<?php

namespace App\Models\Markets;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
                'ulb_id',
            )
            ->orderByDesc('id')
            ->get();
    }

    /**
     * | Reject List For Report
     */
    public function rejectListForReport(){
        return MarRejectedHostel::select('id', 'application_no', 'applicant', 'application_date', 'application_type', 'entity_ward_id', 'rule', 'organization_type','hostel_type', 'ulb_id','license_year',DB::raw("'Reject' as application_status"));
    }
}
