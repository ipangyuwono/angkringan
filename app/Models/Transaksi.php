<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaksi extends Model
{
    protected $fillable = [
        'tanggal',
        'sesi',
        'barang_id',
        'nama_barang',
        'harga_per_unit',
        'jumlah_bawa',
        'sisa',
        'jumlah',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'harga_per_unit' => 'float',
        'jumlah_bawa' => 'float',
        'sisa' => 'float',
        'jumlah' => 'float',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Transaksi $transaksi) {
            $terjual = max(0, (float)$transaksi->jumlah_bawa - (float)$transaksi->sisa);
            if (stripos($transaksi->nama_barang, 'areng') !== false) {
                $transaksi->jumlah = ($terjual / 2.5) * (float)$transaksi->harga_per_unit;
            } else {
                $transaksi->jumlah = $terjual * (float)$transaksi->harga_per_unit;
            }
        });
    }
}
