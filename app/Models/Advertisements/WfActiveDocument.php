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

    
    public function uploadDocumentsViewById($appId,$workflowId){
        $data = WfActiveDocument::select('*',DB::raw("replace(doc_code,'_',' ') as doc_val"),DB::raw("CONCAT(wf_active_documents.relative_path,'/',wf_active_documents.document) as doc_path"))
            ->where(['active_id' => $appId, 'workflow_id' => $workflowId])
            ->get();
        return $data;
    }
}
