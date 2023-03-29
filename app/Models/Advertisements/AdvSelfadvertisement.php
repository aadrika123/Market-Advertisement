<?php

namespace App\Models\Advertisements;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdvSelfadvertisement extends Model
{
    use HasFactory;

    public function allApproveList()
    {
        return AdvSelfadvertisement::select(
            'id',
            'application_no',
            'application_date',
            'applicant',
            'entity_name',
            'entity_address',
            'payment_status',
            'payment_amount',
            'approve_date',
            'ulb_id',
            'workflow_id',
            'citizen_id',
            'license_no',
            'valid_upto',
            'user_id',
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
        foreach($allApproveList as $key => $list){
            $activeSelf=AdvActiveSelfadvertisement::where('application_no',$list['application_no'])->count();
            $current_date=carbon::now()->format('Y-m-d');
            $notify_date=carbon::parse($list['valid_upto'])->subDay(30)->format('Y-m-d');
            if($current_date >= $notify_date){
                if($activeSelf==0){
                    $allApproveList[$key]['renew_option']='1';     // Renew option Show
                }else{
                    $allApproveList[$key]['renew_option']='0';     // Already Renew
                }
            }
            if($current_date < $notify_date){
                $allApproveList[$key]['renew_option']='0';      // Renew option Not Show
            }
            if($list['valid_upto'] < $current_date){
                $allApproveList[$key]['renew_option']='Expired';    // Renew Expired
            }
        }
        if ($userType == 'Citizen') {
            return collect($allApproveList->where('citizen_id', $citizenId))->values();
        } else {
            return collect($allApproveList)->values();
        }
    }

    /**
     * | Get Application Approve List by Role Ids
     */
    public function listJskApprovedApplication($userId)
    {
        return AdvSelfadvertisement::where('user_id', $userId)
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
                'payment_status',
                'payment_amount',
                'approve_date',
                'license_no',
                'ulb_id',
                'workflow_id',
            )
            ->orderByDesc('id')
            ->get();
    }


