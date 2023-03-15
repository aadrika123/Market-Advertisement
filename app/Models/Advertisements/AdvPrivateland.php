<?php

namespace App\Models\Advertisements;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdvPrivateland extends Model
{
    use HasFactory;


    /**
     * | Get Application All Approve List
     */
    public function allApproveList(){
        return AdvPrivateland::select(
            'id',
            'application_no',
            'application_date',
            'payment_amount',
            'approve_date',
            'citizen_id',
            'user_id',
        )
        ->orderByDesc('id')
        ->get();
    }

    /**
     * | Get Application Approve List by Role Ids
     */
    public function listApproved($citizenId,$userType)
    {
        $allApproveList = $this->allApproveList();
        if ($userType == 'Citizen') {
            return  collect($allApproveList->where('citizen_id', $citizenId))->values();
        }else{
            return collect($allApproveList)->values();
        }
    }
    

    /**
     * | Get Application Approve List by Role Ids
     */
    public function listjskApprovedApplication($userId)
    {
        return AdvPrivateland::where('user_id', $userId)
            ->select(
                'id',
                'application_no',
                'application_date',
                // 'entity_address',
                // 'old_application_no',
                //  'payment_status',
                'payment_amount',
                'approve_date',
            )
            ->orderByDesc('id')
            ->get();
    }


    /**
     * | Get Application Details FOr Payments
     */
    public function getApplicationDetailsForPayment($id)
    {
        return AdvPrivateland::where('id', $id)
            ->select(
                'id',
                'application_no',
                'application_date',
                // 'applicant',
                'entity_name',
                'payment_status',
                'payment_amount',
                'approve_date',
                'ulb_id',
                'workflow_id',
            )
            ->first();
    }

    public function getPaymentDetails($paymentId){
        $details=AdvPrivateland::select('payment_details')
        ->where('payment_id', $paymentId)
        ->first();
       return json_decode($details->payment_details);
    }

    
    public function paymentByCash($req){

        if ($req->status == '1') {
            // Self Privateland Table Update
            $mAdvPrivateland = AdvPrivateland::find($req->applicationId);        // Application ID
            $mAdvPrivateland->payment_status = $req->status;
            $pay_id=$mAdvPrivateland->payment_id = "Cash-$req->applicationId/".time();
            // $mAdvCheckDtls->remarks = $req->remarks;
            $mAdvPrivateland->payment_date = Carbon::now();
            $mAdvPrivateland->payment_details = "By Cash";
            if($mAdvPrivateland->renew_no==NULL){
                $mAdvPrivateland->valid_from = Carbon::now();
                $mAdvPrivateland->valid_upto = Carbon::now()->addYears(1)->subDay(1);
            }else{
                $previousApplication=$this->findPreviousApplication($mAdvPrivateland->application_no);
                $mAdvPrivateland->valid_from = date("Y-m-d ",strtotime("+1 Years -1 days", $previousApplication->Payment_date));
                $mAdvPrivateland->valid_upto = date("Y-m-d ",strtotime("+2 Years -1 days", $previousApplication->Payment_date));
            }  
            $mAdvPrivateland->save();
            $renewal_id = $mAdvPrivateland->last_renewal_id;


            // Privateland Renewal Table Updation
            $mAdvPrivatelandRenewal = AdvPrivatelandRenewal::find($renewal_id);
            $mAdvPrivatelandRenewal->payment_status = 1;
            $mAdvPrivatelandRenewal->payment_id =  $pay_id;
            $mAdvPrivatelandRenewal->payment_date = Carbon::now();
            $mAdvPrivatelandRenewal->payment_details = "By Cash";
            return $mAdvPrivatelandRenewal->save();
        }
    }

    // Find Previous Payment Date
    public function findPreviousApplication($application_no){
        return $details=AdvPrivatelandRenewal::select('payment_date')
                                    ->where('application_no',$application_no)
                                    ->orderByDesc('id')
                                    ->skip(1)->first();
    }

    public function applicationDetailsForRenew($appId){
        // $details=AdvPrivateland::find($appId);
        $details=AdvPrivateland::select('adv_privatelands.*',
                                        'adv_privatelands.typology as typology_id',
                                        'adv_privatelands.display_type as display_type_id',
                                        'adv_privatelands.installation_location as installation_location_id',
                                        'il.string_parameter as installation_location',
                                        'dt.string_parameter as display_type',
                                        'typo.descriptions as typology',
                                        )
                                        ->leftJoin('ref_adv_paramstrings as il','il.id','=',DB::raw('adv_privatelands.installation_location::int'))
                                        ->leftJoin('adv_typology_mstrs as typo','typo.id','=','adv_privatelands.typology')
                                        ->leftJoin('ref_adv_paramstrings as dt','dt.id','=',DB::raw('adv_privatelands.display_type::int'))
                                ->where('adv_privatelands.id',$appId)->first();
        if(!empty($details)){
            $mWfActiveDocument = new WfActiveDocument();
            $documents = $mWfActiveDocument->uploadDocumentsViewById($appId, $details->workflow_id);
            $details['documents']=$documents;
        }
        return $details;
    }

}
