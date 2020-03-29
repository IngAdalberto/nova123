<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;

class TesoRecaudosLibreta extends Model
{
    public $fillable = ['core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','id_libreta', 'id_cartera', 'concepto', 'fecha_recaudo', 'teso_medio_recaudo_id', 'cantidad_cuotas','valor_recaudo','mi_token','creado_por','modificado_por'];

    public $encabezado_tabla = ['Documento','Fecha','Estudiante','Detalle','Valor','Acción'];

    public static function consultar_registros()
    {
    	return TesoRecaudosLibreta::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_recaudos_libretas.core_tipo_doc_app_id')
                    ->leftJoin('teso_cartera_estudiantes','teso_cartera_estudiantes.id','=','teso_recaudos_libretas.id_cartera')
                    ->leftJoin('sga_estudiantes','sga_estudiantes.id','=','teso_cartera_estudiantes.id_estudiante')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                    ->select( 
                    			DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_recaudos_libretas.consecutivo) AS campo1'),
                                'teso_recaudos_libretas.fecha_recaudo AS campo2',
                                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo3'),
                                'teso_recaudos_libretas.concepto AS campo4',
                                'teso_recaudos_libretas.valor_recaudo AS campo5',
                                'teso_cartera_estudiantes.id AS campo6')
                    ->get()
                    ->toArray();
    }
}
