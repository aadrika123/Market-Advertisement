<?php

namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuRoleusermap extends Model
{
    use HasFactory;

    protected $connection = 'pgsql_masters';
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

      /**
     * | Create Role Map
     */
    public function addRoleUser($req)
    {
        $data = new MenuRoleusermap;
        $data->menu_role_id = $req->menuRoleId;
        $data->user_id      = $req->userId;
        $data->is_suspended = $req->isSuspended ?? false;
        $data->save();
    }
}
