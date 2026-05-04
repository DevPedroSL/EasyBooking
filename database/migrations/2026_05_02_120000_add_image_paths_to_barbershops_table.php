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
        Schema::table('barbershops', function (Blueprint $table) {
            $table->json('image_paths')->nullable()->after('image_path');
        });

        DB::table('barbershops')
            ->whereNotNull('image_path')
            ->orderBy('id')
            ->get(['id', 'image_path'])
            ->each(function ($barbershop) {
                DB::table('barbershops')
                    ->where('id', $barbershop->id)
                    ->update([
                        'image_paths' => json_encode([$barbershop->image_path], JSON_UNESCAPED_SLASHES),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barbershops', function (Blueprint $table) {
            $table->dropColumn('image_paths');
        });
    }
};
