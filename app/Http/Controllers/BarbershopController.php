<?php

namespace App\Http\Controllers;

use App\Models\Barbershop;
use App\Models\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        return view('barbershops.create');
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
            'visibility' => 'required|in:public,private',
            'image' => 'nullable|image|max:3072',
        ]);

        $validated['barber_id'] = auth()->id();

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('barbershops', 'public');
        }

        Barbershop::create($validated);

        return redirect()->route('barbershops.editMy')->with('success', 'Barbería creada correctamente.');
    }

    public function editMy()
    {
        $barbershop = $this->currentUserBarbershop();
        if (!$barbershop) {
            return redirect()->route('barbershops.create')->with('error', 'No tienes una barbería. Crea una primero.');
        }

        return view('barbershops.edit', compact('barbershop'));
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
            'visibility' => 'required|in:public,private',
            'image' => 'nullable|image|max:3072',
            'remove_image' => 'nullable|boolean',
        ]);

        $barbershop->update([
            'name' => $validated['name'],
            'Description' => $validated['Description'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'visibility' => $validated['visibility'],
        ]);

        if ($request->boolean('remove_image') && $barbershop->image_path) {
            Storage::disk('public')->delete($barbershop->image_path);
            $barbershop->update([
                'image_path' => null,
            ]);
        }

        if ($request->hasFile('image')) {
            if ($barbershop->image_path) {
                Storage::disk('public')->delete($barbershop->image_path);
            }

            $barbershop->update([
                'image_path' => $request->file('image')->store('barbershops', 'public'),
            ]);
        }

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
        ]);

        $barbershop->services()->create($validated);

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
        ]);

        $service->update($validated);

        return redirect()->route('barbershops.services.index')->with('success', 'Servicio actualizado correctamente.');
    }

    public function image(Barbershop $barbershop): StreamedResponse
    {
        abort_unless($barbershop->isVisibleTo(auth()->user()), 404);
        abort_unless($barbershop->image_path && Storage::disk('public')->exists($barbershop->image_path), 404);

        return Storage::disk('public')->response($barbershop->image_path);
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

        $service->delete();

        return redirect()->route('barbershops.services.index')->with('success', 'Servicio eliminado correctamente.');
    }
}
