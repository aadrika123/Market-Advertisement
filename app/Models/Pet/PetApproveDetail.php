<?php

namespace App\Models\Pet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetApproveDetail extends Model
{
    use HasFactory;

    /**
     * | Get the application pet details by application id 
     */
    public function getPetDetailsById($applicationId)
    {
        return PetApproveDetail::where('application_id', $applicationId)
            ->where('status', 1);
    }
}
