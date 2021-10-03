<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierRequestLog extends Model
{
    use HasFactory; 
    protected $fillable = [
        'courier_id',
        'name',
        'sequence',
        'status_id',
        'note',
        'user_id',
    ];
}
