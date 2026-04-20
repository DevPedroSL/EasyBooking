<?php

namespace App\Http\Controllers;

use App\Models\Appointments;
use App\Models\Barbershop;
use App\Models\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function create(Barbershop $barbershop, Services $service)
    {
        // Ensure service belongs to barbershop
        if ($service->barbershop_id != $barbershop->id) {
            abort(404);
        }

        $schedules = $barbershop->schedules;

        // Get next 7 days
        $days = [];
        $today = Carbon::today();
        for ($i = 0; $i < 7; $i++) {
            $date = $today->copy()->addDays($i);
            $dayOfWeek = $date->dayOfWeekIso; // 1=Monday, 7=Sunday
            $schedule = $schedules->where('day_of_week', $dayOfWeek)->first();
            if ($schedule) {
                $availableSlots = $this->getAvailableSlotsForService($barbershop, $date, $schedule, $service);
                if (!empty($availableSlots)) {
                    $days[] = [
                        'date' => $date,
                        'slots' => $availableSlots
                    ];
                }
            }
        }

        return view('appointments.create', compact('barbershop', 'service', 'days'));
    }

    public function store(Request $request, Barbershop $barbershop)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'datetime' => 'required|date_format:Y-m-d H:i',
        ]);

        $service = Services::find($validated['service_id']);
        $startTime = Carbon::createFromFormat('Y-m-d H:i', $validated['datetime']);
        $endTime = $startTime->copy()->addMinutes($service->duration);

        // Check if slot is available
        $existing = Appointments::where('barbershop_id', $barbershop->id)
            ->where('appointment_date', $startTime->format('Y-m-d'))
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime->format('H:i:s'), $endTime->format('H:i:s')])
                      ->orWhereBetween('end_time', [$startTime->format('H:i:s'), $endTime->format('H:i:s')])
                      ->orWhere(function ($q) use ($startTime, $endTime) {
                          $q->where('start_time', '<=', $startTime->format('H:i:s'))
                            ->where('end_time', '>=', $endTime->format('H:i:s'));
                      });
            })
            ->exists();

        if ($existing) {
            return back()->withErrors(['datetime' => 'El horario seleccionado no está disponible.']);
        }

        Appointments::create([
            'client_id' => Auth::id(),
            'barbershop_id' => $barbershop->id,
            'service_id' => $validated['service_id'],
            'appointment_date' => $startTime->format('Y-m-d'),
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'status' => 'pending',
        ]);

        return redirect()->route('appointments.my')->with('success', 'Cita reservada exitosamente.');
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

    private function getAvailableSlotsForService(Barbershop $barbershop, Carbon $date, $schedule, Services $service)
    {
        $start = Carbon::createFromTimeString($schedule->start_time);
        $end = Carbon::createFromTimeString($schedule->end_time);

        $slots = [];

        // Get existing appointments for the day
        $existingAppointments = Appointments::where('barbershop_id', $barbershop->id)
            ->where('appointment_date', $date->format('Y-m-d'))
            ->where('status', '!=', 'cancelled')
            ->get(['start_time', 'end_time']);

        $current = $start->copy();
        while ($current->copy()->addMinutes($service->duration) <= $end) {
            $slotEnd = $current->copy()->addMinutes($service->duration);
            $available = true;

            foreach ($existingAppointments as $appt) {
                $apptStart = Carbon::createFromTimeString($appt->start_time);
                $apptEnd = Carbon::createFromTimeString($appt->end_time);

                if ($current < $apptEnd && $slotEnd > $apptStart) {
                    $available = false;
                    break;
                }
            }

            if ($available) {
                $slots[] = $current->format('H:i');
            }

            $current->addMinutes(15); // Check every 15 min
        }

        return $slots;
    }
}