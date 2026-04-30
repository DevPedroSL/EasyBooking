<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

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
    ];

    protected $casts = [
        'duration' => 'integer',
        'price' => 'decimal:2',
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
}
