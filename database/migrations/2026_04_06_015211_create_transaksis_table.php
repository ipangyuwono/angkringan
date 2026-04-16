<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
            $table->string('nama_barang'); 
            $table->decimal('harga_per_unit', 12, 2); 
            $table->decimal('jumlah_bawa', 12, 2)->default(0); 
            $table->decimal('sisa', 12, 2)->default(0);        
            $table->decimal('jumlah', 12, 2)->default(0);      
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
