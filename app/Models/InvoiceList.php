<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceList extends Model
{
    use HasFactory;
    protected $fillable = ['invoice_id','courier_id','cod','delivery_charge','mercahnt_payable','paid_by','request_date','delivery_date','tracking_id','receiver_name','receiver_phone','receiver_address','cod_payment_status','status_id'];
}
