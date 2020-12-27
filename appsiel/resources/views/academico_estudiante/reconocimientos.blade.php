@extends('layouts.principal')

@section('estilos_1')

	<style type="text/css">
        .img-responsive:hover {
	        transform: scale(1.1);
	        cursor: pointer;
	    }

	</style>

@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')


	<div class="container-fluid">
		<div class="marco_formulario">

			<h2 style="width: 100%; text-align: center;"><i class="fa fa-certificate"></i> Mis Reconocimientos <i class="fa fa-certificate"></i></h2>
			<hr>

			<?php
				$cant_cols = 4;
				$ancho_iconos = '80px';
				$i = $cant_cols;
			?>
			@foreach( $reconocimientos as $reconocimiento )
				
				@if($i % $cant_cols == 0)
					<div class="row">
				@endif				

				<div class="col-sm-{{12/$cant_cols}} col-xs-{{12/$cant_cols}}" style="padding: 5px; text-align: center;">

					<a href="{{ config('configuracion.url_instancia_cliente').'/storage/app/academico_estudiante/reconocimientos/' . $reconocimiento->archivo_adjunto }}" target="_blank">
						<img class="img-responsive" src="{{asset('assets/img/academico_estudiante/reconocimiento.png')}}" width="{{$ancho_iconos}}" title="{{ $reconocimiento->descripcion }}" style="display: inline;" />
					</a>
					
					<p>
						{{ $reconocimiento->descripcion }}
					</p>

					@if( $reconocimiento->resumen != '' )
						<p>
							{!! $reconocimiento->resumen !!}
						</p>
					@endif
				</div>
				
				<?php
					$i++;
				?>

				@if($i % $cant_cols == 0)
					</div>
					<br/>
				@endif

			@endforeach
		</div>
	</div>
	<br/><br/>

@endsection

@section('scripts')

@endsection