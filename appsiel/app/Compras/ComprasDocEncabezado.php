<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;
use App\Sistema\TipoTransaccion;

use App\Inventarios\InvDocEncabezado;

class ComprasDocEncabezado extends Model
{
    //protected $table = 'compras_doc_encabezados';
	protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'core_tercero_id', 'cotizacion_id', 'compras_doc_relacionado_id', 'entrada_almacen_id', 'proveedor_id', 'comprador_id', 'forma_pago', 'fecha_recepcion', 'fecha_vencimiento', 'doc_proveedor_prefijo', 'doc_proveedor_consecutivo', 'descripcion', 'creado_por', 'modificado_por', 'estado','valor_total'];

	public $encabezado_tabla = ['Fecha', 'Documento compra', 'Proveedor', 'Factura', 'Detalle', 'Valor total', 'Estado', 'Acción'];

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero','core_tercero_id');
    }

    public function lineas_registros()
    {
        return $this->hasMany( ComprasDocRegistro::class, 'compras_doc_encabezado_id' );
    }

	public static function consultar_registros()
	{
        $core_tipo_transaccion_id = 25; // Facturas de compras
	    return ComprasDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_doc_encabezados.core_tercero_id')
                    ->where('compras_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                    ->where('compras_doc_encabezados.core_tipo_transaccion_id',$core_tipo_transaccion_id)
                    ->select(
                                'compras_doc_encabezados.fecha AS campo1',
                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo) AS campo2' ),
                                DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3' ),
                                DB::raw('CONCAT(compras_doc_encabezados.doc_proveedor_prefijo," ",compras_doc_encabezados.doc_proveedor_consecutivo) AS campo4'),
                                'compras_doc_encabezados.descripcion AS campo5',
                                'compras_doc_encabezados.valor_total AS campo6',
                                'compras_doc_encabezados.estado AS campo7',
                                'compras_doc_encabezados.id AS campo8')
                    ->get()
                    ->toArray();
	}
	

    public static function opciones_campo_select()
    {
        $opciones = ComprasDocEncabezado::where('compras_doc_encabezados.estado','Activo')
                    ->select('compras_doc_encabezados.id','compras_doc_encabezados.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    /*
        Obtener un registro de encabezado de documento con sus datos relacionados
    */
    public static function get_registro_impresion($id)
    {
        
        // ARREGLAR ESTO:     ->leftJoin('vtas_condiciones_pago','vtas_condiciones_pago.id','=','compras_doc_encabezados.condicion_pago_id')
        return ComprasDocEncabezado::where('compras_doc_encabezados.id',$id)
                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_doc_encabezados.core_tercero_id')
                    ->leftJoin('inv_doc_encabezados', 'inv_doc_encabezados.id', '=', 'compras_doc_encabezados.entrada_almacen_id')
                    ->leftJoin('core_tipos_docs_apps AS doc_inventarios', 'doc_inventarios.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
                    ->select(
                                'compras_doc_encabezados.id',
                                'compras_doc_encabezados.core_empresa_id',
                                'compras_doc_encabezados.entrada_almacen_id',
                                'compras_doc_encabezados.core_tercero_id',
                                'compras_doc_encabezados.proveedor_id',
                                'compras_doc_encabezados.core_tipo_transaccion_id',
                                'compras_doc_encabezados.core_tipo_doc_app_id',
                                'compras_doc_encabezados.consecutivo',
                                'compras_doc_encabezados.fecha',
                                'compras_doc_encabezados.fecha_vencimiento',
                                'compras_doc_encabezados.fecha_recepcion',
                                'compras_doc_encabezados.cotizacion_id',
                                'compras_doc_encabezados.descripcion',
                                'compras_doc_encabezados.compras_doc_relacionado_id',
                                'compras_doc_encabezados.estado',
                                'compras_doc_encabezados.creado_por',
                                'compras_doc_encabezados.modificado_por',
                                'compras_doc_encabezados.created_at',
                                'compras_doc_encabezados.valor_total',
                                'compras_doc_encabezados.doc_proveedor_prefijo',
                                'compras_doc_encabezados.doc_proveedor_consecutivo',
                                'compras_doc_encabezados.forma_pago AS condicion_pago',
                                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                                'compras_doc_encabezados.consecutivo AS documento_transaccion_consecutivo',
                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo) AS documento_transaccion_prefijo_consecutivo' ),
                                DB::raw( 'CONCAT(doc_inventarios.prefijo," ",inv_doc_encabezados.consecutivo) AS documento_remision_prefijo_consecutivo' ),
                                DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social," (",core_terceros.descripcion,")") AS tercero_nombre_completo' ),
                                'core_terceros.numero_identificacion',
                                'core_terceros.direccion1',
                                'core_terceros.telefono1'
                            )
                    ->get()
                    ->first();
    }

    // Devuelve un array de dos posiciones, la primera posicién es una lista de enlaces (<a></a>) con cada uno de las entradas de almacén con que se hizo la factura. La segunda posición es un indicador tipo boolean para decir si hay más de una remisión en la OC
    public static function get_documentos_relacionados( $doc_encabezado )
    {
        $mas_de_uno = false;

        $ids_documentos_relacionados = explode( ',', $doc_encabezado->entrada_almacen_id );
        
        $app_id = 9;

        $cant_registros = count($ids_documentos_relacionados);

        $lista = '';
        $primer = true;
        for ($i=0; $i < $cant_registros; $i++)
        { 
            $un_documento = InvDocEncabezado::get_registro_impresion( $ids_documentos_relacionados[$i] );
            if ( !is_null($un_documento) )
            {
                $transaccion = TipoTransaccion::find( $un_documento->core_tipo_transaccion_id );
                $modelo_doc_relacionado_id = $transaccion->core_modelo_id;
                $transaccion_doc_relacionado_id = $transaccion->id;
                
                if ($primer)
                {
                    $lista .= '<a href="'.url( 'inventarios/'.$un_documento->id.'?id='.$app_id.'&id_modelo='.$modelo_doc_relacionado_id.'&id_transaccion='.$transaccion_doc_relacionado_id ).'" target="_blank">'.$un_documento->documento_transaccion_prefijo_consecutivo.'</a>';
                    $primer = false;
                }else{
                    $lista .= ', &nbsp; <a href="'.url( 'inventarios/'.$un_documento->id.'?id='.$app_id.'&id_modelo='.$modelo_doc_relacionado_id.'&id_transaccion='.$transaccion_doc_relacionado_id ).'" target="_blank">'.$un_documento->documento_transaccion_prefijo_consecutivo.'</a>';
                    $mas_de_uno = true;
                }
            }
        }
        return [$lista,$mas_de_uno];
    }
}
