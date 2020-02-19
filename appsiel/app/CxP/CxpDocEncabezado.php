<?php

namespace App\CxP;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class CxpDocEncabezado extends Model
{
    protected $fillable = ['core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','fecha','fecha_vencimiento','core_empresa_id','core_tercero_id','tipo_documento','documento_soporte','descripcion','valor_total','estado','creado_por','modificado_por','codigo_referencia_tercero'];

    public $encabezado_tabla = ['Documento','Fecha','Inmueble','Propietario','Detalle','Valor Total','Estado','Acción'];

    // Este array se puede usar para automatizar los campos que se muestran en la vista idex, permitiendo agregar o quitar campos a la tabla
    public $campos_vista_index = [
                    ['modo_select' => 'raw',
                    'etiqueta' => 'Documento',
                    'campo' => 'CONCAT(core_tipos_docs_apps.prefijo," ",cxp_doc_encabezados.consecutivo)'],
                    ['modo_select' => 'normal',
                    'etiqueta' => 'Fecha',
                    'campo' => 'cxp_doc_encabezados.fecha'],
                    ['modo_select' => 'raw',
                    'etiqueta' => 'Inmueble',
                    'campo' => 'CONCAT(ph_propiedades.codigo," ",ph_propiedades.nomenclatura)'],
                    ['modo_select' => 'normal',
                    'etiqueta' => 'Propietario',
                    'campo' => 'core_terceros.descripcion'],
                    ['modo_select' => 'normal',
                    'etiqueta' => 'Detalle',
                    'campo' => 'cxp_doc_encabezados.descripcion'],
                    ['modo_select' => 'normal',
                    'etiqueta' => 'Acción',
                    'campo' => 'cxp_doc_encabezados.id']
                    ];

    
    // Se consultan los documentos para la empresa que tiene asignada el usuario
    public static function consultar_registros()
    {        
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",cxp_doc_encabezados.consecutivo) AS campo1';

        $select_raw2 = 'CONCAT(ph_propiedades.codigo," ",ph_propiedades.nomenclatura) AS campo3';

        $core_tipo_transaccion_id = 39; // Cruce de cxp

        $registros = CxpDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxp_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('ph_propiedades', 'ph_propiedades.id', '=', 'cxp_doc_encabezados.codigo_referencia_tercero')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_doc_encabezados.core_tercero_id')
                    ->where('cxp_doc_encabezados.core_empresa_id', Auth::user()->empresa_id )
                    ->where('cxp_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id )
                    ->select(DB::raw($select_raw),'cxp_doc_encabezados.fecha AS campo2',DB::raw($select_raw2),'core_terceros.descripcion as campo4','cxp_doc_encabezados.descripcion AS campo5','cxp_doc_encabezados.valor_total AS campo6','cxp_doc_encabezados.estado AS campo7','cxp_doc_encabezados.id AS campo8')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public function documentos()
    {
        return $this->hasMany('App\CxC\CxcDocumento');
    }

    public function registros()
    {
        return $this->hasMany('App\CxC\CxcRegistro');
    }

    public static function get_un_registro($id)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",cxp_doc_encabezados.consecutivo) AS documento_app';

        $registro = CxpDocEncabezado::where('cxp_doc_encabezados.id',$id)
                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxp_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_empresas', 'core_empresas.id', '=', 'cxp_doc_encabezados.core_empresa_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_doc_encabezados.core_tercero_id')
                    ->leftJoin('ph_propiedades', 'ph_propiedades.id', '=', 'cxp_doc_encabezados.codigo_referencia_tercero')
                    ->select( DB::raw($select_raw),
                        'cxp_doc_encabezados.id',
                        'cxp_doc_encabezados.fecha',
                        'core_terceros.descripcion',
                        'core_terceros.numero_identificacion',
                        'core_terceros.direccion1',
                        'core_terceros.telefono1',
                        'cxp_doc_encabezados.codigo_referencia_tercero',
                        'cxp_doc_encabezados.descripcion AS detalle',
                        'cxp_doc_encabezados.core_empresa_id',
                        'cxp_doc_encabezados.core_tipo_transaccion_id',
                        'cxp_doc_encabezados.core_tipo_doc_app_id',
                        'cxp_doc_encabezados.consecutivo',
                        'cxp_doc_encabezados.fecha_vencimiento',
                        'cxp_doc_encabezados.core_tercero_id',
                        'cxp_doc_encabezados.valor_total',
                        'cxp_doc_encabezados.creado_por',
                        'ph_propiedades.nomenclatura',
                        'ph_propiedades.nombre_arrendatario',
                        'ph_propiedades.telefono_arrendatario',
                        'ph_propiedades.email_arrendatario',
                        'ph_propiedades.tipo_propiedad',
                        'ph_propiedades.codigo AS codigo_inmueble')
                    ->get()[0];

        return $registro;
    }

    public static function get_registro_impresion($id)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",cxp_doc_encabezados.consecutivo) AS documento_app';

        $registro = CxpDocEncabezado::where('cxp_doc_encabezados.id',$id)
                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxp_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_empresas', 'core_empresas.id', '=', 'cxp_doc_encabezados.core_empresa_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_doc_encabezados.core_tercero_id')
                    ->select( DB::raw($select_raw),
                        'cxp_doc_encabezados.id',
                        'cxp_doc_encabezados.fecha',
                        'core_terceros.descripcion',
                        'core_terceros.numero_identificacion',
                        'core_terceros.direccion1',
                        'core_terceros.telefono1',
                        'cxp_doc_encabezados.codigo_referencia_tercero',
                        'cxp_doc_encabezados.descripcion AS detalle',
                        'cxp_doc_encabezados.core_empresa_id',
                        'cxp_doc_encabezados.core_tipo_transaccion_id',
                        'cxp_doc_encabezados.core_tipo_doc_app_id',
                        'cxp_doc_encabezados.consecutivo',
                        'cxp_doc_encabezados.fecha_vencimiento',
                        'cxp_doc_encabezados.core_tercero_id',
                        'cxp_doc_encabezados.valor_total',
                        'cxp_doc_encabezados.creado_por',
                        'cxp_doc_encabezados.estado',
                        'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                        DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",cxp_doc_encabezados.consecutivo) AS documento_transaccion_prefijo_consecutivo' )
                            )
                    ->get()[0];

        return $registro;
    }
}
