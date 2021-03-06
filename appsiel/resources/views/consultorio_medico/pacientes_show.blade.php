<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@extends('layouts.principal')

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-6">
			&nbsp;&nbsp;&nbsp;
			<div class="btn-group">

				@if($url_crear!='')
					&nbsp;&nbsp;&nbsp;{{ Form::bsBtnCreate($url_crear) }}
				@endif

				@if($url_edit!='')
					{{ Form::bsBtnEdit2(str_replace('id_fila', $registro->id, $url_edit),'Editar') }}
				@endif

				@if( is_null($consultas->first()) )
					{{ Form::formEliminar( 'consultorio_medico/eliminar_paciente', $registro->id ) }}
				@endif
			</div>
		</div>

		<div class="col-md-6">
				<div class="btn-group pull-right">
					@if($reg_anterior!='')
						{{ Form::bsBtnPrev('consultorio_medico/pacientes/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
					@endif

					@if($reg_siguiente!='')
						{{ Form::bsBtnNext('consultorio_medico/pacientes/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
					@endif
				</div>
		</div>
	</div>

	<hr>

	@include('layouts.mensajes')


	<div class="container-fluid">
		<div class="marco_formulario">
			
			<br>

	        @include('consultorio_medico.pacientes_datos_historia_clinica_show')

	        @if( (int)config('consultorio_medico.mostrar_datos_laborales_paciente') )
	        	@include('consultorio_medico.pacientes.datos_laborales')
	        @endif

			<div>
				<ul class="nav nav-tabs">
					<li class="active"><a data-toggle="tab" href="#home">Consultas Médicas</a></li>
				</ul>

				<div class="tab-content">
					<div id="home" class="tab-pane fade in active">
						<br>
						@can('salud_anamnesis_create')
							&nbsp;&nbsp;&nbsp;{{ Form::bsBtnCreate( 'consultorio_medico/consultas/create?id='.Input::get('id').'&id_modelo='.$modelo_consultas->id.'&paciente_id='.$registro->id.'&profesional_salud_id='.Auth::user()->id ) }}
						@endcan
						<br><br>
						<table class="table table-striped table-bordered">
							@foreach($consultas as $consulta)
								<tr>
									<td>
										@include( 'consultorio_medico.consultas.datos_consulta' )

						            	<div class="secciones_consulta">
											<ul class="nav nav-tabs">
												<?php $cont = 1; ?>
												@foreach($secciones_consulta as $seccion)
													@if( $seccion->activo )
														<?php $HREF = "#tab_".$cont."_".$consulta->id; ?>
														@if($cont == 1)
															<li class="active"><a data-toggle="tab" href="{{$HREF}}">{{ $seccion->nombre_seccion }}</a></li>
														@else
															<li><a data-toggle="tab" href="{{$HREF}}">{{ $seccion->nombre_seccion }}</a></li>
														@endif
														<?php $cont++; ?>
										            @endif
										        @endforeach
										    </ul>

										    <div class="tab-content">
										    	<?php $cont = 1; ?>
												@foreach($secciones_consulta as $seccion)
													@if( $seccion->activo )
														<?php $ID = "tab_".$cont."_".$consulta->id; ?>
														@if($cont == 1)
															<div id="{{$ID}}" class="tab-pane fade in active">
											            		@include( $seccion->url_vista_show )
											            	</div>
											            @else
											            	<div id="{{$ID}}" class="tab-pane fade">
											            		@include( $seccion->url_vista_show )
											            	</div>
											            @endif
														<?php $cont++; ?>
										            @endif
										        @endforeach
										    </div>
										</div> <!-- FIN secciones_consulta -->
									</td>
									<td>
										@can('salud_consultas_edit')
											{{ Form::bsBtnEdit( 'consultorio_medico/consultas/'.$consulta->id.'/edit?id='.Input::get('id').'&id_modelo='.$modelo_consultas->id.'&paciente_id='.$id ) }}
											<br><br>
										@endcan
										@can('salud_consultas_print')
											{{ Form::bsBtnPrint( 'consultorio_medico/consultas/'.$consulta->id.'/print?paciente_id='.$id ) }}
											<br><br>
										@endcan
										@can('salud_consultas_delete')
											{{ Form::bsBtnEliminar( 'consultorio_medico/consultas/'.$consulta->id.'/delete?id='.Input::get('id').'&id_modelo='.$modelo_consultas->id.'&paciente_id='.$id.'&modelo_pacientes_id='.Input::get('id_modelo') ) }}
										@endcan
									</td>
								</tr>
							@endforeach
						</table>
							
					</div>
				</div>
			</div>
			<br><br>

			<div id="myModal" class="modal fade" role="dialog">
			  <div class="modal-dialog">

			    <!-- Modal content-->
			    <div class="modal-content">
			      <div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal">&times;</button>
			        <h4 class="modal-title">Exámen</h4>
			      </div>
			      <div class="modal-body">
			      	{{ Form::Spin(64) }}
			      	<div id="info_examen"></div>
			      	<div class="alert alert-success alert-dismissible fade in" style="display: none;" id="alert_mensaje">
					    <strong>Registro actualizado correctamente!</strong>
					  </div>
			      </div>
			      <div class="modal-footer">
			      	<button class="btn btn-danger btn-xs" data-dismiss="modal"> <i class="fa fa-close"></i> Cerrar </button>
			        @can('salud_consultas_edit') <!-- -->
						<button class="btn btn-warning btn-xs btn_edit_examen" data-paciente_id="0" data-consulta_id="0" data-examen_id="0"> <i class="fa fa-edit"></i> Editar </button>
						<button class="btn btn-primary btn-xs btn_save_examen" data-paciente_id="0" data-consulta_id="0" data-examen_id="0" style="display: none;"> <i class="fa fa-save"></i> Guardar </button>
						<br><br>
					@endcan <!-- -->
			      </div>
			    </div>

			  </div>
			</div>    
		</div> <!-- Marco -->
	</div>
	<br/><br/>

@endsection


@section('scripts')
	<script>
		$(document).ready(function(){

			$(".btn_eliminar_datos_modelo").click(function(event){
				event.preventDefault();
				var r = confirm("¿Desea eliminar todos los datos de " + $(this).attr('data-descripcion_modelo') + " ingresados?");
				if (r == true) {
				  $(this).parents('.form_eliminar').submit();
				}
			});
			
			$(".btn_ver_examen").click(function(event){
				event.preventDefault();

				$("#alert_mensaje").hide();

				$("#info_examen").html( '' );
				$('#div_spin').fadeIn();

		        $("#myModal").modal(
		        	{keyboard: 'true'}
		        );

		        var url = '../../consultorio_medico/resultado_examen_medico/' + $(this).attr('data-consulta_id') + '-' + $(this).attr('data-paciente_id') + '-' + $(this).attr('data-examen_id') + '?id='+getParameterByName('id') + '&id_modelo='+getParameterByName('id_modelo');

		        $(".btn_edit_examen").attr('data-consulta_id' , $(this).attr('data-consulta_id') );
		        $(".btn_edit_examen").attr('data-paciente_id', $(this).attr('data-paciente_id') );
		        $(".btn_edit_examen").attr('data-examen_id', $(this).attr('data-examen_id') );

		        //console.log( $(this).html() );

		        $(".modal-title").html( $(this).html() );

		        $.get( url, function( respuesta ){
		        	$('#div_spin').hide();
		        	$("#info_examen").html( respuesta );
		        });/**/
		    });
			
			$(".btn_edit_examen").click(function(event){
				event.preventDefault();

				$("#alert_mensaje").hide();

				$('.modal-body .campo_variable').each(function()
				{

				    var cadena = $.trim( $(this).text() );

				    //console.log( cadena );

				    $(this).html( $('<input/>').attr({ type: 'text', value: cadena, name: 'campo_variable_organo-' + $(this).attr('id'), class: 'form-control', size: '5' }) );

				});

				$(this).hide();
				$(".btn_save_examen").show();
		    });
			
			$(".btn_save_examen").click(function(event){
				event.preventDefault();

				$('#div_spin').show();

				var form = $('.modal-body #form_resultados_examenes');
				var url = form.attr('action');
				data = form.serialize();
				$.post(url,data,function(result){

					//alert( result );

					$('.modal-body .campo_variable').each(function()
					{
					    var cadena = $(this).children('input').val();
					    $(this).html( cadena );
					});

					$('#div_spin').hide();
					
					$(".btn_save_examen").hide();
					$(".btn_edit_examen").show();

					$("#alert_mensaje").show();
				});					
		    });

		    $("#myModal").on('hidden.bs.modal', function(){
			    $(".btn_save_examen").hide();
			    $(".btn_edit_examen").show();
			  });


	
			function getParameterByName(name) {
			    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
			    results = regex.exec(location.search);
			    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
			}

		});
	</script>
@endsection