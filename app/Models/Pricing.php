<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'courier_type_id',
        'sender_city_id',
        'receiver_city_id',
        'delivery_mode_id',
        'min_weight',
        'max_weight',
        'price',
        'user_id',
        'addedby'
    ];

    public function courier_type(){
        return $this->belongsTo(CourierType::class);
    }
    public function sender_city(){
        return $this->belongsTo(City::class);
    }
    public function receiver_city(){
        return $this->belongsTo(City::class);
    }
    public function delivery_mode(){
        return $this->belongsTo(DeliveryMode::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
