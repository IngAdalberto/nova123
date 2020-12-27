<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use DB;

class FodaEstudiante extends Model
{
    protected $table = 'sga_foda_estudiantes';

    protected $fillable = ['id_estudiante', 'fecha_novedad', 'tipo_caracteristica', 'descripcion'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Estudiante', 'Fecha novedad', 'Tipo característica', 'Descripción'];

    public static function consultar_registros($nro_registros)
    {
        return FodaEstudiante::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_foda_estudiantes.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->select(
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1'),
                'sga_foda_estudiantes.fecha_novedad AS campo2',
                'sga_foda_estudiantes.tipo_caracteristica AS campo3',
                'sga_foda_estudiantes.descripcion AS campo4',
                'sga_foda_estudiantes.id AS campo5'
            )->orderBy('sga_foda_estudiantes.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function get_foda_un_estudiante($estudiante_id)
    {
        return FodaEstudiante::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_foda_estudiantes.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->where('sga_foda_estudiantes.id_estudiante', $estudiante_id)
            ->select(
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1'),
                'sga_foda_estudiantes.fecha_novedad AS campo2',
                'sga_foda_estudiantes.tipo_caracteristica AS campo3',
                'sga_foda_estudiantes.descripcion AS campo4',
                'sga_foda_estudiantes.id AS campo5'
            )
            ->get()
            ->toArray();
    }
}
