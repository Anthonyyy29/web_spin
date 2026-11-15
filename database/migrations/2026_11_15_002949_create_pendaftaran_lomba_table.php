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
        Schema::create('pendaftaran_lomba', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_lomba', 100);
            $table->string('nama_tim', 100)->nullable();
            $table->string('nama_lengkap', 100)->nullable();
            $table->string('instagram', 100);
            $table->string('email', 100);
            $table->string('no_wa', 20);
            $table->string('bukti_pembayaran');
            $table->string('kartu_identitas');
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_lomba');
    }
};
