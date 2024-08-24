<?php

namespace App\Models\Pet;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PetActiveDetail extends Model
{
    use HasFactory;
    protected $fillable = [];

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
    public function updatePetDetails($req, $petDetails)
    {
        PetActiveDetail::where('id', $petDetails->id)
            ->update([
                "sex"                       => $req->petGender          ?? $petDetails->sex,
                "identification_mark"       => $req->petIdentity        ?? $petDetails->identification_mark,
                "breed"                     => $req->breed              ?? $petDetails->breed,
                "color"                     => $req->color              ?? $petDetails->color,
                "vet_doctor_name"           => $req->doctorName         ?? $petDetails->vet_doctor_name,
                "doctor_registration_no"    => $req->doctorRegNo        ?? $petDetails->doctor_registration_no,
                "rabies_vac_date"           => $req->dateOfRabies       ?? $petDetails->rabies_vac_date,
                "leptospirosis_vac_date"    => $req->dateOfLepVaccine   ?? $petDetails->leptospirosis_vac_date,
                "dob"                       => $req->petBirthDate       ?? $petDetails->dob,
                "pet_name"                  => $req->petName            ?? $petDetails->pet_name,
                "pet_type"                  => $req->petType            ?? $petDetails->pet_type
            ]);
    }

    /**
     * | Update the Status of Pet details 
     */
    public function updatePetStatus($id, $refDetails)
    {
        PetActiveDetail::where('id', $id)
            ->where('status', 1)
            ->update($refDetails);
    }

    // public function getPendingRabiesVaccine($request)
    // {
    //     // $dateFrom = $request->dateFrom ?: Carbon::now()->startOfYear()->format('Y-m-d');
    //     // $dateUpto = $request->dateUpto ?: Carbon::now()->format('Y-m-d');
    //     $perPage = $request->perPage ?: 10;
    //     $page = $request->page ?: 1;

    //     $offset = ($page - 1) * $perPage;
    //     $baseSql = "
    //         SELECT
    //             pet_active_registrations.id,
    //              pet_active_registrations.ward_id,
    //             pet_active_registrations.application_no,
    //             pet_active_registrations.application_type,
    //             pet_active_registrations.payment_status,
    //             pet_active_registrations.application_apply_date,
    //             pet_active_registrations.doc_upload_status,
    //             pet_active_registrations.renewal,
    //             pet_active_applicants.mobile_no,
    //             pet_active_applicants.applicant_name,
    //             pet_active_details.doctor_registration_no,
    //             pet_active_details.rabies_vac_date,
    //             pet_active_details.leptospirosis_vac_date,
    //             pet_active_details.pet_name,
    //             pet_active_details.breed,
    //             pet_active_details.color
    //         FROM pet_active_details
    //         JOIN pet_active_registrations ON pet_active_registrations.id = pet_active_details.application_id
    //         JOIN pet_active_applicants ON pet_active_applicants.application_id = pet_active_registrations.id
    //         WHERE pet_active_details.status = 1
    //         AND pet_active_details.rabies_vac_date BETWEEN :dateFrom AND :dateUpto
    //         AND pet_active_details.rabies_vac_date < current_date
    //     ";
    //     if ($request->wardNo) {
    //         $baseSql .= " AND pet_active_registrations.ward_id = :wardNo";
    //     }

    //     // Determine the date range based on the selected filter
    //     if ($request->vaccinationPending) {
    //         switch ($request->vaccinationPending) {
    //             case 'less_than_1_year':
    //                 $baseSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 = 1";
    //                 break;
    //             case '1_to_3_years':
    //                 $baseSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 > 1
    //                         AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 < 3";
    //                 break;
    //             case '3_to_6_years':
    //                 $baseSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 >= 3
    //                         AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 < 6";
    //                 break;
    //             case 'more_than_6_years':
    //                 $baseSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 >= 6";
    //                 break;
    //             default:
    //                 throw new Exception('Invalid filter type');
    //         }
    //     }

    //     // Add pagination
    //     $dataSql = $baseSql . " LIMIT :limit OFFSET :offset";

    //     $params = [
    //         // 'dateFrom' => $dateFrom,
    //         // 'dateUpto' => $dateUpto,
    //         'limit' => $perPage,
    //         'offset' => $offset
    //     ];

    //     if ($request->wardNo) {
    //         $params['wardNo'] = $request->wardNo;
    //     }

    //     $dataResult = DB::select($dataSql, $params);

    //     $totalRecordsSql = "
    //         SELECT COUNT(*)
    //         FROM pet_active_details
    //         JOIN pet_active_registrations ON pet_active_registrations.id = pet_active_details.application_id
    //         JOIN pet_active_applicants ON pet_active_applicants.application_id = pet_active_details.application_id
    //         WHERE pet_active_details.status = 1
    //         AND pet_active_details.rabies_vac_date BETWEEN :dateFrom AND :dateUpto
    //         AND pet_active_details.rabies_vac_date < current_date
    //     ";

