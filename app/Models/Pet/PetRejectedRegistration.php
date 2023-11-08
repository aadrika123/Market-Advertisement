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
}
