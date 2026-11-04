<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('computer_id')->constrained('computers')->cascadeOnDelete();
            $table->date('reservation_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('purpose'); // Tugas Akhir / Skripsi, Penelitian AI, Praktikum Data Science, dll.
            $table->text('notes')->nullable(); // Catatan software / tools yang dibutuhkan
            $table->string('status')->default('PENDING_APPROVAL'); // PENDING_APPROVAL, APPROVED, REJECTED, CANCELLED, CANCELLED_NO_SHOW, CHECKED_IN, COMPLETED
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};

