<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MCircle extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'm_circle';

    public function getGroupById ($ulbId) {
        return MCircle::select('*')
        ->where('ulb_id', $ulbId)
        ->get();
    }
}
