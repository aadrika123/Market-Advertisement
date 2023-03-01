<?php

namespace App\Models\Advertisements;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvAgencyLicense extends Model
{
    use HasFactory;

    /**
     * | All Approve liST
     */
    public function allApproveList(){
        return AdvAgencyLicense::select(
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
                'user_id'
            )
            ->orderByDesc('temp_id')
            ->get();
    }
    
    /**
     * | Get Application Approve List by Role Ids
     */
    public function listApprovedLicense($citizenId,$usertype)
    {
         $allApproveList=$this->allApproveList();
        if($usertype == 'Citizen'){
            return collect($allApproveList)->where('citizen_id',$citizenId)->where('payment_status','1')->values();
        }else{
            return collect($allApproveList)->where('payment_status','1')->values();
        }
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

}
