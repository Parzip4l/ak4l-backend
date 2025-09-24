<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('safety_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('month'); // contoh: 2024-12

            // KPI utama
            $table->integer('fatality')->default(0);
            $table->integer('lost_time_injuries')->default(0);
            $table->integer('illness')->default(0);
            $table->integer('medical_treatment_cases')->default(0);
            $table->integer('first_aid_cases')->default(0);
            $table->integer('property_damage')->default(0);
            $table->integer('near_miss')->default(0);
            $table->integer('unsafe_action')->default(0);
            $table->integer('unsafe_condition')->default(0);

            // Jam kerja & hari hilang
            $table->bigInteger('work_hours')->default(0);
            $table->integer('lost_days')->default(0);

            // Rasio / index
            $table->decimal('far', 10, 2)->default(0);
            $table->decimal('sr', 10, 2)->default(0);
            $table->decimal('fr', 10, 2)->default(0);

            // Aktivitas safety tambahan
            $table->boolean('safety_inspection')->default(false);
            $table->boolean('emergency_drill')->default(false);
            $table->boolean('incident_investigation')->default(false);
            $table->boolean('internal_audit')->default(false);
            $table->boolean('p2k3_meeting')->default(false);
            $table->boolean('safety_awareness')->default(false);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('safety_metrics');
    }
};
