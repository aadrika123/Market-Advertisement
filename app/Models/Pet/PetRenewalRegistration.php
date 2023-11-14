<?php

namespace App\Models\Pet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
