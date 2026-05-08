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

   public function backup()
    {
        $this->ensureAdmin();

        try {
            $backupDir = storage_path('app/backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $timestamp = now()->format('Y-m-d_H-i-s');
            $backupFileName = "backup_{$timestamp}.zip";
            $backupPath = "$backupDir/$backupFileName";

            $zip = new \ZipArchive();
            if ($zip->open($backupPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('No se pudo crear el archivo ZIP');
            }

            // Agregar base de datos
            $this->addDatabaseToZip($zip, $backupDir);

            // Agregar archivos importantes del proyecto
            $this->addProjectFilesToZip($zip);

            $zip->close();

            return response()->download($backupPath, $backupFileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Error al crear la copia de seguridad: ' . $e->getMessage());
        }
    }

    public function backupDatabase()
    {
        $this->ensureAdmin();

        try {
            $backupDir = storage_path('app/backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $timestamp = now()->format('Y-m-d_H-i-s');
            $backupFileName = "backup_database_{$timestamp}.sql";
            $backupPath = "$backupDir/$backupFileName";

            $dbPath = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPassword = config('database.connections.mysql.password');
            $dbHost = config('database.connections.mysql.host');

            // Usar mysqldump para crear el dump
            $command = sprintf(
                'mysqldump -h %s -u %s %s',
                escapeshellarg($dbHost),
                escapeshellarg($dbUser),
                escapeshellarg($dbPath)
            );

            if (!empty($dbPassword)) {
                $command = sprintf(
                    'mysqldump -h %s -u %s -p%s %s',
                    escapeshellarg($dbHost),
                    escapeshellarg($dbUser),
                    escapeshellarg($dbPassword),
                    escapeshellarg($dbPath)
                );
            }

            $command .= " > " . escapeshellarg($backupPath);

            $output = null;
            $returnVar = null;
            @exec($command, $output, $returnVar);

            if (!file_exists($backupPath) || filesize($backupPath) === 0) {
                throw new \Exception('No se pudo crear el dump de la base de datos');
            }

            return response()->download($backupPath, $backupFileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Error al crear el backup de la base de datos: ' . $e->getMessage());
        }
    }

    private function addDatabaseToZip(\ZipArchive $zip, string $backupDir): void
    {
        $dbPath = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPassword = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');

        $dumpFile = "$backupDir/database_dump.sql";

        // Usar mysqldump si está disponible
        $command = sprintf(
            'mysqldump -h %s -u %s %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbPath)
        );

        if (!empty($dbPassword)) {
            $command = sprintf(
                'mysqldump -h %s -u %s -p%s %s',
                escapeshellarg($dbHost),
                escapeshellarg($dbUser),
                escapeshellarg($dbPassword),
                escapeshellarg($dbPath)
            );
        }

        $command .= " > " . escapeshellarg($dumpFile);

        $output = null;
        $returnVar = null;
        @exec($command, $output, $returnVar);

        if (file_exists($dumpFile)) {
            $zip->addFile($dumpFile, 'database_dump.sql');
        }
    }

    private function addProjectFilesToZip(\ZipArchive $zip): void
    {
        $projectRoot = base_path();
        $filesToBackup = [
            'app',
            'config',
            'database/migrations',
            'database/seeders',
            'routes',
            'resources/views',
            'resources/js',
            'resources/css',
            '.env',
            'composer.json',
            'package.json',
        ];

        foreach ($filesToBackup as $fileOrDir) {
            $fullPath = "$projectRoot/$fileOrDir";

            if (is_file($fullPath)) {
                $zip->addFile($fullPath, $fileOrDir);
            } elseif (is_dir($fullPath)) {
                $this->addDirToZip($zip, $fullPath, $fileOrDir);
            }
        }

        // Agregar imágenes públicas
        $publicImagesPath = "$projectRoot/storage/app/public";
        if (is_dir($publicImagesPath)) {
            $this->addDirToZip($zip, $publicImagesPath, 'storage/app/public');
        }
    }

    private function addDirToZip(\ZipArchive $zip, string $dir, string $zipPath): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $filePath = $file->getRealPath();
                $relativePath = $zipPath . '/' . substr($filePath, strlen($dir) + 1);
                $zip->addFile($filePath, str_replace('\\', '/', $relativePath));
            }
        }
    }
}
