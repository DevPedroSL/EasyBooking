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
        'Description',
        'address',
        'phone',
        'visibility',
        'image_path',
    ];

    protected $casts = [
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
        return $this->hasMany(Services::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedules::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointments::class);
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

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path || !Storage::disk('public')->exists($this->image_path)) {
            return null;
        }

        return route('barbershops.image', $this, false);
    }
}
