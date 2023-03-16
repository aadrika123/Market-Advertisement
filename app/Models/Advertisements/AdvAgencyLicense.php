<?php

namespace App\Models\Advertisements;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdvAgencyLicense extends Model
{
    use HasFactory;

    /**
     * | All Approve liST
     */
    public function allApproveList(){
        return AdvAgencyLicense::select(
                'id',
                'application_no',
                'license_no',
                'application_date',
                // 'entity_address',
                // 'old_application_no',
                'payment_status',
                'payment_amount',
                'approve_date',
                'citizen_id',
                'valid_upto',
                'user_id'
            )
            ->orderByDesc('id')
            ->get();
    }
    
    /**
     * | Get Application Approve List by Role Ids
     */
    public function listApprovedLicense($citizenId,$usertype)
    {
         $allApproveList=$this->allApproveList();
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
        if($usertype == 'Citizen'){
            return collect($allApproveList)->where('citizen_id',$citizenId)->where('payment_status','1')->values();
        }else{
            return collect($allApproveList)->where('payment_status','1')->values();
        }
    }

      /**
     * | Get Application Approve List by Role Ids
     */
    public function getRenewActiveApplications($citizenId,$usertype)
    {
       $allApproveList=$this->allApproveList();
        if($usertype == 'Citizen'){
            
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
    public function listUnpaidLicenses($citizenId,$usertype)
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
        return AdvAgencyLicense::where('user_id', $userId)
            ->select(
                'id',
                'temp_id',
                'application_no',
                'application_date',
                'payment_amount',
                'approve_date',
            )
            ->orderByDesc('temp_id')
            ->get();
    }

    
    /**
     * | Get Application Details FOr Payments
     */
    public function getLicenseApplicationDetailsForPayment($id)
    {
        return AdvAgencyLicense::where('id', $id)
            ->select(
                'id',
                'temp_id',
                'application_no',
                'application_date',
                'payment_status',
                'payment_amount',
                'approve_date',
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
            $data['approvedAppl'] = AdvAgencyLicense::select('*')
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
        $data['unpaideAppl'] = AdvAgencyLicense::select('*')
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
        $data['pendindAppl'] = AdvActiveAgencyLicense::select('*')
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
        $data['rejectAppl'] = AdvRejectedAgencyLicense::select('*')
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

    public function getLicensePaymentDetails($paymentId){
        $details=AdvAgencyLicense::select('payment_details')
        ->where('payment_id', $paymentId)
        ->first();
       return json_decode($details->payment_details);
    }

    public function paymentByCash($req){

        if ($req->status == '1') {
            // Self Agency License Table Update
            $mAdvAgencyLicense = AdvAgencyLicense::find($req->applicationId);        // Application ID
            $mAdvAgencyLicense->payment_status = $req->status;
            $pay_id=$mAdvAgencyLicense->payment_id = "Cash-$req->applicationId/".time();
            // $mAdvCheckDtls->remarks = $req->remarks;
            $mAdvAgencyLicense->payment_date = Carbon::now();
            $mAdvAgencyLicense->payment_details = "By Cash";
            if($mAdvAgencyLicense->renew_no==NULL){
                $mAdvAgencyLicense->valid_from = Carbon::now();
                $mAdvAgencyLicense->valid_upto = Carbon::now()->addYears(1)->subDay(1);
            }else{
                $previousApplication=$this->findPreviousApplication($mAdvAgencyLicense->application_no);
                $mAdvAgencyLicense->valid_from = date("Y-m-d ",strtotime("+1 Years -1 days", $previousApplication->Payment_date));
                $mAdvAgencyLicense->valid_upto = date("Y-m-d ",strtotime("+2 Years -1 days", $previousApplication->Payment_date));
            }
            $mAdvAgencyLicense->save();
            $renewal_id = $mAdvAgencyLicense->last_renewal_id;

            // Agency License Renewal Table Updation
            $mAdvAgencyLicenseRenewal = AdvAgencyLicenseRenewal::find($renewal_id);
            $mAdvAgencyLicenseRenewal->payment_status = 1;
            $mAdvAgencyLicenseRenewal->payment_id =  $pay_id;
            $mAdvAgencyLicenseRenewal->payment_date = Carbon::now();
            $mAdvAgencyLicenseRenewal->payment_details = "By Cash";
            return $mAdvAgencyLicenseRenewal->save();
        }
    }

    
    // Find Previous Payment Date
    public function findPreviousApplication($application_no){
        return $details=AdvAgencyLicense::select('payment_date')
                                    ->where('application_no',$application_no)
                                    ->orderByDesc('id')
                                    ->skip(1)->first();
    }
    
    public function applicationDetailsForRenew($appId){
        $details=AdvAgencyLicense::select('adv_agency_licenses.license_year',
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
}
