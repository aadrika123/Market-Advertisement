<?php

namespace App\Repositories\Markets;

use App\Repositories\Markets\iMarketRepo;
use Illuminate\Support\Facades\DB;

/**
 * | Repository for the Markets
 * | Created On-13-02-2023
 * | Created By-Bikash Kumar
 */

class MarketRepo implements iMarketRepo
{
    public function specialInbox($workflowIds){
        $specialInbox = DB::table('mar_active_banqute_halls')
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address'
            )
            ->orderByDesc('id');
            // ->whereIn('workflow_id', $workflowIds);
        return $specialInbox;
    }


}