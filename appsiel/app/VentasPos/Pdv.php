<?php

namespace App\VentasPos;

use Illuminate\Database\Eloquent\Model;

use App\Inventarios\InvBodega;
use App\Tesoreria\TesoCaja;
use App\Ventas\Cliente;
use App\Core\TipoDocApp;

class Pdv extends Model
{
    protected $table = 'vtas_pos_puntos_de_ventas';
    protected $fillable = ['core_empresa_id', 'descripcion', 'bodega_default_id', 'caja_default_id', 'cajero_default_id', 'cliente_default_id', 'tipo_doc_app_default_id', 'detalle', 'creado_por', 'modificado_por', 'estado'];

    public function bodega()
    {
        return $this->belongsTo(InvBodega::class, 'bodega_default_id');
    }

    public function caja()
    {
        return $this->belongsTo(TesoCaja::class, 'caja_default_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_default_id');
    }

    public function tipo_doc_app()
    {
        return $this->belongsTo(TipoDocApp::class, 'tipo_doc_app_default_id');
    }


    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Bodega', 'Caja', 'Cajero', 'Cliente', 'Tipo Doc.', 'Estado'];
    public static function consultar_registros($nro_registros)
    {
        return Pdv::leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'vtas_pos_puntos_de_ventas.bodega_default_id')
            ->leftJoin('teso_cajas', 'teso_cajas.id', '=', 'vtas_pos_puntos_de_ventas.caja_default_id')
            ->leftJoin('users', 'users.id', '=', 'vtas_pos_puntos_de_ventas.cajero_default_id')
            ->leftJoin('vtas_clientes', 'vtas_clientes.id', '=', 'vtas_pos_puntos_de_ventas.cliente_default_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id')
            ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_pos_puntos_de_ventas.tipo_doc_app_default_id')
            ->select(
                'vtas_pos_puntos_de_ventas.descripcion AS campo1',
                'inv_bodegas.descripcion AS campo2',
                'teso_cajas.descripcion AS campo3',
                'users.name AS campo4',
                'core_terceros.descripcion AS campo5',
                'core_tipos_docs_apps.descripcion AS campo6',
                'vtas_pos_puntos_de_ventas.estado AS campo7',
                'vtas_pos_puntos_de_ventas.id AS campo8'
            )
            ->orderBy('vtas_pos_puntos_de_ventas.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function opciones_campo_select()
    {
        $opciones = Pdv::where('vtas_pos_puntos_de_ventas.estado', 'Activo')
            ->select('vtas_pos_puntos_de_ventas.id', 'vtas_pos_puntos_de_ventas.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
