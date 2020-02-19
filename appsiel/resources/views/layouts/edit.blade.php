@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	@include('layouts.form_edit',compact('form_create'))

@endsection

@section('scripts')
	
	<script type="text/javascript">
		$(document).ready(function(){
			$('#bs_boton_guardar').on('click',function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;
				}
				
				// Desactivar el click del botón
				$( this ).off( event );

				$(this).parents('form:first').submit();
			});/**/
		});
	</script>
	
	@if( isset($archivo_js) )
		<script src="{{ asset( $archivo_js ) }}"></script>
	@endif
@endsection