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
}
