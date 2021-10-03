<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssueList extends Model
{
    use HasFactory;
    protected $fillable = ['issue_id', 'description','added_by','merchant_seen']; 
    public function added(){
        return $this->belongsTo(User::class,'added_by');
    }
}
 