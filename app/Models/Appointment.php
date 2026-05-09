<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'barbershop_id',
        'service_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'client_comment',
        'rejection_reason',
        'barber_comment',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function barbershop()
    {
        return $this->belongsTo(Barbershop::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function getConfirmationCodeAttribute(): string
    {
        $secret = config('app.key') ?: config('app.name', 'EasyBooking');

        return strtoupper(substr(hash_hmac('sha256', (string) $this->id, $secret), 0, 8));
    }
}
