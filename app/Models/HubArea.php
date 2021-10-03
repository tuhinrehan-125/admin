<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HubArea extends Model
{
    use HasFactory;
    protected $fillable = ['area_id', 'hub_id'];
    public function area(){
        return $this->belongsTo(Area::class);
    }
}
