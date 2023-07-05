<?php

namespace App\Models\Pet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetActiveDetail extends Model
{
    use HasFactory;

    /**
     * | Save pet active Details 
     */
    public function savePetDetails($req, $applicationId)
    {
        $mPetActiveDetail = new PetActiveDetail();
        $mPetActiveDetail->application_id           = $applicationId;
        $mPetActiveDetail->sex                      = $req->petGender;
        $mPetActiveDetail->identification_mark      = $req->petIdentity;
        $mPetActiveDetail->breed                    = $req->breed;
        $mPetActiveDetail->color                    = $req->color;
        $mPetActiveDetail->vet_doctor_name          = $req->doctorName;
        $mPetActiveDetail->doctor_registration_no   = $req->doctorRegNo;
        $mPetActiveDetail->rabies_vac_date          = $req->dateOfRabies;
        $mPetActiveDetail->leptospirosis_vac_date   = $req->dateOfLepVaccine;
        $mPetActiveDetail->dob                      = $req->petBirthDate;
        $mPetActiveDetail->pet_name                 = $req->petName;
        $mPetActiveDetail->pet_type                 = $req->petType;
        $mPetActiveDetail->save();
    }


    /**
     * | Get Pet details by applicationId
     */
    public function getPetDetailsByApplicationId($applicationId)
    {
        return PetActiveDetail::where('application_id', $applicationId)
            ->where('status', 1)
            ->orderByDesc('id');
    }

    /**
     * | Update the pet details according to id
     */
    public function updatePetDetails($req)
    {
        $refRequest = $this->metaReq($req);
        $req->update($refRequest);
    }

    /**
     * | Make a meta request
     */
    public function metaReq($req)
    {
        return [
            "sex"                       => $req->petGender,
            "identification_mark"       => $req->petIdentity,
            "breed"                     => $req->breed,
            "color"                     => $req->color,
            "vet_doctor_name"           => $req->doctorName,
            "doctor_registration_no"    => $req->doctorRegNo,
            "rabies_vac_date"           => $req->dateOfRabies,
            "leptospirosis_vac_date"    => $req->dateOfLepVaccine,
            "dob"                       => $req->petBirthDate,
            "pet_name"                  => $req->petName,
            "pet_type"                  => $req->petType
        ];
    }
}
