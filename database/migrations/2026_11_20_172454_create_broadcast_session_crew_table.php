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
        Schema::create('broadcast_session_crew', function (Blueprint $table) {
            $table->foreignId('broadcast_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crew_id')->constrained()->cascadeOnDelete();
            $table->primary(['broadcast_session_id', 'crew_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broadcast_session_crew');
    }
};
