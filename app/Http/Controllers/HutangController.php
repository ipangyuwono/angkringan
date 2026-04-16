<?php

namespace App\Http\Controllers;

use App\Models\Hutang;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HutangController extends Controller
{
    public function index(): Response
    {
        $hutangs = Hutang::orderByRaw("status = 'lunas' ASC")
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(fn($h) => array_merge($h->toArray(), ['sisa' => $h->sisa]));

        $totalBelumLunas = Hutang::where('status', 'belum')->sum('jumlah_hutang');
        $totalLunas      = Hutang::where('status', 'lunas')->count();

        return Inertia::render('Dashboard/Hutang/Index', [
            'hutangs'          => $hutangs,
            'total_belum_lunas' => (float) $totalBelumLunas,
            'total_lunas'      => $totalLunas,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_penjual'  => 'required|string',
            'tanggal'       => 'required|date',
            'jumlah_hutang' => 'required|numeric|min:1',
            'keterangan'    => 'nullable|string',
        ]);

        Hutang::create($validated);

        return back()->with('success', 'Hutang berhasil dicatat.');
    }

    public function myHutang(): Response
    {
        $user    = auth()->user();
        $hutangs = Hutang::where('nama_penjual', $user->nama_penjual)
            ->where('status', 'belum')
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(fn($h) => array_merge($h->toArray(), ['sisa' => $h->sisa]));

        $totalSisa = $hutangs->sum('sisa');

        return Inertia::render('Dashboard/Hutang/MyHutang', [
            'hutangs'    => $hutangs,
            'total_sisa' => (float) $totalSisa,
            'nama'       => $user->nama_penjual,
        ]);
    }

    public function bayar(Request $request, Hutang $hutang)
    {
        $validated = $request->validate([
            'jumlah_bayar' => 'required|numeric|min:1',
        ]);

        $totalBayar = $hutang->jumlah_bayar + $validated['jumlah_bayar'];
        $lunas      = $totalBayar >= $hutang->jumlah_hutang;

        $hutang->update([
            'jumlah_bayar'  => min($totalBayar, $hutang->jumlah_hutang),
            'status'        => $lunas ? 'lunas' : 'belum',
            'tanggal_lunas' => $lunas ? now()->toDateString() : null,
        ]);

        return back()->with('success', $lunas ? 'Hutang lunas!' : 'Pembayaran tercatat.');
    }

    public function destroy(Hutang $hutang)
    {
        $hutang->delete();
        return back()->with('success', 'Data hutang dihapus.');
    }
}
