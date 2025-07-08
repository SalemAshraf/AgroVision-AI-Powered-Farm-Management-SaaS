<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\category;
use App\Models\crop;
use App\Models\order;
use App\Models\User;
class Product extends Model
{
    use HasFactory;

protected $table = 'products';
 protected $fillable = ['name', 'crop_id', 'price', 'quantity', 'images', 'stock_status', 'photo', 'description', 'category_id','farmer_id'];   

    // العلاقة مع المحصول
    public function crop()
    {
        return $this->belongsTo(Crop::class, 'crop_id');
    }

    public function category(){
        return $this->belongsTo(Category::class,'category_id');
    }
    public function orders()
{
    return $this->hasMany(Order::class);
}
public function favoredBy()
{
    return $this->belongsToMany(User::class, 'favorites');
}
}