    //     if ($request->wardNo) {
    //         $totalRecordsSql .= " AND pet_active_registrations.ward_id = :wardNo";
    //     }

    //     if ($request->vaccinationPending) {
    //         switch ($request->vaccinationPending) {
    //             case 'less_than_1_year':
    //                 $totalRecordsSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 = 1";
    //                 break;
    //             case '1_to_3_years':
    //                 $totalRecordsSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 > 1
    //                         AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 < 3";
    //                 break;
    //             case '3_to_6_years':
    //                 $totalRecordsSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 >= 3
    //                         AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 < 6";
    //                 break;
    //             case 'more_than_6_years':
    //                 $totalRecordsSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 >= 6";
    //                 break;
    //         }
    //     }

    //     // $totalRecordsParams = [
    //     //     'dateFrom' => $dateFrom,
    //     //     'dateUpto' => $dateUpto
    //     // ];

    //     if ($request->wardNo) {
    //         $totalRecordsParams['wardNo'] = $request->wardNo;
    //     }

    //     $totalRecordsResult = DB::select($totalRecordsSql, $totalRecordsParams);
    //     $totalRecords = $totalRecordsResult[0]->count;

    //     return [
    //         'current_page' => $page,
    //         'last_page' => ceil($totalRecords / $perPage),
    //         'data' => $dataResult,
    //         'total' => $totalRecords
    //     ];
    // }

    // public function getPendingLeptospirosisVaccine($request)
    // {

    //     $perPage = $request->perPage ?: 10;
    //     $page = $request->page ?: 1;

    //     $offset = ($page - 1) * $perPage;
    //     $baseSql = "
    //         SELECT
    //             pet_active_registrations.id,
    //              pet_active_registrations.ward_id,
    //             pet_active_registrations.application_no,
    //             pet_active_registrations.application_type,
    //             pet_active_registrations.payment_status,
    //             pet_active_registrations.application_apply_date,
    //             pet_active_registrations.doc_upload_status,
    //             pet_active_registrations.renewal,
    //             pet_active_applicants.mobile_no,
    //             pet_active_applicants.applicant_name,
    //             pet_active_details.doctor_registration_no,
    //             pet_active_details.rabies_vac_date,
    //             pet_active_details.leptospirosis_vac_date,
    //             pet_active_details.pet_name,
    //             pet_active_details.breed,
    //             pet_active_details.color
    //         FROM pet_active_details
    //         JOIN pet_active_registrations ON pet_active_registrations.id = pet_active_details.application_id
    //         JOIN pet_active_applicants ON pet_active_applicants.application_id = pet_active_registrations.id
    //         WHERE pet_active_details.status = 1
    //         AND pet_active_details.leptospirosis_vac_date BETWEEN :dateFrom AND :dateUpto
    //         AND pet_active_details.leptospirosis_vac_date < current_date
    //     ";
    //     if ($request->wardNo) {
    //         $baseSql .= " AND pet_active_registrations.ward_id = :wardNo";
    //     }

    //     if ($request->vaccinationPending) {
    //         switch ($request->vaccinationPending) {
    //             case 'less_than_1_year':
    //                 $baseSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 = 1";
    //                 break;
    //             case '1_to_3_years':
    //                 $baseSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 > 1
    //                         AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 < 3";
    //                 break;
    //             case '3_to_6_years':
    //                 $baseSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 >= 3
    //                         AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 < 6";
    //                 break;
    //             case 'more_than_6_years':
    //                 $baseSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 >= 6";
    //                 break;
    //             default:
    //                 throw new Exception('Invalid filter type');
    //         }
    //     }

    //     $dataSql = $baseSql . " LIMIT :limit OFFSET :offset";

    //     $params = [
    //         // 'dateFrom' => $dateFrom,
    //         // 'dateUpto' => $dateUpto,
    //         'limit' => $perPage,
    //         'offset' => $offset
    //     ];

    //     if ($request->wardNo) {
    //         $params['wardNo'] = $request->wardNo;
    //     }

    //     $dataResult = DB::select($dataSql, $params);

    //     $totalRecordsSql = "
    //         SELECT COUNT(*)
    //         FROM pet_active_details
    //         JOIN pet_active_registrations ON pet_active_registrations.id = pet_active_details.application_id
    //         JOIN pet_active_applicants ON pet_active_applicants.application_id = pet_active_details.application_id
    //         WHERE pet_active_details.status = 1
    //         AND pet_active_details.leptospirosis_vac_date BETWEEN :dateFrom AND :dateUpto
    //         AND pet_active_details.leptospirosis_vac_date < current_date
    //     ";

