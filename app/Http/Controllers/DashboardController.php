<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today = now()->toDateString();
        $totalHariIni = Transaksi::where('tanggal', $today)->sum('jumlah');
        $totalBulanIni = Transaksi::whereYear('tanggal', now()->year)
            ->whereMonth('tanggal', now()->month)
            ->sum('jumlah');

        $chart7Hari = collect(range(6, 0))->map(function ($daysAgo) {
            $tanggal = Carbon::today()->subDays($daysAgo)->toDateString();
            $total   = Transaksi::where('tanggal', $tanggal)->sum('jumlah');
            return [
                'tanggal' => Carbon::parse($tanggal)->translatedFormat('D, d M'),
                'total'   => (float) $total,
            ];
        });

        $historyHarga = \App\Models\RiwayatHarga::with('barang')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(function ($riwayat) {
                return [
                    'id'          => $riwayat->id,
                    'nama_barang' => $riwayat->barang->nama_barang ?? 'Unknown',
                    'harga_lama'  => (float) $riwayat->harga_lama,
                    'harga_baru'  => (float) $riwayat->harga_baru,
                    'updated_at'  => $riwayat->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('Dashboard/Index', [
            'total_hari_ini'  => $totalHariIni,
            'total_bulan_ini' => $totalBulanIni,
            'chart_7_hari'    => $chart7Hari,
            'history_harga'   => $historyHarga,
        ]);
    }
}
