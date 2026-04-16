<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class BarangController extends Controller
{
    public function index(): Response
    {
        $barangs = Barang::orderBy('id')->get();
        return Inertia::render('Dashboard/Barang/Index', [
            'barangs' => $barangs,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang'   => 'required|string|max:100',
            'harga_per_unit'=> 'required|numeric|min:0',
            'satuan'        => 'required|string|max:20',
        ]);

        Barang::create($validated);

        return redirect()->back()->with('message', 'Barang berhasil ditambahkan.');
    }

    public function edit(Barang $barang): JsonResponse
    {
        return response()->json($barang);
    }

    public function update(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'nama_barang'   => 'required|string|max:100',
            'harga_per_unit'=> 'required|numeric|min:0',
            'satuan'        => 'required|string|max:20',
            'is_active'     => 'boolean',
        ]);

        if ($barang->harga_per_unit != $validated['harga_per_unit']) {
            \App\Models\RiwayatHarga::create([
                'barang_id'  => $barang->id,
                'harga_lama' => $barang->harga_per_unit,
                'harga_baru' => $validated['harga_per_unit'],
            ]);
        }

        $barang->update($validated);

        return redirect()->back()->with('message', 'Barang berhasil diperbarui.');
    }

    public function destroy(Barang $barang)
    {
        $barang->delete();

        return redirect()->back()->with('message', 'Barang berhasil dihapus.');
    }

    public function generatePdf(): \Illuminate\Http\Response
    {
        $barangs = Barang::orderBy('id')->get();

        $pdf = Pdf::loadView('pdf.master-barang', [
            'barangs' => $barangs,
        ])->setPaper('a6', 'landscape');

        if (request()->has('preview')) {
            return $pdf->stream('form-harian-angkringan.pdf');
        }
        return $pdf->download('form-harian-angkringan.pdf');
    }
}
