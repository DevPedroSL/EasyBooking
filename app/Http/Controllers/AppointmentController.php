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

    public function create(Request $request, Barbershop $barbershop, Services $service)
    {
        abort_unless($barbershop->isVisibleTo(auth()->user()), 404);

        // Ensure service belongs to barbershop
        if ($service->barbershop_id != $barbershop->id) {
            abort(404);
        }

        abort_unless($service->isVisibleTo(auth()->user()), 404);

        $today = Carbon::today();
        $days = [];
        $calendarMonths = [];
        $preselectedDatetime = $request->old('datetime');
        $preselectedDate = null;
        $weekdayLabels = ['Lun.', 'Mar.', 'Mie.', 'Jue.', 'Vie.', 'Sab.', 'Dom.'];
        $firstMonthStart = $today->copy()->startOfMonth();
        $lastMonthStart = $today->copy()->addMonthNoOverflow()->startOfMonth();

        for ($monthDate = $firstMonthStart->copy(); $monthDate->lte($lastMonthStart); $monthDate->addMonth()) {
            $monthStart = $monthDate->copy()->startOfMonth();
            $monthEnd = $monthDate->copy()->endOfMonth();
            $calendarDays = [];

            for ($offset = 0; $offset < $monthStart->dayOfWeekIso - 1; $offset++) {
                $calendarDays[] = null;
            }

            for ($date = $monthStart->copy(); $date->lte($monthEnd); $date->addDay()) {
                $currentDate = $date->copy();

                $schedule = $barbershop->schedules
                    ->where('day_of_week', $currentDate->dayOfWeekIso)
                    ->first();

                $slots = [];
                $availableSlotCount = 0;
                if ($schedule && $currentDate->gte($today)) {
                    $slots = $this->appointmentSelectionService->getSlotsForService($barbershop, $currentDate, $schedule, $service);
                    $availableSlotCount = collect($slots)->where('available', true)->count();
                }

                $day = [
                    'index' => count($days),
                    'date' => $currentDate,
                    'iso_date' => $currentDate->format('Y-m-d'),
                    'day_number' => $currentDate->format('d'),
                    'weekday_label' => $weekdayLabels[$currentDate->dayOfWeekIso - 1],
                    'slots' => $slots,
                    'available_slot_count' => $availableSlotCount,
                    'is_past' => $currentDate->lt($today),
                ];

                $days[] = $day;
                $calendarDays[] = $day;
            }

            while (count($calendarDays) % 7 !== 0) {
                $calendarDays[] = null;
            }

            $calendarMonths[] = [
                'label' => $monthStart->format('F Y'),
                'days' => $calendarDays,
            ];
        }

        if ($preselectedDatetime) {
            try {
                $preselectedDate = Carbon::createFromFormat('Y-m-d H:i', $preselectedDatetime)->format('Y-m-d');
            } catch (\Throwable) {
                $preselectedDatetime = null;
            }
        }

        $hasAvailableSlots = collect($days)->contains(fn (array $day) => $day['available_slot_count'] > 0);
        $initialSelectedDayIndex = collect($days)->search(
            fn (array $day) => $preselectedDate !== null && $day['iso_date'] === $preselectedDate
        );

        if ($initialSelectedDayIndex === false) {
            $initialSelectedDayIndex = $hasAvailableSlots
                ? collect($days)->search(fn (array $day) => $day['available_slot_count'] > 0)
                : collect($days)->search(fn (array $day) => $day['iso_date'] === $today->format('Y-m-d'));
        }

        $initialVisibleMonthIndex = 0;

        if ($initialSelectedDayIndex !== false && isset($days[$initialSelectedDayIndex])) {
            $selectedDate = $days[$initialSelectedDayIndex]['date'];
            $initialVisibleMonthIndex = $selectedDate->isSameMonth($today) ? 0 : 1;
        }

        return view('appointments.create', compact(
            'barbershop',
            'service',
            'days',
            'calendarMonths',
            'hasAvailableSlots',
            'initialSelectedDayIndex',
            'initialVisibleMonthIndex',
            'preselectedDatetime',
        ));
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
