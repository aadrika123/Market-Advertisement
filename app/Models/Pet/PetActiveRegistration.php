<?php

namespace App\Models\Pet;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class PetActiveRegistration extends Model
{
    use HasFactory;

    /**
     * | Save pet Registration 
     * | Application data saving
     */
    public function saveRegistration($req, $user)
    {
        $userType = Config::get("pet.REF_USER_TYPE");
        $mPetActiveRegistration = new PetActiveRegistration();

        $mPetActiveRegistration->renewal                = $req->isRenewal ?? 0;
        $mPetActiveRegistration->registration_id        = $req->registrationId ?? null;

        $mPetActiveRegistration->application_no         = $req->applicationNo;
        $mPetActiveRegistration->address                = $req->address;

        $mPetActiveRegistration->workflow_id            = $req->workflowId;
        $mPetActiveRegistration->initiator_role_id      = $req->initiatorRoleId;
        $mPetActiveRegistration->finisher_role_id       = $req->finisherRoleId;
        $mPetActiveRegistration->ip_address             = $req->ip();
        $mPetActiveRegistration->ulb_id                 = $req->ulbId;
        $mPetActiveRegistration->ward_id                = $req->ward;

        $mPetActiveRegistration->application_type       = $req->applicationType;                    // type new or renewal
        $mPetActiveRegistration->occurrence_type_id     = $req->petFrom;
        $mPetActiveRegistration->apply_through          = $req->applyThrough;                       // holding or saf
        $mPetActiveRegistration->owner_type             = $req->ownerCategory;
        $mPetActiveRegistration->application_type_id    = $req->applicationTypeId;

        $mPetActiveRegistration->created_at             = Carbon::now();
        $mPetActiveRegistration->application_apply_date = Carbon::now();

        $mPetActiveRegistration->holding_no             = $req->holdingNo ?? null;
        $mPetActiveRegistration->saf_no                 = $req->safNo ?? null;
        $mPetActiveRegistration->pet_type               = $req->petType;
        $mPetActiveRegistration->user_type              = $user->user_type;

        if ($user->user_type == $userType['1']) {
            $mPetActiveRegistration->apply_mode = "ONLINE";                                     // Static
            $mPetActiveRegistration->citizen_id = $user->id;
        } else {
            $mPetActiveRegistration->apply_mode = $user->user_type;
            $mPetActiveRegistration->user_id    = $user->id;
        }

        $mPetActiveRegistration->save();
        return [
            "id" => $mPetActiveRegistration->id,
            "applicationNo" => $req->applicationNo
        ];
    }

