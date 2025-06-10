<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Order;
class Orderitem extends Model
{
    protected $table = 'order_items';
protected $fillable = ['product_id', 'order_id', 'price', 'quantity', 'options', 'rstatus'];    
    use HasFactory;
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
