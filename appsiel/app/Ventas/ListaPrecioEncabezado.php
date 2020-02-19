<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class ListaPrecioEncabezado extends Model
{
    protected $table = 'vtas_listas_precios_encabezados';
	protected $fillable = ['descripcion', 'impuestos_incluidos', 'estado'];
	public $encabezado_tabla = ['Descripción', 'Impuestos incluidos', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = ListaPrecioEncabezado::select('vtas_listas_precios_encabezados.descripcion AS campo1', 'vtas_listas_precios_encabezados.impuestos_incluidos AS campo2', 'vtas_listas_precios_encabezados.estado AS campo3', 'vtas_listas_precios_encabezados.id AS campo4')
	    ->get()
	    ->toArray();
	    return $registros;
	}

	public static function opciones_campo_select()
	{
	    $opciones = ListaPrecioEncabezado::where('vtas_listas_precios_encabezados.estado','Activo')
	                ->select('vtas_listas_precios_encabezados.id','vtas_listas_precios_encabezados.descripcion')
	                ->get();

	    $vec['']='';
	    foreach ($opciones as $opcion)
	    {
	        $vec[$opcion->id] = $opcion->descripcion;
	    }

	    return $vec;
	}
}
