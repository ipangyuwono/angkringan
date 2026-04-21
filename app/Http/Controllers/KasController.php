<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KasController extends Controller
{
    public function index(): Response
    {
        $kasData = Transaksi::where('nama_barang', 'SEWA GROBAG + KAS')
            ->orderBy('tanggal', 'desc')
            ->orderBy('sesi', 'asc')
            ->get();

        $totalKas = $kasData->sum('jumlah');

        $rekapKas = $kasData->groupBy('sesi')->map(function ($items, $sesi) {
            return [
                'sesi' => $sesi,
                'total' => $items->sum('jumlah')
            ];
        })->values();   

        return Inertia::render('Dashboard/Kas/Index', [
            'kas_data' => $kasData,
            'total_kas' => (float) $totalKas,
            'rekap_kas' => $rekapKas,
            'is_admin'  => true,
            'session'   => null,
        ]);
    }

    public function myKas(): Response
    {
        $user = auth()->user();
        
        // Determine session based on seller's email
        $session = 1;
        if ($user->email === 'ppang7@gmail.com') $session = 1;
        elseif ($user->email === 'hartanto12@gmail.com') $session = 2;
        elseif ($user->email === 'sarinoang7@gmail.com') $session = 3;
        elseif ($user->email === 'alan7@gmail.com') $session = 4;

        $kasData = Transaksi::where('nama_barang', 'SEWA GROBAG + KAS')
            ->where('sesi', $session)
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalKas = $kasData->sum('jumlah');

        return Inertia::render('Dashboard/Kas/Index', [
            'kas_data' => $kasData,
            'total_kas' => (float) $totalKas,
            'is_admin'  => false,
            'session'   => $session,
        ]);
    }
}
