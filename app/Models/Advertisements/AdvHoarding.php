<?php

namespace App\Models\Advertisements;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdvHoarding extends Model
{
    use HasFactory;

    /**
     * | All Approve liST
     */
    public function allApproveList(){
        return AdvHoarding::select(
                'id',
                'application_no',
                'license_no',
                'application_date',
                'payment_status',
                'payment_amount',
                'approve_date',
                'citizen_id',
                'valid_upto',
                'user_id',
                'is_archived',
                'is_blacklist',
            )
            ->orderByDesc('id')
            ->get();
    }
    
    /**
     * | Get Application Approve List by Role Ids
     */
    public function listApproved($citizenId,$usertype)
    {
        $allApproveList=$this->allApproveList();
         foreach($allApproveList as $key => $list){
            $current_date=Carbon::now()->format('Y-m-d');
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
        // return $allApproveList;
        if($usertype == 'Citizen'){  
            return collect($allApproveList)->where('citizen_id',$citizenId)->where('payment_status','1')->where('is_archived',false)->where('is_blacklist',false)->values();
        }else{
            return collect($allApproveList)->where('payment_status','1')->where('is_archived',false)->where('is_blacklist',false)->values();
        }
    }

      /**
     * | Get Application Approve List by Role Ids
     */
    public function getRenewActiveApplications($citizenId,$usertype)
    {
       $allApproveList=$this->allApproveList();
        if($usertype == 'Citizen'){
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
            $list=collect($allApproveList)->where('citizen_id',$citizenId)->where('payment_status','1')->values();
        }else{
            $list=collect($allApproveList)->where('payment_status','1')->values();
        }
        // return $list;
        $renewList=array();
        foreach($list as $k => $val){
            $currentDate=carbon::now()->format('Y-m-d');
            $notifyDate=carbon::parse($val['valid_upto'])->subDays(30)->format('Y-m-d');
            if($notifyDate <= $currentDate){
                $renewList[]=$val;
            }
        }
        return $renewList;
    }

      /**
     * | Get Application Approve List by Role Ids
     */
    public function listUnpaid($citizenId,$usertype)
    {
        $allApproveList=$this->allApproveList();
        if($usertype == 'Citizen'){
            return collect($allApproveList->where('citizen_id',$citizenId)->where('payment_status','0'))->values();
        }else{
            return collect($allApproveList->where('payment_status',0))->values();
        }
    }

       /**
     * | Get Application Approve List by Role Ids
     */
    public function listJskApprovedLicenseApplication($userId)
    {
        return AdvHoarding::where('user_id', $userId)
            ->select(
                'id',
                'id',
                'application_no',
                'application_date',
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
        return AdvHoarding::where('id', $id)
            ->select(
                'id',
                'license_no',
                'application_date',
                'payment_status',
                'payment_amount',
                'approve_date',
                'property_type',
                'ulb_id',
                'workflow_id',
            )
            ->first();
    }

     /**
     * | Make Agency Dashboard
     */
    public function agencyDashboard($citizenId)
    {
        //Approved Application
            $data['approvedAppl'] = AdvHoarding::select('*')
            ->where(['payment_status' => 1, 'citizen_id' => $citizenId])
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('MY'); // grouping by months
            });
        $allApproved = collect();
        $data['countApprovedAppl'] = $data['approvedAppl']->map(function ($item, $key) use ($allApproved) {
            $allApproved->push($item->count());
            return $data[$key] = $item->count();
        });
        $data['countApprovedAppl']['totalApproved'] = $allApproved->sum();

        // Unpaid Application
        $data['unpaideAppl'] = AdvHoarding::select('*')
            ->where(['payment_status' => 0, 'citizen_id' => $citizenId])
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('MY'); // grouping by months
            });
        $allUnpaid = collect();
        $data['countUnpaideAppl'] = $data['unpaideAppl']->map(function ($item, $key) use ($allUnpaid) {
            $allUnpaid->push($item->count());
            return $data[$key] = $item->count();
        });
        $data['countUnpaideAppl']['totalUnpaid'] = $allUnpaid->sum();


        //pending Application
        $data['pendindAppl'] = AdvActiveHoarding::select('*')
            ->where(['citizen_id' => $citizenId])
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('MY'); // grouping by months
            });
        $allPending = collect();
        $data['countPendindAppl'] = $data['pendindAppl']->map(function ($item, $key) use ($allPending) {
            $allPending->push($item->count());
            return $data[$key] = $item->count();
        });
        $data['countPendindAppl']['totalPending'] = $allPending->sum();


        // Rejected Application
        $data['rejectAppl'] = AdvRejectedHoarding::select('*')
            ->where(['citizen_id' => $citizenId])
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('MY'); // grouping by months
            });
        $allRejected = collect();
        $data['countRejectAppl'] = $data['rejectAppl']->map(function ($item, $key) use ($allRejected) {
            $allRejected->push($item->count());
            return $data[$key] = $item->count();
        });
        $data['countRejectAppl']['totalRejected'] = $allRejected->sum();

        $data['ulb_id'] = AdvAgency::select('ulb_id')->where(['citizen_id' => $citizenId])->first()->ulb_id;
        // $data['ulbId']=;
        return $data;
    }

    public function getPaymentDetails($paymentId){
        $details=AdvHoarding::select('payment_details')
        ->where('payment_id', $paymentId)
        ->first();
       return json_decode($details->payment_details);
    }

    public function paymentByCash($req){

        if ($req->status == '1') {
            // Self Agency License Table Update
            $mAdvHoarding = AdvHoarding::find($req->applicationId);        // Application ID
            $mAdvHoarding->payment_status = $req->status;
            $pay_id=$mAdvHoarding->payment_id = "Cash-$req->applicationId/".time();
            // $mAdvCheckDtls->remarks = $req->remarks;
            $mAdvHoarding->payment_date = Carbon::now();

            $payDetails=array('paymentMode'=>'Cash','id'=>$req->applicationId,'amount'=>$mAdvHoarding->payment_amount,'workflowId'=>$mAdvHoarding->workflow_id,'userId'=>$mAdvHoarding->citizen_id,'ulbId'=>$mAdvHoarding->ulb_id,'transDate'=>Carbon::now(),'paymentId'=>$pay_id);

            $mAdvHoarding->payment_details = json_encode($payDetails);
            if($mAdvHoarding->renew_no==NULL){
                $mAdvHoarding->valid_from = Carbon::now();
                $mAdvHoarding->valid_upto = Carbon::now()->addYears(1)->subDay(1);
            }else{
                $previousApplication=$this->findPreviousApplication($mAdvHoarding->application_no);
                $mAdvHoarding->valid_from = date("Y-m-d ",strtotime("+1 Years -1 days", $previousApplication->Payment_date));
                $mAdvHoarding->valid_upto = date("Y-m-d ",strtotime("+2 Years -1 days", $previousApplication->Payment_date));
            }
            $mAdvHoarding->save();
            $renewal_id = $mAdvHoarding->last_renewal_id;

            // Agency License Renewal Table Updation
            $mAdvHoardingRenewal = AdvHoardingRenewal::find($renewal_id);
            $mAdvHoardingRenewal->payment_status = 1;
            $mAdvHoardingRenewal->payment_id =  $pay_id;
            $mAdvHoardingRenewal->payment_date = Carbon::now();
            $mAdvHoardingRenewal->payment_details = json_encode($payDetails);;
            return $mAdvHoardingRenewal->save();
        }
    }

    
    // Find Previous Payment Date
    public function findPreviousApplication($application_no){
        return $details=AdvHoarding::select('payment_date')
                                    ->where('application_no',$application_no)
                                    ->orderByDesc('id')
                                    ->skip(1)->first();
    }
    
    public function applicationDetailsForRenew($appId){
        $details=AdvHoarding::select('adv_agency_licenses.license_year',
                                            'adv_agency_licenses.display_location as location',
                                            'adv_agency_licenses.workflow_id',
                                            'adv_agency_licenses.longitude',
                                            'adv_agency_licenses.latitude',
                                            'adv_agency_licenses.length',
                                            'adv_agency_licenses.width',
                                            'adv_agency_licenses.display_area as area',
                                            'adv_agency_licenses.display_land_mark as landmark',
                                            'adv_agency_licenses.indicate_facing as facing',
                                            'adv_agency_licenses.property_type',
                                            'adv_agency_licenses.property_owner_name',
                                            'adv_agency_licenses.property_owner_address',
                                            'adv_agency_licenses.property_owner_city',
                                            'adv_agency_licenses.property_owner_mobile_no',
                                            'adv_agency_licenses.property_owner_whatsapp_no',
                                            'adv_agency_licenses.typology as typology_id',
                                            'adv_agency_licenses.license_year as license_year_id',
                                            'adv_agency_licenses.illumination',
                                            'adv_agency_licenses.material',
                                            'adv_agency_licenses.ward_id',
                                            'adv_agency_licenses.zone_id',
                                            'ly.string_parameter as license_year',
                                            // 'ill.string_parameter as illumination_name',
                                            'typo.descriptions as typology_name',
                                            'w.ward_name',
                                            'ulb.ulb_name',
                                            )
                                            ->leftJoin('adv_typology_mstrs as typo','typo.id','=',DB::raw('adv_agency_licenses.typology::int'))
                                            ->leftJoin('ref_adv_paramstrings as ly','ly.id','=',DB::raw('adv_agency_licenses.license_year::int'))
                                            // ->leftJoin('ref_adv_paramstrings as ill','ill.id','=',DB::raw('adv_agency_licenses.illumination::int'))
                                            ->leftJoin('ulb_ward_masters as w','w.id','=','adv_agency_licenses.ward_id')
                                            ->leftJoin('ulb_masters as ulb','ulb.id','=','adv_agency_licenses.ulb_id')
                                            ->where('adv_agency_licenses.id',$appId)->first();
        if(!empty($details)){
            $mWfActiveDocument = new WfActiveDocument();
            $documents = $mWfActiveDocument->uploadDocumentsViewById($appId, $details->workflow_id);
            $details['documents']=$documents;
        }
        return $details;
    }

        /**
     * | Get Application Approve List by Role Ids
     */
    public function listExpiredHording($citizenId,$usertype)
    {
         $allApproveList=$this->allApproveList();
         $current_date=carbon::now()->format('Y-m-d');
        if($usertype == 'Citizen'){
            return collect($allApproveList)->where('citizen_id',$citizenId)->where('payment_status','1')->where('valid_upto','<',$current_date)->values();
        }else{
            return collect($allApproveList)->where('payment_status','1')->values();
        }
    }


    /**
     * | Get Application Archieved List by Role Ids
     */
    public function listHordingArchived($citizenId,$usertype)
    {
         $allApproveList=$this->allApproveList();
        
        if($usertype == 'Citizen'){
            return collect($allApproveList)->where('citizen_id',$citizenId)->where('payment_status','1')->where('is_archived',true)->values();
        }else{
            return collect($allApproveList)->where('payment_status','1')->where('is_archived',true)->values();
        }
    }

    
    /**
     * | Get Application Blacklist List by Role Ids
     */
    public function listHordingBlacklist($citizenId,$usertype)
    {
         $allApproveList=$this->allApproveList();
        
        if($usertype == 'Citizen'){
            return collect($allApproveList)->where('citizen_id',$citizenId)->where('payment_status','1')->where('is_blacklist',true)->values();
        }else{
            return collect($allApproveList)->where('payment_status','1')->where('is_blacklist',true)->values();
        }
    }
}

