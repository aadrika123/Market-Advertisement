<?php

namespace App\Models\Marriage;

use App\Http\Requests\Marriage\ReqApplyMarriage;
use App\Models\Workflows\WorkflowTrack;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarriageActiveRegistration extends Model
{
    use HasFactory;
}
