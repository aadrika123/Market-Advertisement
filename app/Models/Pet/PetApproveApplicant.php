<?php

namespace App\Models\Pet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetApproveApplicant extends Model
{
    use HasFactory;

    /**
     * | Get approve applicant detial by application id
     */
    public function getApproveApplicant($applicationId)
    {
        return PetApproveApplicant::where('application_id', $applicationId)
            ->where('status', 1);
    }
}
