<?php

namespace App\Models\Pet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetDailycollectiondetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function store($req)
    {
        return PetDailycollectiondetail::create($req);
    }
}
