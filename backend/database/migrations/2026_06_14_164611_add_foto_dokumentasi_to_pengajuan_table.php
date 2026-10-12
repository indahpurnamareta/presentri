<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            // Menambahkan field foto_dokumentasi setelah kolom bukti_file
            // Dibuat nullable() karena pengajuan selain Dinas Luar mungkin tidak membutuhkannya
            $table->string('foto_dokumentasi')->nullable()->after('bukti_file');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->dropColumn('foto_dokumentasi');
        });
    }
};