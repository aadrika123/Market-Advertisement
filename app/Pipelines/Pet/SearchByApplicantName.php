<?php

namespace App\Pipelines\Pet;

use Closure;

class SearchByApplicantName
{
    public function handle($request, Closure $next)
    {
        if (!request()->has('name')) {
            return $next($request);
        }
        return $next($request)
            ->where('applicant_name', 'ilike', '%' . request()->input('name') . '%');
    }
}
