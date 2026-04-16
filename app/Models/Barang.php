<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barang extends Model
{
    protected $fillable = [
        'nama_barang',
        'harga_per_unit',
        'satuan',
        'is_active',
    ];

    protected $casts = [
        'harga_per_unit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }
}
