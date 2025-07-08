<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;  // تأكد من إضافة هذه السطر
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Member extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    protected $guard = 'member';
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'password',
        'phone',
        'gender',
        'job_title',
        'role',
        'image',
    ];
}
