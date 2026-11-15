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
        Schema::create('broadcast_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->cascadeOnDelete();
            $table->date('tanggal')->index();
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();
        });

        Schema::create('content_crew', function (Blueprint $table) {
            $table->foreignId('content_id')->constrained('contents')->cascadeOnDelete();
            $table->foreignId('crew_id')->constrained('crews')->cascadeOnDelete();
            $table->primary(['content_id', 'crew_id']);
        });

        Schema::table('absensi_logs', function (Blueprint $table) {
            $table->foreignId('session_id')->nullable()->constrained('broadcast_sessions')->nullOnDelete();
            $table->unique(['rfid_uid', 'session_id']);
        });

        Schema::table('absensi_logs', function (Blueprint $table) {
            $table->dropUnique(['rfid_uid', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_logs', function (Blueprint $table) {
            $table->unique(['rfid_uid', 'tanggal']);
        });

        Schema::table('absensi_logs', function (Blueprint $table) {
            $table->dropForeign(['session_id']);
            $table->dropUnique(['rfid_uid', 'session_id']);
            $table->dropColumn('session_id');
        });

        Schema::dropIfExists('content_crew');
        Schema::dropIfExists('broadcast_sessions');
    }
};
