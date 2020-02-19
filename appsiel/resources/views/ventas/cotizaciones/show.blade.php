<?php  
    $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion;
?>

@extends('transaccion.show')

@section('botones_acciones')
	{{ Form::bsBtnCreate( 'vtas_cotizacion/create'.$variables_url ) }}
	@if($doc_encabezado->estado != 'Anulado')
		{{ Form::bsBtnEdit2(str_replace('id_fila', $id, 'vtas_cotizacion/id_fila/edit'.$variables_url ),'Editar') }}
        <button class="btn btn-danger btn-xs" id="btn_anular"><i class="fa fa-btn fa-close"></i> Anular </button>
    @endif
@endsection

@section('botones_imprimir_email')
	Formato: {{ Form::select('formato_impresion_id',['1'=>'POS','2'=>'Estándar'],null, [ 'id' =>'formato_impresion_id' ]) }}
	{{ Form::bsBtnPrint( 'vtas_cotizacion_imprimir/'.$id.$variables_url.'&formato_impresion_id=1' ) }}
	{{ Form::bsBtnEmail( 'vtas_cotizacion_enviar_por_email/'.$id.$variables_url.'&formato_impresion_id=1' ) }}
@endsection

@section('botones_anterior_siguiente')
	{!! $botones_anterior_siguiente->dibujar( 'vtas_cotizacion/', $variables_url ) !!}
@endsection

@section('datos_adicionales_encabezado')
    <br/>
    <b>Para:</b> {{ $doc_encabezado->tercero_nombre_completo }}
    <br/>
    <b>NIT: &nbsp;&nbsp;</b> {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}
@endsection

@section('filas_adicionales_encabezado')
    <tr>
        <td colspan="2" style="border: solid 1px #ddd;">
            {{ $empresa->descripcion }}, empresa creada con el objetivo de prestar servicios de alta calidad a nuestros clientes, presenta a ustedes la cotización de los siguientes artículos: 
        </td>
    </tr>
@endsection

@section('div_advertencia_anulacion')
	<div class="alert alert-warning" style="display: none;">
		<a href="#" id="close" class="close">&times;</a>
		<strong>Advertencia!</strong>
		<br>
		La anulación no se puede revertir.
		<br>
		Si realmente quiere anular el documento, haga click en el siguiente enlace: <small> <a href="{{ url('vtas_cotizacion_anular/'.$variables_url ) }}"> Anular </a> </small>
	</div>
@endsection