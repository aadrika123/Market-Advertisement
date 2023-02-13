<?php

namespace App\Http\Controllers\Markets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lodge\StoreRequest;
use Illuminate\Http\Request;

class LodgeController extends Controller
{
    

     /**
     * | Store 
     * | @param StoreRequest Request
     */
    public function store(StoreRequest $req)
    {
        echo "hihi";
    }

    /**
     * Summary of inbox
     * @param Request $req
     * @return void
     */
    public function inbox(Request $req){
        echo "inbox";
    }


    /**
     * Outbox List
     * @param Request $req
     * @return void
     */
    public function outbox(Request $req){
        echo "Outbox";
    }


    /**
     * Application Details
     * @param Request $req
     * @return void
     */
    public function details(Request $req){
        echo "Details";
    }



    /**
     * Summary of getCitizenApplications
     * @param Request $req
     * @return void
     */
    public function getCitizenApplications(Request $req){
        echo "getCitizenApplications";
    }


    /**
     *  | Escalate
     * @param Request $req
     * @return void
     */
    public function escalate(Request $req){
        echo 'Esclate';
    }


    /**
     *  Inbox List
     * @param Request $req
     * @return void
     */
    public function specialInbox(Request $req){
        echo "Special Inbox";
    }



    /**
     * Forward or Backward Application
     * @param Request $req
     * @return void
     */
    public function postNextLevel(Request $req){
        echo "Post Next Level";
    }



    /**
     * Post Independent Comment
     * @param Request $req
     * @return void
     */
    public function commentIndependent(Request $req){
        echo "Comment Independent";
    }


    /**
     * Get Uploaded Document by application ID
     * @param Request $req
     * @return void
     */
    public function uploadDocumentsView(Request $req){
        echo "Upload Documents View";
    }



    /**
     * Final Approval and Rejection of the Application
     * @param Request $req
     * @return void
     */
    public function finalApprovalRejection(Request $req){
        echo "final Approval Rejection";
    }

    /**
     * Approved Application List for Citizen
     * @param Request $req
     * @return void
     */
    public function approvedList(Request $req){
        echo "approved List";
    }



    /**
     * Rejected Application List
     * @param Request $req
     * @return void
     */
    public function rejectedList(Request $req){
        echo "Rejected List";
    }



    /**
     * get JSK Applications
     * @param Request $req
     * @return void
     */
    public function getJSKApplications(Request $req){
        echo "get JSK Applications";
    }

    

    /**
     * jsk Approved Application List
     * @param Request $req
     * @return void
     */
    public function jskApprovedList(Request $req){
        echo "jsk Approved List";
    }




    /**
     * jsk Rejected Applications List
     * @param Request $req
     * @return void
     */
    public function jskRejectedList(Request $req){
        echo "jsk Rejected List";
    }



    /**
     * generate Payment OrderId for Payment
     * @param Request $req
     * @return void
     */
    public function generatePaymentOrderId(Request $req){
        echo "generate Payment Order Id";
    }


    /**
     * Get application Details For Payment
     * @return void
     */
    public function applicationDetailsForPayment(){
        echo "application Details For Payment";
    }
}
