<?php

namespace App\Models\Pet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetActiveApplicant extends Model
{
    use HasFactory;

    /**
     * | Save the pet's applicants details  
     */
    public function saveApplicants($req, $applicaionId)
    {
        $mPetActiveApplicant = new PetActiveApplicant();
        $mPetActiveApplicant->mobile_no         = $req->mobileNo;
        $mPetActiveApplicant->email             = $req->email;
        $mPetActiveApplicant->pan_no            = $req->panNo;
        $mPetActiveApplicant->applicant_name    = $req->applicantName;
        $mPetActiveApplicant->uid               = $req->uid ?? null;
        $mPetActiveApplicant->telephone         = $req->telephone;
        $mPetActiveApplicant->voters_card_no    = $req->voterCard;
        $mPetActiveApplicant->owner_type        = $req->ownerCategory;
        $mPetActiveApplicant->application_id    = $applicaionId;
        $mPetActiveApplicant->save();
    }

    /**
     * | Get Details of owner by ApplicationId
     */
    public function getApplicationDetails($applicationId)
    {
        return PetActiveApplicant::where('application_id', $applicationId)
            ->where('status', 1)
            ->orderByDesc('id');
    }

    /**
     * | Get active application details according to related details 
     */
    public function getRelatedApplicationDetails($req, $key, $refNo)
    {
        return PetActiveApplicant::select(
            'pet_active_registrations.id',
            'pet_active_registrations.application_no',
            'pet_active_registrations.application_type',
            'pet_active_registrations.payment_status',
            'pet_active_registrations.application_apply_date',
            'pet_active_registrations.doc_upload_status',
            'pet_active_registrations.renewal',
            'pet_active_applicants.mobile_no',
            'pet_active_applicants.applicant_name',
        )
            ->join('pet_active_registrations', 'pet_active_registrations.id', 'pet_active_applicants.application_id')
            ->where('pet_active_applicants.' . $key, 'LIKE', '%' . $refNo . '%')
            ->where('pet_active_registrations.status', 1)
            ->where('pet_active_registrations.ulb_id', authUser($req)->ulb_id)
            ->orderByDesc('pet_active_registrations.id');
    }
}
