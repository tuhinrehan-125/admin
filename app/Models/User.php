<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
 

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'type',
        'profile_picture',
        'password',
        'bkash_no',
        'nagad_no',
        'rocket_no',
        'bank_ac_no',
        'bank_name',
        'bank_branch',
        'merchant_shop_area',
        'merchant_shop_city',
        'merchant_shop_address',
        'preferred_method',
        'rider_city',
        'rider_hub',
        'rider_area',
        'bankAC_name',
        'free_req',
        'pickup_rider_commission',
        'delivery_rider_commission',
        'pickup_agent_commission',
        'delivery_agent_commission',

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function area(){
        return $this->belongsTo(Area::class);
    }
}
