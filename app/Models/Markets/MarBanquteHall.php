<?php

namespace App\Models\Markets;

use App\Models\Advertisements\WfActiveDocument;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MarBanquteHall extends Model
{
    use HasFactory;

    
    /**
     * Summary of allApproveList
     * @return void
     */
    public function allApproveList()
    {
        return MarBanquteHall::select(
            'id',
            'temp_id',
            'application_no',
            'application_date',
            // 'entity_address',
            // 'old_application_no',
            'payment_status',
            'payment_amount',
            'approve_date',
            'citizen_id',
            'user_id',
        )
            ->orderByDesc('temp_id')
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
     * | Get Application Details FOr Payments
     */
    public function getApplicationDetailsForPayment($id)
    {
        return MarBanquteHall::where('id', $id)
            ->select(
                'id',
                'temp_id',
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

    public function paymentByCash($req){

        if ($req->status == '1') {
            // Banquet Hall Table Update
            $mMarBanquteHall = MarBanquteHall::find($req->applicationId);
            $mMarBanquteHall->payment_status = $req->status;
            $pay_id=$mMarBanquteHall->payment_id = "Cash-$req->applicationId-".time();
            $mMarBanquteHall->payment_date = Carbon::now();

            $payDetails=array('paymentMode'=>'Cash','id'=>$req->applicationId,'amount'=>$mMarBanquteHall->payment_amount,'workflowId'=>$mMarBanquteHall->workflow_id,'userId'=>$mMarBanquteHall->citizen_id,'ulbId'=>$mMarBanquteHall->ulb_id,'transDate'=>Carbon::now(),'paymentId'=>$pay_id);

            $mMarBanquteHall->payment_details = json_encode($payDetails);
            if($mMarBanquteHall->renew_no==NULL){
                $mMarBanquteHall->valid_from = Carbon::now();
                $mMarBanquteHall->valid_upto = Carbon::now()->addYears(1)->subDay(1);
            }else{
                $previousApplication=$this->findPreviousApplication($mMarBanquteHall->application_no);
                $mMarBanquteHall->valid_from = $previousApplication->valid_upto;
                $mMarBanquteHall->valid_upto = date("Y-m-d ",strtotime("+1 Years -1 days", $previousApplication->valid_upto));
            }
            $mMarBanquteHall->save();
            $renewal_id = $mMarBanquteHall->last_renewal_id;

            // Renewal Table Updation
            $mMarBanquteHallRenewal = MarBanquteHallRenewal::find($renewal_id);
            $mMarBanquteHallRenewal->payment_status = 1;
            $mMarBanquteHallRenewal->payment_id =  $pay_id;
            $mMarBanquteHallRenewal->payment_date = Carbon::now();
            $mMarBanquteHallRenewal->valid_from = $mMarBanquteHall->valid_from;
            $mMarBanquteHallRenewal->valid_upto = $mMarBanquteHall->valid_upto;
            $mMarBanquteHallRenewal->payment_details = json_encode($payDetails);
            return $mMarBanquteHallRenewal->save();
        }
    }

    
    // Find Previous Payment Date
    public function findPreviousApplication($application_no){
        return $details=MarBanquteHallRenewal::select('valid_upto')
                                    ->where('application_no',$application_no)
                                    ->orderByDesc('id')
                                    ->skip(1)->first();
    }

      /**
     * | Get Application Details For Renew Applications
     */
    public function applicationDetailsForRenew($appId){
        $details=MarBanquteHall::select('mar_banqute_halls.*',
                        'mar_banqute_halls.organization_type as organization_type_id',
                        'mar_banqute_halls.land_deed_type as land_deed_type_id',
                        'mar_banqute_halls.water_supply_type as water_supply_type_id',
                        'mar_banqute_halls.hall_type as hall_type_id',
                        'mar_banqute_halls.electricity_type as electricity_type_id',
                        'mar_banqute_halls.security_type as security_type_id',
                        'ly.string_parameter as license_year_name',
                        'rw.ward_name as resident_ward_name',
                        'ot.string_parameter as organization_type_name',
                        'ldt.string_parameter as land_deed_type_name',
                        'ldt.string_parameter as water_supply_type_name',
                        'ht.string_parameter as hall_type_name',
                        'et.string_parameter as electricity_type_name',
                        'st.string_parameter as security_type_name',
                        'pw.ward_name as permanent_ward_name',
                        'ulb.ulb_name',
                        )
                        ->leftJoin('ref_adv_paramstrings as ly','ly.id','=',DB::raw('mar_banqute_halls.license_year::int'))
                        ->leftJoin('ulb_ward_masters as rw','rw.id','=',DB::raw('mar_banqute_halls.entity_ward_id::int'))
                        ->leftJoin('ref_adv_paramstrings as ot','ot.id','=',DB::raw('mar_banqute_halls.organization_type::int'))
                        ->leftJoin('ref_adv_paramstrings as ldt','ldt.id','=',DB::raw('mar_banqute_halls.land_deed_type::int'))
                        ->leftJoin('ref_adv_paramstrings as ht','ht.id','=',DB::raw('mar_banqute_halls.hall_type::int'))
                        ->leftJoin('ref_adv_paramstrings as wt','wt.id','=',DB::raw('mar_banqute_halls.water_supply_type::int'))
                        ->leftJoin('ref_adv_paramstrings as et','et.id','=',DB::raw('mar_banqute_halls.electricity_type::int'))
                        ->leftJoin('ref_adv_paramstrings as st','st.id','=',DB::raw('mar_banqute_halls.security_type::int'))
                        ->leftJoin('ulb_ward_masters as ew','ew.id','=','mar_banqute_halls.entity_ward_id')
                        ->leftJoin('ulb_ward_masters as pw','pw.id','=','mar_banqute_halls.permanent_ward_id')
                        ->leftJoin('ulb_masters as ulb','ulb.id','=','mar_banqute_halls.ulb_id')
                        ->where('mar_banqute_halls.id',$appId)->first();
        if(!empty($details)){
            $mWfActiveDocument = new WfActiveDocument();
            $documents = $mWfActiveDocument->uploadDocumentsViewById($appId, $details->workflow_id);
            $details['documents']=$documents;
        }
        return $details;
    }
}
