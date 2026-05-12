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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barbershop_id')->constrained('barbershops')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration');
            $table->decimal('price', 8, 2);
            $table->string('visibility')->default('public');
            $table->string('image_path')->nullable();
            $table->json('image_paths')->nullable();
            $table->timestamps();

            $table->index(['barbershop_id', 'visibility', 'name'], 'services_barbershop_visibility_name_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
