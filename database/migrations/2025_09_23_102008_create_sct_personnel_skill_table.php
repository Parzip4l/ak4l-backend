<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Job Positions
        Schema::create('job_positions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // 2. Skills
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name');               // e.g., Gada Pratama
            $table->string('category');           // Hard Skill / Soft Skill
            $table->string('reference')->nullable(); // Peraturan, dll
            $table->string('criteria')->nullable();  // Ext. Certification / Int. Certification
            $table->timestamps();
        });

        // 3. Personnels
        Schema::create('personnels', function (Blueprint $table) {
            $table->id();
            $table->string('name');                // Nama personil
            $table->string('bujp')->nullable();    // Nama BUJP
            $table->string('kta_number')->nullable(); // Nomor KTA
            $table->string('code')->unique();      // Kode personil
            $table->foreignId('job_position_id')->constrained()->cascadeOnDelete();
            $table->string('photo')->nullable();
            $table->timestamps();
        });

        // 4. Personnel Skills (pivot table)
        Schema::create('personnel_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();

            $table->boolean('certificate')->default(false);      // Ada sertifikat?
            $table->boolean('member_card')->default(false);      // Ada kartu anggota?
            $table->string('certificate_file')->nullable();      // Path file sertifikat
            $table->string('member_card_file')->nullable();      // Path file kartu anggota

            $table->timestamps();

            $table->unique(['personnel_id', 'skill_id']); // kombinasi unik
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel_skills');
        Schema::dropIfExists('personnels');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('job_positions');
    }
};
