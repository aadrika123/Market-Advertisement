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
    protected $guarded = [];

    /**
     * |
     */
    public function saveRegistration($request)
    {
        $mMarriageActiveRegistration = new MarriageActiveRegistration();
        $registrationDtl = $mMarriageActiveRegistration->create($request);
        return [
            "id" => $registrationDtl->id,
            "applicationNo" => $registrationDtl->application_no
        ];
    }

    /**
     * | Update Owner Basic Details
     */
    public function edit($req, $id)
    {
        $req = new Request($req);
        $mMarriageActiveRegistration = MarriageActiveRegistration::find($id);

        $reqs = [
            'owner_name' => strtoupper($req->ownerName),
            'guardian_name' => strtoupper($req->guardianName),
            'relation_type' => $req->relation,
            'mobile_no' => $req->mobileNo,
            'aadhar_no' => $req->aadhar,
            'pan_no' => $req->pan,
            'email' => $req->email,
        ];

        $mMarriageActiveRegistration->update($reqs);
    }

    /**
     * | 
     */
    public function getApplicationById($id)
    {
        return MarriageActiveRegistration::find($id);
    }
}
