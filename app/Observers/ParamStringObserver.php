<?php

namespace App\Observers;

use App\Models\Param\RefAdvParamstring;

/**
 * | Created On-02-01-2023 
 * | Created For the Redis Cache set and delete following the observer pattern
 */
class ParamStringObserver
{
    public function saved(RefAdvParamstring $paramString)
    {
    }
}
