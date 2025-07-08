<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory;

protected $casts = [
    'expires_at' => 'datetime',
];

    protected $fillable = [
        'user_id',
        'license_key',
        'sensor_id',
        'is_active',
        'expires_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
