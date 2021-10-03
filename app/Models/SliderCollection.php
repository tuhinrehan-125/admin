<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Slider;

class SliderCollection extends Model
{
    use HasFactory;

    protected $fillable = ['image','title'];

    public function sliders()
    {
        return $this->belongsToMany(Slider::class, 'slider_collection_sliders');
    }
}
