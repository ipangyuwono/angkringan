<?php

namespace Database\Seeders;

use App\Models\Barang;
use Illuminate\Database\Seeder;

class BarangSeeder extends Seeder
{
    public function run(): void
    {
        $barangs = [
            ['nama_barang' => 'SEWA GROBAG + KAS',  'harga_per_unit' => 7000,  'satuan' => 'hari'],
            ['nama_barang' => 'SUSU',               'harga_per_unit' => 15000, 'satuan' => 'kg'],
            ['nama_barang' => 'GULA PASIR',         'harga_per_unit' => 18000, 'satuan' => 'kg'],
            ['nama_barang' => 'GULA JAWA',          'harga_per_unit' => 16000, 'satuan' => 'kg'],
            ['nama_barang' => 'GULA BATU',          'harga_per_unit' => 16000, 'satuan' => 'kg'],
            ['nama_barang' => 'JAHE',               'harga_per_unit' => 40000, 'satuan' => 'kg'],
            ['nama_barang' => 'ARENG',              'harga_per_unit' => 14000, 'satuan' => 'kg'],
            ['nama_barang' => 'TEH',                'harga_per_unit' => 9000,  'satuan' => 'kg'],
            ['nama_barang' => 'PLASTIK HITAM',      'harga_per_unit' => 2000,  'satuan' => 'pcs'],
            ['nama_barang' => 'PLASTIK PUTIH',      'harga_per_unit' => 15000,  'satuan' => 'pcs'],
            ['nama_barang' => 'ATI',                'harga_per_unit' => 2800,  'satuan' => 'pcs'],
            ['nama_barang' => 'TELOR',              'harga_per_unit' => 1800,  'satuan' => 'pcs'],
            ['nama_barang' => 'CEKER',              'harga_per_unit' => 1000,  'satuan' => 'pcs'],
            ['nama_barang' => 'USUS',               'harga_per_unit' => 1000,  'satuan' => 'pcs'],
            ['nama_barang' => 'KEPALA',             'harga_per_unit' => 2800,  'satuan' => 'pcs'],
            ['nama_barang' => 'GORENGAN',           'harga_per_unit' => 800,   'satuan' => 'pcs'],
            ['nama_barang' => 'BACEMAN',            'harga_per_unit' => 800,   'satuan' => 'pcs'],
            ['nama_barang' => 'NASI',               'harga_per_unit' => 1500,  'satuan' => 'bungkus'],
        ];

        foreach ($barangs as $barang) {
            Barang::firstOrCreate(
                ['nama_barang' => $barang['nama_barang']],
                $barang
            );
        }
    }
}
