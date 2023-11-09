<?php

namespace App\Models\Pet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PetRejectedRegistration extends Model
{
    use HasFactory;

    /**
     * | Get all details according to key 
     */
    public function getAllRejectedApplicationDetails()
    {
        return DB::table('pet_rejected_registrations')
            ->leftJoin('wf_roles', 'wf_roles.id', 'pet_rejected_registrations.current_role_id')
            ->join('pet_rejected_applicants', 'pet_rejected_applicants.application_id', 'pet_rejected_registrations.application_id')
            ->join('pet_rejected_details', 'pet_rejected_details.application_id', 'pet_rejected_registrations.application_id');
    }

    /**
     * | Get the rejected application details using 
     */
    public function getRejectedAppByAppId($id)
    {
        return PetRejectedRegistration::where('pet_rejected_registrations.application_id', $id)
            ->orderByDesc('id');
    }


    /**
     * | Get Rejected Application details according to the related details in request 
     */
    public function getRejectedApplicationDetails($req, $key, $refNo)
    {
        return PetRejectedRegistration::select(
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
            ->join('pet_rejected_applicants', 'pet_rejected_applicants.application_id', 'pet_rejected_registrations.application_id')
            ->where('pet_rejected_registrations.' . $key, 'LIKE', '%' . $refNo . '%')
            ->where('pet_rejected_registrations.status', 1)
            ->where('pet_rejected_registrations.ulb_id', authUser($req)->ulb_id)
            ->orderByDesc('pet_rejected_registrations.id');
    }
}
