<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', // أضف هذا السطر
        'order_id',
        'mode',
        'status',
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
