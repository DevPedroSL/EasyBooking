<?php

namespace App\Services;

use App\Models\Barbershop;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PendingAppointmentService
{
    private const SESSION_KEY = 'pending_appointment';

    public function __construct(
        private AppointmentSelectionService $appointmentSelectionService
    ) {
    }

    public function rememberFromRequest(Request $request): void
    {
        if (!$request->filled(['barbershop_id', 'service_id', 'datetime'])) {
            return;
        }

        $barbershop = Barbershop::findOrFail($request->integer('barbershop_id'));
        $selection = $this->appointmentSelectionService->validateSelectionData(
            $request->only(['service_id', 'datetime']),
            $barbershop
        );

        $request->session()->put(self::SESSION_KEY, [
            'barbershop_id' => $barbershop->id,
            'service_id' => $selection['service']->id,
            'datetime' => $selection['startTime']->format('Y-m-d H:i'),
        ]);
    }

    public function hasPending(Request $request): bool
    {
        return $request->session()->has(self::SESSION_KEY);
    }

    public function consumeAfterAuthentication(Request $request, string $fallbackRoute = 'inicio'): RedirectResponse
    {
        $pending = $request->session()->pull(self::SESSION_KEY);

        if (!$pending) {
            return redirect()->intended(route($fallbackRoute, absolute: false));
        }

        $barbershop = Barbershop::find($pending['barbershop_id'] ?? null);
        if (!$barbershop) {
            return redirect()->route($fallbackRoute);
        }

        $service = Service::where('id', $pending['service_id'] ?? null)
            ->where('barbershop_id', $barbershop->id)
            ->first();

        if (!$service) {
            return redirect()->route($fallbackRoute);
        }

        try {
            $selection = $this->appointmentSelectionService->validateSelectionData([
                'service_id' => $service->id,
                'datetime' => $pending['datetime'] ?? null,
            ], $barbershop);
        } catch (ValidationException $e) {
            return redirect()
                ->route('appointments.create', ['barbershop' => $barbershop, 'service' => $service])
                ->withErrors($e->errors());
        }

        return redirect()->route('appointments.confirm', [
            'barbershop' => $barbershop,
            'service_id' => $selection['service']->id,
            'datetime' => $selection['startTime']->format('Y-m-d H:i'),
        ]);
    }
}
