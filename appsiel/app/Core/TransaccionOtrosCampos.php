<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;
use Schema;

use App\Core\Tercero;

class TransaccionOtrosCampos extends Model
{
    protected $table = 'core_transacciones_otros_datos';
	
	protected $fillable = [ 'core_tipo_transaccion_id', 'terminos_y_condiciones' ];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Transacción', 'Términos y condiciones' ];

    public $urls_acciones = '{ "create":"web/create", "edit":"web/id_fila/edit", "eliminar":"web_eliminar/id_fila"}';

    public function tipo_transaccion()
    {
        return $this->belongsTo('App\Sistema\TipoTransaccion', 'core_tipo_transaccion_id');
    }

	public static function consultar_registros($nro_registros, $search)
    {
        $array = TransaccionOtrosCampos::leftJoin('sys_tipos_transacciones', 'sys_tipos_transacciones.id', '=', 'core_transacciones_otros_datos.core_tipo_transaccion_id')
						            ->select(
						                'sys_tipos_transacciones.descripcion AS campo1',
						                'core_transacciones_otros_datos.terminos_y_condiciones AS campo2',
						                'core_transacciones_otros_datos.id AS campo3'
						            )
						            ->where("sys_tipos_transacciones.descripcion", "LIKE", "%$search%")
						            ->orWhere("core_transacciones_otros_datos.terminos_y_condiciones", "LIKE", "%$search%")
						            ->orderBy('core_transacciones_otros_datos.created_at', 'DESC')
						            ->paginate($nro_registros);

        return $array;
    }

    public static function sqlString($search)
    {
        $string = TransaccionOtrosCampos::leftJoin('sys_tipos_transacciones', 'sys_tipos_transacciones.id', '=', 'core_transacciones_otros_datos.core_tipo_transaccion_id')
						            ->select(
						                'sys_tipos_transacciones.descripcion AS campo1',
						                'core_transacciones_otros_datos.terminos_y_condiciones AS campo2',
						                'core_transacciones_otros_datos.id AS campo3'
						            )
						            ->where("sys_tipos_transacciones.descripcion", "LIKE", "%$search%")
						            ->orWhere("core_transacciones_otros_datos.terminos_y_condiciones", "LIKE", "%$search%")
					            ->orderBy('core_transacciones_otros_datos.created_at', 'DESC')
					            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE OTROS CAMPOS DE TRANSACCIONES";
    }

    public static function opciones_campo_select()
    {
        $opciones = TransaccionOtrosCampos::leftJoin('core_terceros','core_terceros.id','=','core_transacciones_otros_datos.core_tercero_id')->where('core_transacciones_otros_datos.estado','Activo')
                    ->select('core_transacciones_otros_datos.id','core_terceros.descripcion')
                    ->orderby('core_terceros.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public function store_adicional( $datos, $registro )
    {
    	$datos['tipo'] = 'Persona natural';
    	$datos['core_empresa_id'] = Auth::user()->empresa;
    	$datos['numero_identificacion'] = rand(1111,99999);
    	$datos['codigo_ciudad'] = 16920001;
    	$datos['creado_por'] = Auth::user()->email;

    	$tercero = Tercero::create( $datos );

    	$registro->core_tercero_id = $tercero->id;
    	$registro->save();
    }

    public static function get_campos_adicionales_edit($lista_campos, $registro)
    {
        $tercero = Tercero::find( $registro->core_tercero_id );

        /*
            Personalizar los campos
        */
        $cantidad_campos = count($lista_campos);
        for ($i = 0; $i <  $cantidad_campos; $i++)
        {
            switch ($lista_campos[$i]['name'])
            {
                case 'descripcion':
                    $lista_campos[$i]['value'] = $tercero->descripcion;
                    break;
                case 'email':
                    $lista_campos[$i]['value'] = $tercero->email;
                    break;
                case 'telefono1':
                    $lista_campos[$i]['value'] = $tercero->telefono1;
                    break;

                default:
                    # code...
                    break;
            }
        }

        return $lista_campos;
    }



    public static function update_adicional( $datos, $registro_id )
    {
    	$contacto = TransaccionOtrosCampos::find( $registro_id );
        $tercero = Tercero::find( $contacto->core_tercero_id );

        $tercero->descripcion = $datos['descripcion'];
        $tercero->email = $datos['email'];
        $tercero->telefono1 = $datos['telefono1'];
        $tercero->save();

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
            if ( !Schema::hasTable( $una_tabla->tabla ) )
            {
                continue;
            }
            
            $registro = DB::table( $una_tabla->tabla )->where( $una_tabla->llave_foranea, $id )->get();

            if ( !empty($registro) )
            {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
