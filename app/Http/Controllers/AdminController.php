<?php

namespace App\Http\Controllers;

use App\Models\Barbershop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
    }

    public function dashboard()
    {
        $this->ensureAdmin();

        return view('admin.index', [
            'barbershopsCount' => Barbershop::count(),
            'usersCount' => User::count(),
            'barbersCount' => User::where('role', 'barber')->count(),
            'customersCount' => User::where('role', 'customer')->count(),
        ]);
    }

    public function barbershopsIndex()
    {
        $this->ensureAdmin();

        $barbershops = Barbershop::with('barber')->latest()->get();

        return view('admin.barbershops.index', compact('barbershops'));
    }

    public function barbershopsEdit(Barbershop $barbershop)
    {
        $this->ensureAdmin();

        return view('admin.barbershops.edit', compact('barbershop'));
    }

    public function barbershopsUpdate(Request $request, Barbershop $barbershop)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'Description' => 'required|string|max:150',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'visibility' => 'required|in:public,private',
            'image' => 'nullable|image|max:3072',
            'remove_image' => 'nullable|boolean',
        ]);

        $barbershop->update(collect($validated)->except(['image', 'remove_image'])->all());

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

        return redirect()->route('admin.barbershops.index')->with('success', 'Barbería actualizada correctamente.');
    }

    public function barbershopsDestroy(Barbershop $barbershop)
    {
        $this->ensureAdmin();

        if ($barbershop->image_path) {
            Storage::disk('public')->delete($barbershop->image_path);
        }

        $barbershop->delete();

        return redirect()->route('admin.barbershops.index')->with('success', 'Barbería eliminada correctamente.');
    }

    public function usersIndex()
    {
        $this->ensureAdmin();

        $users = User::with('barbershop')->latest()->get();

        return view('admin.users.index', compact('users'));
    }

    public function usersEdit(User $user)
    {
        $this->ensureAdmin();

        return view('admin.users.edit', compact('user'));
    }

    public function usersUpdate(Request $request, User $user)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'role' => 'required|in:admin,barber,customer',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function usersDestroy(User $user)
    {
        $this->ensureAdmin();

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'No puedes eliminar tu propio usuario administrador.');
        }

        if ($user->barbershop) {
            return redirect()->route('admin.users.index')->with('error', 'No puedes eliminar un usuario que tenga una barbería asociada.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
