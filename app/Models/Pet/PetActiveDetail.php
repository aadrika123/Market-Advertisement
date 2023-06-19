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
        $mPetActiveDetail->save();
    }
}
