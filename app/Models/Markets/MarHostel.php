<?php

namespace App\Models\Markets;

use App\Models\Advertisements\WfActiveDocument;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MarHostel extends Model
{
    use HasFactory;

     
    /**
     * Summary of allApproveList
     * @return void
     */
    public function allApproveList()
    {
        return MarHostel::select(
            'id',
            'application_no',
            'application_date',
            'entity_address',
            // 'old_application_no',
            // 'payment_status',
            'payment_amount',
            'approve_date',
            'citizen_id',
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
     * | Get Application Details FOr Payments
     */
    public function getApplicationDetailsForPayment($id)
    {
        return MarHostel::where('id', $id)
            ->select(
                'id',
                'application_no',
                'application_date',
                // 'applicant',
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
            // Hostel Table Update
            $mMarHostel = MarHostel::find($req->applicationId);
            $mMarHostel->payment_status = $req->status;
            $pay_id=$mMarHostel->payment_id = "Cash-$req->applicationId-".time();
            $mMarHostel->payment_date = Carbon::now();

            $payDetails=array('paymentMode'=>'Cash','id'=>$req->applicationId,'amount'=>$mMarHostel->payment_amount,'workflowId'=>$mMarHostel->workflow_id,'userId'=>$mMarHostel->citizen_id,'ulbId'=>$mMarHostel->ulb_id,'transDate'=>Carbon::now(),'paymentId'=>$pay_id);

            $mMarHostel->payment_details = json_encode($payDetails);

            if($mMarHostel->renew_no==NULL){
                $mMarHostel->valid_from = Carbon::now();
                $mMarHostel->valid_upto = Carbon::now()->addYears(1)->subDay(1);
            }else{
                $previousApplication=$this->findPreviousApplication($mMarHostel->application_no);
                $mMarHostel->valid_from = $previousApplication->valid_upto;
                $mMarHostel->valid_upto = date("Y-m-d ",strtotime("+1 Years -1 days", $previousApplication->valid_upto));
            }
            $mMarHostel->save();
            $renewal_id = $mMarHostel->last_renewal_id;

            // Renewal Table Updation
            $mMarHostelRenewal = MarHostelRenewal::find($renewal_id);
            $mMarHostelRenewal->payment_status = 1;
            $mMarHostelRenewal->payment_id =  $pay_id;
            $mMarHostelRenewal->payment_date = Carbon::now();
            $mMarHostelRenewal->valid_from = $mMarHostel->valid_from;
            $mMarHostelRenewal->valid_upto = $mMarHostel->valid_upto;
            $mMarHostelRenewal->payment_details = json_encode($payDetails);
            return $mMarHostelRenewal->save();
        }
    }


     // Find Previous Payment Date
     public function findPreviousApplication($application_no){
        return $details=MarHostelRenewal::select('valid_upto')
                                    ->where('application_no',$application_no)
                                    ->orderByDesc('id')
                                    ->skip(1)->first();
    }


    
    /**
     * | Get Application Details For Renew Applications
     */
    public function applicationDetailsForRenew($appId){
        $details=MarHostel::select('mar_hostels.*',
                        'mar_hostels.hostel_type as hostel_type_id',
                        'mar_hostels.organization_type as organization_type_id',
                        'mar_hostels.land_deed_type as land_deed_type_id',
                        'mar_hostels.mess_type as mess_type_id',
                        'mar_hostels.water_supply_type as water_supply_type_id',
                        'mar_hostels.electricity_type as electricity_type_id',
                        'mar_hostels.security_type as security_type_id',
                        'ly.string_parameter as license_year_name',
                        'rw.ward_name as resident_ward_name',
                        'lt.string_parameter as hostel_type_name',
                        'ot.string_parameter as organization_type_name',
                        'ldt.string_parameter as land_deed_type_name',
                        'mt.string_parameter as mess_type_name',
                        'wt.string_parameter as water_supply_type_name',
                        'et.string_parameter as electricity_type_name',
                        'st.string_parameter as security_type_name',
                        'pw.ward_name as permanent_ward_name',
                        'ulb.ulb_name',
                        )
                        ->leftJoin('ref_adv_paramstrings as ly','ly.id','=',DB::raw('mar_hostels.license_year::int'))
                        ->leftJoin('ulb_ward_masters as rw','rw.id','=',DB::raw('mar_hostels.entity_ward_id::int'))
                        ->leftJoin('ref_adv_paramstrings as lt','lt.id','=',DB::raw('mar_hostels.hostel_type::int'))
                        ->leftJoin('ref_adv_paramstrings as ot','ot.id','=',DB::raw('mar_hostels.organization_type::int'))
                        ->leftJoin('ref_adv_paramstrings as ldt','ldt.id','=',DB::raw('mar_hostels.land_deed_type::int'))
                        ->leftJoin('ref_adv_paramstrings as mt','mt.id','=',DB::raw('mar_hostels.mess_type::int'))
                        ->leftJoin('ref_adv_paramstrings as wt','wt.id','=',DB::raw('mar_hostels.water_supply_type::int'))
                        ->leftJoin('ref_adv_paramstrings as et','et.id','=',DB::raw('mar_hostels.electricity_type::int'))
                        ->leftJoin('ref_adv_paramstrings as st','st.id','=',DB::raw('mar_hostels.security_type::int'))
                        ->leftJoin('ulb_ward_masters as ew','ew.id','=','mar_hostels.entity_ward_id')
                        ->leftJoin('ulb_ward_masters as pw','pw.id','=','mar_hostels.permanent_ward_id')
                        ->leftJoin('ulb_masters as ulb','ulb.id','=','mar_hostels.ulb_id')
                        ->where('mar_hostels.id',$appId)->first();
        if(!empty($details)){
            $mWfActiveDocument = new WfActiveDocument();
            $documents = $mWfActiveDocument->uploadDocumentsViewById($appId, $details->workflow_id);
            $details['documents']=$documents;
        }
        return $details;
    }
}
