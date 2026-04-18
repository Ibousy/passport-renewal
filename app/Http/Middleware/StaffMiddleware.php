<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! auth()->user()->isStaff()) {
            abort(403, 'Accès réservé au personnel autorisé.');
        }

        if (! auth()->user()->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Votre compte a été désactivé. Contactez un administrateur.');
        }

        return $next($request);
    }
}
