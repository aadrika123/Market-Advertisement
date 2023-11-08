<?php

namespace App\Repositories\SelfAdvets;

use App\Models\Advertisements\AdvActiveSelfadvertisement;
use App\Repositories\SelfAdvets\iSelfAdvetRepo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

/**
 * | Repository for the Self Advertisements
 * | Created On - 15-12-2022 
 * | Created By - Anshu Kumar
 * | Change By - Bikash specialAgencyLicenseInbox
 */

class SelfAdvetRepo implements iSelfAdvetRepo
{

    /**
     * | Get Special Inbox List of Self Advertisements
     */
    public function specialInbox($workflowIds)
    {
        $specialInbox = DB::table('adv_active_selfadvertisements')
            ->select(
                'id',
                'application_no',
                DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"),
                'applicant',
                'entity_name',
                'entity_address',
                'application_type',
                'payment_status',
                'workflow_id',
                'ward_id'
            )
            ->orderByDesc('id');
        // ->where('workflow_id', $workflowIds);
        return $specialInbox;
    }

    /**
     * | Get Special Inbox List of Vehicle Advertisements
     */
    public function specialVehicleInbox($workflowIds)
    {
        $specialInbox = DB::table('adv_active_vehicles')
            ->select(
                'id',
                'application_no',
                DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"),
                'applicant',
                'entity_name',
                'application_type',
            )
            ->orderByDesc('id');
        // ->whereIn('workflow_id', $workflowIds);
        return $specialInbox;
    }

    /**
     * | Get Special Inbox List of Agency
     */
    public function specialAgencyInbox($workflowIds)
    {
        $specialInbox = DB::table('adv_active_agencies')
            ->select(
                'id',
                'application_no',
                DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"),
                'entity_name',
                'application_type',
            )
            ->orderByDesc('id');
        // ->whereIn('workflow_id', $workflowIds);
        return $specialInbox;
    }

    /**
     * | Get Special Inbox List of Private Land
     */
    public function specialPrivateLandInbox($workflowIds)
    {
        $specialInbox = DB::table('adv_active_privatelands')
            ->select(
                'id',
                'application_no',
                DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"),
                'entity_name',
                'application_type',
            )
            ->orderByDesc('id');
        // ->whereIn('workflow_id', $workflowIds);
        return $specialInbox;
    }

    /**
     * | Get Special Inbox List of Hoarding 
     */
    public function specialAgencyLicenseInbox($workflowIds)
    {
        $specialInbox = DB::table('adv_active_hoardings')
            ->select(
                'id',
                'application_no',
                'license_no',
                DB::raw("TO_CHAR(application_date, 'DD-MM-YYYY') as application_date"),
                'license_no',
            )
            ->orderByDesc('id');
        // ->whereIn('workflow_id', $workflowIds);
        return $specialInbox;
    }
}
