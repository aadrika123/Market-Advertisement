<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvTypologyMstr extends Model
{
    use HasFactory;

    public function getTypologyList(){
        return AdvTypologyMstr::where('status', '1')
            ->select(
                'id',
                'type',
                'type_inner as subtype',
                'descriptions'
            )
            ->get();
    }
}
