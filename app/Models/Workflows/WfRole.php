<?php

namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WfRole extends Model
{
    use HasFactory;
    protected $connection = 'pgsql_masters';
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    //role by id
    public function getWfRole()
    {
        return  WfRole::select('id', 'role_name')
            ->where('role_name', 'ADVERTISEMENT AGENCY')
            ->where('is_suspended', false)
            ->first();
    }
}
