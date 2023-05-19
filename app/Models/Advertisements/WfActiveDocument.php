<?php

namespace App\Models\Advertisements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WfActiveDocument extends Model
{
    use HasFactory;
    protected $fillable = ['active_id'];

    public function getDocByRefIds($activeId, $workflowId, $moduleId)
    {
        return WfActiveDocument::select(
            DB::raw("concat(relative_path,'/',document) as doc_path"),
            '*'
        )
            ->where('active_id', $activeId)
            ->where('workflow_id', $workflowId)
            ->where('module_id', $moduleId)
            ->where('status', 1)
            ->get();
    }

    /**
     * | Upload document funcation
     */
    public function postDocuments($req)
    {
        $metaReqs = new WfActiveDocument();
        $metaReqs->active_id            = $req->activeId;
        $metaReqs->workflow_id          = $req->workflowId;
        $metaReqs->ulb_id               = $req->ulbId;
        $metaReqs->module_id            = $req->moduleId;
        $metaReqs->relative_path        = $req->relativePath;
        $metaReqs->document             = $req->document;
        $metaReqs->uploaded_by          = authUser()->id;
        $metaReqs->uploaded_by_type     = authUser()->user_type;
        $metaReqs->remarks              = $req->remarks ?? null;
        $metaReqs->doc_code             = $req->docCode;
        $metaReqs->owner_dtl_id         = $req->ownerDtlId;
        $metaReqs->save();
    }

    /**
     * | view Uploaded documents
     */
    public function uploadDocumentsViewById($appId, $workflowId)
    {
        $data = WfActiveDocument::select('*', DB::raw("replace(doc_code,'_',' ') as doc_val"), DB::raw("CONCAT(wf_active_documents.relative_path,'/',wf_active_documents.document) as doc_path"))
            ->where(['active_id' => $appId, 'workflow_id' => $workflowId])
            ->where('current_status', '1')
            ->get();
        return $data;
    }

    /**
     * | view Uploaded documents Active
     */
    public function uploadedActiveDocumentsViewById($appId, $workflowId)
    {
        $data = WfActiveDocument::select('*', DB::raw("replace(doc_code,'_',' ') as doc_val"), DB::raw("CONCAT(wf_active_documents.relative_path,'/',wf_active_documents.document) as doc_path"))
            ->where(['active_id' => $appId, 'workflow_id' => $workflowId])
            ->where('current_status', '1')
            ->get();
        return $data;
    }

    /**
     * | Document Verify Reject
     */
    public function docVerifyReject($id, $req)
    {
        $document = WfActiveDocument::find($id);
        $document->remarks = $req['remarks'];
        $document->verify_status = $req['verify_status'];
        $document->action_taken_by = $req['action_taken_by'];
        $document->save();
    }

    /**
     * | Get Uploaded documents
     */
    public function getDocsByActiveId($req)
    {
        return WfActiveDocument::where('active_id', $req->activeId)
            ->select(
                'doc_code',
                'owner_dtl_id',
                'verify_status'
            )
            ->where('workflow_id', $req->workflowId)
            ->where('module_id', $req->moduleId)
            ->where('verify_status', '!=', 2)
            ->where('status', 1)
            ->get();
    }

    /**
     * | Get Total no of document for upload
     */
    public function totalNoOfDocs($docCode)
    {
        $noOfDocs = RefRequiredDocument::select('requirements')
            ->where('code', $docCode)
            ->first();
        $totalNoOfDocs = explode("#", $noOfDocs);
        return count($totalNoOfDocs);
    }

    /**
     * | Get total uploaded documents
     */
    public function totalUploadedDocs($applicationId, $workflowId, $moduleId)
    {
        return WfActiveDocument::where('active_id', $applicationId)
            ->where('workflow_id', $workflowId)
            ->where('module_id', $moduleId)
            ->where('current_status', '1')
            ->where('verify_status', '!=', 2)
            ->count();
    }

    // public function totalApproveDoc($applicationId,$workflowId,$moduleId){
    //     return WfActiveDocument::where('active_id',$applicationId)
    //                             ->where('workflow_id',$workflowId)
    //                             ->where('module_id',$moduleId)
    //                             ->where('current_status','1')
    //                             ->where('verify_status', '!=', 2)
    //                             ->count();
    // }
}
