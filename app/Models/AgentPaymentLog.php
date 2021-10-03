<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentPaymentLog extends Model
{
    use HasFactory; 
    protected $fillable = [
        'courier_id',
        'amount',
        'transaction_id',
        'phone',
        'user_id',
        'trackingid'
    ];
}
