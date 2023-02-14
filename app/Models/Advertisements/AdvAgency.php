<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class AdvAgency extends Model
{
    use HasFactory;

    public function getagencyDetails($id){
        return AdvAgency::where('citizen_id', $id)->first();
    }



    /**
     * Summary of allApproveList
     * @return void
     */
    public function allApproveList(){
        return AdvAgency::select(
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
    public function listApproved($citizenId,$userType)
    {
        $allApproveList = $this->allApproveList();
        if($userType=='Citizen'){
            return collect($allApproveList->where('citizen_id', $citizenId))->values();;
        }else{
            return collect($allApproveList)->values();;
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
                'temp_id',
                'application_no',
                'application_date',
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
    public function getApplicationDetailsForPayment($id)
    {
        return AdvAgency::where('id', $id)
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


    /**
     * | Check Login User is Asgency or Not
     */
    public function checkAgency($citizenId){
       $details=AdvAgency::where('citizen_id',$citizenId)->select(
                '*' )
            ->first();

           $details=json_decode(json_encode($details), true);
           $temp_id=$details['temp_id'];
           // Convert Std Class to Array
            $directors = DB::table('adv_active_agencydirectors')
                ->select(
                    'adv_active_agencydirectors.*',
                    DB::raw("CONCAT(adv_active_agencydirectors.relative_path,'/',adv_active_agencydirectors.doc_name) as document_path")
                )
                ->where('agency_id', $temp_id)
                ->get();
            $details['directors'] = remove_null($directors->toArray());
            return $details;
    }
 
    /**
     * | Make Agency Dashboard
     */
    public function agencyDashboard($citizenId){

        //Approved Application
       $data['approvedAppl']=AdvAgency::select('*')
        ->where(['payment_status'=>1,'citizen_id'=>$citizenId])
        ->get()
        ->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('MY'); // grouping by months
        });
        $data['countApprovedAppl']=$data['approvedAppl']->map(function ($item, $key) {
            return $data[$key]=$item->count();
        });

        // Unpaid Application
        $data['unpaideAppl']=AdvAgency::select('*')
        ->where(['payment_status'=>0,'citizen_id'=>$citizenId])
        ->get()
        ->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('MY'); // grouping by months
        });
        $data['countUnpaideAppl']=$data['unpaideAppl']->map(function ($item, $key) {
            return $data[$key]=$item->count();
        });

        //pending Application
        $data['pendindAppl']=AdvActiveAgency::select('*')
        ->where(['citizen_id'=>$citizenId])
        ->get()
        ->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('MY'); // grouping by months
        });
        $data['countPendindAppl']=$data['pendindAppl']->map(function ($item, $key) {
            return $data[$key]=$item->count();
        });

        // Rejected Application
        $data['rejectAppl']=AdvRejectedAgency::select('*')
        ->where(['citizen_id'=>$citizenId])
        ->get()
        ->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('MY'); // grouping by months
        });
        $data['countRejectAppl']=$data['rejectAppl']->map(function ($item, $key) {
            return $data[$key]=$item->count();
        });
        return $data;
    }
}
