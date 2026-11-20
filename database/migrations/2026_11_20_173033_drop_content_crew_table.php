<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penugasan crew kini per sesi (broadcast_session_crew); isi content_crew dibuang tanpa disalin.
     */
    public function up(): void
    {
        Schema::dropIfExists('content_crew');
    }

    /**
     * Membuat ulang tabel kosong, tanpa mengembalikan datanya.
     */
    public function down(): void
    {
        Schema::create('content_crew', function (Blueprint $table) {
            $table->foreignId('content_id')->constrained('contents')->cascadeOnDelete();
            $table->foreignId('crew_id')->constrained('crews')->cascadeOnDelete();
            $table->primary(['content_id', 'crew_id']);
        });
    }
};
