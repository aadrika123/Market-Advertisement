<?php

namespace App\Models\Pet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetDailycollection extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function store($req)
    {
        return PetDailycollection::create($req);
    }
}
