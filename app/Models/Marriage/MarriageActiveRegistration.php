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

    /**
     * |
     */
    public function saveRegistration($request, $user)
    {
        $mMarriageActiveRegistration = new MarriageActiveRegistration();
        $mMarriageActiveRegistration->ulb_id                        = $request->ulbId;
        $mMarriageActiveRegistration->bride_name                    = $request->brideName;
        $mMarriageActiveRegistration->bride_dob                     = $request->brideDob;
        $mMarriageActiveRegistration->bride_age                     = $request->brideAge;
        $mMarriageActiveRegistration->bride_nationality             = $request->brideNationality;
        $mMarriageActiveRegistration->bride_religion                = $request->brideReligion;
        $mMarriageActiveRegistration->bride_mobile                  = $request->brideMobile;
        $mMarriageActiveRegistration->bride_aadhar_no               = $request->brideAadharNo;
        $mMarriageActiveRegistration->bride_email                   = $request->brideEmail;
        $mMarriageActiveRegistration->bride_passport_no             = $request->bridePassportNo;
        $mMarriageActiveRegistration->bride_residential_address     = $request->brideResidentialAddress;
        $mMarriageActiveRegistration->bride_martial_status          = $request->brideMartialStatus;
        $mMarriageActiveRegistration->bride_father_name             = $request->brideFatherName;
        $mMarriageActiveRegistration->bride_father_aadhar_no        = $request->brideFatherAadharNo;
        $mMarriageActiveRegistration->bride_mother_name             = $request->brideMotherName;
        $mMarriageActiveRegistration->bride_mother_aadhar_no        = $request->brideMotherAadharNo;
        $mMarriageActiveRegistration->bride_guardian_name           = $request->brideGuardianName;
        $mMarriageActiveRegistration->bride_guardian_aadhar_no      = $request->brideGuardianAadharNo;
        $mMarriageActiveRegistration->groom_name                    = $request->groomName;
        $mMarriageActiveRegistration->groom_dob                     = $request->groomDob;
        $mMarriageActiveRegistration->groom_age                     = $request->groomAge;
        $mMarriageActiveRegistration->groom_aadhar_no               = $request->groomAadharNo;
        $mMarriageActiveRegistration->groom_nationality             = $request->groomNationality;
        $mMarriageActiveRegistration->groom_religion                = $request->groomReligion;
        $mMarriageActiveRegistration->groom_mobile                  = $request->groomMobile;
        $mMarriageActiveRegistration->groom_passport_no             = $request->groomPassportNo;
        $mMarriageActiveRegistration->groom_residential_address     = $request->groomResidentialAddress;
        $mMarriageActiveRegistration->groom_martial_status          = $request->groomMartialStatus;
        $mMarriageActiveRegistration->groom_father_name             = $request->groomFatherName;
        $mMarriageActiveRegistration->groom_father_aadhar_no        = $request->groomFatherAadharNo;
        $mMarriageActiveRegistration->groom_mother_name             = $request->groomMotherName;
        $mMarriageActiveRegistration->groom_mother_aadhar_no        = $request->groomMotherAadharNo;
        $mMarriageActiveRegistration->groom_guardian_name           = $request->groomGuardianName;
        $mMarriageActiveRegistration->groom_guardian_aadhar_no      = $request->groomGuardianAadharNo;
        $mMarriageActiveRegistration->marriage_date                 = $request->marriageDate;
        $mMarriageActiveRegistration->marriage_place                = $request->marriagePlace;
        $mMarriageActiveRegistration->witness1_name                 = $request->witness1Name;
        $mMarriageActiveRegistration->witness1_mobile_no            = $request->witness1MobileNo;
        $mMarriageActiveRegistration->witness1_residential_address  = $request->witness1ResidentialAddress;
        $mMarriageActiveRegistration->witness2_name                 = $request->witness2Name;
        $mMarriageActiveRegistration->witness2_residential_address  = $request->witness2ResidentialAddress;
        $mMarriageActiveRegistration->witness2_mobile_no            = $request->witness2MobileNo;
        $mMarriageActiveRegistration->witness3_name                 = $request->witness3Name;
        $mMarriageActiveRegistration->witness3_mobile_no            = $request->witnessMobileNo;
        $mMarriageActiveRegistration->witness3_residential_address  = $request->witness3ResidentialAddress;
        $mMarriageActiveRegistration->appointment_date              = $request->appointmentDate;
        $mMarriageActiveRegistration->marriage_registration_date    = $request->marriageRegistrationDate;
        $mMarriageActiveRegistration->registrar_id                  = $request->registrarId;
        $mMarriageActiveRegistration->user_id                       = $request->userId;
        $mMarriageActiveRegistration->citizen_id                    = $request->citizenId;
        $mMarriageActiveRegistration->application_no                = $request->applicationNo;
        $mMarriageActiveRegistration->is_bpl                        = $request->bpl;
        // $mMarriageActiveRegistration->initiator_role_id             = $request->initiatorRoleId[0];
        // $mMarriageActiveRegistration->finisher_role_id              = $request->finisherRoleId;
        // $mMarriageActiveRegistration->workflow_id                   = $request->workflowId;
        // $mMarriageActiveRegistration->doc_upload_status             = $request->docUploadStatus;
        // $mMarriageActiveRegistration->payment_status                = $request->paymentStatus;
        $mMarriageActiveRegistration->payment_amount                = 100;
        // $mMarriageActiveRegistration->penalty_amount                = $request->penaltyAmount;
        // $mMarriageActiveRegistration->parked                        = $request->parked;
        // $mMarriageActiveRegistration->status                        = $request->status;
        $mMarriageActiveRegistration->save();

        return [
            "id" => $mMarriageActiveRegistration->id,
            "applicationNo" => $request->applicationNo
        ];
    }

    /**
     * | 
     */
    public function getApplicationById($id)
    {
        return MarriageActiveRegistration::find($id);
    }
}
