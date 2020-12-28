@extends('core.procesos.layout')

@section( 'titulo', 'Liquidar prestaciones sociales' )

@section('detalles')
	<p>
		La liquidacónes de la Prima de servicios, las Vacaciones, las Cesantías e Intereses tienen una liquidación especial con base en acumulados y parametrizaciones específicas.
	</p>
	
	Por esta opción puede liquidar todos estos conceptos.
	
	<br>
@endsection

@section('formulario')
	<div class="row" id="div_formulario">

		<div class="row">
			<div class="col-md-6">

				<div class="marco_formulario">
					<div class="container-fluid">
						<h4>
							Parámetros de selección
						</h4>
						<hr>
						{{ Form::open(['url'=>'nom_procesar_archivo_plano','id'=>'formulario_inicial','files' => true]) }}
							<div class="row" style="padding:5px;">
								<label class="control-label col-sm-4" > <b> *Documento de liquidación: </b> </label>

								<div class="col-sm-8">
									{{ Form::select( 'nom_doc_encabezado_id', App\Nomina\NomDocEncabezado::opciones_campo_select(),null, [ 'class' => 'form-control', 'id' => 'nom_doc_encabezado_id', 'required' => 'required' ]) }}
								</div>					 
							</div>

							<div class="row" style="padding:5px;">
								<h5>Prestaciones a liquidar</h5>
								<hr>
								<label class="checkbox-inline"><input type="checkbox" value="">Vacaciones</label>
								<label class="checkbox-inline"><input type="checkbox" value="">Prima de servicios</label>
								<label class="checkbox-inline"><input type="checkbox" value="">Cesantías</label>
								<label class="checkbox-inline"><input type="checkbox" value="">Intereses de cesantías</label>
							</div>

							<div class="col-md-4">
								<button class="btn btn-success" id="btn_cargar"> <i class="fa fa-calculator"></i> Liquidar </button>
							</div>
						{{ Form::close() }}
					</div>
						
				</div>
					
			</div>
			<div class="col-md-6">
				<h4>
					Empleados del documento
				</h4>
				<hr>
				<div class="div_lista_empleados_del_documento">
					
				</div>
			</div>
		</div>
				
	</div>

	<div class="row" id="div_resultado">
			
	</div>

@endsection

@section('javascripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$("#btn_cargar").on('click',function(event){
		    	event.preventDefault();

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		 		$("#div_spin").show();
		 		$("#div_cargando").show();
				
				var form = $('#formulario_inicial');
				var url = form.attr('action');
				var datos = new FormData(document.getElementById("formulario_inicial"));

				$.ajax({
				    url: url,
				    type: "post",
				    dataType: "html",
				    data: datos,
				    cache: false,
				    contentType: false,
				    processData: false
				})
			    .done(function( respuesta ){
			        $('#div_cargando').hide();
        			$("#div_spin").hide();

        			$("#div_resultado").html( respuesta );
        			$("#div_resultado").fadeIn( 1000 );
			    });
		    });

			$(document).on('click', '.btn_eliminar', function(event) {
				event.preventDefault();
				var fila = $(this).closest("tr");
				if ( confirm('¿Esta seguro de eliminar esta fila de los registros a almacenar?') )
				{
					fila.remove();
				}
			});

			$(document).on('click', '#btn_almacenar_registros', function(event) {
				event.preventDefault();

				var table = $( '#ingreso_registros' ).tableToJSON();
				$('#lineas_registros').val(JSON.stringify(table));

				$('#form_almacenar_registros').submit();
				
				/*
				$("#div_resultado").fadeOut( 1000 );

				$("#div_spin").show();
		 		$("#div_cargando").show();
				
				var form = $('#form_almacenar_registros');
				var url = form.attr('action');
				var datos = new FormData(document.getElementById("form_almacenar_registros"));

				$("#div_resultado").html( '' );

				$.ajax({
				    url: url,
				    type: "post",
				    dataType: "html",
				    data: datos,
				    cache: false,
				    contentType: false,
				    processData: false
				})
			    .done(function( respuesta ){
			        $('#div_cargando').hide();
        			$("#div_spin").hide();

        			$("#div_resultado").html( respuesta );
        			$("#div_resultado").fadeIn( 1000 );
			    });
			    */
			});

		});
	</script>
@endsection