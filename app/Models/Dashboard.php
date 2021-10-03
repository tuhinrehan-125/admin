<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dashboard extends Model
{
    use HasFactory;

    // turn off both 
    public $timestamps = false;

    protected $fillable = [
        'todays_parcel_entry', 
        'todays_cancel_parcel', 
        'total_parcel_entry_till_now', 
        'total_cancel_parcel_till_now',
        'total_delivered_today',
        'total_delivered_till_now',
        'delivery_charge_entered_today',
        'delivery_charge_total_receivable',
        'delivery_charge_collected_today',
        'delivery_charge_due_today',
        'delivery_charge_collected_till_now',
        'cod_entry_today',
        'cod_collected_receivable_by_merchant',
        'total_cod_paid_to_merchant',
        'total_cod_due',
        'cod_collected_till_now'
    ];
}