    /**
     * | Get Application by applicationId
     */
    public function getPetApplicationById($applicationId)
    {
        return PetActiveRegistration::select(
            'pet_active_registrations.id as ref_application_id',
            DB::raw("REPLACE(pet_active_registrations.application_type, '_', ' ') AS ref_application_type"),
            'pet_active_details.id as ref_pet_id',
            'pet_active_applicants.id as ref_applicant_id',
            'pet_active_registrations.*',
            'pet_active_details.*',
            'pet_active_applicants.*',
            'pet_active_registrations.status as registrationStatus',
            'pet_active_details.status as petStatus',
            'pet_active_applicants.status as applicantsStatus',
            'ulb_ward_masters.ward_name',
            'ulb_masters.ulb_name',
            'm_pet_occurrence_types.occurrence_types',
            DB::raw("CASE 
            WHEN pet_active_registrations.apply_through = '1' THEN 'Holding'
            WHEN pet_active_registrations.apply_through = '2' THEN 'Saf'
            END AS apply_through_name"),
            DB::raw("CASE 
            WHEN pet_active_details.sex = '1' THEN 'Male'
            WHEN pet_active_details.sex = '2' THEN 'Female'
            END AS ref_gender"),
            DB::raw("CASE 
            WHEN pet_active_details.pet_type = '1' THEN 'Dog'
            WHEN pet_active_details.pet_type = '2' THEN 'Cat'
            END AS ref_pet_type"),
            'wf_roles.role_name AS roleName'
        )
            ->join('pet_active_applicants', 'pet_active_applicants.application_id', 'pet_active_registrations.id')
            ->join('pet_active_details', 'pet_active_details.application_id', 'pet_active_registrations.id')
            ->join('m_pet_occurrence_types', 'm_pet_occurrence_types.id', 'pet_active_registrations.occurrence_type_id')
            ->join('ulb_masters', 'ulb_masters.id', '=', 'pet_active_registrations.ulb_id')
            ->leftjoin('ulb_ward_masters', 'ulb_ward_masters.id', 'pet_active_registrations.ward_id')
            ->leftjoin('wf_roles', 'wf_roles.id', 'pet_active_registrations.current_role_id')
            ->where('pet_active_registrations.id', $applicationId)
            ->where('pet_active_registrations.status', 1);
    }

    //written by prity pandey
    public function recentApplication($workflowIds, $roleId, $ulbId)
    {
        $data =  PetActiveRegistration::select(
            'pet_active_registrations.id as ref_application_id',
            DB::raw("REPLACE(pet_active_registrations.application_type, '_', ' ') AS ref_application_type"),
            'pet_active_details.id as ref_pet_id',
            'pet_active_applicants.id as ref_applicant_id',
            'pet_active_registrations.*',
            'pet_active_details.*',
            'pet_active_applicants.*',
            'pet_active_registrations.status as registrationStatus',
            'pet_active_details.status as petStatus',
            'pet_active_applicants.status as applicantsStatus',
            'ulb_ward_masters.ward_name',
            'ulb_masters.ulb_name',
            'm_pet_occurrence_types.occurrence_types',
            DB::raw("CASE 
            WHEN pet_active_registrations.apply_through = '1' THEN 'Holding'
            WHEN pet_active_registrations.apply_through = '2' THEN 'Saf'
            END AS apply_through_name"),
            DB::raw("CASE 
            WHEN pet_active_details.sex = '1' THEN 'Male'
            WHEN pet_active_details.sex = '2' THEN 'Female'
            END AS ref_gender"),
            DB::raw("CASE 
            WHEN pet_active_details.pet_type = '1' THEN 'Dog'
            WHEN pet_active_details.pet_type = '2' THEN 'Cat'
            END AS ref_pet_type"),
            'wf_roles.role_name AS roleName'
        )
            ->join('pet_active_applicants', 'pet_active_applicants.application_id', 'pet_active_registrations.id')
            ->join('pet_active_details', 'pet_active_details.application_id', 'pet_active_registrations.id')
            ->join('m_pet_occurrence_types', 'm_pet_occurrence_types.id', 'pet_active_registrations.occurrence_type_id')
            ->join('ulb_masters', 'ulb_masters.id', '=', 'pet_active_registrations.ulb_id')
            ->leftjoin('ulb_ward_masters', 'ulb_ward_masters.id', 'pet_active_registrations.ward_id')
            ->leftjoin('wf_roles', 'wf_roles.id', 'pet_active_registrations.current_role_id')
            ->whereIn('workflow_id', $workflowIds)
            ->where('pet_active_registrations.ulb_id', $ulbId)
            ->whereIn('current_role_id', $roleId)
            ->where('pet_active_registrations.status', 1)
            ->orderBydesc('pet_active_registrations.id')
            ->take(10)
            ->get();
        $application = collect($data)->map(function ($value) {
            $value['applyDate'] = (Carbon::parse($value['applydate']))->format('d-m-Y');
            return $value;
        });
        return $application;
    }

    public function recentApplicationJsk($userId, $ulbId)
    {
        $data =  PetActiveRegistration::select(
            'pet_active_registrations.id as ref_application_id',
            DB::raw("REPLACE(pet_active_registrations.application_type, '_', ' ') AS ref_application_type"),
            'pet_active_details.id as ref_pet_id',
            'pet_active_applicants.id as ref_applicant_id',
            'pet_active_registrations.*',
            'pet_active_details.*',
            'pet_active_applicants.*',
            'pet_active_registrations.status as registrationStatus',
            'pet_active_details.status as petStatus',
            'pet_active_applicants.status as applicantsStatus',
            'ulb_ward_masters.ward_name',
            'ulb_masters.ulb_name',
            'm_pet_occurrence_types.occurrence_types',
            DB::raw("CASE 
            WHEN pet_active_registrations.apply_through = '1' THEN 'Holding'
            WHEN pet_active_registrations.apply_through = '2' THEN 'Saf'
            END AS apply_through_name"),
            DB::raw("CASE 
            WHEN pet_active_details.sex = '1' THEN 'Male'
            WHEN pet_active_details.sex = '2' THEN 'Female'
            END AS ref_gender"),
            DB::raw("CASE 
            WHEN pet_active_details.pet_type = '1' THEN 'Dog'
            WHEN pet_active_details.pet_type = '2' THEN 'Cat'
            END AS ref_pet_type"),
            'wf_roles.role_name AS roleName'
        )
            ->join('pet_active_applicants', 'pet_active_applicants.application_id', 'pet_active_registrations.id')
            ->join('pet_active_details', 'pet_active_details.application_id', 'pet_active_registrations.id')
            ->join('m_pet_occurrence_types', 'm_pet_occurrence_types.id', 'pet_active_registrations.occurrence_type_id')
            ->join('ulb_masters', 'ulb_masters.id', '=', 'pet_active_registrations.ulb_id')
            ->leftjoin('ulb_ward_masters', 'ulb_ward_masters.id', 'pet_active_registrations.ward_id')
            ->leftjoin('wf_roles', 'wf_roles.id', 'pet_active_registrations.current_role_id')
            ->where('pet_active_registrations.ulb_id', $ulbId)
            ->where('pet_active_registrations.user_id', $userId)
            ->where('pet_active_registrations.status', 1)
            ->orderBydesc('pet_active_registrations.id')
            ->take(10)
            ->get();
        $application = collect($data)->map(function ($value) {
            $value['applyDate'] = (Carbon::parse($value['applydate']))->format('d-m-Y');
            return $value;
        });
        return $application;
    }


    public function pendingApplicationCount($ulbId)
    {
        $data =  PetActiveRegistration::select(
            DB::raw('count(pet_active_registrations.id) as total_pending_application')
        )
            ->where('pet_active_registrations.status', 1)
            ->where('pet_active_registrations.ulb_id',  $ulbId)
            ->first();

        return $data;
    }

    public function approvedApplicationCount($ulbId)
    {
        $data =  PetApprovedRegistration::select(
            DB::raw('count(pet_approved_registrations.id) as total_approved_application')
        )
            ->where('pet_approved_registrations.status', 1)
            ->where('pet_approved_registrations.ulb_id',  $ulbId)
            ->first();

        return $data;
    }
    //end of code

    /**
     * | Deactivate the doc Upload Status 
     */
    public function updateUploadStatus($applicationId, $status)
    {
        PetActiveRegistration::where('id', $applicationId)
            ->where('status', 1)
            ->update([
                "doc_upload_status" => $status
            ]);
    }

    /**
     * | Get all details according to key 
     */
    public function getAllApplicationDetails($value, $key)
    {
        return DB::table('pet_active_registrations')
            ->leftJoin('wf_roles', 'wf_roles.id', 'pet_active_registrations.current_role_id')
            ->join('pet_active_applicants', 'pet_active_applicants.application_id', 'pet_active_registrations.id')
            ->join('pet_active_details', 'pet_active_details.application_id', 'pet_active_registrations.id')
            ->where('pet_active_registrations.' . $key, $value)
            ->where('pet_active_registrations.status', 1);
    }


    /**
     * | Get all details according to key 
        | Remove
     */
    public function dummyApplicationDetails($value, $key)
    {
        return DB::table('pet_active_registrations')
            ->join('pet_active_applicants', 'pet_active_applicants.application_id', 'pet_active_registrations.id')
            ->join('pet_active_details', 'pet_active_details.application_id', 'pet_active_registrations.id')
            ->where('pet_active_registrations.' . $key, $value)
            ->where('pet_active_registrations.status', 2);
    }


    /**
     * | Delete the application before the payment 
        | CAUTION ❗❗❗
     */
    public function deleteApplication($applicationId)
    {
        PetActiveRegistration::where('pet_active_registrations.id', $applicationId)
            ->where('pet_active_registrations.status', 1)
            ->delete();
    }

    /** 
     * | Update the Doc related status in active table 
     */
    public function updateDocStatus($applicationId, $status)
    {
        PetActiveRegistration::where('id', $applicationId)
            ->update([
                // 'doc_upload_status' => true,
                'doc_verify_status' => $status
            ]);
    }

    /**
     * | Save the status in Active table
     */
    public function saveApplicationStatus($applicationId, $refRequest)
    {
        PetActiveRegistration::where('id', $applicationId)
            ->update($refRequest);
    }

    /**
     * | Get active application by registration id 
     */
    public function getApplicationByRegId($regstrationId)
    {
        return PetActiveRegistration::where('registration_id', $regstrationId)
            ->where('status', 1);
    }

    /**
     * | Get Application details according to the related details in request 
     */
    public function getActiveApplicationDetails($req, $key, $refNo)
    {
        return PetActiveRegistration::select(
            'pet_active_registrations.id',
            'pet_active_registrations.application_no',
            'pet_active_registrations.application_type',
            'pet_active_registrations.payment_status',
            'pet_active_registrations.application_apply_date',
            'pet_active_registrations.doc_upload_status',
            'pet_active_registrations.renewal',
            'pet_active_applicants.mobile_no',
            'pet_active_applicants.applicant_name',
        )
            ->join('pet_active_applicants', 'pet_active_applicants.application_id', 'pet_active_registrations.id')
            ->where('pet_active_registrations.' . $key, 'LIKE', '%' . $refNo . '%')
            ->where('pet_active_registrations.status', 1)
            ->where('pet_active_registrations.ulb_id', authUser($req)->ulb_id)
            ->orderByDesc('pet_active_registrations.id');
    }

    /**
     * | Get application details by id
     */
    public function getApplicationById($id)
    {
        return PetActiveRegistration::join('ulb_masters', 'ulb_masters.id', 'pet_active_registrations.ulb_id')
            ->join('pet_active_applicants', 'pet_active_applicants.application_id', 'pet_active_registrations.id')
            ->join('pet_active_details', 'pet_active_details.application_id', 'pet_active_registrations.id')
            ->where('pet_active_registrations.id', $id)
            ->where('pet_active_registrations.status', 1);
    }

    /**
     * | Get applcation detials by id 
     */
    public function getApplicationDetailsById($id)
    {
        return PetActiveRegistration::where('id', $id)
            ->where('status', 1);
    }


    public function pendingApplication($request)
    {
        $user = authUser($request);
        $ulbId = $user->ulb_id;
        $key        = $request->filterBy;
        $perPage = $request->perPage ?: 10;
        $parameter = $request->parameter;
        $activeApplication = PetActiveApplicant::select(
            'pet_active_registrations.id',
            'pet_active_registrations.application_no',
            DB::raw("REPLACE(pet_active_registrations.application_type, '_', ' ') AS application_type"),
            'pet_active_registrations.payment_status',
            'pet_active_registrations.saf_no',
            'pet_active_registrations.holding_no',
            'pet_active_registrations.application_apply_date',
            'pet_active_registrations.doc_upload_status',
            'pet_active_registrations.renewal',
            'pet_active_applicants.mobile_no',
            'pet_active_applicants.applicant_name'
        )
            ->join('pet_active_registrations', 'pet_active_registrations.id', 'pet_active_applicants.application_id')
            ->where('pet_active_registrations.status', 1)
            ->where('pet_active_registrations.ulb_id', $ulbId)
            ->orderByDesc('pet_active_registrations.id');

        if ($key && $request->parameter) {
            switch ($key) {
                case ("mobileNo"):
                    $activeApplication = $activeApplication->where('pet_active_applicants.mobile_no', 'LIKE', "%$parameter%");
                    break;
                case ("applicationNo"):
                    $activeApplication = $activeApplication->where('pet_active_registrations.application_no', 'LIKE', "%$parameter%");
                    break;
                case ("applicantName"):
                    $activeApplication = $activeApplication->where('pet_active_applicants.applicant_name', 'LIKE', "%$parameter%");
                    break;
                case ("holdingNo"):
                    $activeApplication = $activeApplication->where('pet_active_registrations.holding_no', 'LIKE', "%$parameter%");
                    break;
                case ("safNo"):
                    $activeApplication = $activeApplication->where('pet_active_registrations.saf_no', 'LIKE', "%$parameter%");
                    break;
                default:
                    throw new Exception("Invalid Data");
            }
        }
        $data = $activeApplication;
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
