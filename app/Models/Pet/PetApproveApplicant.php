<?php

namespace App\Models\Pet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetApproveApplicant extends Model
{
    use HasFactory;

    /**
     * | Get approve applicant detial by application id
     */
    public function getApproveApplicant($applicationId)
    {
        return PetApproveApplicant::where('application_id', $applicationId)
            ->where('status', 1);
    }


    /**
     * | Get active application details according to related details 
     */
    public function getRelatedApproveApplicationDetails($req, $key, $refNo)
    {
        return PetApproveApplicant::select(
            'pet_approved_registrations.id',
            'pet_approved_registrations.application_no',
            'pet_approved_registrations.application_type',
            'pet_approved_registrations.payment_status',
            'pet_approved_registrations.application_apply_date',
            'pet_approved_registrations.doc_upload_status',
            'pet_approved_registrations.renewal',
            'pet_approved_registrations.registration_id',
            'pet_approve_applicants.mobile_no',
            'pet_approve_applicants.applicant_name',
        )
            ->join('pet_approved_registrations', 'pet_approved_registrations.application_id', 'pet_approve_applicants.application_id')
            ->where('pet_approve_applicants.' . $key, 'LIKE', '%' . $refNo . '%')
            ->where('pet_approved_registrations.status', 1)
            ->where('pet_approved_registrations.ulb_id', authUser($req)->ulb_id)
            ->orderByDesc('pet_approved_registrations.id');
    }

    /**
     * | Update the approved applicant details 
     */
    public function updateAproveApplicantDetials($id, $refReq)
    {
        PetApproveApplicant::where('id', $id)
            ->update($refReq);
    }
}
