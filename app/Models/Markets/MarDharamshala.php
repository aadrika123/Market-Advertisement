<?php

namespace App\Models\Markets;

use App\Models\Advertisements\WfActiveDocument;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MarDharamshala extends Model
{
    use HasFactory;
     
    /**
     * Summary of allApproveList
     * @return void
     */
    public function allApproveList()
    {
        return MarDharamshala::select(
            'id',
            'application_no',
            'application_date',
            'entity_address',
            'payment_amount',
            'entity_name',
            'applicant',
            'applicant as owner_name',
            'mobile as mobile_no',
            'approve_date',
            'payment_status',
            'citizen_id',
            'valid_upto',
            'workflow_id',
            'license_no',
            'application_type',
            DB::raw("'dharamshala' as type"),
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
            $activeDharamashala=MarActiveDharamshala::where('application_no',$list['application_no'])->count();
            $current_date=carbon::now()->format('Y-m-d');
            $notify_date=carbon::parse($list['valid_upto'])->subDay(30)->format('Y-m-d');
            if($current_date >= $notify_date){
                if($activeDharamashala==0){
                    $allApproveList[$key]['renew_option']='1';     // Renew option Show
                }else{
                    $allApproveList[$key]['renew_option']='0';     // Already Renew
                }
            }
            if($current_date < $notify_date){
                $allApproveList[$key]['renew_option']='0';      // Renew option Not Show 0
            }
            if($list['valid_upto'] < $current_date){
                $allApproveList[$key]['renew_option']='Expired';    // Renew Expired 
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
        return MarDharamshala::where('id', $id)
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

    
    public function paymentByCash($req){

        if ($req->status == '1') {
            // Dharamshala Table Update
            $mMarDharamshala = MarDharamshala::find($req->applicationId);
            $mMarDharamshala->payment_status = $req->status;
            $pay_id=$mMarDharamshala->payment_id = "Cash-$req->applicationId/".time();
            // $mAdvCheckDtls->remarks = $req->remarks;
            $mMarDharamshala->payment_date = Carbon::now();
            
            $payDetails=array('paymentMode'=>'Cash','id'=>$req->applicationId,'amount'=>$mMarDharamshala->payment_amount,'workflowId'=>$mMarDharamshala->workflow_id,'userId'=>$mMarDharamshala->citizen_id,'ulbId'=>$mMarDharamshala->ulb_id,'transDate'=>Carbon::now(),'paymentId'=>$pay_id);

            $mMarDharamshala->payment_details = json_encode($payDetails);
            if($mMarDharamshala->renew_no==NULL){
                $mMarDharamshala->valid_from = Carbon::now();
                $mMarDharamshala->valid_upto = Carbon::now()->addYears(1)->subDay(1);
            }else{
                $previousApplication=$this->findPreviousApplication($mMarDharamshala->application_no);
                $mMarDharamshala->valid_from = $previousApplication->valid_upto;
                $mMarDharamshala->valid_upto = date("Y-m-d ",strtotime("+1 Years -1 days", $previousApplication->valid_upto));
            }
            $mMarDharamshala->save();
            $renewal_id = $mMarDharamshala->last_renewal_id;

            // Renewal Table Updation
            $mMarDharamshalaRenewal = MarDharamshalaRenewal::find($renewal_id);
            $mMarDharamshalaRenewal->payment_status = 1;
            $mMarDharamshalaRenewal->payment_id =  $pay_id;
            $mMarDharamshalaRenewal->payment_date = Carbon::now();
            $mMarDharamshalaRenewal->valid_from = $mMarDharamshala->valid_from;
            $mMarDharamshalaRenewal->valid_upto = $mMarDharamshala->valid_upto;
            $mMarDharamshalaRenewal->payment_details = json_encode($payDetails);
            $status=$mMarDharamshalaRenewal->save();
            $returnData['status']=$status;
            $returnData['payment_id']=$pay_id;
            return $returnData;
        }
    }

    
    // Find Previous Payment Date
    public function findPreviousApplication($application_no){
        return $details=MarDharamshalaRenewal::select('valid_upto')
                                    ->where('application_no',$application_no)
                                    ->orderByDesc('id')
                                    ->skip(1)->first();
    }


     /**
     * | Get Application Details For Renew Applications
     */
    public function applicationDetailsForRenew($appId){
        $details=MarDharamshala::select('mar_dharamshalas.*',
                        'mar_dharamshalas.organization_type as organization_type_id',
                        'mar_dharamshalas.land_deed_type as land_deed_type_id',
                        'mar_dharamshalas.water_supply_type as water_supply_type_id',
                        'mar_dharamshalas.electricity_type as electricity_type_id',
                        'mar_dharamshalas.security_type as security_type_id',
                        'ly.string_parameter as license_year_name',
                        'rw.ward_name as resident_ward_name',
                        'ot.string_parameter as organization_type_name',
                        'ldt.string_parameter as land_deed_type_name',
                        'wt.string_parameter as water_supply_type_name',
                        'et.string_parameter as electricity_type_name',
                        'ew.ward_name as entity_ward_name',
                        'st.string_parameter as security_type_name',
                        'pw.ward_name as permanent_ward_name',
                        'ulb.ulb_name',
                        )
                        ->leftJoin('ref_adv_paramstrings as ly','ly.id','=',DB::raw('mar_dharamshalas.license_year::int'))
                        ->leftJoin('ulb_ward_masters as rw','rw.id','=',DB::raw('mar_dharamshalas.residential_ward_id::int'))
                        ->leftJoin('ref_adv_paramstrings as ot','ot.id','=',DB::raw('mar_dharamshalas.organization_type::int'))
                        ->leftJoin('ref_adv_paramstrings as ldt','ldt.id','=',DB::raw('mar_dharamshalas.land_deed_type::int'))
                        ->leftJoin('ref_adv_paramstrings as wt','wt.id','=',DB::raw('mar_dharamshalas.water_supply_type::int'))
                        ->leftJoin('ref_adv_paramstrings as et','et.id','=',DB::raw('mar_dharamshalas.electricity_type::int'))
                        ->leftJoin('ref_adv_paramstrings as st','st.id','=',DB::raw('mar_dharamshalas.security_type::int'))
                        ->leftJoin('ulb_ward_masters as ew','ew.id','=','mar_dharamshalas.entity_ward_id')
                        ->leftJoin('ulb_ward_masters as pw','pw.id','=','mar_dharamshalas.permanent_ward_id')
                        ->leftJoin('ulb_masters as ulb','ulb.id','=','mar_dharamshalas.ulb_id')
                        ->where('mar_dharamshalas.id',$appId)->first();
        if(!empty($details)){
            $mWfActiveDocument = new WfActiveDocument();
            $documents = $mWfActiveDocument->uploadDocumentsViewById($appId, $details->workflow_id);
            $details['documents']=$documents;
        }
        return $details;
    }

     /**
     * | Get Payment Details After Payment
     */
    public function getPaymentDetails($paymentId){
        return $details = MarDharamshala::select('payment_amount', 'payment_id', 'payment_date', 'permanent_address as address', 'entity_name')
            ->where('payment_id', $paymentId)
            ->first();
    }
}
