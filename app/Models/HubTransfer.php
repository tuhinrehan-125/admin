<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HubTransfer extends Model
{
    use HasFactory;
    protected $fillable = ['courier_id', 'hub_id','hub_status'];

    public function hub(){
        return $this->belongsTo(Branch::class);
    }
}
