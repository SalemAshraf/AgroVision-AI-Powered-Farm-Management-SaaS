<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    // إذا جدولك مختلف اسمه هنا:
    // protected $table = 'invoices';

    protected $fillable = [
        'client_id',
        'invoice_number',
        'amount',
        'status',
        'user_id',           // ← اضف هذا
    ];

    // العلاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // العلاقة مع العميل
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // العلاقة مع المعاملات
    public function transactions()
    {
        return $this->hasMany(OTransaction::class, 'invoice_id');
    }
}
