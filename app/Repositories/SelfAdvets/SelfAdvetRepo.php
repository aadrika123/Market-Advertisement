<?php

namespace App\Repositories\SelfAdvets;

use App\Models\Advertisements\AdvActiveAgency;
use App\Models\Advertisements\AdvActivePrivateland;
use App\Models\Advertisements\AdvActiveSelfadvertisement;
use App\Models\Advertisements\AdvActiveVehicle;
use App\Models\Advertisements\AdvAgency;
use App\Models\Advertisements\AdvAgencyRenewal;
use App\Models\Advertisements\AdvPrivateland;
use App\Models\Advertisements\AdvPrivatelandRenewal;
use App\Models\Advertisements\AdvRejectedAgency;
use App\Models\Advertisements\AdvRejectedPrivateland;
use App\Models\Advertisements\AdvRejectedVehicle;
use App\Models\Advertisements\AdvVehicle;
use App\Models\Advertisements\AdvVehicleRenewal;
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
    /**
     * |Get Details By Id For Admin Panel (JSK)
     */

    public function getAllById($id)
    {
        try {
            $test = AdvVehicle::select("id")->find($id);
            $table = "adv_vehicles";
            $application = AdvVehicle::select(
                "adv_vehicles.*",
                "ref_adv_paramstrings.string_parameter as vehicle_type",
                "displayType.string_parameter as display_type",
                "ulb_ward_masters.ward_name  as ward_id",
                "permanantWard.ward_name as permanent_ward_id",
                "entityWard.ward_name as entity_ward_id",
                "adv_typology_mstrs.descriptions as typology",
                "ulb_masters.ulb_name",
                // DB::raw("ulb_ward_masters.ward_name AS ward_no, new_ward.ward_name as new_ward_no,ulb_masters.ulb_name, '$table' AS tbl")
            );
            if (!$test) {
                $test = AdvRejectedVehicle::select("id")->find($id);
                $table = "adv_rejected_vehicles";
                $application = AdvRejectedVehicle::select(
                    "adv_rejected_vehicles.*",
                    "ref_adv_paramstrings.string_parameter as vehicle_type",
                    "displayType.string_parameter as display_type",
                    "ulb_ward_masters.ward_name  as ward_id",
                    "permanantWard.ward_name as permanent_ward_id",
                    "adv_typology_mstrs.descriptions as typology",
                    "ulb_masters.ulb_name",
                    // DB::raw("ulb_ward_masters.ward_name AS ward_no, new_ward.ward_name as new_ward_no,ulb_masters.ulb_name,'$table' AS tbl")
                );
            }
            if (!$test) {
                $test = AdvActiveVehicle::select("id")->find($id);
                $table = "adv_active_vehicles";
                $application = AdvActiveVehicle::select(
                    "adv_active_vehicles.*",
                    "ref_adv_paramstrings.string_parameter as vehicle_type",
                    "displayType.string_parameter as display_type",
                    "ulb_ward_masters.ward_name  as ward_id",
                    "permanantWard.ward_name as permanent_ward_id",
                    "adv_typology_mstrs.descriptions as typology",
                    "ulb_masters.ulb_name",
                    // DB::raw("ulb_ward_masters.ward_name AS ward_no, 
                    // new_ward.ward_name as new_ward_no,ulb_masters.ulb_name,'$table' AS tbl")
                );
            }
            if (!$test) {
                $table = "adv_vehicle_renewals";
                $application = AdvVehicleRenewal::select(
                    "adv_vehicle_renewals.*",
                    "ref_adv_paramstrings.string_parameter as vehicle_type",
                    "displayType.string_parameter as display_type",
                    "ulb_ward_masters.ward_name  as ward_id",
                    "permanantWard.ward_name as permanent_ward_id",
                    "adv_typology_mstrs.descriptions as typology",
                    "ulb_masters.ulb_name",
                    // DB::raw("ulb_ward_masters.ward_name AS ward_no, 
                    // new_ward.ward_name as new_ward_no,ulb_masters.ulb_name,'$table' AS tbl")
                );
            }

            $application = $application
                ->leftjoin("ulb_masters", function ($join) use ($table) {
                    $join->on("ulb_masters.id", "=", $table . ".ulb_id");
                })
                ->leftjoin("ref_adv_paramstrings", function ($join) use ($table) {
                    $join->on("ref_adv_paramstrings.id", "=", $table . ".vehicle_type");
                })
                ->join('ref_adv_paramstrings as displayType', 'displayType.id', $table . ".display_type")
                ->join('ulb_ward_masters', 'ulb_ward_masters.id', $table . ".ward_id")
                ->join('ulb_ward_masters as permanantWard', 'permanantWard.id', $table . ".permanent_ward_id")
                ->join('adv_typology_mstrs', 'adv_typology_mstrs.id', $table . ".typology")
                // ->leftjoin("ulb_ward_masters AS new_ward", function ($join) use ($table) {
                //     $join->on("new_ward.id", "=", $table . ".new_ward_id");
                // })
                // ->join("ulb_masters", "ulb_masters.id", $table . ".ulb_id")
                ->where($table . '.id', $id)
                ->first();
            return $application;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * |Get Details By Id For Admin Panel (JSK)
     */

    public function getAllPvtLandById($id)
    {
        try {
            $test = AdvPrivateland::select("id")->find($id);
            $table = "adv_privatelands";
            $application = AdvPrivateland::select(
                "adv_privatelands.*",
                "ulb_ward_masters.ward_name  as ward_id",
                "permanantWard.ward_name as permanent_ward_id",
                "entityWard.ward_name as entity_ward_id",
                "adv_typology_mstrs.descriptions as typology",
                "ulb_masters.ulb_name",
                // DB::raw("ulb_ward_masters.ward_name AS ward_no, new_ward.ward_name as new_ward_no,ulb_masters.ulb_name, '$table' AS tbl")
            );
            if (!$test) {
                $test = AdvRejectedPrivateland::select("id")->find($id);
                $table = "adv_rejected_privatelands";
                $application = AdvRejectedPrivateland::select(
                    "adv_rejected_privatelands.*",
                    "ulb_ward_masters.ward_name  as ward_id",
                    "permanantWard.ward_name as permanent_ward_id",
                    "entityWard.ward_name as entity_ward_id",
                    "adv_typology_mstrs.descriptions as typology",
                    "ulb_masters.ulb_name",
                    // DB::raw("ulb_ward_masters.ward_name AS ward_no, new_ward.ward_name as new_ward_no,ulb_masters.ulb_name,'$table' AS tbl")
                );
            }
            if (!$test) {
                $test = AdvActivePrivateland::select("id")->find($id);
                $table = "adv_active_privatelands";
                $application = AdvActivePrivateland::select(
                    "adv_active_privatelands.*",
                    "ulb_ward_masters.ward_name  as ward_id",
                    "permanantWard.ward_name as permanent_ward_id",
                    "entityWard.ward_name as entity_ward_id",
                    "adv_typology_mstrs.descriptions as typology",
                    "ulb_masters.ulb_name",
                    // DB::raw("ulb_ward_masters.ward_name AS ward_no, 
                    // new_ward.ward_name as new_ward_no,ulb_masters.ulb_name,'$table' AS tbl")
                );
            }
            if (!$test) {
                $table = "adv_privateland_renewals";
                $application = AdvPrivatelandRenewal::select(
                    "adv_privateland_renewals.*",
                    "ulb_ward_masters.ward_name  as ward_id",
                    "permanantWard.ward_name as permanent_ward_id",
                    "entityWard.ward_name as entity_ward_id",
                    "adv_typology_mstrs.descriptions as typology",
                    "ulb_masters.ulb_name",
                    // DB::raw("ulb_ward_masters.ward_name AS ward_no, 
                    // new_ward.ward_name as new_ward_no,ulb_masters.ulb_name,'$table' AS tbl")
                );
            }

            $application = $application
                ->leftjoin("ulb_masters", function ($join) use ($table) {
                    $join->on("ulb_masters.id", "=", $table . ".ulb_id");
                })
                ->join('ulb_ward_masters', 'ulb_ward_masters.id', $table . ".ward_id")
                ->join('ulb_ward_masters as permanantWard', 'permanantWard.id', $table . ".permanent_ward_id")
                ->join('ulb_ward_masters as entityWard', 'entityWard.id', $table . ".entity_ward_id")
                ->join('adv_typology_mstrs', 'adv_typology_mstrs.id', $table . ".typology")
                // ->leftjoin("ulb_ward_masters AS new_ward", function ($join) use ($table) {
                //     $join->on("new_ward.id", "=", $table . ".new_ward_id");
                // })
                // ->join("ulb_masters", "ulb_masters.id", $table . ".ulb_id")
                ->where($table . '.id', $id)
                ->first();
            return $application;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    /**
     * |Get Details By Id For Admin Panel (JSK)
     */

    public function getAllAgencyLandById($id)
    {
        try {
            $test = AdvAgency::select("id")->find($id);
            $table = "adv_agencies";
            $application = AdvAgency::select(
                "adv_agencies.*",
                "ref_adv_paramstrings.string_parameter as entity_type",
                "ulb_masters.ulb_name",
                // DB::raw("ulb_ward_masters.ward_name AS ward_no, new_ward.ward_name as new_ward_no,ulb_masters.ulb_name, '$table' AS tbl")
            );
            if (!$test) {
                $test = AdvRejectedAgency::select("id")->find($id);
                $table = "adv_rejected_agencies";
                $application = AdvRejectedAgency::select(
                    "adv_rejected_agencies.*",
                    "ref_adv_paramstrings.string_parameter as entity_type",
                    "ulb_masters.ulb_name",
                    // DB::raw("ulb_ward_masters.ward_name AS ward_no, new_ward.ward_name as new_ward_no,ulb_masters.ulb_name,'$table' AS tbl")
                );
            }
            if (!$test) {
                $test = AdvActiveAgency::select("id")->find($id);
                $table = "adv_active_agencies";
                $application = AdvActiveAgency::select(
                    "adv_active_agencies.*",
                    "ref_adv_paramstrings.string_parameter as entity_type",
                    "ulb_masters.ulb_name",
                    // DB::raw("ulb_ward_masters.ward_name AS ward_no, 
                    // new_ward.ward_name as new_ward_no,ulb_masters.ulb_name,'$table' AS tbl")
                );
            }
            if (!$test) {
                $table = "adv_agency_renewals";
                $application = AdvAgencyRenewal::select(
                    "adv_agency_renewals.*",
                    "ulb_masters.ulb_name",
                    // DB::raw("ulb_ward_masters.ward_name AS ward_no, 
                    // new_ward.ward_name as new_ward_no,ulb_masters.ulb_name,'$table' AS tbl")
                );
            }

            $application = $application
                ->leftjoin("ulb_masters", function ($join) use ($table) {
                    $join->on("ulb_masters.id", "=", $table . ".ulb_id");
                })
                ->join('ref_adv_paramstrings', 'ref_adv_paramstrings.id', $table . ".entity_type")
                // ->leftjoin("ulb_ward_masters AS new_ward", function ($join) use ($table) {
                //     $join->on("new_ward.id", "=", $table . ".new_ward_id");
                // })
                // ->join("ulb_masters", "ulb_masters.id", $table . ".ulb_id")
                ->where($table . '.id', $id)
                ->first();
            return $application;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
