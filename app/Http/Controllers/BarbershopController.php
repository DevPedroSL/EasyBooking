<?php

namespace App\Http\Controllers;

use App\Models\Barbershop;
use Illuminate\Http\Request;

class BarbershopController extends Controller
{
    public function index()
    {
        $barbershops = Barbershop::all();
        return view('admin.barbershops.index', compact('barbershops'));
    }

    public function edit(Barbershop $barbershop)
    {
        return view('admin.barbershops.edit', compact('barbershop'));
    }

    public function update(Request $request, Barbershop $barbershop)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'Description' => 'required|string',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $barbershop->update($validated);

        return redirect()->route('admin.barbershops.index')->with('success', 'Barbería actualizada correctamente.');
    }

    public function destroy(Barbershop $barbershop)
    {
        $barbershop->delete();
        return redirect()->route('admin.barbershops.index')->with('success', 'Barbería eliminada correctamente.');
    }

    // User methods
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
            'Description' => 'required|string',
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
            'Description' => 'required|string',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'services.*.name' => 'nullable|string|max:255',
            'services.*.description' => 'nullable|string',
            'services.*.duration' => 'nullable|integer|min:1',
            'services.*.price' => 'nullable|numeric|min:0',
            'new_services.*.name' => 'nullable|string|max:255',
            'new_services.*.description' => 'nullable|string',
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
}
