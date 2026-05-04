<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Services extends Model
{
    use HasFactory;

    protected $fillable = [
        'barbershop_id',
        'name',
        'description',
        'duration',
        'price',
        'visibility',
        'image_path',
        'image_paths',
    ];

    protected $casts = [
        'duration' => 'integer',
        'price' => 'decimal:2',
        'image_paths' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function barbershop()
    {
        return $this->belongsTo(Barbershop::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointments::class, 'service_id');
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

        return $user !== null
            && (
                $user->role === 'admin'
                || $user->id === $this->barbershop?->barber_id
            );
    }

    public function getStoredImagePathsAttribute(): array
    {
        $paths = $this->image_paths;

        if (is_array($paths) && $paths !== []) {
            return array_values(array_filter($paths, fn ($path) => is_string($path) && $path !== ''));
        }

        if ($this->image_path) {
            return [$this->image_path];
        }

        return [];
    }

    public function getImageUrlsAttribute(): array
    {
        return collect($this->stored_image_paths)
            ->take(3)
            ->values()
            ->map(fn (string $path, int $index) => route('services.images.show', [
                'service' => $this,
                'index' => $index,
            ], false))
            ->all();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_urls[0] ?? null;
    }
}
