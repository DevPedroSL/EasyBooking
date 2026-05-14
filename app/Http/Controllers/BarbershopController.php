<?php

namespace App\Http\Controllers;

use App\Http\Requests\Barbershops\StoreServiceRequest;
use App\Http\Requests\Barbershops\UpdateServiceRequest;
use App\Mail\BarbershopRequestCreated;
use App\Models\Barbershop;
use App\Models\BarbershopRequest;
use App\Models\Service;
use App\Models\User;
use App\Services\StoredImageService;
use App\Support\SafeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BarbershopController extends Controller
{
    public function __construct(
        private StoredImageService $storedImageService
    ) {}

    private function currentUserBarbershop()
    {
        return auth()->user()->barbershop()->first();
    }

    private function redirectWithoutBarbershop()
    {
        return redirect()->route('inicio')->with('error', 'No tienes una barbería asignada.');
    }

    public function show(string $name)
    {
        $decodedName = urldecode($name);
        $cacheVersion = Cache::get('public_barbershops_version', '1');
        $cacheKey = 'barbershop_detail:'.$cacheVersion.':'.md5($decodedName);

        $barbershop = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($decodedName) {
            $barbershop = Barbershop::where('name', $decodedName)
                ->with([
                    'barber',
                    'services' => fn ($query) => $query->orderBy('name'),
                ])
                ->firstOrFail();

            $barbershop->services->each->setRelation('barbershop', $barbershop);

            return $barbershop;
        });

        abort_unless($barbershop->isVisibleTo(auth()->user()), 404);

        return view('barbershop', compact('barbershop'));
    }

    public function createRequest()
    {
        $user = auth()->user();

        if ($user->barbershop()->exists()) {
            return redirect()->route('barbershops.dashboard');
        }

        $latestRequest = BarbershopRequest::where('requester_id', $user->id)
            ->latest()
            ->first();

        return view('barbershop_requests.create', compact('latestRequest'));
    }

    public function storeRequest(Request $request)
    {
        $user = auth()->user();

        if ($user->barbershop()->exists()) {
            return redirect()->route('barbershops.dashboard');
        }

        if ($user->barbershopRequests()->where('status', 'pending')->exists()) {
            return redirect()
                ->route('barbershop-requests.create')
                ->with('error', 'Ya tienes una solicitud pendiente de revisión.');
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('barbershops', 'name'),
                Rule::unique('barbershop_requests', 'name')->where(fn ($query) => $query->where('status', 'pending')),
            ],
            'address' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
        ]);

        $validated['visibility'] = 'private';

        $barbershopRequest = DB::transaction(function () use ($user, $validated) {
            $barbershop = $user->barbershop()->create([
                'name' => $validated['name'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'visibility' => 'private',
                'is_approved' => false,
            ]);

            return $user->barbershopRequests()->create(array_merge($validated, [
                'visibility' => $barbershop->visibility,
            ]));
        });

        $adminEmails = User::where('role', 'admin')->pluck('email')->all();
        if ($adminEmails !== []) {
            SafeMail::send($adminEmails, new BarbershopRequestCreated($barbershopRequest), [
                'barbershop_request_id' => $barbershopRequest->id,
            ]);
        }

        return redirect()
            ->route('barbershop-requests.create')
            ->with('success', 'Solicitud enviada correctamente. Un administrador la revisará pronto.');
    }

    public function dashboard()
    {
        $barbershop = $this->currentUserBarbershop();
        if (! $barbershop) {
            return $this->redirectWithoutBarbershop();
        }

        $barbershop->loadCount([
            'services',
            'schedules',
            'appointments',
            'appointments as pending_appointments_count' => fn ($query) => $query->where('status', 'pending'),
        ]);

        return view('barbershops.dashboard', compact('barbershop'));
    }

    public function editMy()
    {
        $barbershop = $this->currentUserBarbershop();
        if (! $barbershop) {
            return $this->redirectWithoutBarbershop();
        }

        $barbershop->load('schedules');
        $weekdays = $this->weekdayOptions();

        return view('barbershops.edit', compact('barbershop', 'weekdays'));
    }

    public function updateMy(Request $request)
    {
        $barbershop = $this->currentUserBarbershop();
        if (! $barbershop) {
            return $this->redirectWithoutBarbershop();
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:barbershops,name,'.$barbershop->id,
            'address' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'visibility' => 'required|in:public,private',
            'image' => 'nullable|image|max:3072',
            'remove_image' => 'nullable|boolean',
            'gallery_images' => 'nullable|array|max:4',
            'gallery_images.*' => 'image|max:3072',
            'remove_gallery_images' => 'nullable|array',
            'remove_gallery_images.*' => 'integer',
        ]);

        if (! $barbershop->is_approved && $validated['visibility'] === 'public') {
            return back()
                ->withErrors(['visibility' => 'Un administrador debe aprobar la barberia antes de publicarla.'])
                ->withInput();
        }

        $barbershop->update([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'visibility' => $validated['visibility'],
        ]);

        [$remainingPaths, $removedPaths] = $this->storedImageService->pathsAfterRemovalSelection(
            $barbershop->stored_image_paths,
            $request,
            'remove_gallery_images'
        );
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

        $this->storedImageService->deletePublicImages($removedPaths);

        $finalImagePaths = array_values(array_merge(
            $remainingPaths,
            $this->storedImageService->storeUploadedImages($request, 'gallery_images', 'barbershops', 4)
        ));

        $barbershop->update([
            'image_path' => $barbershop->image_path,
            'image_paths' => $finalImagePaths === [] ? null : $finalImagePaths,
        ]);

        return redirect()->route('barbershops.editMy')->with('success', 'Barbería actualizada correctamente.');
    }

    public function editSchedule()
    {
        $barbershop = $this->currentUserBarbershop();
        if (! $barbershop) {
            return $this->redirectWithoutBarbershop();
        }

        $barbershop->load('schedules');
        $weekdays = $this->weekdayOptions();

        return view('barbershops.schedule', compact('barbershop', 'weekdays'));
    }

    public function updateSchedule(Request $request)
    {
        $barbershop = $this->currentUserBarbershop();
        if (! $barbershop) {
            return $this->redirectWithoutBarbershop();
        }

        $validated = $request->validate([
            'slot_interval_minutes' => 'nullable|integer|in:15,30,45,60',
        ]);
        $scheduleData = $this->validatedScheduleData($request);

        $barbershop->update([
            'slot_interval_minutes' => $validated['slot_interval_minutes'] ?? $barbershop->slot_interval_minutes ?? 60,
        ]);
        $this->syncSchedules($barbershop, $scheduleData);

        return redirect()->route('barbershops.schedule.edit')->with('success', 'Horario actualizado correctamente.');
    }

    public function servicesIndex()
    {
        $barbershop = $this->currentUserBarbershop();
        if (! $barbershop) {
            return $this->redirectWithoutBarbershop();
        }

        $services = $barbershop->services()->orderBy('name')->get();

        return view('barbershops.services.index', compact('barbershop', 'services'));
    }

    public function createService()
    {
        $barbershop = $this->currentUserBarbershop();
        if (! $barbershop) {
            return $this->redirectWithoutBarbershop();
        }

        return view('barbershops.services.create', compact('barbershop'));
    }

    public function storeService(StoreServiceRequest $request)
    {
        $barbershop = $this->currentUserBarbershop();
        if (! $barbershop) {
            return $this->redirectWithoutBarbershop();
        }

        $validated = $request->validated();

        $serviceData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'duration' => $validated['duration'],
            'price' => $validated['price'],
            'visibility' => $validated['visibility'],
        ];

        $imagePaths = $this->storedImageService->storeUploadedImages($request, 'images', 'services', 3);
        if ($imagePaths !== []) {
            $serviceData['image_paths'] = $imagePaths;
            $serviceData['image_path'] = $imagePaths[0];
        }

        $barbershop->services()->create($serviceData);

        return redirect()->route('barbershops.services.index')->with('success', 'Servicio creado correctamente.');
    }

    public function editService(Service $service)
    {
        $barbershop = $this->currentUserBarbershop();
        if (! $barbershop) {
            return $this->redirectWithoutBarbershop();
        }

        abort_unless($service->barbershop_id === $barbershop->id, 403);

        return view('barbershops.services.edit', compact('barbershop', 'service'));
    }

    public function updateService(UpdateServiceRequest $request, Service $service)
    {
        $barbershop = $this->currentUserBarbershop();
        if (! $barbershop) {
            return $this->redirectWithoutBarbershop();
        }

        abort_unless($service->barbershop_id === $barbershop->id, 403);

        $validated = $request->validated();

        [$remainingPaths, $removedPaths] = $this->storedImageService->pathsAfterRemovalSelection(
            $service->stored_image_paths,
            $request,
            'remove_images'
        );
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

        $this->storedImageService->deletePublicImages($removedPaths);

        $finalImagePaths = array_values(array_merge(
            $remainingPaths,
            $this->storedImageService->storeUploadedImages($request, 'images', 'services', 3)
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

    public function serviceImage(Service $service): StreamedResponse
    {
        abort_unless($service->isVisibleTo(auth()->user()), 404);
        $imagePath = $service->stored_image_paths[0] ?? null;
        abort_unless($imagePath && Storage::disk('public')->exists($imagePath), 404);

        return Storage::disk('public')->response($imagePath);
    }

    public function serviceGalleryImage(Service $service, int $index): StreamedResponse
    {
        abort_unless($service->isVisibleTo(auth()->user()), 404);

        $imagePath = $service->stored_image_paths[$index] ?? null;
        abort_unless($imagePath && Storage::disk('public')->exists($imagePath), 404);

        return Storage::disk('public')->response($imagePath);
    }

    public function destroyService(Service $service)
    {
        $barbershop = $this->currentUserBarbershop();
        if (! $barbershop) {
            return $this->redirectWithoutBarbershop();
        }

        abort_unless($service->barbershop_id === $barbershop->id, 403);

        if ($service->appointments()->exists()) {
            return redirect()->route('barbershops.services.index')->with('error', 'No puedes eliminar un servicio que ya tiene citas asociadas.');
        }

        $this->storedImageService->deletePublicImages($service->stored_image_paths);

        $service->delete();

        return redirect()->route('barbershops.services.index')->with('success', 'Servicio eliminado correctamente.');
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

                    if (! $startTime && ! $endTime) {
                        continue;
                    }

                    if (! $startTime || ! $endTime) {
                        $validator->errors()->add("schedules.$day.$index.start_time", "Indica apertura y cierre para $dayName.");

                        continue;
                    }

                    if (! $this->isValidTime($startTime) || ! $this->isValidTime($endTime)) {
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

        if (! is_array($rawSchedules)) {
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
}
