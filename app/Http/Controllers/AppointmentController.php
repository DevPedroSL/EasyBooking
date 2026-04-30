<?php

namespace App\Http\Controllers;

use App\Models\Appointments;
use App\Models\Barbershop;
use App\Models\Services;
use App\Services\AppointmentSelectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function __construct(
        private AppointmentSelectionService $appointmentSelectionService
    ) {
    }

    public function create(Barbershop $barbershop, Services $service)
    {
        abort_unless($barbershop->isVisibleTo(auth()->user()), 404);

        // Ensure service belongs to barbershop
        if ($service->barbershop_id != $barbershop->id) {
            abort(404);
        }

        abort_unless($service->isVisibleTo(auth()->user()), 404);

        $days = [];
        $tomorrow = Carbon::tomorrow();
        $schedule = $barbershop->schedules
            ->where('day_of_week', $tomorrow->dayOfWeekIso)
            ->first();

        if ($schedule) {
            $availableSlots = $this->appointmentSelectionService->getAvailableSlotsForService($barbershop, $tomorrow, $schedule, $service);
            if (!empty($availableSlots)) {
                $days[] = [
                    'date' => $tomorrow,
                    'slots' => $availableSlots
                ];
            }
        }

        return view('appointments.create', compact('barbershop', 'service', 'days'));
    }

    public function confirm(Request $request, Barbershop $barbershop)
    {
        abort_unless($barbershop->isVisibleTo(auth()->user()), 404);

        $selection = $this->appointmentSelectionService->validateSelectionRequest($request, $barbershop);

        if (!$this->appointmentSelectionService->isSlotAvailable($barbershop, $selection['startTime'], $selection['endTime'])) {
            return redirect()
                ->route('appointments.create', ['barbershop' => $barbershop, 'service' => $selection['service']])
                ->withErrors(['datetime' => 'El horario seleccionado no está disponible.']);
        }

        return view('appointments.confirm', [
            'barbershop' => $barbershop,
            'service' => $selection['service'],
            'startTime' => $selection['startTime'],
            'endTime' => $selection['endTime'],
            'datetime' => $selection['startTime']->format('Y-m-d H:i'),
        ]);
    }

    public function store(Request $request, Barbershop $barbershop)
    {
        abort_unless($barbershop->isVisibleTo(auth()->user()), 404);

        $selection = $this->appointmentSelectionService->validateSelectionRequest($request, $barbershop, [
            'client_comment' => 'nullable|string|max:150',
        ]);

        $validated = $selection['validated'];
        $service = $selection['service'];
        $startTime = $selection['startTime'];
        $endTime = $selection['endTime'];

        if (!$this->appointmentSelectionService->isSlotAvailable($barbershop, $startTime, $endTime)) {
            return redirect()
                ->route('appointments.create', ['barbershop' => $barbershop, 'service' => $service])
                ->withErrors(['datetime' => 'El horario seleccionado no está disponible.'])
                ->withInput();
        }

        Appointments::create([
            'client_id' => Auth::id(),
            'barbershop_id' => $barbershop->id,
            'service_id' => $service->id,
            'appointment_date' => $startTime->format('Y-m-d'),
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'client_comment' => $validated['client_comment'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('appointments.my')->with('success', 'Cita reservada exitosamente.');
    }

    public function show(Appointments $appointment)
    {
        $appointment->load('client', 'service', 'barbershop');

        $user = Auth::user();
        $userBarbershop = $user->barbershop;
        $canViewAsBarber = $userBarbershop && $appointment->barbershop_id === $userBarbershop->id;
        $canViewAsClient = $appointment->client_id === $user->id;

        if (!$canViewAsBarber && !$canViewAsClient) {
            abort(403);
        }

        return view('appointments.show', compact('appointment'));
    }

    public function my()
    {
        $appointments = Auth::user()->appointmentsAsClient()->with('barbershop', 'service')->get();
        return view('appointments.my', compact('appointments'));
    }

    public function barberAppointments()
    {
        $barbershop = Auth::user()->barbershop;
        if (!$barbershop) {
            abort(403, 'No tienes una barbería asignada.');
        }

        $appointments = Appointments::where('barbershop_id', $barbershop->id)
            ->with('client', 'service')
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->get();

        return view('appointments.barber', compact('appointments', 'barbershop'));
    }

    public function updateStatus(Request $request, Appointments $appointment)
    {
        $barbershop = Auth::user()->barbershop;
        if (!$barbershop || $appointment->barbershop_id != $barbershop->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        $appointment->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Estado de la cita actualizado.');
    }

    public function cancel(Appointments $appointment)
    {
        if ($appointment->client_id !== Auth::id()) {
            abort(403);
        }

        if ($appointment->status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', 'Solo puedes cancelar citas que estén pendientes.');
        }

        $appointment->update([
            'status' => 'cancelled',
        ]);

        return redirect()
            ->back()
            ->with('success', 'La cita ha sido cancelada correctamente.');
    }
}
