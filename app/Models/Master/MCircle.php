<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MCircle extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'm_circle';
    /**
     * | Get Circle List By ULB Id
     */
    public function getCircleNameByUlbId($circleName, $ulbId)
    {
        return MCircle::select('*')
            ->where('ulb_id', $ulbId)
            ->where('circle_name', $circleName)
            ->get();
    }

    /**
     * | Get Circle By ULB Id
     */
    public function getCircleByUlbId($ulbId)
    {
        return MCircle::select('*')
            ->where('ulb_id', $ulbId)
            ->get();
    }

    /**
     * | Get All Active Circle
     */
    public function getAllActive()
    {
        return MCircle::select('*')
            ->where('is_active', 1)
            ->get();
    }
}