    //     if ($request->wardNo) {
    //         $totalRecordsSql .= " AND pet_active_registrations.ward_id = :wardNo";
    //     }

    //     if ($request->vaccinationPending) {
    //         switch ($request->vaccinationPending) {
    //             case 'less_than_1_year':
    //                 $totalRecordsSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 = 1";
    //                 break;
    //             case '1_to_3_years':
    //                 $totalRecordsSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 > 1
    //                         AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 < 3";
    //                 break;
    //             case '3_to_6_years':
    //                 $totalRecordsSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 >= 3
    //                         AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 < 6";
    //                 break;
    //             case 'more_than_6_years':
    //                 $totalRecordsSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
    //                         + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 >= 6";
    //                 break;
    //         }
    //     }

    //     // $totalRecordsParams = [
    //     //     'dateFrom' => $dateFrom,
    //     //     'dateUpto' => $dateUpto
    //     // ];

    //     if ($request->wardNo) {
    //         $totalRecordsParams['wardNo'] = $request->wardNo;
    //     }

    //     $totalRecordsResult = DB::select($totalRecordsSql, $totalRecordsParams);
    //     $totalRecords = $totalRecordsResult[0]->count;

    //     return [
    //         'current_page' => $page,
    //         'last_page' => ceil($totalRecords / $perPage),
    //         'data' => $dataResult,
    //         'total' => $totalRecords
    //     ];
    // }

