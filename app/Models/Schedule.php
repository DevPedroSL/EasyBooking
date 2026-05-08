<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;
    protected $table = 'schedules';

    protected $fillable = [
        'barbershop_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    /**
     * Relación: un horario pertenece a una barbería
     */
    public function barbershop()
    {
        return $this->belongsTo(Barbershop::class);
    }
}
