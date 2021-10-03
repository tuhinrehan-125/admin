<?php

namespace App\Models;

use http\Env\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pricing;

class CourierRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'courier_type_id',
        'sender_city_id',
        'receiver_city_id',
        'sender_area_id',
        'receiver_area_id',
        'packaging_type_id',
        'delivery_mode_id',
        'sender_address',
        'receiver_address',
        'receiver_name',
        'receiver_phone',
        'fragile',
        'note',
        'paid_by',
        'cash_on_delivery',
        'cash_on_delivery_amount',
        'approximate_weight',
        'actual_weight',
        'pricing_id',
        'customer_id',
        'branch_id',
        'status_id',
        'rider_id',
        'tracking_id',
        'delivery_hub',
        'preferred_method',
        'preferred_method_number',
    ];
    public function delivery_mode(){
        return $this->belongsTo(DeliveryMode::class);
    }
    public function packaging_type(){
        return $this->belongsTo(PackagingType::class);
    }
    public function receiver_area(){
        return $this->belongsTo(Area::class);
    }
    public function sender_area(){
        return $this->belongsTo(Area::class);
    }
    public function branch(){
        return $this->belongsTo(Branch::class);
    }
    public function receiver_city(){
        return $this->belongsTo(City::class);
    }
    public function sender_city(){
        return $this->belongsTo(City::class);
    }
    public function courier_type(){
        return $this->belongsTo(CourierType::class);
    }
    public function status(){
        return $this->belongsTo(Status::class);
    }
    public function pricing(){
        return $this->belongsTo(Pricing::class);
    }
    public function customer(){
        return $this->belongsTo(User::class,'customer_id');
    }
    public function rider(){
        return $this->belongsTo(User::class,'rider_id');
    }
    public function delivery(){
        return $this->belongsTo(Branch::class,'delivery_hub')->select('id','name');
    }
    public function getTotalAmountAttribute(){
        return $this->pricing->price + $this->cash_on_delivery_amount?$this->cash_on_delivery_amount:0;
    }
}
