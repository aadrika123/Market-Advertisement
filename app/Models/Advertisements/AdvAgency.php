<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class AdvAgency extends Model
{
    use HasFactory;

    public function getagencyDetails($id)
    {
       $details1=DB::table('adv_agencies')
                    ->select('adv_agencies.*','et.string_parameter as entity_type_name')
                    ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', 'adv_agencies.entity_type')
                    ->where('adv_agencies.id', $id)
                    ->first();
        $details = json_decode(json_encode($details1), true);
        if (!empty($details)) {
            $details['expiry_date']=$details['valid_upto'];
            $warning_date=carbon::parse($details['valid_upto'])->subDay(30)->format('Y-m-d');;
            $details['warning_date'] = $warning_date;
            $current_date = date('Y-m-d');
            if ($current_date < $warning_date) {
                $details['warning'] = 0; // Warning Not Enabled
            } elseif ($current_date >= $warning_date) {
                $details['warning'] = 1; // Warning Enabled
            }
            if ($current_date > $details['expiry_date']) {
                $details['warning'] = 2;  // Expired
            }
            $directors = DB::table('adv_active_agencydirectors')
                ->select(
                    'adv_active_agencydirectors.*',
                    DB::raw("CONCAT(adv_active_agencydirectors.relative_path,'/',adv_active_agencydirectors.doc_name) as document_path")
                )
                ->where('agency_id', $details['id'])
                ->get();
            $details['directors'] = remove_null($directors->toArray());
        }
        return $details;
    }

    /**
     * Summary of allApproveList
     * @return void
     */
    public function allApproveList()
    {
        return AdvAgency::select(
            'id',
            'application_no',
            'application_date',
            'entity_name',
            'payment_status',
            'mobile_no',
            'payment_amount',
            'approve_date',
            'valid_upto',
            'valid_from',
            'citizen_id',
            'user_id',
            'workflow_id',
            'license_no',
            DB::raw("'agency' as type"),
            DB::raw("'' as owner_name")
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
        if ($userType == 'Citizen') {
            return collect($allApproveList)->where('citizen_id', $citizenId)->values();
        } else {
            return collect($allApproveList)->values();
        }
    }

    /**
     * | Get Application Approve List by Role Ids
     */
    public function listjskApprovedApplication($userId)
    {
        return AdvAgency::where('user_id', $userId)
            ->select(
                'id',
                'application_no',
                'application_date',
                'payment_status',
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
        return AdvAgency::where('id', $id)
            ->select(
                'id',
                'application_no',
                'application_date',
                'entity_name',
                'payment_status',
                'payment_amount',
                'approve_date',
                'ulb_id',
                'workflow_id',
            )
            ->first();
    }


    /**
     * | Check Login User is Asgency or Not
     */
    public function checkAgency($citizenId)
    {
        $details = AdvAgency::where('citizen_id', $citizenId)->select('*')
            ->first();
        $details = json_decode(json_encode($details), true);
        if (!empty($details)) {
            $temp_id = $details['id'];
            // Convert Std Class to Array
            $directors = DB::table('adv_active_agencydirectors')
                ->select(
                    'adv_active_agencydirectors.*',
                    DB::raw("CONCAT(adv_active_agencydirectors.relative_path,'/',adv_active_agencydirectors.doc_name) as document_path")
                )
                ->where('agency_id', $temp_id)
                ->get();
            $details['directors'] = remove_null($directors->toArray());
        }
        return $details;
    }


    public function getPaymentDetails($paymentId)
    {
         $details = AdvAgency::select(
            'adv_agencies.payment_amount',
            'adv_agencies.payment_id',
            'adv_agencies.payment_date',
            'adv_agencies.address',
            'adv_agencies.entity_name',
            'adv_agencies.payment_details',
            'ulb_masters.ulb_name as ulbName'
            )
        ->leftjoin('ulb_masters','adv_agencies.ulb_id','=','ulb_masters.id')
        ->where('adv_agencies.payment_id', $paymentId)
        ->first();
        $details->payment_details=json_decode($details->payment_details);
        $details->towards="Agency Payments";
        $details->payment_date=Carbon::createFromFormat('Y-m-d H:i:s',  $details->payment_date)->format('d-m-Y');
        return $details;
    }


    public function searchByNameorMobile($req){
       $list=AdvAgency::select('adv_agencies.*','et.string_parameter as entityType','adv_agencies.entity_type as entity_type_id',
                DB::raw("'Agency' as workflow_name" ))
             ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', 'adv_agencies.entity_type');
        if($req->filterBy=='mobileNo'){
            $filterList=$list->where('adv_agencies.mobile_no',$req->parameter);
        }
        if($req->filterBy=='entityName'){
            $filterList=$list->where('adv_agencies.entity_name',$req->parameter);
        }
        return $filterList->get();
    }

     
    public function paymentByCash($req){

        if ($req->status == '1') {
            // Agency Table Update
            $mAdvAgency = AdvAgency::find($req->applicationId);
            $mAdvAgency->payment_status = $req->status;
            $pay_id=$mAdvAgency->payment_id = "Cash-$req->applicationId-".time();
            // $mAdvCheckDtls->remarks = $req->remarks;
            $mAdvAgency->payment_date = Carbon::now();
            
            $payDetails=array('paymentMode'=>'Cash','id'=>$req->applicationId,'amount'=>$mAdvAgency->payment_amount,'workflowId'=>$mAdvAgency->workflow_id,'userId'=>$mAdvAgency->citizen_id,'ulbId'=>$mAdvAgency->ulb_id,'transDate'=>Carbon::now(),'paymentId'=>$pay_id);

            $mAdvAgency->payment_details = json_encode($payDetails);
            if($mAdvAgency->renew_no==NULL){
                $mAdvAgency->valid_from = Carbon::now();
                $mAdvAgency->valid_upto = Carbon::now()->addYears(5)->subDay(1);
            }else{
                $previousApplication=$this->findPreviousApplication($mAdvAgency->license_no);
                $mAdvAgency->valid_from = $previousApplication->valid_upto;
                $mAdvAgency->valid_upto = Carbon::createFromFormat('Y-m-d', $previousApplication->valid_upto)->addYears(5)->subDay(1);
            }
            $mAdvAgency->save();
            $renewal_id = $mAdvAgency->last_renewal_id;

            // Renewal Table Updation
            $mAdvAgencyRenewal = AdvAgencyRenewal::find($renewal_id);
            $mAdvAgencyRenewal->payment_status = 1;
            $mAdvAgencyRenewal->payment_amount =  $mAdvAgency->amount;
            $mAdvAgencyRenewal->payment_id =  $pay_id;
            $mAdvAgencyRenewal->payment_date = Carbon::now();
            $mAdvAgencyRenewal->valid_from = $mAdvAgency->valid_from;
            $mAdvAgencyRenewal->valid_upto = $mAdvAgency->valid_upto;
            $mAdvAgencyRenewal->payment_details = json_encode($payDetails);
            $ret['status']=$mAdvAgencyRenewal->save();
            $ret['paymentId']=$pay_id;
            return  $ret;
        }
    }

    
    // Find Previous Application Valid Date
    public function findPreviousApplication($license_no){
        return $details=AdvAgencyRenewal::select('valid_upto')
                                    ->where('license_no',$license_no)
                                    ->orderByDesc('id')
                                    ->skip(1)->first();
    }

     
        /**
     * | Get Reciept Details 
     */
    public function getApprovalLetter($applicationId){
        $recieptDetails = AdvAgency::select('adv_agencies.approve_date',
                                                'adv_agencies.entity_name as applicant_name',
                                                'adv_agencies.application_no',
                                                'adv_agencies.license_no',
                                                'adv_agencies.payment_date as license_start_date',
                                                DB::raw('CONCAT(application_date,id) AS reciept_no')
                                                )
                                                ->where('adv_agencies.id',$applicationId)
                                                ->first();
        // $recieptDetails->payment_details=json_decode($recieptDetails->payment_details);
        return $recieptDetails;
    }
}
