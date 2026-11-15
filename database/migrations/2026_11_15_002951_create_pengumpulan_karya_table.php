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
        Schema::create('pengumpulan_karya', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_lomba', 30);
            $table->string('nama', 100);
            $table->text('judul_karya');
            $table->text('link_gdrive');
            $table->text('link_instagram');
            $table->string('surat_pernyataan')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengumpulan_karya');
    }
};
