<?php

namespace App\Http\Controllers;

use App\Models\Barbershop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'gallery_images' => 'nullable|array|max:4',
            'gallery_images.*' => 'image|max:3072',
            'remove_gallery_images' => 'nullable|array',
            'remove_gallery_images.*' => 'integer',
        ]);

        $barbershop->update(collect($validated)->except(['image', 'remove_image', 'gallery_images', 'remove_gallery_images'])->all());

        [$remainingPaths, $removedPaths] = $this->barbershopImagePathsAfterRemovalSelection($barbershop, $request);
        $newImageCount = count($request->file('gallery_images', []));

        if (count($remainingPaths) + $newImageCount > 4) {
            return back()
                ->withErrors(['gallery_images' => 'Cada barbería puede tener como máximo 4 imágenes de carrusel.'])
                ->withInput();
        }

        if ($request->boolean('remove_image') && $barbershop->image_path) {
            Storage::disk('public')->delete($barbershop->image_path);
            $barbershop->image_path = null;
        }

        if ($request->hasFile('image')) {
            if ($barbershop->image_path) {
                Storage::disk('public')->delete($barbershop->image_path);
            }

            $barbershop->image_path = $request->file('image')->store('barbershops', 'public');
        }

        $this->deleteBarbershopImages($removedPaths);

        $finalImagePaths = array_values(array_merge(
            $remainingPaths,
            $this->storeUploadedBarbershopGalleryImages($request)
        ));

        $barbershop->update([
            'image_path' => $barbershop->image_path,
            'image_paths' => $finalImagePaths === [] ? null : $finalImagePaths,
        ]);

        return redirect()->route('admin.barbershops.index')->with('success', 'Barbería actualizada correctamente.');
    }

    public function barbershopsDestroy(Barbershop $barbershop)
    {
        $this->ensureAdmin();

        $this->deleteBarbershopImages($barbershop->stored_image_paths);

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
            'is_banned' => 'nullable|boolean',
        ]);

        $isBanned = $request->boolean('is_banned');

        if ($user->id === auth()->id() && $isBanned) {
            return redirect()->route('admin.users.edit', $user)->with('error', 'No puedes deshabilitar tu propia cuenta.');
        }

        $wasBanned = $user->is_banned;

        $user->update([
            ...$validated,
            'is_banned' => $isBanned,
        ]);

        if (! $wasBanned && $isBanned) {
            $this->disableUserAccess($user);
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function usersBan(User $user)
    {
        $this->ensureAdmin();

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'No puedes deshabilitar tu propia cuenta.');
        }

        if ($user->is_banned) {
            return redirect()->route('admin.users.index')->with('success', 'La cuenta ya estaba deshabilitada.');
        }

        $user->forceFill(['is_banned' => true])->save();
        $this->disableUserAccess($user);

        return redirect()->route('admin.users.index')->with('success', 'Cuenta deshabilitada correctamente.');
    }

    public function usersUnban(User $user)
    {
        $this->ensureAdmin();

        if (! $user->is_banned) {
            return redirect()->route('admin.users.index')->with('success', 'La cuenta ya estaba activa.');
        }

        $user->forceFill(['is_banned' => false])->save();

        return redirect()->route('admin.users.index')->with('success', 'Cuenta reactivada correctamente.');
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

    private function storeUploadedBarbershopGalleryImages(Request $request): array
    {
        return collect($request->file('gallery_images', []))
            ->take(4)
            ->map(fn ($image) => $image->store('barbershops', 'public'))
            ->all();
    }

    private function barbershopImagePathsAfterRemovalSelection(Barbershop $barbershop, Request $request): array
    {
        $currentPaths = $barbershop->stored_image_paths;
        $removeIndexes = collect($request->input('remove_gallery_images', []))
            ->map(fn ($index) => (int) $index)
            ->filter(fn ($index) => array_key_exists($index, $currentPaths))
            ->unique()
            ->values()
            ->all();

        $removedPaths = [];
        $remainingPaths = [];

        foreach ($currentPaths as $index => $path) {
            if (in_array($index, $removeIndexes, true)) {
                $removedPaths[] = $path;
                continue;
            }

            $remainingPaths[] = $path;
        }

        return [$remainingPaths, $removedPaths];
    }

    private function deleteBarbershopImages(array $paths): void
    {
        foreach ($paths as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    private function disableUserAccess(User $user): void
    {
        $user->forceFill([
            'remember_token' => Str::random(60),
        ])->save();

        DB::table('sessions')->where('user_id', $user->id)->delete();
    }
}
