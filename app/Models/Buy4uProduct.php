<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buy4uProduct extends Model
{
    use HasFactory;
    public function buy4u_request(){
        return $this->belongsTo(Buy4uRequest::class);
    }

    protected $fillable = [
        'name',
        'quantity',
        'buy4u_request_id',
        'unit_type_id',
        'approximate_price',
        'note'
    ];
}
