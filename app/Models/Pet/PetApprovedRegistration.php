<?php

namespace App\Models\Pet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PetApprovedRegistration extends Model
{
    use HasFactory;

    /**
     * | Get Approve registration by Id
     */
    public function getApproveRegById($registerId)
    {
        return PetApprovedRegistration::select('pet_active_registrations.*')
            ->join("pet_active_details", "pet_active_details.application_id", "pet_active_registrations.id")
            ->join("pet_active_applicants", "pet_active_applicants.application_id", "pet_active_registrations.id")
            ->where('pet_active_registrations.id', $registerId)
            ->where('pet_active_details.status', 1)
            ->where('pet_active_applicants.status', 1)
            ->where('pet_active_registrations.status', 1);
    }

    /**
     * | Deactivate the previous data for new Entry 
        | (CAUTION)
     */
    public function deactivateOldRegistration($registrationId)
    {
        PetApprovedRegistration::join('pet_approve_applicants', 'pet_approve_applicants.application_id', 'pet_approved_registrations.application_id')
            ->join('pet_approve_details', 'pet_approve_details.application_id', 'pet_approved_registrations.application_id')
            ->where('pet_approved_registrations.registration_id', $registrationId)
            ->update([
                "pet_approved_registrations.status" => 2
            ]);
    }

    /**
     * | Get the approved application details by id
     */
    public function getApplictionByRegId($id)
    {
        return PetApprovedRegistration::select(
            "pet_approved_registrations.id AS approveId",
            "pet_approved_registrations.owner_type as ref_owner_type",
            "pet_approve_applicants.id AS applicantId",
            "pet_approve_details.id AS petId",
            "pet_approved_registrations.*",
            "pet_approve_applicants.*",
            "pet_approve_details.*"
        )
            ->join('pet_approve_applicants', 'pet_approve_applicants.application_id', 'pet_approved_registrations.application_id')
            ->join('pet_approve_details', 'pet_approve_details.application_id', 'pet_approved_registrations.application_id')
            ->where('pet_approved_registrations.id', $id)
            ->where('pet_approved_registrations.status', 1);
    }

    /**
     * | Update the related status for Approved appications
     */
    public function updateRelatedStatus($id, $refReq)
    {
        PetApprovedRegistration::where('id', $id)
            ->where('status', 1)
            ->update($refReq);
    }

    /**
     * | Get application details according to id
     */
    public function getApproveDetailById($id)
    {
        return PetApprovedRegistration::join('ulb_masters', 'ulb_masters.id', '=', 'pet_approved_registrations.ulb_id')
            ->join('pet_approve_details', 'pet_approve_details.application_id', 'pet_approved_registrations.application_id')
            ->join('pet_approve_applicants', 'pet_approve_applicants.application_id', 'pet_approved_registrations.application_id')
            ->where('pet_approved_registrations.application_id', $id)
            ->where('pet_approved_registrations.status', '<>', 0);
    }

    /**
     * | Get the approve application details using 
     */
    public function getApproveAppByAppId($id)
    {
        return PetApprovedRegistration::where('pet_approved_registrations.application_id', $id)
            ->orderByDesc('id');
    }

    /**
     * | Get the approve application details using 
     */
    public function getApproveAppByRegId($id)
    {
        return PetApprovedRegistration::where('registration_id', $id)
            ->orderByDesc('id');
    }

    /**
     * | Update the Approve application Detials
     */
    public function updateApproveAppStatus($id, $refDetails)
    {
        PetApprovedRegistration::where('id', $id)
            ->update($refDetails);
    }


