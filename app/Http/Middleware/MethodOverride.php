<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MethodOverride
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST') && $override = $request->query('_method')) {
            $override = strtoupper($override);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                $request->setMethod($override);
                // Also set on server vars for framework compatibility
                $request->server->set('REQUEST_METHOD', $override);
            }
        }

        return $next($request);
    }
}
