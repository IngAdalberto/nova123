<?php

namespace App\Http\Controllers\Calificaciones;

use App\Http\Controllers\Sistema\ModeloController;

use Illuminate\Http\Request;

use Input;
use DB;
use PDF;
use Auth;
use Storage;
use View;
use File;

use App\Http\Requests;

use App\Matriculas\PeriodoLectivo;
use App\Matriculas\Curso;
use App\Matriculas\Matricula;

use App\Calificaciones\Periodo;
use App\Calificaciones\Calificacion;
use App\Calificaciones\CursoTieneAsignatura;
use App\Calificaciones\Asignatura;

class ProcesoController extends ModeloController
{

    public function form_generar_promedio_notas_periodo_final()
    {

        $miga_pan = [
                        ['url' => $this->aplicacion->app.'?id='.Input::get('id'), 'etiqueta'=> $this->aplicacion->descripcion ],
                        ['url' => 'NO','etiqueta'=> 'Proceso: Generar promedio de notas periodo final']
                    ];

        $periodos_lectivos = PeriodoLectivo::get_array_activos();

        return view( 'calificaciones.procesos.generar_promedio_notas_periodo_final', compact( 'miga_pan', 'periodos_lectivos') );
    }


    public function consultar_periodos_periodo_lectivo( $periodo_lectivo_id )
    {

        $periodos = Periodo::get_activos_periodo_lectivo( $periodo_lectivo_id );

        $tabla = '<table class="table table-bordered">';

        $fila_periodos_promediar = '<tr> <td> <b>Periodos a promediar:</b> </td> <td>';

        $el_primero = true;
        foreach ($periodos as $fila)
        {
            if ( !$fila->periodo_de_promedios )
            {
                if ( $el_primero )
                {
                    $fila_periodos_promediar .= $fila->descripcion;
                    $el_primero = false;
                }else{
                    $fila_periodos_promediar .= ', '.$fila->descripcion;
                }
            }
        }
        $fila_periodos_promediar .= '</td> </tr>';

        $hay_periodo_final = 0; // Solo debe valer hasta 1
        
        $fila_periodo_final = '<tr> <td> <b>Periodo de promedios:</b> </td> <td>';
        $el_primero = true;
        foreach ($periodos as $fila)
        {
            if ( $fila->periodo_de_promedios )
            {
                if ( $el_primero )
                {
                    $fila_periodo_final .= $fila->descripcion;
                    $el_primero = false;
                }else{
                    $fila_periodo_final .= ', '.$fila->descripcion;
                }
                $hay_periodo_final++;
            }
        }
        $fila_periodo_final .= '</td> </tr>';

        if ( $hay_periodo_final == 0)
        {
            $fila_periodo_final = '<tr> <td> <b>Periodo de promedios:</b> </td> <td> <span style="color: red;">No creado</span> </td> </tr>';
        }

        $tabla .= $fila_periodos_promediar.$fila_periodo_final.'</table>';
        
        return [ $tabla, $hay_periodo_final ];
    }

    


    public function calcular_promedio_notas_periodo_final( $periodo_lectivo_id )
    {

        $usuario_email = Auth::user()->email;

        $periodo_lectivo = PeriodoLectivo::find( $periodo_lectivo_id );
        
        $periodo_final_id = 0;

        $periodos = Periodo::get_activos_periodo_lectivo( $periodo_lectivo_id );
        $array_ids_periodos_promediar = []; // A promediar
        $i = 0;
        foreach ($periodos as $fila)
        {
            if ( !$fila->periodo_de_promedios )
            {
                $array_ids_periodos_promediar[$i] = $fila->id;
                $i++;
            }else{
                $periodo_final_id = $fila->id;
            }
        }


        // PASO 1. Vaciar los datos del periodo final en ese periodo lectivo
        Calificacion::where('id_periodo',$periodo_final_id)->delete();

        // PASO 2. Calcular y almacenar las nuevas calificaciones promedios
        $cursos_del_periodo_lectivo = Curso::get_registros_del_periodo_lectivo( $periodo_lectivo_id );        
        $cantidad_registros = 0;



        foreach ($cursos_del_periodo_lectivo as $curso)
        {
            $asignaturas_del_curso_y_periodo_lectivo = Asignatura::asignadas_al_curso( $periodo_lectivo_id, $curso->id );


            // Listado de estudiantes con matriculas activas en el curso y año indicados
            $estudiantes = Matricula::estudiantes_matriculados( $curso->id, $periodo_lectivo_id, null );
            
            /*if ($curso->id == 24 )
            {
                dd( [ $curso->descripcion, $asignaturas_del_curso_y_periodo_lectivo->count(), $estudiantes->count() ] );
            }*/
            

            foreach ($asignaturas_del_curso_y_periodo_lectivo as $asignatura)
            {
                foreach ($estudiantes as $estudiante)
                {
                    
                    $prom = Calificacion::get_calificacion_promedio_asignatura_estudiante_periodos($array_ids_periodos_promediar, $curso->id, $estudiante->id, $asignatura->id);

                    Calificacion::create( [
                                            'codigo_matricula' => $estudiante->codigo,
                                            'id_colegio' => $estudiante->id_colegio,
                                            'anio' => explode('-', $periodo_lectivo->fecha_desde)[0] ,
                                            'id_periodo' => $periodo_final_id,
                                            'curso_id' =>$curso->id,
                                            'id_estudiante' => $estudiante->id,
                                            'id_asignatura' => $asignatura->id,
                                            'calificacion' => (float)$prom,
                                            'creado_por' => $usuario_email  
                                        ] );
                    /**/
                    
                    $cantidad_registros++;
                }

            }
        }
        
        return $cantidad_registros;
    }

}
