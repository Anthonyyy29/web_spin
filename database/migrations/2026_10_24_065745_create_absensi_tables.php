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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('day_of_week');
            $table->time('crewcall_time');
            $table->time('tapping_start');
            $table->time('tapping_end');
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamps();
        });

        Schema::create('program_crew', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('position');
            $table->timestamps();
            $table->unique(['program_id', 'user_id']);
        });

        Schema::create('broadcast_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->date('regular_date');
            $table->date('session_date')->nullable();
            $table->time('crewcall_time')->nullable();
            $table->time('tapping_start')->nullable();
            $table->time('tapping_end')->nullable();
            $table->string('status')->default('scheduled');
            $table->boolean('is_customized')->default(false);
            $table->timestamps();
            $table->index('regular_date');
            $table->index('session_date');
        });

        Schema::create('session_crew', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_session_id')->constrained('broadcast_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('position');
            $table->timestamps();
            $table->unique(['broadcast_session_id', 'user_id']);
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_session_id')->constrained('broadcast_sessions')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('checked_in_at')->nullable();
            $table->string('status');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['broadcast_session_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('session_crew');
        Schema::dropIfExists('broadcast_sessions');
        Schema::dropIfExists('program_crew');
        Schema::dropIfExists('programs');
    }
};
