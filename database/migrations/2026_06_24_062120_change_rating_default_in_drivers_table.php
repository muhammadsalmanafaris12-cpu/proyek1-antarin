<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            // Ubah rating dari integer default 5 → decimal nullable (null = belum ada rating)
            $table->decimal('rating', 3, 1)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->integer('rating')->default(5)->change();
        });
    }
};
