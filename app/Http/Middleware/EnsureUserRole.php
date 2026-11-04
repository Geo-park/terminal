<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->role !== $role) {
            // Jika kepala lab mengakses rute mahasiswa, arahkan ke rute kepala lab yang sesuai
            if ($user->role === 'kepala_lab') {
                if ($request->is('buku-booking') || $request->is('buku-booking/*')) {
                    $qs = $request->getQueryString();
                    return redirect('/lab/buku-booking' . ($qs ? '?' . $qs : ''));
                }
                return redirect()->route('kepala-lab.dashboard');
            }

            // Jika mahasiswa mencoba mengakses rute kepala lab, arahkan ke dashboard mahasiswa
            return redirect()->route('mahasiswa.dashboard')->with('error', 'Akses ditolak: Halaman ini khusus untuk Kepala Laboratorium.');
        }

        return $next($request);
    }
}
