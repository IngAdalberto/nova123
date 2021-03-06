<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Inventarios\InvDocEncabezado;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class VtasDocEncabezado extends Model
{
    //protected $table = 'vtas_doc_encabezados'; 

    protected $fillable = ['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_tercero_id', 'descripcion', 'estado', 'creado_por', 'modificado_por', 'remision_doc_encabezado_id', 'ventas_doc_relacionado_id', 'cliente_id', 'vendedor_id', 'forma_pago', 'fecha_entrega', 'fecha_vencimiento', 'orden_compras', 'valor_total'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Cliente', 'Detalle', 'Valor total', 'Forma de pago', 'Estado'];

    public $vistas = '{"index":"layouts.index3"}';

    public function tipo_documento_app()
    {
        return $this->belongsTo( 'App\Core\TipoDocApp', 'core_tipo_doc_app_id' );
    }

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero','core_tercero_id');
    }

    public function cliente()
    {
        return $this->belongsTo( Cliente::class,'cliente_id');
    }

    public function vendedor()
    {
        return $this->belongsTo( Vendedor::class,'vendedor_id');
    }

    public function lineas_registros()
    {
        return $this->hasMany( VtasDocRegistro::class, 'vtas_doc_encabezado_id' );
    }

    public function movimientos()
    {
        return $this->hasMany( VtasMovimiento::class );
    }

    public static function sqlString($search)
    {
        $core_tipo_transaccion_id = 23; // Facturas
        $string = VtasDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'vtas_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS DOCUMENTO'),
                DB::raw('core_terceros.descripcion AS CLIENTE'),
                'vtas_doc_encabezados.descripcion AS DETALLE',
                'vtas_doc_encabezados.valor_total AS VALOR_TOTAL',
                'vtas_doc_encabezados.forma_pago AS FORMA_DE_PAGO',
                'vtas_doc_encabezados.estado AS ESTADO'
            )
            ->orWhere("vtas_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('core_terceros.descripcion'), "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.forma_pago", "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('vtas_doc_encabezados.fecha', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CLIENTES";
    }

    public static function consultar_registros2($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 23; // Facturas

        $collection = VtasDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
                                ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
                                ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                                ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
                                ->select(
                                    'vtas_doc_encabezados.fecha AS campo1',
                                    DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS campo2'),
                                    DB::raw('core_terceros.descripcion AS campo3'),
                                    'vtas_doc_encabezados.descripcion AS campo4',
                                    'vtas_doc_encabezados.valor_total AS campo5',
                                    'vtas_doc_encabezados.forma_pago AS campo6',
                                    'vtas_doc_encabezados.estado AS campo7',
                                    'vtas_doc_encabezados.id AS campo8'
                                )
                                ->orderBy('vtas_doc_encabezados.fecha', 'DESC')
                                ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if ( self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7, $c->campo8], $search)) {
                        return $c;
                    }
                });
            } else {
                $nuevaColeccion = $collection;
            }
        }

        $request = request(); //obtenemos el Request para obtener la url y la query builder

        if ( empty($nuevaColeccion) )
        {
            return $array = new LengthAwarePaginator([], 1, 1, 1, [
                                                                    'path' => $request->url(),
                                                                    'query' => $request->query(),
                                                                ]);
        }
        
        //obtenemos el numero de la página actual, por defecto 1
        $page = 1;
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        }
        $total = count($nuevaColeccion); //Total para contar los registros mostrados
        $starting_point = ($page * $nro_registros) - $nro_registros; // punto de inicio para mostrar registros
        $array = $nuevaColeccion->slice($starting_point, $nro_registros); //indicamos desde donde y cuantos registros mostrar
        $array = new LengthAwarePaginator($array, $total, $nro_registros, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]); //finalmente se pagina y organiza la coleccion a devolver con todos los datos

        return $array;
    }

    /**
     * SQL Like operator in PHP.
     * Returns TRUE if match else FALSE.
     * @param array $valores_campos_seleccionados de campos donde se busca
     * @param string $searchTerm termino de busqueda
     * @return bool
     */
    public static function likePhp($valores_campos_seleccionados, $searchTerm)
    {
        $encontrado = false;
        $searchTerm = str_slug($searchTerm); // Para eliminar acentos
        foreach ($valores_campos_seleccionados as $valor_campo)
        {
            $str = str_slug($valor_campo);
            $pos = strpos($str, $searchTerm);
            if ($pos !== false)
            {
                $encontrado = true;
            }
        }
        return $encontrado;
    }

    /*
        Obtener un registro de encabezado de documento con sus datos relacionados
    */
    public static function get_registro_impresion($id)
    {
        // ARREGLAR ESTO:     ->leftJoin('vtas_condiciones_pago','vtas_condiciones_pago.id','=','vtas_doc_encabezados.condicion_pago_id')
        return VtasDocEncabezado::where('vtas_doc_encabezados.id', $id)
            ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->leftJoin('vtas_vendedores', 'vtas_vendedores.id', '=', 'vtas_doc_encabezados.vendedor_id')
            ->leftJoin('inv_doc_encabezados', 'inv_doc_encabezados.id', '=', 'vtas_doc_encabezados.remision_doc_encabezado_id')
            ->leftJoin('core_tipos_docs_apps AS doc_inventarios', 'doc_inventarios.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros AS vendedores', 'vendedores.id', '=', 'vtas_vendedores.core_tercero_id')
            ->select(
                'vtas_doc_encabezados.id',
                'vtas_doc_encabezados.core_empresa_id',
                'vtas_doc_encabezados.core_tercero_id',
                'vtas_doc_encabezados.cliente_id',
                'vtas_doc_encabezados.remision_doc_encabezado_id',
                'vtas_doc_encabezados.core_tipo_transaccion_id',
                'vtas_doc_encabezados.core_tipo_doc_app_id',
                'vtas_doc_encabezados.consecutivo',
                'vtas_doc_encabezados.fecha',
                'vtas_doc_encabezados.fecha_vencimiento',
                'vtas_doc_encabezados.fecha_entrega',
                'vtas_doc_encabezados.vendedor_id',
                'vtas_doc_encabezados.descripcion',
                'vtas_doc_encabezados.estado',
                'vtas_doc_encabezados.creado_por',
                'vtas_doc_encabezados.modificado_por',
                'vtas_doc_encabezados.created_at',
                'vtas_doc_encabezados.orden_compras',
                'vtas_doc_encabezados.ventas_doc_relacionado_id',
                'vtas_doc_encabezados.forma_pago AS forma_pago',
                'vtas_doc_encabezados.forma_pago AS condicion_pago',
                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                'vtas_doc_encabezados.consecutivo AS documento_transaccion_consecutivo',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS documento_transaccion_prefijo_consecutivo'),
                DB::raw('CONCAT(doc_inventarios.prefijo," ",inv_doc_encabezados.consecutivo) AS documento_remision_prefijo_consecutivo'),
                DB::raw('core_terceros.descripcion AS tercero_nombre_completo'),
                'core_terceros.numero_identificacion',
                'core_terceros.direccion1',
                'core_terceros.telefono1',
                DB::raw('CONCAT(vendedores.apellido1, " ",vendedores.apellido2, " ",vendedores.nombre1, " ",vendedores.otros_nombres) AS vendedor_nombre_completo')
            )
            ->get()
            ->first();
    }

    public static function get_documentos_relacionados($doc_encabezado)
    {
        $mas_de_uno = false;
        $ids_documentos_relacionados = explode(',', $doc_encabezado->remision_doc_encabezado_id);

        $app_id = 13; // Ventas
        $modelo_doc_relacionado_id = 164; // Remisiones de ventas
        $transaccion_doc_relacionado_id = 24; // Remisión de ventas

        $cant_registros = count($ids_documentos_relacionados);

        $lista = '';
        $primer = true;
        for ($i=0; $i < $cant_registros; $i++)
        { 
            $un_documento = InvDocEncabezado::get_registro_impresion( $ids_documentos_relacionados[$i] );
            if ( !is_null($un_documento) )
            {
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
