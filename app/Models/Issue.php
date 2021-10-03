<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    use HasFactory;
    protected $fillable = ['courier_id', 'merchant_id','title','status','added_by','admin_view','merchant_view','supervisior_view'];
}
 