<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoredImageService
{
    public function storeUploadedImages(Request $request, string $inputName, string $directory, int $limit): array
    {
        return collect($request->file($inputName, []))
            ->take($limit)
            ->map(fn ($image) => $image->store($directory, 'public'))
            ->all();
    }

    public function pathsAfterRemovalSelection(array $currentPaths, Request $request, string $inputName): array
    {
        $removeIndexes = collect($request->input($inputName, []))
            ->map(fn ($index) => (int) $index)
            ->filter(fn ($index) => array_key_exists($index, $currentPaths))
            ->unique()
            ->values()
            ->all();

        $removedPaths = [];
        $remainingPaths = [];

        foreach ($currentPaths as $index => $path) {
            if (in_array($index, $removeIndexes, true)) {
                $removedPaths[] = $path;

                continue;
            }

            $remainingPaths[] = $path;
        }

        return [$remainingPaths, $removedPaths];
    }

    public function deletePublicImages(array $paths): void
    {
        foreach ($paths as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
