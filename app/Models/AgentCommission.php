<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentCommission extends Model 
{
    use HasFactory;
    protected $fillable = ['courier_id', 'agent_id','type','percentage','amount','addedby','charge'];
}
