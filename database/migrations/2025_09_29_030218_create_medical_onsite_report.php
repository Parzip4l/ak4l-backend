<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medical_onsite_reports', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['laporan_kegiatan', 'absensi_kehadiran', 'penggunaan_obat', 'limbah']);
            $table->date('month'); // pakai format YYYY-MM-01
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('medical_onsite_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('medical_onsite_reports')->cascadeOnDelete();
            $table->foreignId('approved_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['approved', 'rejected']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_onsite_approvals');
        Schema::dropIfExists('medical_onsite_reports');
    }
};
