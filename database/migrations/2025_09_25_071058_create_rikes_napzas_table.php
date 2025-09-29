<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rikes_napzas', function (Blueprint $table) {
            $table->id();
            $table->string('periode'); // format: Jan-24, Feb-24, dst
            $table->integer('passed')->nullable();     // %
            $table->integer('not_passed')->nullable(); // %
            $table->integer('kehadiran')->nullable();  // %
            $table->integer('target')->nullable();     // %
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rikes_napzas');
    }
};
