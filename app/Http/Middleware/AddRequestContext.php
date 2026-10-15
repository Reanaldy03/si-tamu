<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AddRequestContext
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $this->resolveRequestId($request);

        $request->attributes->set('request_id', $requestId);

        $response = $next($request);

        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }

    /**
     * Terima correlation ID dari klien hanya bila pendek dan aman; selain itu
     * pakai UUID agar nilai tidak pernah melebihi kolom audit_logs.request_id (64)
     * atau merusak header respons.
     */
    private function resolveRequestId(Request $request): string
    {
        $candidate = (string) $request->header('X-Request-ID');

        return preg_match('/^[A-Za-z0-9._:-]{1,64}$/', $candidate) === 1
            ? $candidate
            : (string) Str::uuid();
    }
}
