<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitType extends Model
{
    use HasFactory;

    public function buy4u_product(){
        return $this->belongsTo(Buy4uProduct::class);
    }

    protected $fillable = [
        'title'
    ];
}
