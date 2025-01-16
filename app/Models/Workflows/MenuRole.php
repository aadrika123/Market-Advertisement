<?php

namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuRole extends Model
{
    use HasFactory;

    protected $connection = 'pgsql_masters';
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    //role by id
    public function getMenuRole()
    {
        return  MenuRole::select('id', 'menu_role_name')
            ->where('menu_role_name', 'Adv Agency')
            ->where('is_suspended', false)
            ->first();
    }
}
