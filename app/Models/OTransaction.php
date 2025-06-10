<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OTransaction extends Model
{
    use HasFactory;

    protected $table = 'O_transactions';

    protected $fillable = [
        'invoice_id', 'payment_date', 'amount', 'payment_method'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
