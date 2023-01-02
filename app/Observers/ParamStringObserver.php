<?php

namespace App\Observers;

use App\Models\Param\RefAdvParamstring;

/**
 * | Created On-02-01-2023 
 * | Created For the Redis Cache set and delete following the observer pattern
 */
class ParamStringObserver
{
    // Saved Function
    public function saved(RefAdvParamstring $paramString)
    {
    }

    // Updated
    public function updated(RefAdvParamstring $paramString)
    {
    }

    // Deleted
    public function deleted(RefAdvParamstring $paramString)
    {
    }
}
