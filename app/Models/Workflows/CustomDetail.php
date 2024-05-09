<?php

namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomDetail extends Model
{
    use HasFactory;


    /**
     * |
     */
    public function getCustomDetails($request)
    {
        $customDetails = CustomDetail::select(
            'id',
            'ref_id',
            'ref_type',
            // 'relative_path',
            // 'doc_name as docUrl',
            'remarks',
            'type',
            'created_at as date',
            'ref_type as customFor'
        )
            ->orderBy("id", 'desc')
            ->where('ref_id', $request->applicationId)
            ->where('ref_type', trim(strtoupper($request->customFor)))
            ->get();
        // $customDetails = $customDetails->map(function ($val) {
        //     $path = config('app.url') . '/' . $val->relative_path . '/' . $val->docUrl;
        //     $val->docUrl = $path;
        //     return $val;
        // });

        // $docUpload = new DocUpload;
        // $customDetails = CustomDetail::select(
        //     'id',
        //     'ref_id',
        //     'ref_type',
        //     'doc_ref_no as reference_no',
        //     'doc_unique_id',
        //     'remarks',
        //     'type',
        //     'created_at as date',
        //     'ref_type as customFor'
        // )
        //     ->where('ref_id', $request->applicationId)
        //     ->where('ref_type', trim(strtoupper($request->customFor)))
        //     ->orderByDesc('id')
        //     ->get();

        // $customDetails = $customDetails->map(function ($val) use ($docUpload) {
        //     $docResponse =  $docUpload->getSingleDocUrl($val);
        //     $val->docUrl =  $docResponse['doc_path'] ?? null;
        //     return $val;
        // });
        // return responseMsg(true, "Data Retrieved", $customDetails);
    }
}
