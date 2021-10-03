<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

    protected $fillable = ['image'];

    public function slider_collections()
    {
        return $this->belongsToMany(SliderCollection::class, 'slider_collection_sliders', 'slider_collection_id');
    }
}
