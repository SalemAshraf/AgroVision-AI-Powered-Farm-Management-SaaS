<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    use HasFactory;

    protected $fillable = [
        'sensor_id',
        'ec',
        'fertility',
        'hum',
        'k',
        'n',
        'p',
        'ph',
        'temp',
        'recorded_at',
    ];
}
