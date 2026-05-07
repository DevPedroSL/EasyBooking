<?php

namespace App\Http\Controllers;

use App\Models\Barbershop;
use App\Models\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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

        $weekdays = $this->weekdayOptions();

        return view('barbershops.create', compact('weekdays'));
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
            'slot_interval_minutes' => 'nullable|integer|in:15,30,45,60',
            'visibility' => 'required|in:public,private',
            'image' => 'nullable|image|max:3072',
            'gallery_images' => 'nullable|array|max:4',
            'gallery_images.*' => 'image|max:3072',
        ]);
        $scheduleData = $this->validatedScheduleData($request);

        $validated['barber_id'] = auth()->id();
        $validated['slot_interval_minutes'] = $validated['slot_interval_minutes'] ?? 60;

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('barbershops', 'public');
        }

        $galleryImagePaths = $this->storeUploadedBarbershopGalleryImages($request);
        if ($galleryImagePaths !== []) {
            $validated['image_paths'] = $galleryImagePaths;
        }

        $barbershop = Barbershop::create($validated);
        $this->syncSchedules($barbershop, $scheduleData);

        return redirect()->route('barbershops.editMy')->with('success', 'Barbería creada correctamente.');
    }

    public function editMy()
    {
        $barbershop = $this->currentUserBarbershop();
        if (!$barbershop) {
            return redirect()->route('barbershops.create')->with('error', 'No tienes una barbería. Crea una primero.');
        }

        $barbershop->load('schedules');
        $weekdays = $this->weekdayOptions();

        return view('barbershops.edit', compact('barbershop', 'weekdays'));
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
            'slot_interval_minutes' => 'nullable|integer|in:15,30,45,60',
            'visibility' => 'required|in:public,private',
            'image' => 'nullable|image|max:3072',
            'remove_image' => 'nullable|boolean',
            'gallery_images' => 'nullable|array|max:4',
            'gallery_images.*' => 'image|max:3072',
            'remove_gallery_images' => 'nullable|array',
            'remove_gallery_images.*' => 'integer',
        ]);
        $scheduleData = $this->validatedScheduleData($request);

        $barbershop->update([
            'name' => $validated['name'],
            'Description' => $validated['Description'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'slot_interval_minutes' => $validated['slot_interval_minutes'] ?? $barbershop->slot_interval_minutes ?? 60,
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
        $this->syncSchedules($barbershop, $scheduleData);

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

    private function weekdayOptions(): array
    {
        return [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miercoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sabado',
            7 => 'Domingo',
        ];
    }

    private function validatedScheduleData(Request $request): array
    {
        $scheduleDays = collect($request->input('schedule_days', []))
            ->map(fn ($day) => (int) $day)
            ->filter(fn ($day) => $day >= 1 && $day <= 7)
            ->unique()
            ->values();

        $validator = Validator::make($request->all(), [
            'schedule_days' => 'required|array|min:1',
            'schedule_days.*' => 'integer|between:1,7',
            'schedules' => 'required|array',
        ]);

        $validator->after(function ($validator) use ($request, $scheduleDays) {
            foreach ($scheduleDays as $day) {
                $dayName = $this->weekdayOptions()[$day];
                $intervals = $this->scheduleIntervalsFromRequest($request, $day);
                $validIntervals = [];

                foreach ($intervals as $index => $interval) {
                    $startTime = $interval['start_time'];
                    $endTime = $interval['end_time'];

                    if (!$startTime && !$endTime) {
                        continue;
                    }

                    if (!$startTime || !$endTime) {
                        $validator->errors()->add("schedules.$day.$index.start_time", "Indica apertura y cierre para $dayName.");
                        continue;
                    }

                    if (!$this->isValidTime($startTime) || !$this->isValidTime($endTime)) {
                        $validator->errors()->add("schedules.$day.$index.start_time", "El horario de $dayName debe tener formato HH:MM.");
                        continue;
                    }

                    if ($endTime <= $startTime) {
                        $validator->errors()->add("schedules.$day.$index.end_time", "El cierre de $dayName debe ser posterior a la apertura.");
                        continue;
                    }

                    $validIntervals[] = $interval;
                }

                if ($validIntervals === []) {
                    $validator->errors()->add("schedules.$day.0.start_time", "Indica al menos un tramo horario para $dayName.");
                    continue;
                }

                $sortedIntervals = collect($validIntervals)->sortBy('start_time')->values();
                for ($index = 1; $index < $sortedIntervals->count(); $index++) {
                    if ($sortedIntervals[$index - 1]['end_time'] > $sortedIntervals[$index]['start_time']) {
                        $validator->errors()->add("schedules.$day.$index.start_time", "Los tramos de $dayName no se pueden solapar.");
                    }
                }
            }
        });

        $validator->validate();

        return $scheduleDays
            ->mapWithKeys(function ($day) use ($request) {
                $intervals = collect($this->scheduleIntervalsFromRequest($request, $day))
                    ->filter(fn (array $interval) => $interval['start_time'] && $interval['end_time'])
                    ->sortBy('start_time')
                    ->values()
                    ->all();

                return [$day => $intervals];
            })
            ->all();
    }

    private function syncSchedules(Barbershop $barbershop, array $scheduleData): void
    {
        $barbershop->schedules()->delete();

        foreach ($scheduleData as $day => $schedules) {
            foreach ($schedules as $schedule) {
                $barbershop->schedules()->create([
                    'day_of_week' => $day,
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time'],
                ]);
            }
        }
    }

    private function scheduleIntervalsFromRequest(Request $request, int $day): array
    {
        $rawSchedules = $request->input("schedules.$day", []);

        if (!is_array($rawSchedules)) {
            return [];
        }

        if (array_key_exists('start_time', $rawSchedules) || array_key_exists('end_time', $rawSchedules)) {
            return [[
                'start_time' => $rawSchedules['start_time'] ?? null,
                'end_time' => $rawSchedules['end_time'] ?? null,
            ]];
        }

        return collect([0, 1])
            ->map(fn (int $index) => [
                'start_time' => $request->input("schedules.$day.$index.start_time"),
                'end_time' => $request->input("schedules.$day.$index.end_time"),
            ])
            ->all();
    }

    private function isValidTime(?string $time): bool
    {
        return is_string($time) && preg_match('/\A([01]\d|2[0-3]):[0-5]\d\z/', $time) === 1;
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
