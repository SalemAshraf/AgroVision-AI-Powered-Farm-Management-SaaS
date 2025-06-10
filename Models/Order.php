<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // اسم الجدول (إذا كان مختلفًا عن اسم النموذج)
    protected $table = 'orders';

    // الحقول التي يمكن تعبئتها (Fillable)
    protected $fillable = [
        'user_id',
        'product_id',
        'subtotal',
        'discount',
        'tax',
        'total',
        'name',
        'phone',
        'locality',
        'address',
        'city',
        'state',
        'country',
        'zip',
        'type',
        'status',
        'is_shipping_different',
        'delivered_date',
        'canceled_date',
    ];

    // الحقول التي يجب إخفاؤها عند التحويل إلى JSON (مثل كلمات المرور)
    protected $hidden = [];

    // الحقول التي يجب تحويلها إلى أنواع بيانات محددة (مثل التواريخ)
    protected $casts = [
        'is_shipping_different' => 'boolean',
        'delivered_date' => 'date',
        'canceled_date' => 'date',
    ];

    // العلاقة مع نموذج User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // العلاقة مع نموذج Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function orderItems()
{
    return $this->hasMany(Orderitem::class);
}
}