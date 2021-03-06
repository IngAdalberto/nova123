<?php

namespace App\Calificaciones;

use App\Calificaciones\Calificacion;
use Illuminate\Database\Eloquent\Model;

use App\Calificaciones\CursoTieneAsignatura;

use DB;
use App\Calificaciones\Periodo;
use App\Matriculas\PeriodoLectivo;

class LogroAdicional extends Model
{
    protected $table = 'sga_logros';

    protected $fillable = ['id_colegio', 'codigo', 'asignatura_id', 'descripcion', 'estado', 'ocupado', 'escala_valoracion_id', 'curso_id', 'periodo_id'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Cód.', 'Año lectivo', 'Periodo', 'Curso', 'Asignatura', 'Descripción', 'Estado'];


    // El archivo js debe estar en la carpeta public
    //public $archivo_js = 'assets/js/calificaciones/logros.js';

    // [index, create, edit, show, ]
    public $vistas = '{ 
                        "index":layouts.index,
                        "create":"layouts.create",
                        "edit":null,
                        "show":null
                    }';

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","store":"calificaciones_logros","update":"calificaciones_logros/id_fila","cambiar_estado":"a_i/id_fila"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $periodo_lectivo_id = PeriodoLectivo::get_actual()->id;

        return LogroAdicional::leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_logros.periodo_id')
            ->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_periodos.periodo_lectivo_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_logros.curso_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_logros.asignatura_id')
            ->leftJoin('sga_escala_valoracion', 'sga_escala_valoracion.id', '=', 'sga_logros.escala_valoracion_id')
            ->where('sga_periodos.periodo_lectivo_id', $periodo_lectivo_id)
            ->where('sga_logros.escala_valoracion_id', 0)
            ->select(
                'sga_logros.codigo AS campo1',
                'sga_periodos_lectivos.descripcion AS campo2',
                'sga_periodos.descripcion AS campo3',
                'sga_cursos.descripcion AS campo4',
                'sga_asignaturas.descripcion AS campo5',
                'sga_logros.descripcion AS campo6',
                'sga_logros.estado AS campo7',
                'sga_logros.id AS campo8'
            )
            ->where("sga_logros.codigo", "LIKE", "%$search%")
            ->orWhere("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_logros.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_logros.estado", "LIKE", "%$search%")
            ->orderBy('sga_logros.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $periodo_lectivo_id = PeriodoLectivo::get_actual()->id;

        $string = LogroAdicional::leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_logros.periodo_id')
            ->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_periodos.periodo_lectivo_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_logros.curso_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_logros.asignatura_id')
            ->leftJoin('sga_escala_valoracion', 'sga_escala_valoracion.id', '=', 'sga_logros.escala_valoracion_id')
            ->where('sga_periodos.periodo_lectivo_id', $periodo_lectivo_id)
            ->where('sga_logros.escala_valoracion_id', 0)
            ->select(
                'sga_logros.codigo AS CODIGO',
                'sga_periodos_lectivos.descripcion AS AÑO_LECTIVO',
                'sga_periodos.descripcion AS PERIODO',
                'sga_cursos.descripcion AS CURSO',
                'sga_asignaturas.descripcion AS ASIGNATURA',
                'sga_logros.descripcion AS DESCRIPCIÓN',
                'sga_logros.estado AS ESTADO'
            )
            ->where("sga_logros.codigo", "LIKE", "%$search%")
            ->orWhere("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_logros.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_logros.estado", "LIKE", "%$search%")
            ->orderBy('sga_logros.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE LOGROS ADICIONALES";
    }

    public static function get_logros($id_colegio, $curso_id, $asignatura_id, $periodo_id = null, $nro_registros, $search)
    {

        $array_wheres = ['sga_logros.id_colegio' => $id_colegio];

        if ($curso_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_logros.curso_id' => $curso_id]);
        }

        if ($asignatura_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_logros.asignatura_id' => $asignatura_id]);
        }

        if ($periodo_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_logros.periodo_id' => $periodo_id]);
        }

        $registros = Logro::where($array_wheres)
            ->where('sga_logros.escala_valoracion_id', 0)
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_logros.periodo_id')
            ->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_periodos.periodo_lectivo_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_logros.curso_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_logros.asignatura_id')
            ->leftJoin('sga_escala_valoracion', 'sga_escala_valoracion.id', '=', 'sga_logros.escala_valoracion_id')
            ->select(
                'sga_logros.codigo AS campo1',
                'sga_periodos_lectivos.descripcion AS campo2',
                'sga_periodos.descripcion AS campo3',
                'sga_cursos.descripcion AS campo4',
                'sga_asignaturas.descripcion AS campo5',
                'sga_logros.descripcion AS campo6',
                'sga_logros.estado AS campo7',
                'sga_logros.id AS campo8'
            )
            ->orWhere("sga_logros.codigo", "LIKE", "%$search%")
            ->orWhere("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_logros.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_logros.estado", "LIKE", "%$search%")
            ->orderBy('sga_logros.codigo', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString2($id_colegio, $curso_id, $asignatura_id, $periodo_id = null, $search)
    {
        $array_wheres = ['sga_logros.id_colegio' => $id_colegio];

        if ($curso_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_logros.curso_id' => $curso_id]);
        }

        if ($asignatura_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_logros.asignatura_id' => $asignatura_id]);
        }

        if ($periodo_id != null) {
            $array_wheres = array_merge($array_wheres, ['sga_logros.periodo_id' => $periodo_id]);
        }

        $string = Logro::where($array_wheres)
            ->where('sga_logros.escala_valoracion_id', 0)
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_logros.periodo_id')
            ->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_periodos.periodo_lectivo_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_logros.curso_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_logros.asignatura_id')
            ->leftJoin('sga_escala_valoracion', 'sga_escala_valoracion.id', '=', 'sga_logros.escala_valoracion_id')
            ->select(
                'sga_logros.codigo AS CÓDIGO',
                'sga_periodos_lectivos.descripcion AS AÑO_LECTIVO',
                'sga_periodos.descripcion AS PERÍODO',
                'sga_cursos.descripcion AS CURSO',
                'sga_asignaturas.descripcion AS ASIGNATURA',
                'sga_logros.descripcion AS LOGRO',
                'sga_logros.estado AS ESTADO'
            )
            ->orWhere("sga_logros.codigo", "LIKE", "%$search%")
            ->orWhere("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_logros.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_logros.estado", "LIKE", "%$search%")
            ->orderBy('sga_logros.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    /**
     * Obtener cada logro que pertenece a la calificación.
     */
    public function calificacion()
    {
        return $this->belongsTo(Calificacion::class);
    }

    // PADRE = CURSO, HIJO = asignaturas
    public static function get_registros_select_hijo($id_select_padre)
    {
        $registros = CursoTieneAsignatura::asignaturas_del_curso($id_select_padre, null, null, null);

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros as $campo) {

            $opciones .= '<option value="' . $campo->id . '">' . $campo->descripcion . '</option>';
        }
        return $opciones;
    }
}
