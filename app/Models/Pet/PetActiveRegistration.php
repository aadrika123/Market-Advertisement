<?php

namespace App\Models\Pet;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class PetActiveRegistration extends Model
{
    use HasFactory;

    /**
     * | Save pet Registration 
     * | Application data saving
     */
    public function saveRegistration($req, $user)
    {
        $userType = Config::get("pet.REF_USER_TYPE");
        $mPetActiveRegistration = new PetActiveRegistration();

        $mPetActiveRegistration->renewal                = $req->isRenewal ?? 0;
        $mPetActiveRegistration->registration_id        = $req->registrationId ?? null;

        $mPetActiveRegistration->application_no         = $req->applicationNo;
        $mPetActiveRegistration->address                = $req->address;

        $mPetActiveRegistration->workflow_id            = $req->workflowId;
        $mPetActiveRegistration->initiator_role_id      = $req->initiatorRoleId;
        $mPetActiveRegistration->finisher_role_id       = $req->finisherRoleId;
        $mPetActiveRegistration->ip_address             = $req->ip();
        $mPetActiveRegistration->ulb_id                 = $req->ulbId;

        $mPetActiveRegistration->application_type       = $req->applicationType;                    // type new or renewal
        $mPetActiveRegistration->occurrence_type_id     = $req->petFrom;
        $mPetActiveRegistration->apply_through          = $req->applyThrough;                       // holding or saf
        $mPetActiveRegistration->owner_type             = $req->ownerCategory;

        $mPetActiveRegistration->created_at             = Carbon::now();
        $mPetActiveRegistration->application_apply_date = Carbon::now();

        $mPetActiveRegistration->holding_no             = $req->holdingNo ?? null;
        $mPetActiveRegistration->saf_no                 = $req->safNo ?? null;

        $mPetActiveRegistration->user_type              = $user->user_type;
        switch ($user->user_type) {
            case ($userType['1']):
                $mPetActiveRegistration->apply_mode = "ONLINE";
                $mPetActiveRegistration->citizen_id = $user->id;
                break;
            default:
                $mPetActiveRegistration->apply_mode = $user->user_type;
                $mPetActiveRegistration->user_id    = $req->userId;
                break;
        }
        $mPetActiveRegistration->save();
        return [
            "id" => $mPetActiveRegistration->id
        ];
    }
}
