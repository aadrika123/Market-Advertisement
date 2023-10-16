<?php

namespace App\Models\Pet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
            ->where('pet_approved_registrations.id', $registrationId)
            ->update([
                "status" => 2
            ]);
    }

    /**
     * | Get the approved application details by id
     */
    public function getApplictionByRegId($id)
    {
        return PetApprovedRegistration::select(
            "pet_approved_registrations.id AS approveId",
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
            ->join('pet_approve_applicants', 'pet_approve_applicants.application_id', 'pet_approved_registrations.application_id')
            ->where('pet_approved_registrations.application_id', $id)
            ->where('pet_approved_registrations.status', 1);
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
    public function getApproveAppById($id)
    {
        return PetApprovedRegistration::where('id', $id)
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
        return PetActiveRegistration::select(
            'pet_approved_registrations.id',
            'pet_approved_registrations.application_no',
            'pet_approved_registrations.application_type',
            'pet_approved_registrations.payment_status',
            'pet_approved_registrations.application_apply_date',
            'pet_approved_registrations.doc_upload_status',
            'pet_approved_registrations.renewal',
            'pet_approve_applicants.mobile_no',
            'pet_approve_applicants.applicant_name',
        )
            ->join('pet_approve_applicants', 'pet_approve_applicants.application_id', 'pet_approved_registrations.application_id')
            ->where('pet_approved_registrations.' . $key, 'LIKE', '%' . $refNo . '%')
            ->where('pet_approved_registrations.status', 1)
            ->where('pet_approved_registrations.ulb_id', authUser($req)->ulb_id)
            ->orderByDesc('pet_approved_registrations.id');
    }
}
