<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Hutang;

class TransaksiController extends Controller
{
    public function index(): Response
    {
        $user    = auth()->user();
        $barangs = Barang::where('is_active', true)->orderBy('nama_barang')->get();

        return Inertia::render('Dashboard/Transaksi/Index', [
            'barangs' => $barangs,
            'user_role'     => $user->role,
            'nama_penjual'  => $user->nama_penjual ?? $user->name,
            'user_email'    => $user->email,
        ]);
    }

    public function getByTanggal(string $tanggal): JsonResponse
    {
        $sesi    = (int) request('sesi', 1);
        $barangs = Barang::where('is_active', true)->orderBy('id')->get();
        $transaksis = Transaksi::where('tanggal', $tanggal)
            ->where('sesi', $sesi)
            ->get()->keyBy('barang_id');

        $rows = $barangs->map(function ($barang) use ($transaksis, $tanggal, $sesi) {
            if ($transaksis->has($barang->id)) {
                return $transaksis->get($barang->id);
            }

            // Virtual row — not yet saved in DB
            return [
                'id'             => null,
                'sesi'           => $sesi,
                'barang_id'      => $barang->id,
                'nama_barang'    => $barang->nama_barang,
                'harga_per_unit' => $barang->harga_per_unit,
                'jumlah_bawa'    => 0,
                'sisa'           => 0,
                'jumlah'         => 0,
                'catatan'        => null,
            ];
        });

        $jumlahTotal = $rows->sum('jumlah');

        return response()->json([
            'transaksis'   => $rows->values(),
            'jumlah_total' => $jumlahTotal,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal'       => 'required|date',
            'sesi'          => 'required|integer|min:1|max:4',
            'barang_id'     => 'required|exists:barangs,id',
            'harga_per_unit'=> 'required|numeric|min:0',
            'jumlah_bawa'   => 'required|numeric|min:0',
            'sisa'          => 'required|numeric|min:0',
            'catatan'       => 'nullable|string',
        ]);

        $barang = Barang::findOrFail($validated['barang_id']);
        $validated['nama_barang'] = $barang->nama_barang;
        $terjual = (float) max(0, (float)$validated['jumlah_bawa'] - (float)$validated['sisa']);
        if (stripos($validated['nama_barang'], 'areng') !== false) {
            $validated['jumlah'] = ($terjual / 2.5) * (float)$validated['harga_per_unit'];
        } else {
            $validated['jumlah'] = $terjual * (float)$validated['harga_per_unit'];
        }

        $transaksi = Transaksi::create($validated);

        return response()->json(['transaksi' => $transaksi], 201);
    }

    public function update(Request $request, Transaksi $transaksi): JsonResponse
    {
        $validated = $request->validate([
            'harga_per_unit'=> 'required|numeric|min:0',
            'jumlah_bawa'   => 'required|numeric|min:0',
            'sisa'          => 'required|numeric|min:0',
            'catatan'       => 'nullable|string',
        ]);

        $terjual = (float) max(0, (float)$validated['jumlah_bawa'] - (float)$validated['sisa']);
        if (stripos($transaksi->nama_barang, 'areng') !== false) {
            $validated['jumlah'] = ($terjual / 2.5) * (float)$validated['harga_per_unit'];
        } else {
            $validated['jumlah'] = $terjual * (float)$validated['harga_per_unit'];
        }
        $transaksi->update($validated);

        return response()->json(['transaksi' => $transaksi->fresh()]);
    }

    public function destroy(Transaksi $transaksi)
    {
        $transaksi->delete();

        return redirect()->back()->with('message', 'Transaksi berhasil dihapus.');
    }

    public function generatePdf(string $tanggal): \Illuminate\Http\Response
    {
        $sesi = (int) request('sesi', 1);
        $barangs = Barang::where('is_active', true)
            ->orderBy('id')
            ->get();

        $transaksis = Transaksi::where('tanggal', $tanggal)
            ->where('sesi', $sesi)
            ->orderBy('id')
            ->get();

        $jumlahTotal = 0;
        $transaksiMap = $transaksis->keyBy('barang_id');
        foreach ($barangs as $barang) {
            if ($transaksiMap->has($barang->id)) {
                $jumlahTotal += (float) $transaksiMap->get($barang->id)->jumlah;
            }
        }

        $setor = (float) request('setor', $jumlahTotal);
        $nama  = request('nama', '-');

        $pdf = Pdf::loadView('pdf.transaksi', [
            'barangs'      => $barangs,
            'transaksis'   => $transaksis,
            'tanggal'      => $tanggal,
            'sesi'         => $sesi,
            'jumlah_total' => $jumlahTotal,
            'setor'        => $setor,
            'nama'         => $nama,
        ])->setPaper('a6', 'landscape');

        // Automatic Debt Recording (only on real download, Sesi 4, and if deficit exists)
        if (!request()->has('preview') && $sesi === 4) {
            $deficit = (float)($jumlahTotal - $setor);
            if ($deficit > 0) {
                // Use provided name or a fallback identifier if name is empty/dash
                $finalNama = ($nama !== '-' && !empty($nama)) ? $nama : "Penjual Sesi 4 ($tanggal)";
                $uniqueKeterangan = "Hutang otomatis Sesi 4 ($tanggal)";

                // Update existing record if it exists and is unpaid, otherwise create new
                Hutang::updateOrCreate(
                    [
                        'nama_penjual' => $finalNama,
                        'tanggal'      => $tanggal,
                        'keterangan'   => $uniqueKeterangan,
                    ],
                    [
                        'jumlah_hutang' => $deficit,
                        'status'        => 'belum',
                    ]
                );
            }
        }

        $filename = 'angkringan-sesi' . $sesi . '-' . $tanggal . '.pdf';

        if (request()->has('preview')) {
            return $pdf->stream($filename);
        }
        return $pdf->download($filename);
    }
}
