<?php

namespace App\Models\Rentals;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarDailycollectiondetail extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'mar_dailycollectiondetails';
    public function store($req)
    {
        return MarDailycollectiondetail::create($req);
    }
}
