<?php

namespace App\Models\Pet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PetRenewalRegistration extends Model
{
    use HasFactory;

    /**
     * | Get pet renewal application details by id
     */
    public function getRenewalApplicationById($id)
    {
        return PetRenewalRegistration::join('ulb_masters', 'ulb_masters.id', '=', 'pet_renewal_registrations.ulb_id')
            ->join('pet_renewal_applicants', 'pet_renewal_applicants.application_id', 'pet_renewal_registrations.application_id')
            ->where('pet_renewal_registrations.application_id', $id)
            ->where('pet_renewal_registrations.status', '<>', 0);
    }

    /**
     * | Get pet renewal application list by registration id
     */
    public function getRenewalApplicationByRegId($regId)
    {
        return PetRenewalRegistration::join('ulb_masters', 'ulb_masters.id', '=', 'pet_renewal_registrations.ulb_id')
            ->join('pet_renewal_applicants', 'pet_renewal_applicants.application_id', 'pet_renewal_registrations.application_id')
            ->where('pet_renewal_registrations.registration_id', $regId)
            ->where('pet_renewal_registrations.status', '<>', 0);
    }

    /**
     * | Get Renewal Application details by applicationId
     */
    public function getPetRenewalApplicationById($registrationId)
    {
        return PetRenewalRegistration::select(
            DB::raw("REPLACE(pet_renewal_registrations.application_type, '_', ' ') AS ref_application_type"),
            'pet_renewal_registrations.id as rejected_id',
            'pet_renewal_details.id as ref_pet_id',
            'pet_renewal_applicants.id as ref_applicant_id',
            'pet_renewal_registrations.*',
            'pet_renewal_details.*',
            'pet_renewal_applicants.*',
            'pet_renewal_registrations.status as registrationStatus',
            'pet_renewal_details.status as petStatus',
            'pet_renewal_applicants.status as applicantsStatus',
            'ulb_ward_masters.ward_name',
            'ulb_masters.ulb_name',
            'm_pet_occurrence_types.occurrence_types',
            DB::raw("CASE 
            WHEN pet_renewal_registrations.apply_through = '1' THEN 'Holding'
            WHEN pet_renewal_registrations.apply_through = '2' THEN 'Saf'
            END AS apply_through_name"),
            DB::raw("CASE 
            WHEN pet_renewal_details.sex = '1' THEN 'Male'
            WHEN pet_renewal_details.sex = '2' THEN 'Female'
            END AS ref_gender"),
            DB::raw("CASE 
            WHEN pet_renewal_details.pet_type = '1' THEN 'Dog'
            WHEN pet_renewal_details.pet_type = '2' THEN 'Cat'
            END AS ref_pet_type"),
        )
            ->join('ulb_masters', 'ulb_masters.id', 'pet_renewal_registrations.ulb_id')
            ->leftjoin('ulb_ward_masters', 'ulb_ward_masters.id', 'pet_renewal_registrations.ward_id')
            ->join('m_pet_occurrence_types', 'm_pet_occurrence_types.id', 'pet_renewal_registrations.occurrence_type_id')
            ->join('pet_renewal_applicants', 'pet_renewal_applicants.application_id', 'pet_renewal_registrations.application_id')
            ->join('pet_renewal_details', 'pet_renewal_details.application_id', 'pet_renewal_registrations.application_id')
            ->where('pet_renewal_registrations.id', $registrationId);
    }
}
