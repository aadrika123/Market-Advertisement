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
            $details['expiry_date']=date('Y-m-d', strtotime($details['payment_date']."+ 5 Years"));
            $warning_date=date('Y-m-d', strtotime($details['payment_date']."+ 5 Years -1 months"));
            // $details['expiry_date'] = date('Y-m-d', strtotime($details['payment_date'] . "+ 1 months"));
            // $warning_date = date('Y-m-d', strtotime($details['payment_date'] . "+ 15 days"));
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

    /**
     * | Make Agency Dashboard
     */
    // public function agencyDashboard($citizenId)
    // {
    //     //Approved Application
    //     $data['approvedAppl'] = AdvAgency::select('*')
    //         ->where(['payment_status' => 1, 'citizen_id' => $citizenId])
    //         ->get()
    //         ->groupBy(function ($date) {
    //             return Carbon::parse($date->created_at)->format('MY'); // grouping by months
    //         });
    //     $allApproved = collect();
    //     $data['countApprovedAppl'] = $data['approvedAppl']->map(function ($item, $key) use ($allApproved) {
    //         $allApproved->push($item->count());
    //         return $data[$key] = $item->count();
    //     });
    //     $data['countApprovedAppl']['totalApproved'] = $allApproved->sum();

    //     // Unpaid Application
    //     $data['unpaideAppl'] = AdvAgency::select('*')
    //         ->where(['payment_status' => 0, 'citizen_id' => $citizenId])
    //         ->get()
    //         ->groupBy(function ($date) {
    //             return Carbon::parse($date->created_at)->format('MY'); // grouping by months
    //         });
    //     $allUnpaid = collect();
    //     $data['countUnpaideAppl'] = $data['unpaideAppl']->map(function ($item, $key) use ($allUnpaid) {
    //         $allUnpaid->push($item->count());
    //         return $data[$key] = $item->count();
    //     });
    //     $data['countUnpaideAppl']['totalUnpaid'] = $allUnpaid->sum();


    //     //pending Application
    //     $data['pendindAppl'] = AdvActiveAgency::select('*')
    //         ->where(['citizen_id' => $citizenId])
    //         ->get()
    //         ->groupBy(function ($date) {
    //             return Carbon::parse($date->created_at)->format('MY'); // grouping by months
    //         });
    //     $allPending = collect();
    //     $data['countPendindAppl'] = $data['pendindAppl']->map(function ($item, $key) use ($allPending) {
    //         $allPending->push($item->count());
    //         return $data[$key] = $item->count();
    //     });
    //     $data['countPendindAppl']['totalPending'] = $allPending->sum();


    //     // Rejected Application
    //     $data['rejectAppl'] = AdvRejectedAgency::select('*')
    //         ->where(['citizen_id' => $citizenId])
    //         ->get()
    //         ->groupBy(function ($date) {
    //             return Carbon::parse($date->created_at)->format('MY'); // grouping by months
    //         });
    //     $allRejected = collect();
    //     $data['countRejectAppl'] = $data['rejectAppl']->map(function ($item, $key) use ($allRejected) {
    //         $allRejected->push($item->count());
    //         return $data[$key] = $item->count();
    //     });
    //     $data['countRejectAppl']['totalRejected'] = $allPending->sum();
    //     return $data;
    // }

    public function getPaymentDetails($paymentId)
    {
       return $details = AdvAgency::select('payment_amount','payment_id','payment_date','address','entity_name')
            ->where('payment_id', $paymentId)
            ->first();
        // return json_decode($details->payment_details);
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
                $mAdvAgency->valid_upto = Carbon::now()->addYears(1)->subDay(1);
            }else{
                $previousApplication=$this->findPreviousApplication($mAdvAgency->application_no);
                $mAdvAgency->valid_from = $previousApplication->valid_upto;
                $mAdvAgency->valid_upto = Carbon::createFromFormat('Y-m-d', $previousApplication->valid_upto)->addYears(5)->subDay(1);
            }
            $mAdvAgency->save();
            $renewal_id = $mAdvAgency->last_renewal_id;

            // Renewal Table Updation
            $mAdvAgencyRenewal = AdvAgencyRenewal::find($renewal_id);
            $mAdvAgencyRenewal->payment_status = 1;
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

    
    // Find Previous Payment Date
    public function findPreviousApplication($application_no){
        return $details=AdvAgencyRenewal::select('valid_upto')
                                    ->where('application_no',$application_no)
                                    ->orderByDesc('id')
                                    ->skip(1)->first();
    }
}
