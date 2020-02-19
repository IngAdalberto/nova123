<?php

namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;
use App\Calificaciones\CursoTieneAsignatura;

class EncabezadoCalificacion extends Model
{
    protected $table = 'sga_calificaciones_encabezados';
	protected $fillable = ['columna_calificacion', 'descripcion', 'fecha', 'anio', 'periodo_id', 'curso_id', 'asignatura_id', 'creado_por', 'modificado_por'];
	public $encabezado_tabla = ['Año lectivo', 'Periodo', 'Curso', 'Asignatura', 'Fecha', 'Columna calificación', 'Detalle', 'Acción'];
	public static function consultar_registros()
	{
	    return EncabezadoCalificacion::leftJoin('sga_periodos','sga_periodos.id','=','sga_calificaciones_encabezados.periodo_id')
					    	->leftJoin('sga_periodos_lectivos','sga_periodos_lectivos.id','=','sga_periodos.periodo_lectivo_id')
					    	->leftJoin('sga_cursos','sga_cursos.id','=','sga_calificaciones_encabezados.curso_id')
					    	->leftJoin('sga_asignaturas','sga_asignaturas.id','=','sga_calificaciones_encabezados.asignatura_id')
					    	->select(
					    			'sga_periodos_lectivos.descripcion AS campo1',
					    			'sga_periodos.descripcion AS campo2',
					    			'sga_cursos.descripcion AS campo3',
					    			'sga_asignaturas.descripcion AS campo4',
					    			'sga_calificaciones_encabezados.fecha AS campo5',
					    			'sga_calificaciones_encabezados.columna_calificacion AS campo6',
					    			'sga_calificaciones_encabezados.descripcion AS campo7',
					    			'sga_calificaciones_encabezados.id AS campo8')
						    ->get()
						    ->toArray();
	}

	/*
		$id_select_padre corresponde a curso_id
	*/
    public static function get_registros_select_hijo( $id_select_padre )
    {
        $registros = CursoTieneAsignatura::asignaturas_del_curso( $id_select_padre, null, null, null );

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros as $campo) {
                            
            $opciones .= '<option value="'.$campo->id.'">'.$campo->descripcion.'</option>';
        }
        return $opciones;
    }
}
