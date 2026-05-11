<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'barbershop_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('barbershop_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'barbershop_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('barbershop_id')->nullable();
        });
    }
};
