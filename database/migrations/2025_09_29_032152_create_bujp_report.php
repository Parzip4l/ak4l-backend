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
        Schema::create('bujp_reports', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['kegiatan', 'absensi', 'obat', 'limbah', 'lain']);
            $table->date('month'); // filter by month & year
            $table->foreignId('submitted_by')->constrained('users')->onDelete('cascade');
            $table->string('file_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bujp_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('bujp_reports')->onDelete('cascade');
            $table->foreignId('approved_by')->constrained('users')->onDelete('cascade');
            $table->enum('action', ['approved', 'rejected']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bujp_report');
    }
};