    public function getPendingLeptospirosisVaccine($request)
    {
        $perPage = $request->perPage ?: 10;
        $page = $request->page ?: 1;
        $offset = ($page - 1) * $perPage;

        $baseSql = "
        SELECT
            pet_active_registrations.id,
            pet_active_registrations.ward_id,
            pet_active_registrations.application_no,
            pet_active_registrations.application_type,
            pet_active_registrations.payment_status,
            pet_active_registrations.application_apply_date,
            pet_active_registrations.doc_upload_status,
            pet_active_registrations.renewal,
            pet_active_applicants.mobile_no,
            pet_active_applicants.applicant_name,
            pet_active_details.doctor_registration_no,
            pet_active_details.rabies_vac_date,
            pet_active_details.leptospirosis_vac_date,
            pet_active_details.pet_name,
            pet_active_details.breed,
            pet_active_details.color,
             ulb_ward_masters.ward_name
        FROM pet_active_details
        JOIN pet_active_registrations ON pet_active_registrations.id = pet_active_details.application_id
        JOIN pet_active_applicants ON pet_active_applicants.application_id = pet_active_registrations.id
        JOIN ulb_ward_masters ON ulb_ward_masters.id = pet_active_registrations.ward_id
        WHERE pet_active_details.status = 1
        AND pet_active_details.leptospirosis_vac_date < current_date
    ";

        if ($request->wardNo) {
            $baseSql .= " AND pet_active_registrations.ward_id = :wardNo";
        }

        if ($request->vaccinationPending) {
            switch ($request->vaccinationPending) {
                case 'less_than_1_year':
                    $baseSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 < 1";
                    break;
                case '1_to_3_years':
                    $baseSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 >= 1
                        AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 < 3";
                    break;
                case '3_to_6_years':
                    $baseSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 >= 3
                        AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 < 6";
                    break;
                case 'more_than_6_years':
                    $baseSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 >= 6";
                    break;
                default:
                    throw new Exception('Invalid filter type');
            }
        }

        $dataSql = $baseSql . " LIMIT :limit OFFSET :offset";

        $params = [
            'limit' => $perPage,
            'offset' => $offset
        ];

        if ($request->wardNo) {
            $params['wardNo'] = $request->wardNo;
        }

        $dataResult = DB::select($dataSql, $params);

        $totalRecordsSql = "
        SELECT COUNT(*)
        FROM pet_active_details
        JOIN pet_active_registrations ON pet_active_registrations.id = pet_active_details.application_id
        JOIN pet_active_applicants ON pet_active_applicants.application_id = pet_active_details.application_id
        WHERE pet_active_details.status = 1
        AND pet_active_details.leptospirosis_vac_date < current_date
    ";

        if ($request->wardNo) {
            $totalRecordsSql .= " AND pet_active_registrations.ward_id = :wardNo";
        }

        if ($request->vaccinationPending) {
            switch ($request->vaccinationPending) {
                case 'less_than_1_year':
                    $totalRecordsSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 < 1";
                    break;
                case '1_to_3_years':
                    $totalRecordsSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 >= 1
                        AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 < 3";
                    break;
                case '3_to_6_years':
                    $totalRecordsSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 >= 3
                        AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 < 6";
                    break;
                case 'more_than_6_years':
                    $totalRecordsSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.leptospirosis_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.leptospirosis_vac_date::DATE))) / 12 >= 6";
                    break;
                default:
                    throw new Exception('Invalid filter type');
            }
        }

        $totalRecordsParams = [];

        if ($request->wardNo) {
            $totalRecordsParams['wardNo'] = $request->wardNo;
        }

        $totalRecordsResult = DB::select($totalRecordsSql, $totalRecordsParams);
        $totalRecords = $totalRecordsResult[0]->count;

        return [
            'data' => $dataResult,
            'totalRecords' => $totalRecords
        ];
    }

    public function getPendingRabiesVaccine($request)
    {
        $perPage = $request->perPage ?: 10;
        $page = $request->page ?: 1;

        $offset = ($page - 1) * $perPage;
        $baseSql = "
        SELECT
            pet_active_registrations.id,
            pet_active_registrations.ward_id,
            pet_active_registrations.application_no,
            pet_active_registrations.application_type,
            pet_active_registrations.payment_status,
            pet_active_registrations.application_apply_date,
            pet_active_registrations.doc_upload_status,
            pet_active_registrations.renewal,
            pet_active_applicants.mobile_no,
            pet_active_applicants.applicant_name,
            pet_active_details.doctor_registration_no,
            pet_active_details.rabies_vac_date,
            pet_active_details.leptospirosis_vac_date,
            pet_active_details.pet_name,
            pet_active_details.breed,
            pet_active_details.color,
            ulb_ward_masters.ward_name
        FROM pet_active_details
        JOIN pet_active_registrations ON pet_active_registrations.id = pet_active_details.application_id
        JOIN pet_active_applicants ON pet_active_applicants.application_id = pet_active_registrations.id
        LEFT JOIN ulb_ward_masters ON ulb_ward_masters.id = pet_active_registrations.ward_id
        WHERE pet_active_details.status = 1
        AND pet_active_details.rabies_vac_date < current_date
    ";

        if ($request->wardNo) {
            $baseSql .= " AND pet_active_registrations.ward_id = :wardNo";
        }

        if ($request->vaccinationPending) {
            switch ($request->vaccinationPending) {
                case 'less_than_1_year':
                    $baseSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 < 1";
                    break;
                case '1_to_3_years':
                    $baseSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 >= 1
                        AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 < 3";
                    break;
                case '3_to_6_years':
                    $baseSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 >= 3
                        AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 < 6";
                    break;
                case 'more_than_6_years':
                    $baseSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 >= 6";
                    break;
                default:
                    throw new Exception('Invalid filter type');
            }
        }

        $dataSql = $baseSql . " LIMIT :limit OFFSET :offset";

        $params = [
            'limit' => $perPage,
            'offset' => $offset
        ];

        if ($request->wardNo) {
            $params['wardNo'] = $request->wardNo;
        }

        $dataResult = DB::select($dataSql, $params);

        $totalRecordsSql = "
        SELECT COUNT(*)
        FROM pet_active_details
        JOIN pet_active_registrations ON pet_active_registrations.id = pet_active_details.application_id
        JOIN pet_active_applicants ON pet_active_applicants.application_id = pet_active_details.application_id
        WHERE pet_active_details.status = 1
        AND pet_active_details.rabies_vac_date < current_date
    ";

        if ($request->wardNo) {
            $totalRecordsSql .= " AND pet_active_registrations.ward_id = :wardNo";
        }

        if ($request->vaccinationPending) {
            switch ($request->vaccinationPending) {
                case 'less_than_1_year':
                    $totalRecordsSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 < 1";
                    break;
                case '1_to_3_years':
                    $totalRecordsSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 >= 1
                        AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 < 3";
                    break;
                case '3_to_6_years':
                    $totalRecordsSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 >= 3
                        AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 < 6";
                    break;
                case 'more_than_6_years':
                    $totalRecordsSql .= " AND ((DATE_PART('YEAR', current_date::DATE) - DATE_PART('YEAR', pet_active_details.rabies_vac_date::DATE)) * 12
                        + (DATE_PART('Month', current_date::DATE) - DATE_PART('Month', pet_active_details.rabies_vac_date::DATE))) / 12 >= 6";
                    break;
            }
        }

        $totalRecordsParams = [];

        if ($request->wardNo) {
            $totalRecordsParams['wardNo'] = $request->wardNo;
        }

        $totalRecordsResult = DB::select($totalRecordsSql, $totalRecordsParams);
        $totalRecords = $totalRecordsResult[0]->count;

        return [
            'current_page' => $page,
            'last_page' => ceil($totalRecords / $perPage),
            'data' => $dataResult,
            'total' => $totalRecords
        ];
    }
}
