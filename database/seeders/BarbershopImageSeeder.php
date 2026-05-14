<?php

namespace Database\Seeders;

use App\Models\Barbershop;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class BarbershopImageSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $imagePaths = $this->publishDefaultImages();

        if ($imagePaths === []) {
            return;
        }

        Barbershop::orderBy('id')
            ->get()
            ->each(function (Barbershop $barbershop, int $index) use ($imagePaths): void {
                $imagePath = $imagePaths[$index % count($imagePaths)];

                $barbershop->forceFill([
                    'image_path' => $imagePath,
                    'image_paths' => [$imagePath],
                ])->save();
            });
    }

    /**
     * @return array<int, string>
     */
    private function publishDefaultImages(): array
    {
        return collect([
            ['source' => database_path('seeders/assets/barbershops/barberia-2.jpeg'), 'target' => 'barbershops/defaults/barberia-1.jpeg'],
            ['source' => database_path('seeders/assets/barbershops/barberia-1.jpeg'), 'target' => 'barbershops/defaults/barberia-2.jpeg'],
            ['source' => database_path('seeders/assets/barbershops/barberia-3.jpeg'), 'target' => 'barbershops/defaults/barberia-3.jpeg'],
            ['source' => database_path('seeders/assets/barbershops/barberia-4.png'), 'target' => 'barbershops/defaults/barberia-4.png'],
        ])
            ->filter(fn (array $image): bool => file_exists($image['source']))
            ->map(function (array $image): string {
                Storage::disk('public')->put($image['target'], file_get_contents($image['source']));

                return $image['target'];
            })
            ->values()
            ->all();
    }
}
