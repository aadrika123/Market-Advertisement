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


    /**
     * | Get Rejected Application by applicationId
     */
    public function getPetRejectedApplicationById($registrationId)
    {
        return PetRejectedRegistration::select(
            DB::raw("REPLACE(pet_rejected_registrations.application_type, '_', ' ') AS ref_application_type"),
            'pet_rejected_registrations.id as rejected_id',
            'pet_rejected_details.id as ref_pet_id',
            'pet_rejected_applicants.id as ref_applicant_id',
            'pet_rejected_registrations.*',
            'pet_rejected_details.*',
            'pet_rejected_applicants.*',
            'pet_rejected_registrations.status as registrationStatus',
            'pet_rejected_details.status as petStatus',
            'pet_rejected_applicants.status as applicantsStatus',
            'ulb_ward_masters.ward_name',
            'ulb_masters.ulb_name',
            'm_pet_occurrence_types.occurrence_types',
            DB::raw("CASE 
            WHEN pet_rejected_registrations.apply_through = '1' THEN 'Holding'
            WHEN pet_rejected_registrations.apply_through = '2' THEN 'Saf'
            END AS apply_through_name"),
            DB::raw("CASE 
            WHEN pet_rejected_details.sex = '1' THEN 'Male'
            WHEN pet_rejected_details.sex = '2' THEN 'Female'
            END AS ref_gender"),
            DB::raw("CASE 
            WHEN pet_rejected_details.pet_type = '1' THEN 'Dog'
            WHEN pet_rejected_details.pet_type = '2' THEN 'Cat'
            END AS ref_pet_type"),
        )
            ->join('ulb_masters', 'ulb_masters.id', 'pet_rejected_registrations.ulb_id')
            ->leftjoin('ulb_ward_masters', 'ulb_ward_masters.id', 'pet_rejected_registrations.ward_id')
            ->join('m_pet_occurrence_types', 'm_pet_occurrence_types.id', 'pet_rejected_registrations.occurrence_type_id')
            ->join('pet_rejected_applicants', 'pet_rejected_applicants.application_id', 'pet_rejected_registrations.application_id')
            ->join('pet_rejected_details', 'pet_rejected_details.application_id', 'pet_rejected_registrations.application_id')
            ->where('pet_rejected_registrations.id', $registrationId);
    }
}
