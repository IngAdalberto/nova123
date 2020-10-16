<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Modal extends Model
{
    protected $table = 'pw_modal';
    protected $fillable = ['id', 'title', 'body', 'enlace', 'tipo_recurso', 'path', 'widget_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

}