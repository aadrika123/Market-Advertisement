<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvPrivateland extends Model
{
    use HasFactory;


    /**
  * | Get Application Approve List by Role Ids
  */
 public function approvedList($citizenId)
 {
     return AdvPrivateland::where('citizen_id', $citizenId)
         ->select(
             'id',
             'temp_id',
             'application_no',
             'application_date',
             // 'entity_address',
             // 'old_application_no',
            //  'payment_status',
             'payment_amount',
             'approve_date',
         )
         ->orderByDesc('temp_id')
         ->get();
 }

    /**
  * | Get Application Approve List by Role Ids
  */
 public function jskApprovedList($userId)
 {
     return AdvPrivateland::where('user_id', $userId)
         ->select(
             'id',
             'temp_id',
             'application_no',
             'application_date',
             // 'entity_address',
             // 'old_application_no',
            //  'payment_status',
             'payment_amount',
             'approve_date',
         )
         ->orderByDesc('temp_id')
         ->get();
 }
}
