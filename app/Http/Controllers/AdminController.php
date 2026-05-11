<?php

namespace App\Http\Controllers;

use App\Mail\BarbershopRequestApproved;
use App\Mail\BarbershopRequestRejected;
use App\Models\Barbershop;
use App\Models\BarbershopRequest;
use App\Models\User;
use App\Services\StoredImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class AdminController extends Controller
{
    public function __construct(
        private StoredImageService $storedImageService
    ) {}

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
            'pendingBarbershopRequestsCount' => BarbershopRequest::where('status', 'pending')->count(),
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

        [$remainingPaths, $removedPaths] = $this->storedImageService->pathsAfterRemovalSelection(
            $barbershop->stored_image_paths,
            $request,
            'remove_gallery_images'
        );
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

        $this->storedImageService->deletePublicImages($removedPaths);

        $finalImagePaths = array_values(array_merge(
            $remainingPaths,
            $this->storedImageService->storeUploadedImages($request, 'gallery_images', 'barbershops', 4)
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

        $this->storedImageService->deletePublicImages($barbershop->stored_image_paths);

        $barbershop->delete();

        return redirect()->route('admin.barbershops.index')->with('success', 'Barbería eliminada correctamente.');
    }

    public function barbershopRequestsIndex()
    {
        $this->ensureAdmin();

        $requests = BarbershopRequest::with(['requester', 'reviewer'])
            ->latest()
            ->get();

        return view('admin.barbershop_requests.index', compact('requests'));
    }

    public function barbershopRequestsApprove(BarbershopRequest $barbershopRequest)
    {
        $this->ensureAdmin();

        if (! $barbershopRequest->isPending()) {
            return redirect()
                ->route('admin.barbershop-requests.index')
                ->with('error', 'Esta solicitud ya fue revisada.');
        }

        if (Barbershop::where('name', $barbershopRequest->name)->exists()) {
            return redirect()
                ->route('admin.barbershop-requests.index')
                ->with('error', 'Ya existe una barbería con ese nombre.');
        }

        if ($barbershopRequest->requester?->barbershop) {
            return redirect()
                ->route('admin.barbershop-requests.index')
                ->with('error', 'Este usuario ya tiene una barbería asignada.');
        }

        DB::transaction(function () use ($barbershopRequest) {
            $requester = User::whereKey($barbershopRequest->requester_id)->lockForUpdate()->firstOrFail();

            $requester->barbershop()->create([
                'name' => $barbershopRequest->name,
                'address' => $barbershopRequest->address,
                'phone' => $barbershopRequest->phone,
                'visibility' => $barbershopRequest->visibility,
            ]);

            $requester->forceFill([
                'role' => 'barber',
            ])->save();

            $barbershopRequest->update([
                'status' => 'approved',
                'rejection_reason' => null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        });

        $barbershopRequest->refresh()->load('requester');
        Mail::to($barbershopRequest->requester->email)->send(new BarbershopRequestApproved($barbershopRequest));

        return redirect()
            ->route('admin.barbershop-requests.index')
            ->with('success', 'Solicitud aceptada. La barbería ya está creada.');
    }

    public function barbershopRequestsReject(Request $request, BarbershopRequest $barbershopRequest)
    {
        $this->ensureAdmin();

        if (! $barbershopRequest->isPending()) {
            return redirect()
                ->route('admin.barbershop-requests.index')
                ->with('error', 'Esta solicitud ya fue revisada.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $barbershopRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $barbershopRequest->load('requester');
        Mail::to($barbershopRequest->requester->email)->send(new BarbershopRequestRejected($barbershopRequest));

        return redirect()
            ->route('admin.barbershop-requests.index')
            ->with('success', 'Solicitud rechazada correctamente.');
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
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
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

        $backupPath = null;
        $dumpFile = null;
        $zip = new \ZipArchive;
        $zipOpen = false;

        try {
            $backupDir = $this->backupDirectory();

            $timestamp = now()->format('Y-m-d_H-i-s');
            $backupFileName = "backup_{$timestamp}.zip";
            $backupPath = "$backupDir/$backupFileName";
            $dumpFile = "$backupDir/database_dump_{$timestamp}.sql";

            if ($zip->open($backupPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('No se pudo crear el archivo ZIP');
            }
            $zipOpen = true;

            $this->dumpDatabaseToFile($dumpFile);
            $zip->addFile($dumpFile, 'database_dump.sql');
            $this->addProjectFilesToZip($zip);

            $zip->close();
            $zipOpen = false;

            return response()->download($backupPath, $backupFileName)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            if ($backupPath && file_exists($backupPath)) {
                @unlink($backupPath);
            }

            return $this->backupFailureResponse($e, 'Error al crear la copia de seguridad.');
        } finally {
            if ($zipOpen) {
                $zip->close();
            }

            if ($dumpFile && file_exists($dumpFile)) {
                @unlink($dumpFile);
            }
        }
    }

    public function backupDatabase()
    {
        $this->ensureAdmin();

        $backupPath = null;

        try {
            $backupDir = $this->backupDirectory();

            $timestamp = now()->format('Y-m-d_H-i-s');
            $backupFileName = "backup_database_{$timestamp}.sql";
            $backupPath = "$backupDir/$backupFileName";

            $this->dumpDatabaseToFile($backupPath);

            return response()->download($backupPath, $backupFileName)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            if ($backupPath && file_exists($backupPath)) {
                @unlink($backupPath);
            }

            return $this->backupFailureResponse($e, 'Error al crear el backup de la base de datos.');
        }
    }

    private function backupDirectory(): string
    {
        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0750, true);
        }

        return $backupDir;
    }

    private function dumpDatabaseToFile(string $dumpFile): void
    {
        $connection = config('database.default');
        if (! in_array($connection, ['mysql', 'mariadb'], true)) {
            throw new \RuntimeException('El backup automatico solo soporta conexiones mysql o mariadb.');
        }

        $config = config("database.connections.$connection");
        $database = (string) ($config['database'] ?? '');
        $username = (string) ($config['username'] ?? '');
        $password = (string) ($config['password'] ?? '');
        $host = (string) ($config['host'] ?? '127.0.0.1');
        $port = (string) ($config['port'] ?? '3306');

        if ($database === '' || $username === '') {
            throw new \RuntimeException('La conexion de base de datos no esta configurada para backup.');
        }

        $process = new Process([
            'mysqldump',
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--host='.$host,
            '--port='.$port,
            '--user='.$username,
            $database,
        ]);

        if ($password !== '') {
            $process->setEnv(['MYSQL_PWD' => $password]);
        }

        $process->setTimeout(120);
        $errorOutput = '';
        $handle = fopen($dumpFile, 'wb');

        if ($handle === false) {
            throw new \RuntimeException('No se pudo preparar el archivo de backup.');
        }

        try {
            $process->run(function (string $type, string $buffer) use ($handle, &$errorOutput): void {
                if ($type === Process::OUT) {
                    fwrite($handle, $buffer);

                    return;
                }

                $errorOutput .= $buffer;
            });
        } finally {
            fclose($handle);
        }

        if (! $process->isSuccessful() || ! file_exists($dumpFile) || filesize($dumpFile) === 0) {
            @unlink($dumpFile);

            throw new \RuntimeException(trim($errorOutput) ?: 'No se pudo crear el dump de la base de datos.');
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
            '.env.example',
            'composer.json',
            'composer.lock',
            'package.json',
            'package-lock.json',
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
                $relativePath = $zipPath.'/'.substr($filePath, strlen($dir) + 1);
                $zip->addFile($filePath, str_replace('\\', '/', $relativePath));
            }
        }
    }

    private function backupFailureResponse(\Throwable $e, string $message)
    {
        Log::error($message, [
            'exception' => $e,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.dashboard')
            ->with('error', $message.' Revisa los logs del servidor.');
    }
}