    /**
     * | Get Application Details For Payments
     */
    public function applicationDetailsForPayment($id)
    {
        return AdvSelfadvertisement::where('id', $id)
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                'entity_address',
                'payment_status',
                'payment_amount',
                'license_no',
                'approve_date',
                'ulb_id',
                'workflow_id',
            )
            ->first();
    }

    
    public function getPaymentDetails($paymentId){
        $details=AdvSelfadvertisement::select('payment_details')
        ->where('payment_id', $paymentId)
        ->first();
       return json_decode($details->payment_details);
    }

    public function paymentByCash($req){

        if ($req->status == '1') {
            // Self Advertisement Table Update
            $mAdvSelfadvertisement = AdvSelfadvertisement::find($req->applicationId);
            $mAdvSelfadvertisement->payment_status = $req->status;
            $pay_id=$mAdvSelfadvertisement->payment_id = "Cash-$req->applicationId/".time();
            $mAdvSelfadvertisement->payment_date = Carbon::now();
            
            $payDetails=array('paymentMode'=>'Cash','id'=>$req->applicationId,'amount'=>$mAdvSelfadvertisement->payment_amount,'workflowId'=>$mAdvSelfadvertisement->workflow_id,'userId'=>$mAdvSelfadvertisement->citizen_id,'ulbId'=>$mAdvSelfadvertisement->ulb_id,'transDate'=>Carbon::now(),'paymentId'=>$pay_id);

            $mAdvSelfadvertisement->payment_details = json_encode($payDetails);
            if($mAdvSelfadvertisement->renew_no==NULL){                             // Fresh Application Time 
                $mAdvSelfadvertisement->valid_from = Carbon::now();
                $mAdvSelfadvertisement->valid_upto = Carbon::now()->addYears(1)->subDay(1);
            }else{                                                              // Renewal Application Time 
                $previousApplication=$this->findPreviousApplication($mAdvSelfadvertisement->license_no);
                $mAdvSelfadvertisement->valid_from = $previousApplication->valid_upto;
                $mAdvSelfadvertisement->valid_upto = Carbon::createFromFormat('Y-m-d', $previousApplication->valid_upto)->addYears(1)->subDay(1);
            }   
            $mAdvSelfadvertisement->save();
            $renewal_id = $mAdvSelfadvertisement->last_renewal_id;

            // Renewal Table Updation
            $mAdvSelfAdvertRenewal = AdvSelfadvetRenewal::find($renewal_id);
            $mAdvSelfAdvertRenewal->payment_status = 1;
            $mAdvSelfAdvertRenewal->payment_id =  $pay_id;
            $mAdvSelfAdvertRenewal->payment_date = Carbon::now();
            $mAdvSelfAdvertRenewal->valid_from = $mAdvSelfadvertisement->valid_from;
            $mAdvSelfAdvertRenewal->valid_upto = $mAdvSelfadvertisement->valid_upto;
            $mAdvSelfAdvertRenewal->payment_details = json_encode($payDetails);
            return $mAdvSelfAdvertRenewal->save();
        }
    }

    public function findPreviousApplication($license_no){
        return $details=AdvSelfadvetRenewal::select('valid_upto')
                                    ->where('license_no',$license_no)
                                    ->orderByDesc('id')
                                    ->skip(1)->first();
    }



    public function applicationDetailsForRenew($appId){
        $details=AdvSelfadvertisement::select('adv_selfadvertisements.*',
                                        'adv_selfadvertisements.license_year as license_year_id',
                                        'adv_selfadvertisements.installation_location as installation_location_id',
                                        'ly.string_parameter as license_year_name',
                                        'ew.ward_name as entity_ward_name',
                                        'il.string_parameter as installation_location_name',
                                        'w.ward_name',
                                        'pw.ward_name as permanent_ward_name',
                                        'cat.type as advt_category_name',
                                        'ulb.ulb_name',
                                        )
                                        ->leftJoin('ref_adv_paramstrings as ly','ly.id','=',DB::raw('adv_selfadvertisements.license_year::int'))
                                        ->leftJoin('ulb_ward_masters as ew','ew.id','=',DB::raw('adv_selfadvertisements.entity_ward_id::int'))
                                        ->leftJoin('ref_adv_paramstrings as il','il.id','=',DB::raw('adv_selfadvertisements.installation_location::int'))
                                        ->leftJoin('ulb_ward_masters as w','w.id','=','adv_selfadvertisements.ward_id')
                                        ->leftJoin('ulb_ward_masters as pw','pw.id','=','adv_selfadvertisements.permanent_ward_id')
                                        ->leftJoin('ulb_masters as ulb','ulb.id','=','adv_selfadvertisements.ulb_id')
                                        ->leftJoin('adv_selfadv_categories as cat','cat.id','=','adv_selfadvertisements.advt_category')
                                        ->where('adv_selfadvertisements.id',$appId)->first();
        if(!empty($details)){
            $mWfActiveDocument = new WfActiveDocument();
            $documents = $mWfActiveDocument->uploadDocumentsViewById($appId, $details->workflow_id);
            $details['documents']=$documents;
        }
        return $details;
    }

    /**
     * | Get Total Approved Self Advertisement Application
     */
    // public function getApprovedApplication(){
    //     return 
    // }

    public function searchByNameorMobile($req){
        $list=AdvSelfadvertisement::select('adv_agencies.*','et.string_parameter as entityType','adv_agencies.entity_type as entity_type_id')
              ->leftJoin('ref_adv_paramstrings as et', 'et.id', '=', 'adv_agencies.entity_type');
         if($req->filterBy=='mobileNo'){
             $filterList=$list->where('adv_agencies.mobile_no',$req->parameter);
         }
         if($req->filterBy=='entityName'){
             $filterList=$list->where('adv_agencies.entity_name',$req->parameter);
         }
         return $filterList->get();
     }
}
