<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

use App\Core\Tercero;

use DB;

class TerceroFormularioCampana extends Model
{
    protected $table = 'core_terceros';

    protected $fillable = ['core_empresa_id', 'tipo', 'razon_social', 'nombre1', 'otros_nombres', 'apellido1', 'apellido2', 'descripcion', 'id_tipo_documento_id', 'numero_identificacion', 'digito_verificacion', 'ciudad_expedicion', 'direccion1', 'direccion2', 'barrio', 'codigo_ciudad', 'codigo_postal', 'telefono1', 'telefono2', 'email', 'pagina_web', 'estado', 'user_id', 'contab_anticipo_cta_id', 'contab_cartera_cta_id', 'contab_cxp_cta_id', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nombre', 'Teléfono', 'Email', 'Fecha registro'];

    public static function consultar_registros($nro_registros, $search)
    {
        return Tercero::where('creado_por', 'formulario_campana')
            ->select(
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo1'),
                'core_terceros.telefono1 AS campo2',
                'core_terceros.email AS campo3',
                'core_terceros.created_at AS campo4',
                'core_terceros.id AS campo5'
            )->where(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere("core_terceros.telefono1", "LIKE", "%$search%")
            ->orWhere("core_terceros.email", "LIKE", "%$search%")
            ->orWhere("core_terceros.created_at", "LIKE", "%$search%")
            ->orderBy('core_terceros.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Tercero::where('creado_por', 'formulario_campana')
            ->select(
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS CLIENTE'),
                'core_terceros.telefono1 AS TELÉFONO',
                'core_terceros.email AS CORREO',
                'core_terceros.created_at AS CREADO'
            )->where(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere("core_terceros.telefono1", "LIKE", "%$search%")
            ->orWhere("core_terceros.email", "LIKE", "%$search%")
            ->orWhere("core_terceros.created_at", "LIKE", "%$search%")
            ->orderBy('core_terceros.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CLIENTES PARA CAMPAÑA WEB";
    }
}
