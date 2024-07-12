<?php

namespace App\Models\Pet;

use Carbon\Carbon;
use Exception;
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
            ->join('pet_rejected_applicants', 'pet_rejected_applicants.application_id', 'pet_rejected_registrations.id')
            ->join('pet_rejected_details', 'pet_rejected_details.application_id', 'pet_rejected_registrations.id');
    }

    /**
     * | Get the rejected application details using 
     */
    public function getRejectedAppByAppId($id)
    {
        return PetRejectedRegistration::where('pet_rejected_registrations.application_id', $id)
            ->orderByDesc('id');
    }


    /**
     * | Get Rejected Application details according to the related details in request 
     */
    public function getRejectedApplicationDetails($req, $key, $refNo)
    {
        return PetRejectedRegistration::select(
            'pet_rejected_registrations.id',
            'pet_rejected_registrations.application_no',
            'pet_rejected_registrations.application_type',
            'pet_rejected_registrations.payment_status',
            'pet_rejected_registrations.application_apply_date',
            'pet_rejected_registrations.doc_upload_status',
            'pet_rejected_registrations.renewal',
            'pet_rejected_registrations.registration_id',
            'pet_rejected_applicants.mobile_no',
            'pet_rejected_applicants.applicant_name',
        )
            ->join('pet_rejected_applicants', 'pet_rejected_applicants.application_id', 'pet_rejected_registrations.application_id')
            ->where('pet_rejected_registrations.' . $key, 'LIKE', '%' . $refNo . '%')
            ->where('pet_rejected_registrations.status', 1)
            ->where('pet_rejected_registrations.ulb_id', authUser($req)->ulb_id)
            ->orderByDesc('pet_rejected_registrations.id');
    }


    /**
     * | Get Rejected Application by applicationId
     */
    public function getPetRejectedApplicationById($registrationId)
    {
        return PetRejectedRegistration::select(
            DB::raw("REPLACE(pet_rejected_registrations.application_type, '_', ' ') AS ref_application_type"),
            'pet_rejected_registrations.id as rejected_id',
            'pet_rejected_details.id as ref_pet_id',
            'pet_rejected_applicants.id as ref_applicant_id',
            'pet_rejected_registrations.*',
            'pet_rejected_details.*',
            'pet_rejected_applicants.*',
            'pet_rejected_registrations.status as registrationStatus',
            'pet_rejected_details.status as petStatus',
            'pet_rejected_applicants.status as applicantsStatus',
            'ulb_ward_masters.ward_name',
            'ulb_masters.ulb_name',
            'm_pet_occurrence_types.occurrence_types',
            DB::raw("CASE 
            WHEN pet_rejected_registrations.apply_through = '1' THEN 'Holding'
            WHEN pet_rejected_registrations.apply_through = '2' THEN 'Saf'
            END AS apply_through_name"),
            DB::raw("CASE 
            WHEN pet_rejected_details.sex = '1' THEN 'Male'
            WHEN pet_rejected_details.sex = '2' THEN 'Female'
            END AS ref_gender"),
            DB::raw("CASE 
            WHEN pet_rejected_details.pet_type = '1' THEN 'Dog'
            WHEN pet_rejected_details.pet_type = '2' THEN 'Cat'
            END AS ref_pet_type"),
        )
            ->join('ulb_masters', 'ulb_masters.id', 'pet_rejected_registrations.ulb_id')
            ->leftjoin('ulb_ward_masters', 'ulb_ward_masters.id', 'pet_rejected_registrations.ward_id')
            ->leftjoin('m_pet_occurrence_types', 'm_pet_occurrence_types.id', 'pet_rejected_registrations.occurrence_type_id')
            ->join('pet_rejected_applicants', 'pet_rejected_applicants.application_id', 'pet_rejected_registrations.id')
            ->join('pet_rejected_details', 'pet_rejected_details.id', 'pet_rejected_registrations.application_id')
            ->where('pet_rejected_registrations.id', $registrationId);
    }


    /**
     * | Get pet rejected application details by id
     */
    public function getRejectedApplicationById($id)
    {
        return PetRejectedRegistration::join('ulb_masters', 'ulb_masters.id', '=', 'pet_rejected_registrations.ulb_id')
            ->join('pet_rejected_details', 'pet_rejected_details.application_id', 'pet_rejected_registrations.id')
            ->join('pet_rejected_applicants', 'pet_rejected_applicants.application_id', 'pet_rejected_registrations.id')
            ->where('pet_rejected_registrations.id', $id)
            ->where('pet_rejected_registrations.status',1);
    }

    public function rejectedApplication($request)
    {
        $user = authUser($request);
        $ulbId = $user->ulb_id;
        $key        = $request->filterBy;
        $perPage = $request->perPage ?: 10;
        $parameter = $request->parameter;
        $dateFrom = $request->dateFrom ?: Carbon::now()->format('Y-m-d');
        $dateUpto = $request->dateUpto ?: Carbon::now()->format('Y-m-d');
        $rejectedApplication =  DB::table('pet_rejected_registrations')
            ->select(
                'pet_rejected_registrations.id',
                'pet_rejected_registrations.holding_no',
                'pet_rejected_registrations.saf_no',
                'pet_rejected_registrations.application_no',
                DB::raw("REPLACE(pet_rejected_registrations.application_type, '_', ' ') AS application_type"),
                'pet_rejected_registrations.payment_status',
                DB::raw("TO_CHAR(pet_rejected_registrations.application_apply_date, 'DD-MM-YYYY') as application_apply_date"),
                'pet_rejected_registrations.doc_upload_status',
                'pet_rejected_registrations.renewal',
                'pet_rejected_registrations.registration_id',
                'pet_rejected_registrations.ward_id',
                'pet_rejected_applicants.mobile_no',
                'pet_rejected_applicants.applicant_name'
            )
            ->join('pet_rejected_applicants', 'pet_rejected_registrations.application_id', 'pet_rejected_applicants.application_id')
            ->where('pet_rejected_registrations.status', 1)
            ->where('pet_rejected_registrations.ulb_id', $ulbId)
            ->whereBetween('pet_rejected_registrations.application_apply_date', [$dateFrom, $dateUpto])
            ->orderByDesc('pet_rejected_registrations.id');


        if ($key && $request->parameter) {
            $msg = "Pet rejected appliction details according to $key!";
            switch ($key) {
                case ("mobileNo"):
                    $rejectedApplication = $rejectedApplication->where('pet_rejected_applicants.mobile_no', 'LIKE', "%$parameter%");
                    break;
                case ("applicationNo"):
                    $rejectedApplication = $rejectedApplication->where('pet_rejected_registrations.application_no', 'LIKE', "%$parameter%");
                    break;
                case ("applicantName"):
                    $rejectedApplication = $rejectedApplication->where('pet_rejected_applicants.applicant_name', 'LIKE', "%$parameter%");
                    break;
                case ("holdingNo"):
                    $rejectedApplication = $rejectedApplication->where('pet_rejected_registrations.holding_no', 'LIKE', "%$parameter%");
                    break;
                case ("safNo"):
                    $rejectedApplication = $rejectedApplication->where('pet_rejected_registrations.saf_no', 'LIKE', "%$parameter%");
                    break;
                default:
                    throw new Exception("Invalid Data");
            }
        }
        if ($request->wardNo) {
            $rejectedApplication->where('pet_rejected_registrations.ward_id', $request->wardNo);
        }
        $data = $rejectedApplication;
        if ($perPage) {
            $data = $data->paginate($perPage);
        } else {
            $data = $data->get();
        }
        return [
            'current_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->currentPage() : 1,
            'last_page' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->lastPage() : 1,
            'data' => $data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->items() : $data,
            'total' => $data->total()
        ];
    }
}
