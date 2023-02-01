<?php

namespace App\Repositories\SelfAdvets;

use App\Models\Advertisements\AdvActiveSelfadvertisement;
use App\Repositories\SelfAdvets\iSelfAdvetRepo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

/**
 * | Repository for the Self Advertisements
 * | Created On-15-12-2022 
 * | Created By-Anshu Kumar
 */

class SelfAdvetRepo implements iSelfAdvetRepo
{
    public function specialInbox($workflowIds){
        $specialInbox = DB::table('adv_active_selfadvertisements')
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
                'old_application_no',
                'payment_status'
            )
            ->orderByDesc('id');
            // ->whereIn('workflow_id', $workflowIds);
        return $specialInbox;
    }

    public function specialVehicleInbox($workflowIds){
        $specialInbox = DB::table('adv_active_vehicles')
        ->select(
            'id',
            'application_no',
            'application_date',
            'applicant',
            'entity_name',
        )
        ->orderByDesc('id');
        // ->whereIn('workflow_id', $workflowIds);
    return $specialInbox;
    }

    
    public function specialAgencyInbox($workflowIds){
        $specialInbox = DB::table('adv_active_agencies')
        ->select(
            'id',
            'application_no',
            'application_date',
            'entity_name',
        )
        ->orderByDesc('id');
        // ->whereIn('workflow_id', $workflowIds);
    return $specialInbox;
    }

    public function specialPrivateLandInbox($workflowIds){
        $specialInbox = DB::table('adv_active_privatelands')
        ->select(
            'id',
            'application_no',
            'application_date',
            'entity_name',
        )
        ->orderByDesc('id');
        // ->whereIn('workflow_id', $workflowIds);
    return $specialInbox;
    }

    public function specialAgencyLicenseInbox($workflowIds){
        $specialInbox = DB::table('adv_active_agency_licenses')
        ->select(
            'id',
            'application_no',
            'application_date',
            'license_no',
            'bank_name',
            'account_no',
            'ifsc_code',
            'total_charge'
        )
        ->orderByDesc('id');
        // ->whereIn('workflow_id', $workflowIds);
    return $specialInbox;
    }
}
