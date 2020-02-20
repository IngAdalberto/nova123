<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;

class InvMotivo extends Model
{
    //protected $table = 'inv_motivos'; 

    protected $fillable = ['core_empresa_id','core_tipo_transaccion_id','descripcion','movimiento','cta_contrapartida_id','estado','creado_por','modificado_por'];

    public $encabezado_tabla = ['Código','Descripción','Transacción asociada', 'Movimiento','Cta. Contrapartida','Estado','Acción'];

    public static function consultar_registros()
    {
    	$registros = InvMotivo::leftJoin('sys_tipos_transacciones', 'sys_tipos_transacciones.id', '=', 'inv_motivos.core_tipo_transaccion_id')
                    ->leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'inv_motivos.cta_contrapartida_id')
                    ->where('inv_motivos.core_empresa_id', Auth::user()->empresa_id)
                    ->select('inv_motivos.id AS campo1','inv_motivos.descripcion AS campo2','sys_tipos_transacciones.descripcion AS campo3','inv_motivos.movimiento AS campo4',DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS campo5'),'inv_motivos.estado AS campo6','inv_motivos.id AS campo7')
                    ->get()
                    ->toArray();

        return $registros;
    }
    

    public static function opciones_campo_select()
    {
        $opciones = InvMotivo::where('estado','Activo')
                            ->where('core_empresa_id', Auth::user()->empresa_id)
                            ->get();
        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }


    public static function get_motivos_transaccion( $transaccion_id )
    {
        $motivos = InvMotivo::where('core_tipo_transaccion_id',$transaccion_id)
                            ->where('estado','Activo')
                            ->get();      
        $vec_m = [];
        foreach ($motivos as $fila) {
            $vec_m[$fila->id.'-'.$fila->movimiento]=$fila->descripcion; 
        }
        
        return $vec_m;
    }

}
