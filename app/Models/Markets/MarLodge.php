<?php

namespace App\Models\Markets;

use App\Models\Advertisements\WfActiveDocument;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MarLodge extends Model
{
    use HasFactory;

    /**
     * Summary of allApproveList
     * @return void
     */
    public function allApproveList()
    {
        return MarLodge::select(
            'id',
            'application_no',
            'application_date',
            'entity_address',
            'entity_name',
            'applicant',
            'applicant as owner_name',
            'mobile as mobile_no',
            'payment_amount',
            'payment_status',
            'approve_date',
            'citizen_id',
            'valid_upto',
            'workflow_id',
            'license_no',
            'application_type',
            DB::raw("'lodge' as type"),
        )
            ->orderByDesc('id')
            ->get();
    }

    /**
     * | Get Application Approve List by Role Ids
     */
    public function listApproved($citizenId, $userType)
    {
        $allApproveList = $this->allApproveList();
        foreach ($allApproveList as $key => $list) {
            $activeLodge = MarActiveLodge::where('application_no', $list['application_no'])->count();
            $current_date = carbon::now()->format('Y-m-d');
            $notify_date = carbon::parse($list['valid_upto'])->subDay(30)->format('Y-m-d');
            if ($current_date >= $notify_date) {
                if ($activeLodge == 0) {
                    $allApproveList[$key]['renew_option'] = '1';     // Renew option Show
                } else {
                    $allApproveList[$key]['renew_option'] = '0';     // Already Renew
                }
            }
            if ($current_date < $notify_date) {
                $allApproveList[$key]['renew_option'] = '0';      // Renew option Not Show 0
            }
            if ($list['valid_upto'] < $current_date) {
                $allApproveList[$key]['renew_option'] = 'Expired';    // Renew Expired 
            }
        }
        if ($userType == 'Citizen') {
            return collect($allApproveList)->where('citizen_id', $citizenId)->values();
        } else {
            return collect($allApproveList)->values();
        }
    }

    /**
     * | Get Application Details FOr Payments
     */
    public function getApplicationDetailsForPayment($id)
    {
        return MarLodge::where('id', $id)
            ->select(
                'id',
                'application_no',
                'application_date',
                'entity_name',
                'payment_amount',
                'approve_date',
                'ulb_id',
                'workflow_id',
            )
            ->first();
    }


    public function paymentByCash($req)
    {

        if ($req->status == '1') {
            // Lodge Table Update
            $mMarLodge = MarLodge::find($req->applicationId);
            $mMarLodge->payment_status = $req->status;
            $pay_id = $mMarLodge->payment_id = "Cash-$req->applicationId-" . time();
            $mMarLodge->payment_date = Carbon::now();

            $payDetails = array('paymentMode' => 'Cash', 'id' => $req->applicationId, 'amount' => $mMarLodge->payment_amount, 'workflowId' => $mMarLodge->workflow_id, 'userId' => $mMarLodge->citizen_id, 'ulbId' => $mMarLodge->ulb_id, 'transDate' => Carbon::now(), 'paymentId' => $pay_id);

            $mMarLodge->payment_details = json_encode($payDetails);

            if ($mMarLodge->renew_no == NULL) {
                $mMarLodge->valid_from = Carbon::now();
                $mMarLodge->valid_upto = Carbon::now()->addYears(1)->subDay(1);
            } else {
                $previousApplication = $this->findPreviousApplication($mMarLodge->application_no);
                $mMarLodge->valid_from = $previousApplication->valid_upto;
                $mMarLodge->valid_upto = Carbon::createFromFormat('Y-m-d', $previousApplication->valid_upto)->addYears(1)->subDay(1);
            }
            $mMarLodge->save();
            $renewal_id = $mMarLodge->last_renewal_id;

            // Renewal Table Updation
            $mMarLodgeRenewal = MarLodgeRenewal::find($renewal_id);
            $mMarLodgeRenewal->payment_status = 1;
            $mMarLodgeRenewal->payment_id =  $pay_id;
            $mMarLodgeRenewal->payment_date = Carbon::now();
            $mMarLodgeRenewal->valid_from = $mMarLodge->valid_from;
            $mMarLodgeRenewal->valid_upto = $mMarLodge->valid_upto;
            $mMarLodgeRenewal->payment_details = json_encode($payDetails);
            $status = $mMarLodgeRenewal->save();
            $returnData['status'] = $status;
            $returnData['payment_id'] = $pay_id;
            return $returnData;
        }
    }

    // Find Previous Payment Date
    public function findPreviousApplication($application_no)
    {
        return $details = MarLodgeRenewal::select('valid_upto')
            ->where('application_no', $application_no)
            ->orderByDesc('id')
            ->skip(1)->first();
    }

    /**
     * | Get Application Details For Renew Applications
     */
    public function applicationDetailsForRenew($appId)
    {
        $details = MarLodge::select(
            'mar_lodges.*',
            'mar_lodges.lodge_type as lodge_type_id',
            'mar_lodges.organization_type as organization_type_id',
            'mar_lodges.land_deed_type as land_deed_type_id',
            'mar_lodges.mess_type as mess_type_id',
            'mar_lodges.water_supply_type as water_supply_type_id',
            'mar_lodges.electricity_type as electricity_type_id',
            'mar_lodges.security_type as security_type_id',
            'ly.string_parameter as license_year_name',
            'rw.ward_name as resident_ward_name',
            'lt.string_parameter as lodge_type_name',
            'ot.string_parameter as organization_type_name',
            'ldt.string_parameter as land_deed_type_name',
            'mt.string_parameter as mess_type_name',
            'wt.string_parameter as water_supply_type_name',
            'et.string_parameter as electricity_type_name',
            'st.string_parameter as security_type_name',
            'ew.ward_name as entity_ward_name',
            'pw.ward_name as permanent_ward_name',
            'ulb.ulb_name',
        )
            ->leftJoin('ref_adv_paramstrings as ly', 'ly.id', '=', DB::raw('mar_lodges.license_year::int'))
            ->leftJoin('ulb_ward_masters as rw', 'rw.id', '=', DB::raw('mar_lodges.residential_ward_id::int'))
            ->leftJoin('ref_adv_paramstrings as lt', 'lt.id', '=', DB::raw('mar_lodges.lodge_type::int'))
            ->leftJoin('ref_adv_paramstrings as ot', 'ot.id', '=', DB::raw('mar_lodges.organization_type::int'))
            ->leftJoin('ref_adv_paramstrings as ldt', 'ldt.id', '=', DB::raw('mar_lodges.land_deed_type::int'))
            ->leftJoin('ref_adv_paramstrings as mt', 'mt.id', '=', DB::raw('mar_lodges.mess_type::int'))
            ->leftJoin('ref_adv_paramstrings as wt', 'wt.id', '=', DB::raw('mar_lodges.water_supply_type::int'))
            ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', DB::raw('mar_lodges.electricity_type::int'))
            ->leftJoin('ref_adv_paramstrings as st', 'st.id', '=', DB::raw('mar_lodges.security_type::int'))
            ->leftJoin('ulb_ward_masters as ew', 'ew.id', '=', 'mar_lodges.entity_ward_id')
            ->leftJoin('ulb_ward_masters as pw', 'pw.id', '=', 'mar_lodges.permanent_ward_id')
            ->leftJoin('ulb_masters as ulb', 'ulb.id', '=', 'mar_lodges.ulb_id')
            ->where('mar_lodges.id', $appId)->first();
        if (!empty($details)) {
            $mWfActiveDocument = new WfActiveDocument();
            $documents = $mWfActiveDocument->uploadDocumentsViewById($appId, $details->workflow_id);
            $details['documents'] = $documents;
        }
        return $details;
    }

    /**
     * | Get Payment Details After Payment
     */
    public function getPaymentDetails($paymentId)
    {
        $details = MarLodge::select(
            'adv_lodges.payment_amount',
            'adv_lodges.payment_id',
            'adv_lodges.payment_date',
            'adv_lodges.permanent_address as address',
            'adv_lodges.entity_name',
            'adv_lodges.payment_details',
            'ulb_masters.ulb_name as ulbName'
        )
            ->leftjoin('ulb_masters', 'adv_lodges.ulb_id', '=', 'ulb_masters.id')
            ->where('adv_lodges.payment_id', $paymentId)
            ->first();
        $details->payment_details = json_decode($details->payment_details);
        $details->towards = "Lodge Payments";
        $details->payment_date = Carbon::createFromFormat('Y-m-d', $details->payment_date)->format('d/m/Y');
        return $details;
    }
}
