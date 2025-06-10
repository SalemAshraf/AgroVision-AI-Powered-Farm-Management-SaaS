<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SoftDeletes;
class Crop extends Model
{
   // protected $table='crops';
    protected $fillable = [
        'productName',
    'productCategory',
    'pricePerKilo',
    'quantity',
    'status',
    'photo',
    'user_id',
    ];
}
