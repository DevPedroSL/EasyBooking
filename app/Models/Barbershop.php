<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Barbershop extends Model
{
    use HasFactory;

    protected $fillable = [
        'barber_id',
        'name',
        'address',
        'phone',
        'slot_interval_minutes',
        'visibility',
        'image_path',
        'image_paths',
    ];

    protected $casts = [
        'image_paths' => 'array',
        'slot_interval_minutes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function barber()
    {
        return $this->belongsTo(User::class, 'barber_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('visibility', 'public');
    }

    public function isVisibleTo(?User $user): bool
    {
        if ($this->visibility === 'public') {
            return true;
        }

        return $user !== null && ($user->role === 'admin' || $user->id === $this->barber_id);
    }

    public function getStoredImagePathsAttribute(): array
    {
        $paths = $this->image_paths;

        if (is_array($paths) && $paths !== []) {
            return array_values(array_filter($paths, fn ($path) => is_string($path) && $path !== ''));
        }

        return [];
    }

    public function getGalleryImagesAttribute(): array
    {
        return collect($this->stored_image_paths)
            ->map(function (string $path, int $index): ?array {
                if ($this->image_path && $path === $this->image_path) {
                    return null;
                }

                return [
                    'index' => $index,
                    'url' => route('barbershops.images.show', [
                        'barbershop' => $this,
                        'index' => $index,
                    ], false),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function getImageUrlsAttribute(): array
    {
        return collect($this->gallery_images)
            ->pluck('url')
            ->all();
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path || !Storage::disk('public')->exists($this->image_path)) {
            return null;
        }

        return route('barbershops.image', $this, false);
    }
}
