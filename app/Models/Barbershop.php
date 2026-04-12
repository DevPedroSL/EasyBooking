<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Barbershop extends Model
{
    use HasFactory;
    protected $fillable = [
        'barber_id',
        'name',
        'Description',
        'address',
        'phone',
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
}
