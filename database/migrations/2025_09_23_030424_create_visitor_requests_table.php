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
        Schema::create('visitor_requests', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_name');
            $table->string('visitor_company')->nullable();
            $table->string('purpose'); // alasan kunjungan
            $table->dateTime('visit_date');
            $table->unsignedBigInteger('host_id'); // tujuan tamu (pegawai penerima)
            $table->enum('status', ['pending', 'approved', 'rejected', 'onsite', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('host_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_requests');
    }
};
