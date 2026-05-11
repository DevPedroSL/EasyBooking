<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['barbershop_id', 'appointment_date', 'status', 'start_time', 'end_time'], 'appointments_barbershop_date_status_time_idx');
            $table->index(['client_id', 'appointment_date', 'status'], 'appointments_client_date_status_idx');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->index(['barbershop_id', 'day_of_week', 'start_time'], 'schedules_barbershop_day_start_idx');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->index(['barbershop_id', 'visibility', 'name'], 'services_barbershop_visibility_name_idx');
        });

        Schema::table('barbershops', function (Blueprint $table) {
            $table->index('visibility', 'barbershops_visibility_idx');
        });

        Schema::table('barbershop_requests', function (Blueprint $table) {
            $table->index(['name', 'status'], 'barbershop_requests_name_status_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'users_role_idx');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_barbershop_date_status_time_idx');
            $table->dropIndex('appointments_client_date_status_idx');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('schedules_barbershop_day_start_idx');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('services_barbershop_visibility_name_idx');
        });

        Schema::table('barbershops', function (Blueprint $table) {
            $table->dropIndex('barbershops_visibility_idx');
        });

        Schema::table('barbershop_requests', function (Blueprint $table) {
            $table->dropIndex('barbershop_requests_name_status_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_idx');
        });
    }
};
