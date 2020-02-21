<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use Auth;

use App\Inventarios\InvGrupo;

use App\Contabilidad\Impuesto;


class InvProductoColegio extends Model
{
    protected $table = 'inv_productos'; 

    protected $fillable = ['core_empresa_id','descripcion','tipo','unidad_medida1','unidad_medida2','categoria_id','inv_grupo_id','impuesto_id','precio_compra','precio_venta','estado','referencia','codigo_barras','imagen','mostrar_en_pagina_web','creado_por','modificado_por'];

    public $encabezado_tabla = ['Código', 'Grupo inventario', 'Descripción', 'Editorial', 'Grado', 'Código barras', 'Cantidad', 'Estado', 'Acción'];


    // unidad_medida2 = Editorial
    // categoria_id = Grado
    // referencia = Cantidad. Se ejecutar proceso de entrada incial automática: crear documento EA y dejar en cero este campo.
    // precio_compra: usado para indicar que es un Elemento de biblioteca
    public static function consultar_registros()
    {
        $array_wheres = [ 
                            ['inv_productos.precio_compra' ,'=', 77.77],
                            [ 'inv_productos.core_empresa_id', Auth::user()->empresa_id]
                        ];

        $registros = InvProductoColegio::leftJoin('sga_grados', 'sga_grados.id', '=', 'inv_productos.categoria_id')
                    ->leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
                    ->where( $array_wheres )
                    ->select(
                                'inv_productos.id AS campo1',
                                'inv_grupos.descripcion AS campo2',
                                'inv_productos.descripcion AS campo3',
                                'inv_productos.unidad_medida2 AS campo4',
                                'sga_grados.descripcion AS campo5',
                                'inv_productos.codigo_barras AS campo6',
                                'inv_productos.referencia AS campo7',
                                'inv_productos.estado AS campo8',
                                'inv_productos.id AS campo9')
                    ->get()
                    ->toArray();

        return $registros;
    }
    

    public static function opciones_campo_select()
    {
        $opciones = InvProductoColegio::where('estado','Activo')
                            ->where('core_empresa_id', Auth::user()->empresa_id)
                            ->get();
        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id]=$opcion->id.' '.$opcion->descripcion;
        }

        return $vec;
    }
}
