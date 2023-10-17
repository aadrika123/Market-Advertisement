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

    /**
     * | Update the approved pet details 
     */
    public function updateApprovePetStatus($id, $refReq)
    {
        PetApproveDetail::where('id', $id)
            ->update($refReq);
    }
}
