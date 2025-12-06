<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon::now();

        // Check if locations already exist to avoid duplicates
        $existingVajnory = DB::table('locations')->where('svg_map', 'vajnory-2026.svg')->exists();
        $existingSeatmap = DB::table('locations')->where('svg_map', 'seatmap.svg')->exists();

        if (!$existingVajnory) {
            DB::table('locations')->insert([
                'address' => 'DK Vajnory, Pod lipami 10036/2, 831 07 Vajnory',
                'svg_map' => '/sedenie/vajnory-2026.svg',
                'places_total' => 144,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (!$existingSeatmap) {
            DB::table('locations')->insert([
                'address' => 'Default Seatmap Location',
                'svg_map' => '/sedenie/seatmap.svg',
                'places_total' => 50,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('locations')->whereIn('svg_map', ['vajnory-2026.svg', 'seatmap.svg'])->delete();
    }
};
