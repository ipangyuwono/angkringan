<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hutangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_penjual');
            $table->date('tanggal');
            $table->decimal('jumlah_hutang', 12, 2);
            $table->decimal('jumlah_bayar', 12, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['belum', 'lunas'])->default('belum');
            $table->date('tanggal_lunas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hutangs');
    }
};