    /**
     * | Get Approved Application details according to the related details in request 
     */
    public function getApprovedApplicationDetails($req, $key, $refNo)
    {
        return PetApprovedRegistration::select(
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
            ->join('pet_approve_applicants', 'pet_approve_applicants.application_id', 'pet_approved_registrations.application_id')
            ->where('pet_approved_registrations.' . $key, 'LIKE', '%' . $refNo . '%')
            ->where('pet_approved_registrations.status', 1)
            ->where('pet_approved_registrations.ulb_id', authUser($req)->ulb_id)
            ->orderByDesc('pet_approved_registrations.id');
    }


    /**
     * | Get Approved Application by applicationId
     */
    public function getPetApprovedApplicationById($applicationId)
    {
        return PetApprovedRegistration::select(
            DB::raw("REPLACE(pet_approved_registrations.application_type, '_', ' ') AS ref_application_type"),
            'pet_approved_registrations.id as approve_id',
            'pet_approve_details.id as ref_pet_id',
            'pet_approve_applicants.id as ref_applicant_id',
            'pet_approved_registrations.*',
            'pet_approve_details.*',
            'pet_approve_applicants.*',
            'pet_approved_registrations.status as registrationStatus',
            'pet_approve_details.status as petStatus',
            'pet_approve_applicants.status as applicantsStatus',
            'ulb_ward_masters.ward_name',
            'ulb_masters.ulb_name',
            'm_pet_occurrence_types.occurrence_types',
            DB::raw("CASE 
            WHEN pet_approved_registrations.apply_through = '1' THEN 'Holding'
            WHEN pet_approved_registrations.apply_through = '2' THEN 'Saf'
            END AS apply_through_name"),
            DB::raw("CASE 
            WHEN pet_approve_details.sex = '1' THEN 'Male'
            WHEN pet_approve_details.sex = '2' THEN 'Female'
            END AS ref_gender"),
            DB::raw("CASE 
            WHEN pet_approve_details.pet_type = '1' THEN 'Dog'
            WHEN pet_approve_details.pet_type = '2' THEN 'Cat'
            END AS ref_pet_type"),
        )
            ->join('ulb_masters', 'ulb_masters.id', 'pet_approved_registrations.ulb_id')
            ->leftjoin('ulb_ward_masters', 'ulb_ward_masters.id', 'pet_approved_registrations.ward_id')
            ->join('m_pet_occurrence_types', 'm_pet_occurrence_types.id', 'pet_approved_registrations.occurrence_type_id')
            ->join('pet_approve_applicants', 'pet_approve_applicants.application_id', 'pet_approved_registrations.application_id')
            ->join('pet_approve_details', 'pet_approve_details.application_id', 'pet_approved_registrations.application_id')
            ->where('pet_approved_registrations.id', $applicationId);
    }


    /**
     * | Get Approved Application by applicationId
     */
    public function getPetApprovedApplicationRegistrationId($registrationId)
    {
        return PetApprovedRegistration::select(
            DB::raw("REPLACE(pet_approved_registrations.application_type, '_', ' ') AS ref_application_type"),
            'pet_approved_registrations.id as approve_id',
            'pet_approve_details.id as ref_pet_id',
            'pet_approve_applicants.id as ref_applicant_id',
            'pet_approved_registrations.*',
            'pet_approve_details.*',
            'pet_approve_applicants.*',
            'pet_approved_registrations.status as registrationStatus',
            'pet_approve_details.status as petStatus',
            DB::raw("TO_CHAR(pet_approve_details.dob, 'DD-MM-YYYY') as dob"),
            'pet_approve_applicants.status as applicantsStatus',
            'ulb_ward_masters.ward_name',
            'ulb_masters.ulb_name',
            'm_pet_occurrence_types.occurrence_types',
            DB::raw("CASE 
            WHEN pet_approved_registrations.apply_through = '1' THEN 'Holding'
            WHEN pet_approved_registrations.apply_through = '2' THEN 'Saf'
            END AS apply_through_name"),
            DB::raw("CASE 
            WHEN pet_approve_details.sex = '1' THEN 'Male'
            WHEN pet_approve_details.sex = '2' THEN 'Female'
            END AS ref_gender"),
            DB::raw("CASE 
            WHEN pet_approve_details.pet_type = '1' THEN 'Dog'
            WHEN pet_approve_details.pet_type = '2' THEN 'Cat'
            END AS ref_pet_type"),
        )
            ->join('ulb_masters', 'ulb_masters.id', 'pet_approved_registrations.ulb_id')
            ->leftjoin('ulb_ward_masters', 'ulb_ward_masters.id', 'pet_approved_registrations.ward_id')
            ->join('m_pet_occurrence_types', 'm_pet_occurrence_types.id', 'pet_approved_registrations.occurrence_type_id')
            ->join('pet_approve_applicants', 'pet_approve_applicants.application_id', 'pet_approved_registrations.application_id')
            ->join('pet_approve_details', 'pet_approve_details.application_id', 'pet_approved_registrations.application_id')
            ->where('pet_approved_registrations.registration_id', $registrationId);
    }



    /**
     * | Get all details according to key 
     */
    public function getAllApprovdApplicationDetails()
    {
        return DB::table('pet_approved_registrations')
            ->leftJoin('wf_roles', 'wf_roles.id', 'pet_approved_registrations.current_role_id')
            ->join('pet_approve_applicants', 'pet_approve_applicants.application_id', 'pet_approved_registrations.application_id')
            ->join('pet_approve_details', 'pet_approve_details.application_id', 'pet_approved_registrations.application_id');
    }
}
