<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table): void {
            $table->dropColumn(['period_start', 'period_end']);
        });

        DB::table('broadcast_sessions')
            ->whereNull('regular_date')
            ->update(['regular_date' => DB::raw('COALESCE(session_date, DATE(created_at))')]);

        Schema::table('broadcast_sessions', function (Blueprint $table): void {
            $table->date('regular_date')->nullable(false)->change();
            $table->dropColumn('is_customized');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broadcast_sessions', function (Blueprint $table): void {
            $table->boolean('is_customized')->default(false);
            $table->date('regular_date')->nullable()->change();
        });

        Schema::table('programs', function (Blueprint $table): void {
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
        });
    }
};
