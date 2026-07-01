<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->text('appeal_reason')->nullable()->after('suspend_reason');
            $table->timestamp('appeal_at')->nullable()->after('appeal_reason');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['appeal_reason', 'appeal_at']);
        });
    }
};
