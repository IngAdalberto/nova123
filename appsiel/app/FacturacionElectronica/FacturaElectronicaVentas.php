<?php

namespace App\FacturacionElectronica;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;

use App\Ventas\VtasDocEncabezado;

use App\Tesoreria\RegistrosMediosPago;


class FacturaElectronicaVentas extends VtasDocEncabezado
{    
    protected $table = 'vtas_doc_encabezados';
    
    protected $fillable = ['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_tercero_id', 'descripcion', 'estado', 'creado_por', 'modificado_por', 'remision_doc_encabezado_id', 'ventas_doc_relacionado_id', 'cliente_id', 'vendedor_id', 'forma_pago', 'fecha_entrega', 'fecha_vencimiento', 'orden_compras', 'valor_total'];

    public $encabezado_tabla = ['Fecha', 'Documento', 'Cliente', 'Detalle', 'Valor total', 'Estado', 'Acción'];

    public $urls_acciones = '{"create":"web/create","store":"fe_factura","show":"fe_factura/id_fila"}';

    public $vistas = '{"index":"layouts.index3","create":"facturacion_electronica.facturas.create"}';

    // ¡Extiende métodos!

    public static function consultar_registros2()
    {
        $core_tipo_transaccion_id = 52; // Factura Electrónica
        return VtasDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'vtas_doc_encabezados.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS campo2'),
                DB::raw('core_terceros.descripcion AS campo3'),
                'vtas_doc_encabezados.descripcion AS campo4',
                'vtas_doc_encabezados.valor_total AS campo5',
                'vtas_doc_encabezados.estado AS campo6',
                'vtas_doc_encabezados.id AS campo7'
            )
            ->orderBy('vtas_doc_encabezados.created_at', 'DESC')
            ->paginate(500);
    }

}
