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
        Schema::create('obats', function (Blueprint $table) {
        $table->id();
        $table->string('nama');
        $table->string('kode')->unique();
        $table->text('deskripsi')->nullable();
        $table->integer('stok');
        $table->decimal('harga', 10, 2);
        $table->date('tanggal_kadaluarsa');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obats');
    }
};
