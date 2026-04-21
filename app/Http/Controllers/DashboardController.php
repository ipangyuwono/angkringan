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

        $totalBulanLalu = Transaksi::whereYear('tanggal', now()->subMonth()->year)
            ->whereMonth('tanggal', now()->subMonth()->month)
            ->sum('jumlah');

        $persentaseBulanIni = 0;
        if ($totalBulanLalu > 0) {
            $persentaseBulanIni = (($totalBulanIni - $totalBulanLalu) / $totalBulanLalu) * 100;
        } else if ($totalBulanIni > 0) {
            $persentaseBulanIni = 100;
        }

        $chart7Hari = collect(range(6, 0))->map(function ($daysAgo) {
            $tanggal = Carbon::today()->subDays($daysAgo)->toDateString();
            $total   = Transaksi::where('tanggal', $tanggal)->sum('jumlah');
            return [
                'tanggal' => Carbon::parse($tanggal)->translatedFormat('D, d M'),
                'total'   => (float) $total,
            ];
        });

        $chart6Bulan = collect(range(5, 0))->map(function ($monthsAgo) {
            $date = Carbon::today()->startOfMonth()->subMonth($monthsAgo);
            $total = Transaksi::whereYear('tanggal', $date->year)
                ->whereMonth('tanggal', $date->month)
                ->sum('jumlah');
            return [
                'bulan' => $date->translatedFormat('M Y'),
                'total' => (float) $total,
            ];
        });

        // Top 5 Menu Laris Bulan Ini
        $topMenu = Transaksi::whereYear('tanggal', now()->year)
            ->whereMonth('tanggal', now()->month)
            ->selectRaw('barang_id, sum(jumlah_bawa - sisa) as terjual')
            ->groupBy('barang_id')
            ->with('barang:id,nama_barang')
            ->orderByDesc('terjual')
            ->take(5)
            ->get()
            ->map(function ($t) {
                return [
                    'nama_barang' => $t->barang->nama_barang ?? 'Unknown',
                    'terjual'     => (int) $t->terjual,
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
                    'updated_at'  => $riwayat->created_at->toIso8601String(),
                ];
            });

        return Inertia::render('Dashboard/Index', [
            'total_hari_ini'       => $totalHariIni,
            'total_bulan_ini'      => $totalBulanIni,
            'persentase_bulan_ini' => round($persentaseBulanIni, 1),
            'chart_7_hari'         => $chart7Hari,
            'chart_6_bulan'        => $chart6Bulan,
            'top_menu'             => $topMenu,
            'history_harga'        => $historyHarga,
        ]);
    }
}
