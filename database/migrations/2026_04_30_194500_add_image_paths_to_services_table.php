<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->json('image_paths')->nullable()->after('image_path');
        });

        DB::table('services')
            ->whereNotNull('image_path')
            ->orderBy('id')
            ->get(['id', 'image_path'])
            ->each(function ($service) {
                DB::table('services')
                    ->where('id', $service->id)
                    ->update([
                        'image_paths' => json_encode([$service->image_path], JSON_UNESCAPED_SLASHES),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('image_paths');
        });
    }
};
