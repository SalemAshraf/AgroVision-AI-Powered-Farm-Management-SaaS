<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    // إذا كان الجدول اسمه O_clients:
    protected $table = 'O_clients';

    protected $fillable = [
      'name','email','phone','address','user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
