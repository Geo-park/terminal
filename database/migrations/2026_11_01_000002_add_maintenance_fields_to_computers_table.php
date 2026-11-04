<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('computers', function (Blueprint $table) {
            $table->timestamp('maintenance_started_at')->nullable()->after('status');
            $table->timestamp('maintenance_until')->nullable()->after('maintenance_started_at');
            $table->string('maintenance_reason')->nullable()->after('maintenance_until');
        });
    }

    public function down(): void
    {
        Schema::table('computers', function (Blueprint $table) {
            $table->dropColumn(['maintenance_started_at', 'maintenance_until', 'maintenance_reason']);
        });
    }
};

