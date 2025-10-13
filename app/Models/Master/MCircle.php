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
            ->whereRaw('LOWER(circle_name) = (?)', [strtolower($circleName)])
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
     * | List ULB wise Circle
     */
    public function getListCircleByUlbId($ulbId)
    {
        return MCircle::select('*')
            ->where('ulb_id', $ulbId)
            ->orderByDesc('id');
    }

    /**
     * | Get Circle Details By Id
     */
    public function getCircleDetails($id)
    {
        return MCircle::select('id', 'circle_name')
            ->where('id', $id)
            ->first();
    }
    public function createCirlce($req)
    {
        $circleData = [
            'ulb_id' => $req->auth['ulb_id'] ?? 0,
            'circle_name' => $req->circleName,
            'created_by' => auth()->user()->id,
        ];
        $createCircle = MCircle::create($circleData);
        return $createCircle;
    }
}
