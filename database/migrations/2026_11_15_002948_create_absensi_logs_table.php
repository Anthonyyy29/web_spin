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
        Schema::create('absensi_logs', function (Blueprint $table) {
            $table->id();
            $table->string('rfid_uid', 50);
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->enum('status', ['Hadir', 'Terlambat', 'Pulang'])->default('Hadir');
            $table->unique(['rfid_uid', 'tanggal']);
            $table->foreign('rfid_uid')->references('rfid_uid')->on('rfid_crews')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_logs');
    }
};
