<?php

namespace App\Http\Controllers\Advertisements;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\SelfAdvets\iSelfAdvetRepo;

/**
 * | Created On-14-12-2022 
 * | Created By-Anshu Kumar
 * | Created for Operations on Self Advertisements
 */

class SelfAdvetController extends Controller
{
    private $_repo;
    public function __construct(iSelfAdvetRepo $repo)
    {
        $this->_repo = $repo;
    }

    // Store in DB
    public function store(Request $req)
    {
        return $this->_repo->store($req);
    }
}
