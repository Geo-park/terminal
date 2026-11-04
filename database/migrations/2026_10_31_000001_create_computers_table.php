<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('computers', function (Blueprint $table) {
            $table->id();
            $table->string('computer_code')->unique(); // PC-01, PC-02, PC-03, PC-04
            $table->string('computer_name'); // Workstation Data Science 01
            $table->string('specs')->nullable(); // Intel i7-13700, 32GB RAM, RTX 3060 12GB
            $table->string('location')->default('Lab Data Sains - Gedung Terpadu Lt. 2');
            $table->string('status')->default('ONLINE_LOCKED'); // ONLINE_LOCKED, ONLINE_ACTIVE, OFFLINE, MAINTENANCE
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computers');
    }
};

