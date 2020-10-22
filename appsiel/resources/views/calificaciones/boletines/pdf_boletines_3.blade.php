<style>

	img {
		padding-left:30px;
	}

	table {
		width:100%;
		border-collapse: collapse;
	}

	table.contenido td {
		border: 1px solid;
	}

	th {
		background-color: #E0E0E0;
		border: 1px solid;
	}

	ul{
		padding:0px;
		margin:0px;
	}

	li{
		list-style-type: none;
	}


	.lbl_asignatura_descripcion
	{
		 width: 65%;
		 float: left;
		 /*
		 display: inline-block;
		 height: { {$tam_letra-3}}px;*/
	}

	.lbl_calificacion
	{
		 width: 35%;
		 float: left;
		 /*
		 display: inline-block;
		 height: { {$tam_letra-3}}px;*/
	}

	.page-break {
		page-break-after: always;
	}
</style>

	<?php
	    $colegio = App\Core\Colegio::where('empresa_id','=', Auth::user()->empresa_id )
	                    ->get()[0];

	    $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/'.$colegio->imagen;


	?>

	@foreach($estudiantes as $estudiante)
	
	    @include('calificaciones.boletines.banner_2')

		<?php 

			$observacion = App\Calificaciones\ObservacionesBoletin::get_x_estudiante( $periodo->id, $curso->id, $estudiante->id);

			$nombre_completo = $estudiante->nombre_completo;

			$area_anterior = '';
		?>
		
		@include('calificaciones.boletines.encabezado_2')
				
		<table class="contenido">
			<tbody>
				@foreach($asignaturas as $asignatura)
					<?php
						// Se llama a la calificacion de cada asignatura
						$calificacion = App\Calificaciones\Calificacion::get_la_calificacion($periodo->id, $curso->id, $estudiante->id_estudiante, $asignatura->id);
						$cant_columnas = 1;	
					?>

					@include('calificaciones.boletines.fila_area')

					<tr style="font-size: {{$tam_letra}}mm; background-color: #E8E8E8;">
						<td> 
							<table width="100%" style="border: 0px;">
								<tr>
									<td style="border: 0px;">
										{{ $asignatura->descripcion }}
									</td>
									<td style="border: 0px;" width="35%">
										@if($asignatura->intensidad_horaria != 0)
											<b>IH: </b>{{ $asignatura->intensidad_horaria }} &nbsp;
										@endif
										
										@if( $calificacion->valor > 0)
											<b>Cal: </b> @include('calificaciones.boletines.lbl_descripcion_calificacion')
										@endif
									</td>
								</tr>
							</table>					
						</td>
					</tr>

					<tr style="font-size: {{$tam_letra}}mm;">
						<td>

							@include('calificaciones.boletines.proposito')
                            
                            <b>Logro: </b>
							@include('calificaciones.boletines.lista_logros')

							<?php
								if ( $mostrar_nombre_docentes == 'Si') 
								{
									
									$usuario = App\AcademicoDocente\AsignacionProfesor::get_user_segun_curso_asignatura( $curso->id, $asignatura->id, $periodo->periodo_lectivo_id);

									//dd($usuario->name);
									
									$nombre = '';

									if ( !is_null($usuario) ) 
									{
										$nombre = $usuario->name;
									}

							?>
								<span style="display: inline-block;text-align: right;">
									<b>docente: </b> {{ ucwords( ($nombre) ) }} <!-- strtolower -->
								</span>
							<?php } ?>
						</td>
					</tr>

					<?php 
						$area_anterior = $asignatura->area;
					?>

				@endforeach {{--  Asignaturas --}}

				<tr style="font-size: {{$tam_letra}}mm;">
					<td>
						<b> Observaciones: </b>
						<br/>&nbsp;&nbsp;
						@if( !is_null($observacion) )
							{{ $observacion->observacion }}
						@endif
					</td>
				</tr>

				@if( $mostrar_etiqueta_final != 'No' )
					<tr style="font-size: {{$tam_letra}}mm;">
						<td>
							@include('calificaciones.boletines.mostrar_etiqueta_final')
						</td>
					</tr>
				@endif
				
			</tbody>
		</table>


		@if( $mostrar_usuarios_estudiantes == 'Si') 
			@include('calificaciones.boletines.mostrar_usuarios_estudiantes')
		@endif


		@include('calificaciones.boletines.seccion_firmas')

		<div class="page-break"></div>
	@endforeach {{-- Estudiante --}}