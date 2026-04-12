<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointments extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_id',
        'barbershop_id',
        'service_id',
        'start_time',
        'end_time',
        'status',
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
        return $this->belongsTo(User::class, 'barbershop_id'); // Assuming barbershop_id is to users, as per migration
    }

    public function service()
    {
        return $this->belongsTo(Services::class);
    }
}
