<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buy4uRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function buy4u_type(){
        return $this->belongsTo(Buy4uType::class);
    }
    public function city(){
        return $this->belongsTo(City::class);
    }
    public function area(){
        return $this->belongsTo(Area::class);
    }
    public function customer(){
        return $this->belongsTo(User::class,'customer_id');
    }
    public function branch(){
        return $this->belongsTo(Branch::class);
    }
    public function rider(){
        return $this->belongsTo(User::class,'rider_id');
    }
    public function pricing(){
        return $this->belongsTo(Pricing::class);
    }
    public function status(){
        return $this->belongsTo(Status::class);
    }
    public function products(){
        return $this->hasMany(Buy4uProduct::class);
    }
}
