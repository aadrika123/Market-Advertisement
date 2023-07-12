<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MMarket extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'm_market';

    public function getGroupById($circleId) {
        return MMarket::select('*')
        ->where('circle_id', $circleId)
        ->get();
    }
}
