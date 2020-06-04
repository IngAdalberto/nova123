<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

use App\Core\Tercero;

use DB;

class TerceroFormularioCampana extends Model
{
    protected $table = 'core_terceros';

    protected $fillable = ['core_empresa_id', 'tipo', 'razon_social', 'nombre1', 'otros_nombres', 'apellido1', 'apellido2', 'descripcion', 'id_tipo_documento_id', 'numero_identificacion', 'digito_verificacion', 'ciudad_expedicion', 'direccion1', 'direccion2', 'barrio', 'codigo_ciudad', 'codigo_postal', 'telefono1', 'telefono2', 'email', 'pagina_web', 'estado', 'user_id', 'contab_anticipo_cta_id', 'contab_cartera_cta_id', 'contab_cxp_cta_id', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = [ 'ID', 'Nombre', 'Teléfono', 'Email', 'Acción' ];

    public static function consultar_registros()
    {
        return Tercero::where('creado_por','formulario_campana')
                        ->select(
                                'core_terceros.id AS campo1',
                                DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo2' ),
                                'core_terceros.telefono1 AS campo3',
                                'core_terceros.email AS campo4',
                                'core_terceros.id AS campo5')
                        ->get()
                        ->toArray();
    }
    
}
