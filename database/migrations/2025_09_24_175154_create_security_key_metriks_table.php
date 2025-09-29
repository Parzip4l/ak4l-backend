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
        Schema::create('security_key_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('month');
            $table->integer('kasus_kriminal')->default(0);
            $table->integer('kasus_ancaman_bom')->default(0);
            $table->integer('kasus_huru_hara')->default(0);
            $table->integer('kasus_vandalisme')->default(0);
            $table->integer('kasus_lainnya')->default(0);
            $table->integer('inspeksi_pengamanan')->default(0);
            $table->integer('investigasi_insiden_pengamanan')->default(0);
            $table->integer('audit_internal_smp')->default(0);
            $table->integer('simulasi_tanggap_darurat_pengamanan')->default(0);
            $table->integer('rapat_koordinasi_3_pilar')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('security_key_metrics', function (Blueprint $table) {
            //
        });
    }
};
