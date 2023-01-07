<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

/**
 * | Workflow Masters Trait
 */

trait WorkflowTrait
{
    /**
     * | Get Ulb Workflow Id By Ulb Id
     * | @param Bearer bearer token from request
     * | @param ulbId 
     * | @param workflowId 
     */
    public function getUlbWorkflowId($bearer, $ulbId, $wfMasterId)
    {
        $baseUrl = Config::get('constants.BASE_URL');
        $workflows = Http::withHeaders([
            "Authorization" => "Bearer $bearer",
            "contentType" => "application/json"

        ])->post($baseUrl . 'api/workflow/get-ulb-workflow', [
            "ulbId" => $ulbId,
            "workflowMstrId" => $wfMasterId
        ])->json();

        return $workflows;
    }

    /**
     * | Get Roles by Logged In user Id
     * | @param userId Logged In UserId
     */
    public function getRoleByUserId($bearer)
    {
        $baseUrl = Config::get('constants.BASE_URL');
        $roles = Http::withHeaders([
            "Authorization" => "Bearer $bearer",
            "contentType" => "application/json"
        ])->post($baseUrl . 'api/role-by-user')->json();
        return $roles['data'];
    }
}
