<?php

namespace App\Models\Advertisements;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


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
            $current_date=carbon::now()->format('Y-m-d');
            $notify_date=carbon::parse($list['valid_upto'])->subDay(30)->format('Y-m-d');
            if($current_date >= $notify_date){
                $allApproveList[$key]['renew_option']='1';     // Renew option Show
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
        // return AdvSelfadvertisement::where('citizen_id', $citizenId)
        //     ->select(
        //         'id',
        //         'application_no',
        //         'application_date',
        //         'applicant',
        //         'entity_name',
        //         'entity_address',
        //         'old_application_no',
        //         'payment_status',
        //         'payment_amount',
        //         'approve_date',
        //         'ulb_id',
        //         'workflow_id',
        //     )
        //     ->orderByDesc('temp_id')
        //     ->get();
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
            // $mAdvCheckDtls->remarks = $req->remarks;
            $mAdvSelfadvertisement->payment_date = Carbon::now();
            $mAdvSelfadvertisement->payment_details = "By Cash";
            if($mAdvSelfadvertisement->renew_no==NULL){
                $mAdvSelfadvertisement->valid_from = Carbon::now();
                $mAdvSelfadvertisement->valid_upto = Carbon::now()->addYears(1)->subDay(1);
            }else{
                $previousApplication=$this->findPreviousApplication($mAdvSelfadvertisement->application_no);
                $mAdvSelfadvertisement->valid_from = date("Y-m-d ",strtotime("+1 Years -1 days", $previousApplication->Payment_date));
                $mAdvSelfadvertisement->valid_upto = date("Y-m-d ",strtotime("+2 Years -1 days", $previousApplication->Payment_date));
            }   
            $mAdvSelfadvertisement->save();
            $renewal_id = $mAdvSelfadvertisement->last_renewal_id;

            // Renewal Table Updation
            $mAdvSelfAdvertRenewal = AdvSelfadvetRenewal::find($renewal_id);
            $mAdvSelfAdvertRenewal->payment_status = 1;
            $mAdvSelfAdvertRenewal->payment_id =  $pay_id;
            $mAdvSelfAdvertRenewal->payment_date = Carbon::now();
            $mAdvSelfAdvertRenewal->payment_details = "By Cash";
            return $mAdvSelfAdvertRenewal->save();
        }
    }

    public function findPreviousApplication($application_no){
        return $details=AdvSelfadvetRenewal::select('payment_date')
                                    ->where('application_no',$application_no)
                                    ->orderByDesc('id')
                                    ->skip(1)->first();
    }



    public function applicationDetailsForRenew($appId){
        $details=AdvSelfadvertisement::find($appId);
        if(!empty($details)){
            $mWfActiveDocument = new WfActiveDocument();
            $documents = $mWfActiveDocument->uploadDocumentsViewById($appId, $details->workflow_id);
            $details['documents']=$documents;
        }
        return $details;
    }
}
