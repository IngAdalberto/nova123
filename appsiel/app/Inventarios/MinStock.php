<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

class MinStock extends Model
{
	protected $table = 'inv_min_stocks';
	protected $fillable = ['inv_bodega_id', 'inv_producto_id', 'stock_minimo'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Bodega', 'Producto', 'Stock Mínimo'];
	public static function consultar_registros($nro_registros)
	{
		$registros = MinStock::leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_min_stocks.inv_bodega_id')->leftJoin('inv_productos', 'inv_productos.id', '=', 'inv_min_stocks.inv_producto_id')->select('inv_bodegas.descripcion AS campo1', 'inv_productos.descripcion AS campo2', 'inv_min_stocks.stock_minimo AS campo3', 'inv_min_stocks.id AS campo4')
			->orderBy('inv_min_stocks.created_at', 'DESC')
			->paginate($nro_registros);
		return $registros;
	}
}
