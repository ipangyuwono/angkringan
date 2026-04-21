<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OtpVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->get('otp_verified')) {
            return redirect()->route('otp.show');
        }

        return $next($request);
    }
}
