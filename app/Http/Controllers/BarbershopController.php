<?php

namespace App\Http\Controllers;

use App\Models\Barbershop;
use App\Models\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BarbershopController extends Controller
{
    private function currentUserBarbershop()
    {
        return auth()->user()->barbershop;
    }

    public function create()
    {
        if ($this->currentUserBarbershop()) {
            return redirect()->route('barbershops.editMy')->with('error', 'Ya tienes una barbería.');
        }
        return view('barbershops.create');
    }

    public function store(Request $request)
    {
        if ($this->currentUserBarbershop()) {
            return redirect()->route('barbershops.editMy')->with('error', 'Ya tienes una barbería.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:barbershops,name',
            'Description' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'visibility' => 'required|in:public,private',
            'image' => 'nullable|image|max:3072',
            'gallery_images' => 'nullable|array|max:4',
            'gallery_images.*' => 'image|max:3072',
        ]);

        $validated['barber_id'] = auth()->id();

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('barbershops', 'public');
        }

        $galleryImagePaths = $this->storeUploadedBarbershopGalleryImages($request);
        if ($galleryImagePaths !== []) {
            $validated['image_paths'] = $galleryImagePaths;
        }

        Barbershop::create($validated);

        return redirect()->route('barbershops.editMy')->with('success', 'Barbería creada correctamente.');
    }

    public function editMy()
    {
        $barbershop = $this->currentUserBarbershop();
        if (!$barbershop) {
            return redirect()->route('barbershops.create')->with('error', 'No tienes una barbería. Crea una primero.');
        }

        return view('barbershops.edit', compact('barbershop'));
    }

    public function updateMy(Request $request)
    {
        $barbershop = $this->currentUserBarbershop();
        if (!$barbershop) {
            return redirect()->route('barbershops.create')->with('error', 'No tienes una barbería.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:barbershops,name,' . $barbershop->id,
            'Description' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'visibility' => 'required|in:public,private',
            'image' => 'nullable|image|max:3072',
            'remove_image' => 'nullable|boolean',
            'gallery_images' => 'nullable|array|max:4',
            'gallery_images.*' => 'image|max:3072',
            'remove_gallery_images' => 'nullable|array',
            'remove_gallery_images.*' => 'integer',
        ]);

        $barbershop->update([
            'name' => $validated['name'],
            'Description' => $validated['Description'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'visibility' => $validated['visibility'],
        ]);

        [$remainingPaths, $removedPaths] = $this->barbershopImagePathsAfterRemovalSelection($barbershop, $request);
        $newImageCount = count($request->file('gallery_images', []));

        if (count($remainingPaths) + $newImageCount > 4) {
            return back()
                ->withErrors(['gallery_images' => 'Cada barbería puede tener como máximo 4 imágenes de carrusel.'])
                ->withInput();
        }

        if ($request->boolean('remove_image') && $barbershop->image_path) {
            Storage::disk('public')->delete($barbershop->image_path);
            $barbershop->image_path = null;
        }

        if ($request->hasFile('image')) {
            if ($barbershop->image_path) {
                Storage::disk('public')->delete($barbershop->image_path);
            }

            $barbershop->image_path = $request->file('image')->store('barbershops', 'public');
        }

        $this->deleteBarbershopImages($removedPaths);

        $finalImagePaths = array_values(array_merge(
            $remainingPaths,
            $this->storeUploadedBarbershopGalleryImages($request)
        ));

        $barbershop->update([
            'image_path' => $barbershop->image_path,
            'image_paths' => $finalImagePaths === [] ? null : $finalImagePaths,
        ]);

        return redirect()->route('barbershops.editMy')->with('success', 'Barbería actualizada correctamente.');
    }

    public function servicesIndex()
    {
        $barbershop = $this->currentUserBarbershop();
        if (!$barbershop) {
            return redirect()->route('barbershops.create')->with('error', 'No tienes una barbería.');
        }

        $services = $barbershop->services()->orderBy('name')->get();

        return view('barbershops.services.index', compact('barbershop', 'services'));
    }

    public function createService()
    {
        $barbershop = $this->currentUserBarbershop();
        if (!$barbershop) {
            return redirect()->route('barbershops.create')->with('error', 'No tienes una barbería.');
        }

        return view('barbershops.services.create', compact('barbershop'));
    }

    public function storeService(Request $request)
    {
        $barbershop = $this->currentUserBarbershop();
        if (!$barbershop) {
            return redirect()->route('barbershops.create')->with('error', 'No tienes una barbería.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:50',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'visibility' => 'required|in:public,private',
            'images' => 'nullable|array|max:3',
            'images.*' => 'image|max:3072',
        ]);

        $serviceData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'duration' => $validated['duration'],
            'price' => $validated['price'],
            'visibility' => $validated['visibility'],
        ];

        $imagePaths = $this->storeUploadedServiceImages($request);
        if ($imagePaths !== []) {
            $serviceData['image_paths'] = $imagePaths;
            $serviceData['image_path'] = $imagePaths[0];
        }

        $barbershop->services()->create($serviceData);

        return redirect()->route('barbershops.services.index')->with('success', 'Servicio creado correctamente.');
    }

    public function editService(Services $service)
    {
        $barbershop = $this->currentUserBarbershop();
        if (!$barbershop) {
            return redirect()->route('barbershops.create')->with('error', 'No tienes una barbería.');
        }

        abort_unless($service->barbershop_id === $barbershop->id, 403);

        return view('barbershops.services.edit', compact('barbershop', 'service'));
    }

    public function updateService(Request $request, Services $service)
    {
        $barbershop = $this->currentUserBarbershop();
        if (!$barbershop) {
            return redirect()->route('barbershops.create')->with('error', 'No tienes una barbería.');
        }

        abort_unless($service->barbershop_id === $barbershop->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:50',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'visibility' => 'required|in:public,private',
            'images' => 'nullable|array|max:3',
            'images.*' => 'image|max:3072',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'integer',
        ]);

        [$remainingPaths, $removedPaths] = $this->serviceImagePathsAfterRemovalSelection($service, $request);
        $newImageCount = count($request->file('images', []));

        if (count($remainingPaths) + $newImageCount > 3) {
            return back()
                ->withErrors(['images' => 'Cada servicio puede tener como maximo 3 imagenes.'])
                ->withInput();
        }

        $service->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'duration' => $validated['duration'],
            'price' => $validated['price'],
            'visibility' => $validated['visibility'],
        ]);

        $this->deleteServiceImages($removedPaths);

        $finalImagePaths = array_values(array_merge(
            $remainingPaths,
            $this->storeUploadedServiceImages($request)
        ));

        $service->update([
            'image_paths' => $finalImagePaths === [] ? null : $finalImagePaths,
            'image_path' => $finalImagePaths[0] ?? null,
        ]);

        return redirect()->route('barbershops.services.index')->with('success', 'Servicio actualizado correctamente.');
    }

    public function image(Barbershop $barbershop): StreamedResponse
    {
        abort_unless($barbershop->isVisibleTo(auth()->user()), 404);
        $imagePath = $barbershop->image_path;
        abort_unless($imagePath && Storage::disk('public')->exists($imagePath), 404);

        return Storage::disk('public')->response($imagePath);
    }

    public function galleryImage(Barbershop $barbershop, int $index): StreamedResponse
    {
        abort_unless($barbershop->isVisibleTo(auth()->user()), 404);

        $imagePath = $barbershop->stored_image_paths[$index] ?? null;
        abort_unless($imagePath && Storage::disk('public')->exists($imagePath), 404);

        return Storage::disk('public')->response($imagePath);
    }

    public function serviceImage(Services $service): StreamedResponse
    {
        abort_unless($service->isVisibleTo(auth()->user()), 404);
        $imagePath = $service->stored_image_paths[0] ?? null;
        abort_unless($imagePath && Storage::disk('public')->exists($imagePath), 404);

        return Storage::disk('public')->response($imagePath);
    }

    public function serviceGalleryImage(Services $service, int $index): StreamedResponse
    {
        abort_unless($service->isVisibleTo(auth()->user()), 404);

        $imagePath = $service->stored_image_paths[$index] ?? null;
        abort_unless($imagePath && Storage::disk('public')->exists($imagePath), 404);

        return Storage::disk('public')->response($imagePath);
    }

    public function destroyService(Services $service)
    {
        $barbershop = $this->currentUserBarbershop();
        if (!$barbershop) {
            return redirect()->route('barbershops.create')->with('error', 'No tienes una barbería.');
        }

        abort_unless($service->barbershop_id === $barbershop->id, 403);

        if ($service->appointments()->exists()) {
            return redirect()->route('barbershops.services.index')->with('error', 'No puedes eliminar un servicio que ya tiene citas asociadas.');
        }

        $this->deleteServiceImages($service->stored_image_paths);

        $service->delete();

        return redirect()->route('barbershops.services.index')->with('success', 'Servicio eliminado correctamente.');
    }

    private function storeUploadedServiceImages(Request $request): array
    {
        return collect($request->file('images', []))
            ->take(3)
            ->map(fn ($image) => $image->store('services', 'public'))
            ->all();
    }

    private function storeUploadedBarbershopGalleryImages(Request $request): array
    {
        return collect($request->file('gallery_images', []))
            ->take(4)
            ->map(fn ($image) => $image->store('barbershops', 'public'))
            ->all();
    }

    private function barbershopImagePathsAfterRemovalSelection(Barbershop $barbershop, Request $request): array
    {
        $currentPaths = $barbershop->stored_image_paths;
        $removeIndexes = collect($request->input('remove_gallery_images', []))
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

    private function serviceImagePathsAfterRemovalSelection(Services $service, Request $request): array
    {
        $currentPaths = $service->stored_image_paths;
        $removeIndexes = collect($request->input('remove_images', []))
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

    private function deleteServiceImages(array $paths): void
    {
        foreach ($paths as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    private function deleteBarbershopImages(array $paths): void
    {
        foreach ($paths as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
