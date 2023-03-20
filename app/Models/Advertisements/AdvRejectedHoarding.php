<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvRejectedHoarding extends Model
{
    use HasFactory;

         
    /**
    * | Get Application Reject List by Role Ids
    */
   public function listRejected($citizenId)
   {
       return AdvRejectedHoarding::where('citizen_id', $citizenId)
           ->select(
               'id',
               'application_no',
               'license_no',
               'application_date',
               'rejected_date',
           )
           ->orderByDesc('id')
           ->get();
   }
   
    /**
    * | Get Application Reject List by Login JSK
    */
   public function listJskRejectedLicenseApplication($userId)
   {
       return AdvRejectedHoarding::where('user_id', $userId)
           ->select(
               'id',
               'application_no',
               'application_date',
               'rejected_date',
           )
           ->orderByDesc('id')
           ->get();
   }
}
