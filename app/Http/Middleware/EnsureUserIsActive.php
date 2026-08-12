<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isBlocked()) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                abort(403, 'Tai khoan cua ban da bi khoa.');
            }

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Tai khoan cua ban da bi khoa.',
                ]);
        }

        return $next($request);
    }
}
