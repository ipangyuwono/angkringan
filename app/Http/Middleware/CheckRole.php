<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        if (auth()->user()->role !== $role) {
            return redirect()->route('transaksi.index')
                ->with('error', 'Akses tidak diizinkan.');
        }

        return $next($request);
    }
}
