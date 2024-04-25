<?php

namespace App\Pipelines\Rentals;

use Closure;

class SearchByShopAlottee
{
    public function handle($request, Closure $next)
    {
        if (!request()->has('allottee')) {
            return $next($request);
        }
        return $next($request)
            ->where('allottee', 'ilike', '%' . request()->input('allottee') . '%');
    }
}
