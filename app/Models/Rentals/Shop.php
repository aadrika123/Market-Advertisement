<?php

namespace App\Models\Rentals;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Shop extends Model
{
  use HasFactory;
  protected $guarded = [];
  protected $table = 'mar_shops';

  public function getGroupById($id)
  {
    return Shop::select(
      '*',
      DB::raw("
        CASE 
        WHEN status = '0' THEN 'Deactivated'  
        WHEN status = '1' THEN 'Active'
      END as status,
      TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
      TO_CHAR(created_at,'HH12:MI:SS AM') as time
        ")
    )
      ->where('id', $id)
      ->first();
  }


  public function retrieveAll()
  {
    return Shop::select(
      '*',
      DB::raw("
        CASE 
        WHEN status = '0' THEN 'Deactivated'  
        WHEN status = '1' THEN 'Active'
      END as status,
      TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
      TO_CHAR(created_at,'HH12:MI:SS AM') as time
        ")
    )
      ->orderBy('id', 'desc')
      ->get();
  }


  public function retrieveActive()
  {
    return Shop::select(
      '*',
      DB::raw("
        CASE 
        WHEN status = '0' THEN 'Deactivated'  
        WHEN status = '1' THEN 'Active'
      END as status,
      TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
      TO_CHAR(created_at,'HH12:MI:SS AM') as time
        ")
    )
      ->where('status', 1)
      ->orderBy('id', 'desc')
      ->get();
  }

  // Delete
  // public function delete($id) {
  //   $siblingData = DB::table('mar_shops')
  //        ->where('id', $id)
  //        ->update([
  //             'stauts' => "0"
  //         ]);
  // }
}
