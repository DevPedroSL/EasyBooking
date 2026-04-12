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
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('barbershop_id')->references('id')->on('barbershops')->onDelete('cascade');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->foreign('barbershop_id')->references('id')->on('barbershops')->onDelete('cascade');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreign('barbershop_id')->references('id')->on('barbershops')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->foreign('barbershop_id')->references('id')->on('barbershops')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['barbershop_id']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['barbershop_id']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['barbershop_id']);
            $table->dropForeign(['service_id']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['barbershop_id']);
        });
    }
};
