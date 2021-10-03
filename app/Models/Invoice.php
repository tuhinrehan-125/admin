<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $fillable = ['number','merchant_id','added_by','merchant_email','merchant_name','merchant_phone','merchant_address','reference_id'];
}
