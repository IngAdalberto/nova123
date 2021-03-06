<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Plantillaarticulonumeral extends Model
{
    protected $table = 'cte_plantillaarticulonumerals';
    protected $fillable = ['id', 'numeracion', 'texto', 'plantillaarticulo_id', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Numeración', 'Texto', 'Artículo', 'Plantilla'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function opciones_campo_select()
    {
        $opciones = Plantillaarticulonumeral::leftJoin('cte_plantillaarticulos', 'cte_plantillaarticulos.id', '=', 'cte_plantillaarticulonumerals.plantillaarticulo_id')
            ->select('cte_plantillaarticulonumerals.id', 'cte_plantillaarticulonumerals.numeracion', 'cte_plantillaarticulonumerals.texto', 'cte_plantillaarticulos.titulo AS articulo_titulo')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->articulo_titulo . ' > ' . $opcion->numeracion . ') ' . $opcion->texto;
        }

        return $vec;
    }

    public static function consultar_registros2($nro_registros, $search)
    {
        return Plantillaarticulonumeral::leftJoin('cte_plantillaarticulos', 'cte_plantillaarticulos.id', '=', 'cte_plantillaarticulonumerals.plantillaarticulo_id')
            ->leftJoin('cte_plantillas', 'cte_plantillas.id', '=', 'cte_plantillaarticulos.plantilla_id')
            ->select(
                'cte_plantillaarticulonumerals.numeracion AS campo1',
                'cte_plantillaarticulonumerals.texto AS campo2',
                DB::raw('CONCAT(cte_plantillaarticulos.titulo," ",cte_plantillaarticulos.texto) AS campo3'),
                'cte_plantillas.titulo AS campo4',
                'cte_plantillaarticulonumerals.id AS campo5'
            )->where("cte_plantillaarticulonumerals.numeracion", "LIKE", "%$search%")
            ->orWhere("cte_plantillaarticulonumerals.texto", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(cte_plantillaarticulos.titulo," ",cte_plantillaarticulos.texto)'), "LIKE", "%$search%")
            ->orWhere("cte_plantillas.titulo", "LIKE", "%$search%")
            ->orderBy('cte_plantillaarticulonumerals.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Plantillaarticulonumeral::leftJoin('cte_plantillaarticulos', 'cte_plantillaarticulos.id', '=', 'cte_plantillaarticulonumerals.plantillaarticulo_id')
            ->leftJoin('cte_plantillas', 'cte_plantillas.id', '=', 'cte_plantillaarticulos.plantilla_id')
            ->select(
                'cte_plantillaarticulonumerals.numeracion AS NÚMERO',
                'cte_plantillaarticulonumerals.texto AS TEXTO',
                DB::raw('CONCAT(cte_plantillaarticulos.titulo," ",cte_plantillaarticulos.texto) AS ARTÍCULO'),
                'cte_plantillas.titulo AS PLANTILLA'
            )->where("cte_plantillaarticulonumerals.numeracion", "LIKE", "%$search%")
            ->orWhere("cte_plantillaarticulonumerals.texto", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(cte_plantillaarticulos.titulo," ",cte_plantillaarticulos.texto)'), "LIKE", "%$search%")
            ->orWhere("cte_plantillas.titulo", "LIKE", "%$search%")
            ->orderBy('cte_plantillaarticulonumerals.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE NUMERALES DE ARTÍCULOS DE PLANTILLAS FORMATO FUEC";
    }

    public function numeraltablas()
    {
        return $this->hasMany(Numeraltabla::class);
    }

    public function plantillaarticulo()
    {
        return $this->belongsTo(Plantillaarticulo::class);
    }
}
