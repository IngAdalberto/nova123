<?php

namespace App\AcademicoDocente;

use Illuminate\Database\Eloquent\Model;

use App\Calificaciones\CursoTieneAsignatura;

use App\AcademicoDocente\PlanClaseEstrucElemento;
use App\AcademicoDocente\PlanClaseEstrucPlantilla;
use App\AcademicoDocente\PlanClaseRegistro;

use Form;
use Input;
use Auth;

use App\Matriculas\Curso;

use App\Calificaciones\Asignatura;

use App\Matriculas\PeriodoLectivo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class PlanClaseEncabezado extends Model
{
    protected $table = 'sga_plan_clases_encabezados';

    protected $fillable = ['plantilla_plan_clases_id', 'fecha', 'semana_calendario_id', 'periodo_id', 'curso_id', 'asignatura_id', 'user_id', 'archivo_adjunto', 'descripcion', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Plan de clases', 'Fecha', 'Semana académica', 'Periodo', 'Curso', 'Asignatura', 'Profesor', 'Estado'];

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/academico_docente/planes_clases.js';

    public static function consultar_registros($nro_registros, $search)
    {
        $user = Auth::user();

        $array_wheres = [ ['sga_plan_clases_encabezados.plantilla_plan_clases_id', '<>', 99999] ];

        if ( $user->hasRole('Profesor') || $user->hasRole('Director de grupo') )
        {
            $array_wheres = array_merge($array_wheres, ['sga_plan_clases_encabezados.user_id' => $user->id]);
        }

        $collection = PlanClaseEncabezado::leftJoin('sga_plan_clases_struc_plantillas', 'sga_plan_clases_struc_plantillas.id', '=', 'sga_plan_clases_encabezados.plantilla_plan_clases_id')
                                ->leftJoin('sga_semanas_calendario', 'sga_semanas_calendario.id', '=', 'sga_plan_clases_encabezados.semana_calendario_id')
                                ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_plan_clases_encabezados.periodo_id')
                                ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_plan_clases_encabezados.curso_id')
                                ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_plan_clases_encabezados.asignatura_id')
                                ->leftJoin('users', 'users.id', '=', 'sga_plan_clases_encabezados.user_id')
                                ->where( $array_wheres )
                                ->select(
                                    'sga_plan_clases_struc_plantillas.descripcion AS campo1',
                                    'sga_plan_clases_encabezados.fecha AS campo2',
                                    'sga_semanas_calendario.descripcion AS campo3',
                                    'sga_periodos.descripcion AS campo4',
                                    'sga_cursos.descripcion AS campo5',
                                    'sga_asignaturas.descripcion AS campo6',
                                    'users.name AS campo7',
                                    'sga_plan_clases_encabezados.estado AS campo8',
                                    'sga_plan_clases_encabezados.id AS campo9'
                                )->orderBy('sga_plan_clases_encabezados.created_at', 'DESC')
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

    public static function sqlString($search)
    {
        $user = Auth::user();

        $array_wheres = [['sga_plan_clases_encabezados.plantilla_plan_clases_id', '<>', 99999]];

        if ($user->hasRole('Profesor') || $user->hasRole('Director de grupo')) {
            $array_wheres = array_merge($array_wheres, ['sga_plan_clases_encabezados.user_id' => $user->id]);
        }

        $string = PlanClaseEncabezado::leftJoin('sga_plan_clases_struc_plantillas', 'sga_plan_clases_struc_plantillas.id', '=', 'sga_plan_clases_encabezados.plantilla_plan_clases_id')
            ->leftJoin('sga_semanas_calendario', 'sga_semanas_calendario.id', '=', 'sga_plan_clases_encabezados.semana_calendario_id')
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_plan_clases_encabezados.periodo_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_plan_clases_encabezados.curso_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_plan_clases_encabezados.asignatura_id')
            ->leftJoin('users', 'users.id', '=', 'sga_plan_clases_encabezados.user_id')
            ->where($array_wheres)
            ->select(
                'sga_plan_clases_struc_plantillas.descripcion AS PLAN_DE_CLASES',
                'sga_plan_clases_encabezados.fecha AS FECHA',
                'sga_semanas_calendario.descripcion AS SEMANA_ACADÉMICA',
                'sga_periodos.descripcion AS PERIODO',
                'sga_cursos.descripcion AS CURSO',
                'sga_asignaturas.descripcion AS ASIGNATURA',
                'users.name AS PROFESOR',
                'sga_plan_clases_encabezados.estado AS ESTADO'
            )->where("sga_plan_clases_struc_plantillas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_plan_clases_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere("sga_semanas_calendario.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("users.name", "LIKE", "%$search%")
            ->orWhere("sga_plan_clases_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('sga_plan_clases_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PLAN DE CLASES";
    }

    public static function opciones_campo_select()
    {
        $opciones = PlanClaseEncabezado::where('sga_plan_clases_encabezados.estado', 'Activo')
            ->select('sga_plan_clases_encabezados.id', 'sga_plan_clases_encabezados.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public function plantilla()
    {
        return $this->belongsTo(PlanClaseEstrucPlantilla::class, 'plantilla_plan_clases_id');
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

    public static function get_campos_adicionales_create($lista_campos)
    {
        $user = Auth::user();

        // Enviar formulario vacío. Se evita la creación, si se presiona el botón desde Académico Docente, pues no se han enviado ni el curos ni la asignatura 
        if (($user->hasRole('Profesor') || $user->hasRole('Director de grupo')) && is_null(Input::get('curso_id'))) {
            return [
                [
                    "id" => 999,
                    "descripcion" => "Label no se puede ingresar registros desde esta opción.",
                    "tipo" => "personalizado",
                    "name" => "lbl_planilla",
                    "opciones" => "",
                    "value" => '<div class="form-group">                    
                                                    <label class="control-label col-sm-3" style="color: red;" > <b> No se pueden ingresar registros desde esta opción. </b> </label>
                                                    <br>
                                                    <a href="' . url('academico_docente?id=' . Input::get('id')) . '" class="btn btn-sm btn-info"> <i class="fa fa-th-large"></i> Ir a mi listado de asignaturas. </a>      
                                                </div>',
                    "atributos" => [],
                    "definicion" => "",
                    "requerido" => 0,
                    "editable" => 1,
                    "unico" => 0
                ]
            ];
        }


        /*
            Personalizar los campos
        */
        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++) {
            // Cuando se envían la asignatura y el curso en la URL

            if (!is_null(Input::get('curso_id'))) {
                switch ($lista_campos[$i]['name']) {
                    case 'curso_id':
                        $curso = Curso::find(Input::get('curso_id'));
                        $lista_campos[$i]['opciones'] = [$curso->id => $curso->descripcion];
                        break;
                    case 'asignatura_id':
                        $asignatura = Asignatura::find(Input::get('asignatura_id'));
                        $lista_campos[$i]['opciones'] = [$asignatura->id => $asignatura->descripcion];
                        break;

                    default:
                        # code...
                        break;
                }
            }
        }


        /*
            Agregar nuevos campos
        */

        $plantilla_default = PlanClaseEstrucPlantilla::get_actual();

        if (is_null($plantilla_default)) {
            return [
                [
                    "id" => 999,
                    "descripcion" => "",
                    "tipo" => "personalizado",
                    "name" => "lbl_planilla",
                    "opciones" => "",
                    "value" => '<div class="form-group">                    
                                                <label class="control-label col-sm-3" style="color: red;" > <b> No se ha creado ninguna plantilla para PLANES DE CLASES en el año lectivo actual . </b> </label>
                                            </div>',
                    "atributos" => [],
                    "definicion" => "",
                    "requerido" => 0,
                    "editable" => 1,
                    "unico" => 0
                ]
            ];
        }

        // Agregar al comienzo del documento
        array_unshift($lista_campos, [
            "id" => 999,
            "descripcion" => "Planilla plan de clases",
            "tipo" => "personalizado",
            "name" => "lbl_planilla",
            "opciones" => "",
            "value" => '<div class="form-group">                    
                                                            <label class="control-label col-sm-3" > <b> Planilla plan de clases: </b> </label>

                                                            <div class="col-sm-9">
                                                                ' . $plantilla_default->descripcion . '
                                                                <input name="plantilla_plan_clases_id" id="plantilla_plan_clases_id" type="hidden" value="' . $plantilla_default->id . '"/>
                                                            </div>                   
                                                        </div>',
            "atributos" => [],
            "definicion" => "",
            "requerido" => 0,
            "editable" => 1,
            "unico" => 0
        ]);

        $elementos_plantilla = PlanClaseEstrucElemento::where('plantilla_plan_clases_id', $plantilla_default->id)
                                ->where('estado', 'Activo')
                                ->orderBy('orden')
                                ->get();

        foreach ($elementos_plantilla as $elemento) {
            array_push($lista_campos, [
                "id" => $elemento->id,
                "descripcion" => $elemento->descripcion,
                "tipo" => "bsTextArea",
                "name" => "elemento_descripcion[]",
                "opciones" => "",
                "value" => null,
                "atributos" => ['class' => 'contenido'],
                "definicion" => "",
                "requerido" => 0,
                "editable" => 1,
                "unico" => 0
            ], [
                "id" => $elemento->id,
                "descripcion" => $elemento->descripcion,
                "tipo" => "hidden",
                "name" => "elemento_id[]",
                "opciones" => "",
                "value" => $elemento->id,
                "atributos" => [],
                "definicion" => "",
                "requerido" => 0,
                "editable" => 1,
                "unico" => 0
            ]);
        }

        array_push(
            $lista_campos,
            [
                "id" => 999,
                "descripcion" => 'user_id',
                "tipo" => "hidden",
                "name" => "user_id",
                "opciones" => "",
                "value" => Auth::user()->id,
                "atributos" => [],
                "definicion" => "",
                "requerido" => 0,
                "editable" => 1,
                "unico" => 0
            ]
        );

        return $lista_campos;
    }


    public static function get_campos_adicionales_edit($lista_campos, $registro)
    {
        $user = Auth::user();

        /*
            Personalizar los campos
        */
        if ($user->hasRole('Profesor') || $user->hasRole('Director de grupo')) {
            $cantida_campos = count($lista_campos);
            for ($i = 0; $i <  $cantida_campos; $i++) {
                switch ($lista_campos[$i]['name']) {
                    case 'curso_id':
                        $curso = Curso::find($registro->curso_id);
                        $lista_campos[$i]['opciones'] = [$curso->id => $curso->descripcion];
                        break;
                    case 'asignatura_id':
                        $asignatura = Asignatura::find($registro->asignatura_id);
                        $lista_campos[$i]['opciones'] = [$asignatura->id => $asignatura->descripcion];
                        break;

                    default:
                        # code...
                        break;
                }
            }
        }


        /*
            Agregar nuevos campos
        */
        $plantilla = PlanClaseEstrucPlantilla::find($registro->plantilla_plan_clases_id);

        // Agregar al comienzo del documento
        array_unshift($lista_campos, [
            "id" => 999,
            "descripcion" => "Planilla plan de",
            "tipo" => "personalizado",
            "name" => "lbl_planilla",
            "opciones" => "",
            "value" => '<div class="form-group">                    
                                                            <label class="control-label col-sm-3" > <b> Planilla plan de clases: </b> </label>

                                                            <div class="col-sm-9">
                                                                ' . $plantilla->descripcion . '
                                                                <input name="plantilla_plan_clases_id" id="plantilla_plan_clases_id" type="hidden" value="' . $plantilla->id . '"/>
                                                            </div>                   
                                                        </div>',
            "atributos" => [],
            "definicion" => "",
            "requerido" => 0,
            "editable" => 1,
            "unico" => 0
        ]);

        $elementos_plantilla = PlanClaseEstrucElemento::where('plantilla_plan_clases_id', $plantilla->id)
            ->where('estado', 'Activo')
            ->orderBy('orden')
            ->get();

        foreach ($elementos_plantilla as $elemento) {
            $registro_elemento = PlanClaseRegistro::where('plan_clase_encabezado_id', $registro->id)
                ->where('plan_clase_estruc_elemento_id', $elemento->id)
                ->get()
                ->first();

            $contenido = '';
            if (!is_null($registro_elemento)) {
                $contenido = $registro_elemento->contenido;
            }

            array_push($lista_campos, [
                "id" => $elemento->id,
                "descripcion" => $elemento->descripcion,
                "tipo" => "bsTextArea",
                "name" => "elemento_descripcion[]",
                "opciones" => "",
                "value" => $contenido,
                "atributos" => ['class' => 'contenido'],
                "definicion" => "",
                "requerido" => 0,
                "editable" => 1,
                "unico" => 0
            ], [
                "id" => $elemento->id,
                "descripcion" => $elemento->descripcion,
                "tipo" => "hidden",
                "name" => "elemento_id[]",
                "opciones" => "",
                "value" => $elemento->id,
                "atributos" => [],
                "definicion" => "",
                "requerido" => 0,
                "editable" => 1,
                "unico" => 0
            ]);
        }

        return $lista_campos;
    }

    public static function get_registros_anterior_siguiente($id)
    {
        $user = Auth::user();

        $where = [['sga_plan_clases_encabezados.user_id', '>', 0]];

        if ($user->hasRole('Profesor') || $user->hasRole('Director de grupo')) {
            $where = [['sga_plan_clases_encabezados.user_id', '=', $user->id]];
        }

        $reg_anterior = PlanClaseEncabezado::where('id', '<', $id)->where($where)->max('id');
        $reg_siguiente = PlanClaseEncabezado::where('id', '>', $id)->where($where)->min('id');

        return [$reg_anterior, $reg_siguiente];
    }

    public static function get_registro_impresion($id)
    {
        return PlanClaseEncabezado::leftJoin('sga_plan_clases_struc_plantillas', 'sga_plan_clases_struc_plantillas.id', '=', 'sga_plan_clases_encabezados.plantilla_plan_clases_id')
            ->leftJoin('sga_semanas_calendario', 'sga_semanas_calendario.id', '=', 'sga_plan_clases_encabezados.semana_calendario_id')
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_plan_clases_encabezados.periodo_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_plan_clases_encabezados.curso_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_plan_clases_encabezados.asignatura_id')
            ->leftJoin('users', 'users.id', '=', 'sga_plan_clases_encabezados.user_id')
            ->where('sga_plan_clases_encabezados.id', $id)
            ->select(
                'sga_plan_clases_struc_plantillas.descripcion AS plantilla_decripcion',
                'sga_plan_clases_encabezados.fecha',
                'sga_plan_clases_encabezados.archivo_adjunto',
                'sga_plan_clases_encabezados.descripcion',
                'sga_semanas_calendario.descripcion AS semana_decripcion',
                'sga_periodos.descripcion AS periodo_decripcion',
                'sga_cursos.descripcion AS curso_decripcion',
                'sga_asignaturas.descripcion AS asignatura_decripcion',
                'users.name AS usuario_decripcion',
                'sga_plan_clases_encabezados.plantilla_plan_clases_id',
                'sga_plan_clases_encabezados.estado',
                'sga_plan_clases_encabezados.id'
            )
            ->get()
            ->first();
    }



    public static function consultar_guias_estudiantes($curso_id, $asignatura_id)
    {
        $array_wheres = [ ['sga_plan_clases_encabezados.plantilla_plan_clases_id', '=', 99999], ['sga_plan_clases_encabezados.fecha', 'LIKE', date('Y').'%'] ];
        $array_wheres = array_merge(
            $array_wheres,
            ['sga_plan_clases_encabezados.curso_id' => $curso_id, 'sga_plan_clases_encabezados.asignatura_id' => $asignatura_id]
        );

        return PlanClaseEncabezado::leftJoin('sga_plan_clases_struc_plantillas', 'sga_plan_clases_struc_plantillas.id', '=', 'sga_plan_clases_encabezados.plantilla_plan_clases_id')
            ->leftJoin('sga_semanas_calendario', 'sga_semanas_calendario.id', '=', 'sga_plan_clases_encabezados.semana_calendario_id')
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_plan_clases_encabezados.periodo_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_plan_clases_encabezados.curso_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_plan_clases_encabezados.asignatura_id')
            ->leftJoin('users', 'users.id', '=', 'sga_plan_clases_encabezados.user_id')
            ->where($array_wheres)
            ->select(
                'sga_plan_clases_struc_plantillas.descripcion AS plan_clases',
                'sga_plan_clases_encabezados.fecha',
                'sga_plan_clases_encabezados.archivo_adjunto',
                'sga_plan_clases_encabezados.descripcion',
                'sga_semanas_calendario.descripcion AS semana',
                'sga_periodos.descripcion AS periodo_decripcion',
                'sga_cursos.descripcion AS curso_decripcion',
                'sga_asignaturas.descripcion AS asignatura_decripcion',
                'users.name AS profesor',
                'sga_plan_clases_encabezados.estado',
                'sga_plan_clases_encabezados.id'
            )
            ->get();
    }


    public static function get_plan_periodo_lectivo($periodo_lectivo_id = null)
    {
        if (is_null($periodo_lectivo_id)) {
            $periodo_lectivo_id = PeriodoLectivo::get_actual()->id;
        }

        // Se obtienen la plantilla del periodo lectivo        
        $plantilla_plan_clases = PlanClaseEstrucPlantilla::where('periodo_lectivo_id', $periodo_lectivo_id)
            ->where('tipo_plantilla', 'planeador')
            ->get()
            ->first();

        // Se devuelven los datos del plan que tenga asociado la plantilla
        return PlanClaseEncabezado::get_registro_impresion(PlanClaseEncabezado::where('plantilla_plan_clases_id', $plantilla_plan_clases->id)->value('id'));
    }
}
