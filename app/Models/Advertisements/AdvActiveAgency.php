<?php

namespace App\Models\Advertisements;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvActiveAgency extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $_applicationDate;

    // Initializing construction
    public function __construct()
    {
        $this->_applicationDate = Carbon::now()->format('Y-m-d');
    }

    /**
     * | Meta Data Uses to Store data in DB
     */
    public function metaReqs($req)
    {
        $metaReqs = [
            'application_date' => $this->_applicationDate,
            'entity_type' => $req->entityType,
            'entity_name' => $req->entityName,
            'address' => $req->address,
            'mobile_no' => $req->mobileNo,
            'telephone' => $req->officeTelephone,
            'fax' => $req->fax,
            'email' => $req->email,
            'pan_no' => $req->panNo,
            'gst_no' => $req->gstNo,
            'blacklisted' => $req->blacklisted,
            'pending_court_case' => $req->pendingCourtCase,
            'pending_amount' => $req->pendingAmount,
            'citizen_id' => $req->citizenId,
            'ulb_id' => $req->ulbId
        ];
        return $metaReqs;
    }

    /**
     * | Store function to apply(1)
     * | @param request 
     */
    public function store($req)
    {
        $directors = $req->directors;
        $metaReqs = $this->metaReqs($req);
        $mClientIpAddress = ['ip_address' => getClientIpAddress()];
        $applicationNo = ['application_no' => "AGENCY-" . random_int(100000, 999999)];
        $metaReqs = array_merge($metaReqs, $applicationNo, $mClientIpAddress);     // Final Merged Meta Requests
        $agencyDirector = new AdvActiveAgencydirector();
        $agencyId = AdvActiveAgency::create($metaReqs)->id;

        // Store Director Details
        collect($directors)->map(function ($director) use ($agencyId, $agencyDirector) {
            $agencyDirector->store($director, $agencyId);       // Model function to store
        });

        return $applicationNo;
    }
}
