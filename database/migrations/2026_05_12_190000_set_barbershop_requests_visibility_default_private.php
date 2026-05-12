<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('barbershop_requests')
            ->where('status', 'pending')
            ->update(['visibility' => 'private']);

        Schema::table('barbershop_requests', function (Blueprint $table) {
            $table->string('visibility')->default('private')->change();
        });
    }

    public function down(): void
    {
        Schema::table('barbershop_requests', function (Blueprint $table) {
            $table->string('visibility')->default('public')->change();
        });
    }
};
