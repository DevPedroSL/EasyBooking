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
        Schema::create('barbershops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barber_id')->constrained('users');
            $table->string('name')->unique();
            $table->string('address');
            $table->string('phone');
            $table->unsignedSmallInteger('slot_interval_minutes')->default(60);
            $table->string('visibility')->default('public');
            $table->string('image_path')->nullable();
            $table->json('image_paths')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barbershops');
    }
};
