<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $table = 'pw_slider';
    protected $fillable = ['id', 'disposicion', 'configuracionfuente_id', 'widget_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

    public function items()
    {
        return $this->hasMany(ItemSlider::class);
    }

    public function configuracionfuente()
    {
        return $this->belongsTo(Configuracionfuente::class);
    }
}
