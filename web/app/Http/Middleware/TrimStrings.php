<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrimStrings
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Trim all input
        $request->merge(array_map(function ($value) {
            if (is_string($value)) {
                return trim($value);
            } else {
                return $value;
            }
        }, $request->all()));

        return $next($request);
    }
}
