<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderCommission extends Model
{
    use HasFactory;
    protected $fillable = ['courier_id', 'rider_id','type','amount','addedby'];
}
 