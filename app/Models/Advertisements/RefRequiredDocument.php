<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefRequiredDocument extends Model
{
    use HasFactory;

    public function getDocsByDocCode($moduldId, $docCode)
    {
        return RefRequiredDocument::select('requirements')
            ->where('module_id', $moduldId)
            ->where('code', $docCode)
            ->first();
    }

    public function listDocument($advtModuleId){
        return RefRequiredDocument::select('requirements','module_id','code')
        ->where('module_id', $advtModuleId)
        ->get();

    }

}
