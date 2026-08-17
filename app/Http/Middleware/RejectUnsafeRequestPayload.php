<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectUnsafeRequestPayload
{
    public function handle(Request $request, Closure $next): Response
    {
        $maxBytes = (int) env('MAX_REQUEST_BYTES', 10485760);
        $contentLength = $request->server('CONTENT_LENGTH');

        if ($contentLength !== null && (int) $contentLength > $maxBytes) {
            abort(413, 'Request payload is too large.');
        }

        if (
            str_contains((string) $request->headers->get('content-type'), 'application/json')
            && $request->getContent() !== ''
            && json_decode($request->getContent()) === null
            && json_last_error() !== JSON_ERROR_NONE
        ) {
            abort(400, 'Malformed JSON payload.');
        }

        return $next($request);
    }
}
