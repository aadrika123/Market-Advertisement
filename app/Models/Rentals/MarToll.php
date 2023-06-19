<?php

namespace App\Models\Rentals;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class MarToll extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function retrieveAll()
    {
      return MarToll::select(
        '*',
        DB::raw("
        CASE 
        WHEN status = '0' THEN 'Deactivated'  
        WHEN status = '1' THEN 'Active'
      END as status,
      TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
      TO_CHAR(created_at,'HH12:MI:SS AM') as time
        ") )
        ->orderBy('id', 'desc')
        ->get();
    }

    public function retrieveActive() {
      return MarToll::select(
        '*',
        DB::raw("
        CASE 
        WHEN status = '0' THEN 'Deactivated'  
        WHEN status = '1' THEN 'Active'
      END as status,
      TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
      TO_CHAR(created_at,'HH12:MI:SS AM') as time
        ") )
       ->where('status', 1)
       ->orderBy('id', 'desc')
       ->get();
      }
    
    public function getTollById($id) {
      return MarToll::select(
        '*',
        DB::raw("
        CASE 
        WHEN status = '0' THEN 'Deactivated'  
        WHEN status = '1' THEN 'Active'
      END as status,
      TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
      TO_CHAR(created_at,'HH12:MI:SS AM') as time
        ") )
          ->where('id', $id)
          ->first();
    }
}
