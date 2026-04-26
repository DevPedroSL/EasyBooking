<?php

namespace App\Http\Controllers;

use App\Models\Barbershop;
use App\Models\Services;
use Illuminate\Http\Request;

class BarbershopController extends Controller
{
    public function create()
    {
        if (auth()->user()->barbershop) {
            return redirect()->route('barbershops.editMy')->with('error', 'Ya tienes una barbería.');
        }
        return view('barbershops.create');
    }

    public function store(Request $request)
    {
        if (auth()->user()->barbershop) {
            return redirect()->route('barbershops.editMy')->with('error', 'Ya tienes una barbería.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:barbershops,name',
            'Description' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $validated['barber_id'] = auth()->id();

        Barbershop::create($validated);

        return redirect()->route('barbershops.editMy')->with('success', 'Barbería creada correctamente.');
    }

    public function editMy()
    {
        $barbershop = auth()->user()->barbershop;
        if (!$barbershop) {
            return redirect()->route('barbershops.create')->with('error', 'No tienes una barbería. Crea una primero.');
        }
        $services = $barbershop->services;
        return view('barbershops.edit', compact('barbershop', 'services'));
    }

    public function updateMy(Request $request)
    {
        $barbershop = auth()->user()->barbershop;
        if (!$barbershop) {
            return redirect()->route('barbershops.create')->with('error', 'No tienes una barbería.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:barbershops,name,' . $barbershop->id,
            'Description' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'services.*.name' => 'nullable|string|max:255',
            'services.*.description' => 'nullable|string|max:50',
            'services.*.duration' => 'nullable|integer|min:1',
            'services.*.price' => 'nullable|numeric|min:0',
            'new_services.*.name' => 'nullable|string|max:255',
            'new_services.*.description' => 'nullable|string|max:50',
            'new_services.*.duration' => 'nullable|integer|min:1',
            'new_services.*.price' => 'nullable|numeric|min:0',
        ]);

        $barbershop->update([
            'name' => $validated['name'],
            'Description' => $validated['Description'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
        ]);

        // Update existing services
        if (isset($validated['services'])) {
            foreach ($validated['services'] as $serviceId => $serviceData) {
                if (!empty($serviceData['name'])) {
                    $service = $barbershop->services()->find($serviceId);
                    if ($service) {
                        $service->update($serviceData);
                    }
                }
            }
        }

        // Create new services
        if (isset($validated['new_services'])) {
            foreach ($validated['new_services'] as $newServiceData) {
                if (!empty($newServiceData['name'])) {
                    $barbershop->services()->create($newServiceData);
                }
            }
        }

        return redirect()->route('barbershops.editMy')->with('success', 'Barbería y servicios actualizados correctamente.');
    }

    public function destroyService(Services $service)
    {
        $barbershop = auth()->user()->barbershop;
        if (!$barbershop) {
            return redirect()->route('barbershops.create')->with('error', 'No tienes una barbería.');
        }

        abort_unless($service->barbershop_id === $barbershop->id, 403);

        if ($service->appointments()->exists()) {
            return redirect()->route('barbershops.editMy')->with('error', 'No puedes eliminar un servicio que ya tiene citas asociadas.');
        }

        $service->delete();

        return redirect()->route('barbershops.editMy')->with('success', 'Servicio eliminado correctamente.');
    }
}
