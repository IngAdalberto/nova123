<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Articlesetup extends Model
{
    protected  $table = 'pw_articlesetups';
    protected  $fillable = ['id', 'titulo', 'descripcion', 'formato', 'orden', 'widget_id', 'article_id', 'articlecategory_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function articlecategory()
    {
        return $this->belongsTo(Articlecategory::class);
    }
}
