<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hutang extends Model
{
    protected $fillable = [
        'nama_penjual',
        'tanggal',
        'jumlah_hutang',
        'jumlah_bayar',
        'keterangan',
        'status',
        'tanggal_lunas',
    ];

    protected $casts = [
        'tanggal'       => 'date',
        'tanggal_lunas' => 'date',
        'jumlah_hutang' => 'float',
        'jumlah_bayar'  => 'float',
    ];

    public function getSisaAttribute(): float
    {
        return max(0, $this->jumlah_hutang - $this->jumlah_bayar);
    }
}
