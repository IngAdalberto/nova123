@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	&nbsp;&nbsp;&nbsp;
	<div class="btn-group">
		<a class="btn btn-primary btn-xs btn-detail" href="{{ url( 'web/create?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}" title="Crear"><i class="fa fa-btn fa-plus"></i>&nbsp;</a>
		{{ Form::bsBtnEdit( 'matriculas/inscripcion/'.$id.'/edit?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
		{{ Form::bsBtnPrint( 'matriculas/inscripcion_print/'.$id ) }}
	</div>

	<div class="pull-right">
		@if($reg_anterior!='')
			{{ Form::bsBtnPrev( 'matriculas/inscripcion/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
		@endif

		@if($reg_siguiente!='')
			{{ Form::bsBtnNext( 'matriculas/inscripcion/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
		@endif
	</div>
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">

			<?php
				echo $view_pdf;
			?>
			
		</div>
	</div>
	<br/><br/>	

@endsection