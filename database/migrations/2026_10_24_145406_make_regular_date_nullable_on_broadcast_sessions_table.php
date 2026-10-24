<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A null regular_date marks an extra session added by HR outside the program's weekly schedule.
     */
    public function up(): void
    {
        Schema::table('broadcast_sessions', function (Blueprint $table) {
            $table->date('regular_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Extra sessions get their own (or creation) date as regular_date so the column can be required again.
     */
    public function down(): void
    {
        DB::table('broadcast_sessions')
            ->whereNull('regular_date')
            ->update(['regular_date' => DB::raw('COALESCE(session_date, DATE(created_at))')]);

        Schema::table('broadcast_sessions', function (Blueprint $table) {
            $table->date('regular_date')->nullable(false)->change();
        });
    }
};
