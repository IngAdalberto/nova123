<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class NomCuota extends Model
{
    //protected $table = 'nom_cuotas';
	protected $fillable = ['core_tercero_id', 'nom_concepto_id', 'fecha_inicio','periodicidad_mensual', 'valor_cuota', 'tope_maximo', 'valor_acumulado', 'estado', 'detalle'];
	
	public $encabezado_tabla = ['Empleado', 'Concepto', 'Fecha inicio', 'Valor cuota', 'Tope Máximo', 'Valor acumulado', 'Estado', 'Detalle', 'ID', 'Acción'];

	// El archivo js debe estar en la carpeta public
	public $archivo_js = 'assets/js/nom_cuotas.js';

	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","cambiar_estado":"a_i/id_fila"}';
	
	public static function consultar_registros()
	{
		return NomCuota::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_cuotas.core_tercero_id')
            				->leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_cuotas.nom_concepto_id')
            				->select(
            							'core_terceros.descripcion AS campo1',
            							'nom_conceptos.descripcion AS campo2',
            							'nom_cuotas.fecha_inicio AS campo3',
            							'nom_cuotas.valor_cuota AS campo4',
            							'nom_cuotas.tope_maximo AS campo5',
            							'nom_cuotas.valor_acumulado AS campo6',
            							'nom_cuotas.estado AS campo7',
            							'nom_cuotas.detalle AS campo8',
            							'nom_cuotas.id AS campo9',
            							'nom_cuotas.id AS campo10')
						    ->get()
						    ->toArray();
	}
}
