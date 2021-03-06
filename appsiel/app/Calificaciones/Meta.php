<?php

namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;

use DB;
use App\Calificaciones\CursoTieneAsignatura;

class Meta extends Model
{
    protected $table = 'sga_metas';

    protected $fillable = ['colegio_id', 'codigo', 'periodo_id', 'curso_id', 'asignatura_id', 'descripcion', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Periodo', 'Curso', 'Asignatura', 'Descripción', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = Meta::leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_metas.periodo_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_metas.curso_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_metas.asignatura_id')
            ->select(
                'sga_periodos.descripcion AS campo1',
                'sga_cursos.descripcion AS campo2',
                'sga_asignaturas.descripcion AS campo3',
                'sga_metas.descripcion AS campo4',
                'sga_metas.estado AS campo5',
                'sga_metas.id AS campo6'
            )
            ->where("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_metas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_metas.estado", "LIKE", "%$search%")
            ->orderBy('sga_metas.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = Meta::leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_metas.periodo_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_metas.curso_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_metas.asignatura_id')
            ->select(
                'sga_periodos.descripcion AS PERIODO',
                'sga_cursos.descripcion AS CURSO',
                'sga_asignaturas.descripcion AS ASIGNATURA',
                'sga_metas.descripcion AS DESCRIPCIÓN',
                'sga_metas.estado AS ESTADO'
            )
            ->where("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_metas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_metas.estado", "LIKE", "%$search%")
            ->orderBy('sga_metas.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE METAS/PROPOSITOS";
    }

    public static function get_para_boletin($periodo_id, $curso_id, $asignatura_id)
    {
        return Meta::where(
            [
                'periodo_id' => $periodo_id,
                'curso_id' => $curso_id,
                'asignatura_id' => $asignatura_id
            ]
        )
            ->get();
    }


    // PADRE = CURSO, HIJO = ASIGNATURAS
    public static function get_registros_select_hijo($id_select_padre)
    {
        $registros = CursoTieneAsignatura::asignaturas_del_curso($id_select_padre, null, null, null);

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros as $campo) {

            $opciones .= '<option value="' . $campo->id . '">' . $campo->descripcion . '</option>';
        }
        return $opciones;
    }



    public static function get_metas($colegio_id, $curso_id, $asignatura_id, $nro_registros, $search)
    {

        $array_wheres = ['sga_metas.colegio_id' => $colegio_id];

        if ($curso_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_metas.curso_id' => $curso_id]);
        }

        if ($asignatura_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_metas.asignatura_id' => $asignatura_id]);
        }

        $registros = Meta::where($array_wheres)
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_metas.periodo_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_metas.curso_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_metas.asignatura_id')
            ->select(
                'sga_periodos.descripcion AS campo1',
                'sga_cursos.descripcion AS campo2',
                'sga_asignaturas.descripcion AS campo3',
                'sga_metas.descripcion AS campo4',
                'sga_metas.estado AS campo5',
                'sga_metas.id AS campo6'
            )
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_metas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_metas.estado", "LIKE", "%$search%")
            ->orderBy('sga_metas.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString2($colegio_id, $curso_id, $asignatura_id, $search)
    {
        $array_wheres = ['sga_metas.colegio_id' => $colegio_id];

        if ($curso_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_metas.curso_id' => $curso_id]);
        }

        if ($asignatura_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_metas.asignatura_id' => $asignatura_id]);
        }

        $string = Meta::where($array_wheres)
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_metas.periodo_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_metas.curso_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_metas.asignatura_id')
            ->select(
                'sga_periodos.descripcion AS PERÍODO',
                'sga_cursos.descripcion AS CURSO',
                'sga_asignaturas.descripcion AS ASIGNATURA',
                'sga_metas.descripcion AS DESCRIPCIÓN',
                'sga_metas.estado AS ESTADO'
            )
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_metas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_metas.estado", "LIKE", "%$search%")
            ->orderBy('sga_metas.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }
}
