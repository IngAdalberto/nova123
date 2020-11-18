<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use App\Ventas\ClaseCliente;
use App\Ventas\Vendedor;
use App\Core\Tercero;

use DB;

class Cliente extends Model
{
    protected $table = 'vtas_clientes';
	
	protected $fillable = ['core_tercero_id', 'encabezado_dcto_pp_id', 'clase_cliente_id', 'lista_precios_id', 'lista_descuentos_id', 'vendedor_id','inv_bodega_id', 'zona_id', 'liquida_impuestos', 'condicion_pago_id', 'cupo_credito', 'bloquea_por_cupo', 'bloquea_por_mora', 'estado'];

	public $encabezado_tabla = ['ID','Identificación', 'Tercero', 'Dirección', 'Teléfono', 'Clase de cliente', 'Lista de precios', 'Lista de descuentos', 'Zona', 'Acción'];

    public $urls_acciones = '{"eliminar":"web_eliminar/id_fila"}';
    

    public static function get_cuenta_cartera( $cliente_id )
    {
        $clase_cliente_id = Cliente::where( 'id', $cliente_id )->value( 'clase_cliente_id' );
        return ClaseCliente::where( 'id', $clase_cliente_id )->value( 'cta_x_cobrar_id' );
    }

    public function lista_precios()
    {
        return $this->belongsTo(ListaPrecioEncabezado::class);
    }

    public function lista_descuentos()
    {
        return $this->belongsTo(ListaDctoEncabezado::class);
    }

    public function tercero()
    {
        return $this->belongsTo( Tercero::class, 'core_tercero_id');
    }

    public function clase_cliente()
    {
        return $this->belongsTo( ClaseCliente::class, 'clase_cliente_id');
    }

    public function condicion_pago()
    {
        return $this->belongsTo( CondicionPago::class, 'condicion_pago_id');
    }

    public function vendedor()
    {
        return $this->belongsTo( Vendedor::class, 'vendedor_id');
    }

	public static function consultar_registros()
	{
	    return Cliente::leftJoin( 'core_terceros','core_terceros.id','=','vtas_clientes.core_tercero_id')
                            ->leftJoin('vtas_clases_clientes','vtas_clases_clientes.id','=','vtas_clientes.clase_cliente_id')
                            ->leftJoin('vtas_listas_precios_encabezados','vtas_listas_precios_encabezados.id','=','vtas_clientes.lista_precios_id')
                            ->leftJoin('vtas_listas_dctos_encabezados','vtas_listas_dctos_encabezados.id','=','vtas_clientes.lista_descuentos_id')
                            ->leftJoin('vtas_zonas','vtas_zonas.id','=','vtas_clientes.zona_id')
                            ->select(
                                        'vtas_clientes.id AS campo1',
                                        'core_terceros.numero_identificacion AS campo2',
                                        'core_terceros.descripcion AS campo3',
                                        'core_terceros.direccion1 AS campo4',
                                        'core_terceros.telefono1 AS campo5',
                                        'vtas_clases_clientes.descripcion AS campo6',
                                        'vtas_listas_precios_encabezados.descripcion AS campo7',
                                        'vtas_listas_dctos_encabezados.descripcion AS campo8',
                                        'vtas_zonas.descripcion AS campo9',
                                        'vtas_clientes.id AS campo10')
                    	    ->get()
                    	    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = Cliente::leftJoin('core_terceros','core_terceros.id','=','vtas_clientes.core_tercero_id')->where('vtas_clientes.estado','Activo')
                    ->select('vtas_clientes.id','core_terceros.descripcion')
                    ->orderby('core_terceros.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"vtas_doc_encabezados",
                                    "llave_foranea":"cliente_id",
                                    "mensaje":"Cliente tiene documentos de ventas estándar."
                                },
                            "1":{
                                    "tabla":"vtas_movimientos",
                                    "llave_foranea":"cliente_id",
                                    "mensaje":"Cliente tiene movimientos de ventas estándar."
                                },
                            "2":{
                                    "tabla":"vtas_pos_doc_encabezados",
                                    "llave_foranea":"cliente_id",
                                    "mensaje":"Cliente tiene documentos de ventas POS."
                                },
                            "3":{
                                    "tabla":"vtas_pos_movimientos",
                                    "llave_foranea":"cliente_id",
                                    "mensaje":"Cliente tiene movimientos de ventas POS."
                                },
                            "4":{
                                    "tabla":"vtas_pos_puntos_de_ventas",
                                    "llave_foranea":"cliente_default_id",
                                    "mensaje":"Cliente está asociado a punto de ventas (POS)."
                                },
                            "5":{
                                    "tabla":"vtas_vendedores",
                                    "llave_foranea":"cliente_id",
                                    "mensaje":"Cliente está asociado a un vendedor."
                                }
                        }';
        $tablas = json_decode( $tablas_relacionadas );
        foreach($tablas AS $una_tabla)
        { 
            $registro = DB::table( $una_tabla->tabla )->where( $una_tabla->llave_foranea, $id )->get();

            if ( !empty($registro) )
            {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
