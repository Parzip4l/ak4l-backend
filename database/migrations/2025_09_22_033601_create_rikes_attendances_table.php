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
        Schema::create('rikes_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('division')->nullable();
            $table->string('department')->nullable();
            $table->date('date');

            // Kehadiran
            $table->enum('attendance_status', ['Y', 'N', 'OD'])
                ->comment('Y=Hadir, N=Tidak Hadir, OD=Off Duty');

            // Hasil pemeriksaan
            $table->enum('result_status', ['FTW', 'FTTWN', 'TU'])
                ->nullable()
                ->comment('Fit To Work, Fit To Work With Notes, Tidak Layak');

            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rikes_attendances');
    }
};
