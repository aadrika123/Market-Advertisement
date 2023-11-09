<?php

namespace App\Models\Pet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetRejectedApplicant extends Model
{
    use HasFactory;


    /**
     * | Get active application details according to related details 
     */
    public function getRelatedRejectedApplicationDetails($req, $key, $refNo)
    {
        return PetRejectedApplicant::select(
            'pet_rejected_registrations.id',
            'pet_rejected_registrations.application_no',
            'pet_rejected_registrations.application_type',
            'pet_rejected_registrations.payment_status',
            'pet_rejected_registrations.application_apply_date',
            'pet_rejected_registrations.doc_upload_status',
            'pet_rejected_registrations.renewal',
            'pet_rejected_registrations.registration_id',
            'pet_rejected_applicants.mobile_no',
            'pet_rejected_applicants.applicant_name',
        )
            ->join('pet_rejected_registrations', 'pet_rejected_registrations.application_id', 'pet_rejected_applicants.application_id')
            ->where('pet_rejected_applicants.' . $key, 'ILIKE', '%' . $refNo . '%')
            ->where('pet_rejected_registrations.status', 1)
            ->where('pet_rejected_registrations.ulb_id', authUser($req)->ulb_id)
            ->orderByDesc('pet_rejected_registrations.id');
    }
}
