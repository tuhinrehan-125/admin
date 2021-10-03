<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryMode extends Model
{
    use HasFactory;

    protected $fillable = ['courier_type_id', 'buy4u_type_id', 'title', 'time_in_hours'];
}
