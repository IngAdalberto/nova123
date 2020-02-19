<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class RemisionVentas extends InvDocEncabezado
{
    protected $table = 'inv_doc_encabezados';

    public $encabezado_tabla = ['Fecha','Documento','Bodega','Tercero','Detalle','Estado','Acción'];

    public static function consultar_registros()
    {
    	$select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS campo2';

        $select_raw2 = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo4';

        $core_tipo_transaccion_id = 24; // Remisión de ventas
        $registros = RemisionVentas::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
                    ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_encabezados.inv_bodega_id')
                    ->where('inv_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                    ->where('inv_doc_encabezados.core_tipo_transaccion_id',$core_tipo_transaccion_id)
                    ->select('inv_doc_encabezados.fecha AS campo1',DB::raw($select_raw),'inv_bodegas.descripcion AS campo3',DB::raw($select_raw2),'inv_doc_encabezados.descripcion AS campo5','inv_doc_encabezados.estado AS campo6','inv_doc_encabezados.id AS campo7')
                    ->get()
                    ->toArray();

        return $registros;
    }

}
