<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Barbershop;
use App\Models\Service;
use App\Services\AppointmentSelectionService;
use App\Mail\AppointmentAccepted;
use App\Mail\AppointmentCreated;
use App\Mail\AppointmentRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function __construct(
        private AppointmentSelectionService $appointmentSelectionService
    ) {
    }

    public function create(Request $request, Barbershop $barbershop, Service $service)
    {
        abort_unless($barbershop->isVisibleTo(Auth::user()), 404);

        // Ensure service belongs to barbershop
        if ($service->barbershop_id != $barbershop->id) {
            abort(404);
        }

        abort_unless($service->isVisibleTo(Auth::user()), 404);

        $today = Carbon::today();
        $days = [];
        $calendarMonths = [];
        $preselectedDatetime = $request->old('datetime', $request->query('datetime'));
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

                $schedules = $barbershop->schedules
                    ->where('day_of_week', $currentDate->dayOfWeekIso)
                    ->sortBy('start_time')
                    ->values();

                $slots = [];
                $availableSlotCount = 0;
                if ($schedules->isNotEmpty() && $currentDate->gte($today)) {
                    $slots = $this->appointmentSelectionService->getSlotsForServiceInSchedules($barbershop, $currentDate, $schedules, $service);
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
        abort_unless($barbershop->isVisibleTo(Auth::user()), 404);

        $selection = $this->appointmentSelectionService->validateSelectionRequest($request, $barbershop);

        if (!$this->appointmentSelectionService->isSlotAvailable($barbershop, $selection['startTime'], $selection['endTime'])) {
            return redirect()
                ->route('appointments.create', ['barbershop' => $barbershop, 'service' => $selection['service']])
                ->withErrors(['datetime' => 'El horario seleccionado no está disponible.']);
        }

        if ($this->appointmentSelectionService->clientHasAppointmentOnDate(Auth::id(), $selection['startTime'])) {
            return redirect()
                ->route('appointments.create', ['barbershop' => $barbershop, 'service' => $selection['service']])
                ->withErrors(['datetime' => 'Ya tienes una cita reservada para este día.']);
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
        abort_unless($barbershop->isVisibleTo(Auth::user()), 404);

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

        if ($this->appointmentSelectionService->clientHasAppointmentOnDate(Auth::id(), $startTime)) {
            return redirect()
                ->route('appointments.create', ['barbershop' => $barbershop, 'service' => $service])
                ->withErrors(['datetime' => 'Ya tienes una cita reservada para este día.'])
                ->withInput();
        }

        $appointment = Appointment::create([
            'client_id' => Auth::id(),
            'barbershop_id' => $barbershop->id,
            'service_id' => $service->id,
            'appointment_date' => $startTime->format('Y-m-d'),
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'client_comment' => $validated['client_comment'] ?? null,
            'status' => 'pending',
        ]);

        Mail::to($barbershop->barber->email)->send(new AppointmentCreated($appointment));

        return redirect()->route('appointments.my')->with('success', 'Cita reservada exitosamente.');
    }

    public function show(Appointment $appointment)
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
        $appointments = Appointment::where('client_id', Auth::id())
            ->with('barbershop', 'service')
            ->get();

        return view('appointments.my', compact('appointments'));
    }

    public function barberAppointments(Request $request)
    {
        $barbershop = Auth::user()->barbershop;
        if (!$barbershop) {
            abort(403, 'No tienes una barbería asignada.');
        }

        $statusOptions = [
            'pending' => 'Pendientes',
            'accepted' => 'Aceptadas',
            'rejected' => 'Rechazadas',
        ];
        $selectedStatus = $request->query('status');
        if (!array_key_exists($selectedStatus, $statusOptions)) {
            $selectedStatus = null;
        }

        $appointments = Appointment::query()
            ->where('barbershop_id', $barbershop->id)
            ->when($selectedStatus, fn ($query) => $query->where('status', $selectedStatus))
            ->with('client', 'service')
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->get();

        return view('appointments.barber', compact('appointments', 'barbershop', 'statusOptions', 'selectedStatus'));
    }

    public function barberAgenda(Request $request)
    {
        $barbershop = Auth::user()->barbershop;
        if (!$barbershop) {
            abort(403, 'No tienes una barbería asignada.');
        }

        $today = Carbon::today();
        $firstMonthStart = $today->copy()->startOfMonth();
        $lastMonthStart = $today->copy()->addMonthNoOverflow()->startOfMonth();
        $calendarEnd = $lastMonthStart->copy()->endOfMonth();
        $weekdayLabels = ['Lun.', 'Mar.', 'Mie.', 'Jue.', 'Vie.', 'Sab.', 'Dom.'];

        try {
            $selectedDate = $request->query('date')
                ? Carbon::createFromFormat('Y-m-d', (string) $request->query('date'))->startOfDay()
                : $today->copy();
        } catch (\Throwable) {
            $selectedDate = $today->copy();
        }

        if ($selectedDate->lt($firstMonthStart) || $selectedDate->gt($calendarEnd)) {
            $selectedDate = $today->copy();
        }

        $schedules = $barbershop->schedules
            ->where('day_of_week', $selectedDate->dayOfWeekIso)
            ->sortBy('start_time')
            ->values();
        $schedule = $schedules->first();

        $appointmentCountsByDate = Appointment::where('barbershop_id', $barbershop->id)
            ->whereBetween('appointment_date', [$firstMonthStart->format('Y-m-d'), $calendarEnd->format('Y-m-d')])
            ->whereIn('status', ['pending', 'accepted'])
            ->selectRaw('appointment_date, count(*) as appointments_count')
            ->groupBy('appointment_date')
            ->pluck('appointments_count', 'appointment_date');

        $calendarMonths = [];
        $calendarDays = [];

        for ($monthDate = $firstMonthStart->copy(); $monthDate->lte($lastMonthStart); $monthDate->addMonth()) {
            $monthStart = $monthDate->copy()->startOfMonth();
            $monthEnd = $monthDate->copy()->endOfMonth();
            $monthDays = [];

            for ($offset = 0; $offset < $monthStart->dayOfWeekIso - 1; $offset++) {
                $monthDays[] = null;
            }

            for ($date = $monthStart->copy(); $date->lte($monthEnd); $date->addDay()) {
                $currentDate = $date->copy();
                $daySchedules = $barbershop->schedules
                    ->where('day_of_week', $currentDate->dayOfWeekIso)
                    ->values();
                $appointmentCount = (int) ($appointmentCountsByDate[$currentDate->format('Y-m-d')] ?? 0);

                $day = [
                    'index' => count($calendarDays),
                    'date' => $currentDate,
                    'iso_date' => $currentDate->format('Y-m-d'),
                    'day_number' => $currentDate->format('d'),
                    'weekday_label' => $weekdayLabels[$currentDate->dayOfWeekIso - 1],
                    'has_schedule' => $daySchedules->isNotEmpty(),
                    'is_past' => $currentDate->lt($today),
                    'is_selected' => $currentDate->isSameDay($selectedDate),
                    'is_selectable' => $daySchedules->isNotEmpty() && $currentDate->gte($today),
                    'active_appointment_count' => $appointmentCount,
                ];

                $calendarDays[] = $day;
                $monthDays[] = $day;
            }

            while (count($monthDays) % 7 !== 0) {
                $monthDays[] = null;
            }

            $calendarMonths[] = [
                'label' => $monthStart->format('F Y'),
                'days' => $monthDays,
            ];
        }

        $appointments = Appointment::where('barbershop_id', $barbershop->id)
            ->where('appointment_date', $selectedDate->format('Y-m-d'))
            ->whereIn('status', ['pending', 'accepted'])
            ->with('client', 'service')
            ->orderBy('start_time')
            ->get();

        $agendaSlots = [];
        $slotInterval = $this->appointmentSelectionService->slotIntervalMinutes($barbershop);

        foreach ($schedules as $scheduleSlot) {
            $current = $selectedDate->copy()->setTimeFromTimeString($scheduleSlot->start_time);
            $end = $selectedDate->copy()->setTimeFromTimeString($scheduleSlot->end_time);

            while ($current->lt($end)) {
                $slotEnd = $current->copy()->addMinutes($slotInterval);
                if ($slotEnd->gt($end)) {
                    $slotEnd = $end->copy();
                }

                $slotAppointments = $appointments->filter(function (Appointment $appointment) use ($selectedDate, $current, $slotEnd) {
                    $appointmentStart = $selectedDate->copy()->setTimeFromTimeString(
                        $appointment->start_time instanceof Carbon ? $appointment->start_time->format('H:i:s') : (string) $appointment->start_time
                    );
                    $appointmentEnd = $selectedDate->copy()->setTimeFromTimeString(
                        $appointment->end_time instanceof Carbon ? $appointment->end_time->format('H:i:s') : (string) $appointment->end_time
                    );

                    return $appointmentStart->lt($slotEnd) && $appointmentEnd->gt($current);
                })->values();

                $agendaSlots[] = [
                    'start' => $current->copy(),
                    'end' => $slotEnd->copy(),
                    'appointments' => $slotAppointments,
                    'is_busy' => $slotAppointments->isNotEmpty(),
                ];

                $current = $slotEnd;
            }
        }

        $initialVisibleMonthIndex = $selectedDate->isSameMonth($today) ? 0 : 1;

        return view('appointments.agenda', compact(
            'barbershop',
            'selectedDate',
            'schedule',
            'schedules',
            'appointments',
            'agendaSlots',
            'calendarMonths',
            'initialVisibleMonthIndex',
        ));
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $barbershop = Auth::user()->barbershop;
        if (!$barbershop || $appointment->barbershop_id != $barbershop->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:accepted,rejected',
            'rejection_reason' => 'nullable|string|max:300',
            'barber_comment' => 'nullable|string|max:300',
        ]);
        $barberComment = $validated['barber_comment'] ?? $validated['rejection_reason'] ?? null;

        $appointment->update([
            'status' => $validated['status'],
            'barber_comment' => $barberComment,
            'rejection_reason' => $validated['status'] === 'rejected'
                ? $barberComment
                : null,
        ]);

        // Enviar email al cliente
        if ($validated['status'] === 'accepted') {
            Mail::to($appointment->client->email)->send(new AppointmentAccepted($appointment));
        } elseif ($validated['status'] === 'rejected') {
            Mail::to($appointment->client->email)->send(new AppointmentRejected($appointment));
        }

        return redirect()->back()->with('success', 'Estado de la cita actualizado.');
    }

    public function cancel(Appointment $appointment)
    {
        if ($appointment->client_id !== Auth::id()) {
            abort(403);
        }

        if ($appointment->status !== 'pending') {
            return redirect()->back()->with('error', 'Solo puedes cancelar citas pendientes.');
        }

        $appointment->update([
            'status' => 'cancelled',
        ]);

        return redirect()->back()->with('success', 'Cita cancelada correctamente.');
    }
}
