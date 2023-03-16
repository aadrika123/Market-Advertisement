<?php

namespace App\Models\Advertisements;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdvVehicle extends Model
{
    use HasFactory;


    public function allApproveList()
    {
        return AdvVehicle::select(
            'id',
            'application_no',
            'application_date',
            'applicant',
            'entity_name',
            // 'entity_address',
            // 'old_application_no',
            'payment_status',
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
        if($userType=='Citizen'){
            return collect($allApproveList->where('citizen_id', $citizenId))->values();
        }else{
            return collect($allApproveList)->values(); 
        }
        // return AdvVehicle::where('citizen_id', $citizenId)
        //     ->select(
        //         'id',
        //         'application_no',
        //         'application_date',
        //         'applicant',
        //         'entity_name',
        //         // 'entity_address',
        //         // 'old_application_no',
        //         'payment_status',
        //         'payment_amount',
        //         'approve_date',
        //     )
        //     ->orderByDesc('temp_id')
        //     ->get();
    }

    /**
     * | Get Application Approve List by Role Ids
     */
    public function listjskApprovedApplication($userId)
    {
        return AdvVehicle::where('user_id', $userId)
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
                'entity_name',
                // 'entity_address',
                // 'old_application_no',
                'payment_status',
                'payment_amount',
                'approve_date',
            )
            ->orderByDesc('temp_id')
            ->get();
    }

    /**
     * | Get Application Details FOr Payments
     */
    public function detailsForPayments($id)
    {
        return AdvVehicle::where('id', $id)
            ->select(
                'id',
                'application_no',
                'application_date',
                'applicant',
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
        $details=AdvVehicle::select('payment_details')
        ->where('payment_id', $paymentId)
        ->first();
       return json_decode($details->payment_details);
    }

    
    public function paymentByCash($req){

        if ($req->status == '1') {
            // Self Privateland Table Update
            $mAdvVehicle = AdvVehicle::find($req->applicationId);        // Application ID
            $mAdvVehicle->payment_status = $req->status;
            $pay_id=$mAdvVehicle->payment_id = "Cash-$req->applicationId/".time();
            // $mAdvCheckDtls->remarks = $req->remarks;
            $mAdvVehicle->payment_date = Carbon::now();
            $mAdvVehicle->payment_details = "By Cash";
            if($mAdvVehicle->renew_no==NULL){
                $mAdvVehicle->valid_from = Carbon::now();
                $mAdvVehicle->valid_upto = Carbon::now()->addYears(1)->subDay(1);
            }else{
                $previousApplication=$this->findPreviousApplication($mAdvVehicle->application_no);
                $mAdvVehicle->valid_from = date("Y-m-d ",strtotime("+1 Years -1 days", $previousApplication->Payment_date));
                $mAdvVehicle->valid_upto = date("Y-m-d ",strtotime("+2 Years -1 days", $previousApplication->Payment_date));
            }  
            $mAdvVehicle->save();
            $renewal_id = $mAdvVehicle->last_renewal_id;

            // Privateland Renewal Table Updation
            $mAdvVehicleRenewal = AdvVehicleRenewal::find($renewal_id);
            $mAdvVehicleRenewal->payment_status = 1;
            $mAdvVehicleRenewal->payment_id =  $pay_id;
            $mAdvVehicleRenewal->payment_date = Carbon::now();
            $mAdvVehicleRenewal->payment_details = "By Cash";
            return $mAdvVehicleRenewal->save();
        }
    }

    // Find Previous Payment Date
    public function findPreviousApplication($application_no){
        return $details=AdvVehicle::select('payment_date')
                                    ->where('application_no',$application_no)
                                    ->orderByDesc('id')
                                    ->skip(1)->first();
    }

    
    public function applicationDetailsForRenew($appId){
        $details=AdvVehicle::select('adv_vehicles.*',
                            'adv_vehicles.typology as typology_id',
                            'adv_vehicles.display_type as display_type_id',
                            'adv_vehicles.vehicle_type as vehicle_type_id',
                            'dt.string_parameter as display_type',
                            'vt.string_parameter as vehicle_type',
                            'typo.descriptions as typology',
                            'w.ward_name',
                            'pw.ward_name as permanent_ward_name',
                            'ulb.ulb_name',
                            )
                            ->leftJoin('ref_adv_paramstrings as dt','dt.id','=',DB::raw('adv_vehicles.display_type::int'))
                            ->leftJoin('ref_adv_paramstrings as vt','vt.id','=',DB::raw('adv_vehicles.vehicle_type::int'))
                            ->leftJoin('adv_typology_mstrs as typo','typo.id','=','adv_vehicles.typology')
                            ->leftJoin('ulb_ward_masters as w','w.id','=','adv_vehicles.ward_id')
                            ->leftJoin('ulb_ward_masters as pw','pw.id','=','adv_vehicles.permanent_ward_id')
                            ->leftJoin('ulb_masters as ulb','ulb.id','=','adv_vehicles.ulb_id')
                            ->where('adv_vehicles.id',$appId)->first();
        if(!empty($details)){
            $mWfActiveDocument = new WfActiveDocument();
            $documents = $mWfActiveDocument->uploadDocumentsViewById($appId, $details->workflow_id);
            $details['documents']=$documents;
        }
        return $details;
    }


}
