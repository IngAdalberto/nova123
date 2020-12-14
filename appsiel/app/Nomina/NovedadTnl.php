<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

/*
	tipo_novedad_tnl: { incapacidad | permiso_remunerado | permiso_no_remunerado | suspencion }
	origen_incapacidad: { comun | laboral }
	clase_incapacidad: { enfermedad_general | licencia_maternidad | licencia_paternidad | accidente_trabajo | enfermedad_profesional}
*/
class NovedadTnl extends Model
{
	protected $table = 'nom_novedades_tnl';
	
	protected $fillable = ['nom_concepto_id', 'nom_contrato_id', 'fecha_inicial_tnl', 'fecha_final_tnl', 'cantidad_dias_tnl', 'cantidad_horas_tnl', 'tipo_novedad_tnl', 'codigo_diagnostico_incapacidad', 'numero_incapacidad', 'fecha_expedicion_incapacidad', 'origen_incapacidad', 'clase_incapacidad', 'fecha_incapacidad', 'valor_a_pagar_eps', 'valor_a_pagar_arl', 'valor_a_pagar_afp', 'valor_a_pagar_empresa', 'observaciones', 'estado', 'cantidad_dias_amortizados', 'cantidad_dias_pendientes_amortizar', 'es_prorroga', 'novedad_tnl_anterior_id'];
	
	public $encabezado_tabla = ['Concepto', 'Empleado', 'Tipo novedad', 'Fecha inicial TNL', 'Cant. días TNL', 'Cant. días amortizados', 'Cant. días pend.', 'Observaciones', 'Estado', 'Acción'];

	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

	public $archivo_js = 'assets/js/nomina/novedades_tnl.js';

	public function concepto()
	{
		return $this->belongsTo(NomConcepto::class, 'nom_concepto_id');
	}

	public function contrato()
	{
		return $this->belongsTo(NomContrato::class, 'nom_contrato_id');
	}

	public static function consultar_registros()
	{
	    return NovedadTnl::leftJoin('nom_conceptos','nom_conceptos.id','=','nom_novedades_tnl.nom_concepto_id')
	    				->leftJoin('nom_contratos','nom_contratos.id','=','nom_novedades_tnl.nom_contrato_id')
	    				->leftJoin('core_terceros','core_terceros.id','=','nom_contratos.core_tercero_id')
	    				->select(
	    						'nom_conceptos.descripcion AS campo1',
	    						'core_terceros.descripcion AS campo2',
	    						'nom_novedades_tnl.tipo_novedad_tnl AS campo3',
	    						'nom_novedades_tnl.fecha_inicial_tnl AS campo4',
	    						'nom_novedades_tnl.cantidad_dias_tnl AS campo5',
                                'nom_novedades_tnl.cantidad_dias_amortizados AS campo6',
                                'nom_novedades_tnl.cantidad_dias_pendientes_amortizar AS campo7',
	    						'nom_novedades_tnl.observaciones AS campo8',
	    						'nom_novedades_tnl.estado AS campo9',
	    						'nom_novedades_tnl.id AS campo10')
					    ->get()
					    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = NovedadTnl::where('nom_novedades_tnl.estado','Activo')
                    ->select('nom_novedades_tnl.id','nom_novedades_tnl.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function get_campos_adicionales_edit( $lista_campos, $registro )
    {
    	//dd( $lista_campos );

        if( $registro->cantidad_dias_amortizados != 0 ) 
        {
         	return [[
         	                        "id" => 999,
         	                        "descripcion" => "",
         	                        "tipo" => "personalizado",
         	                        "name" => "lbl_planilla",
         	                        "opciones" => "",
         	                        "value" => '<div class="form-group">                    
         	                                        <div class="alert alert-danger">
         											  <strong>¡Advertencia!</strong>
         											  <br>
         											  Novedad de TNL no puede ser modifcada: ya tienes registros de tiempo amortizado.
         											</div>
         	                                    </div>',
         	                        "atributos" => [],
         	                        "definicion" => "",
         	                        "requerido" => 0,
         	                        "editable" => 1,
         	                        "unico" => 0
         	                    ]];       
        }

        return $lista_campos;
    }
}
