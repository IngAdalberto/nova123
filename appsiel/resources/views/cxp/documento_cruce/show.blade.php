<?php  
    $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion;
?>

@extends('transaccion.show')

@section('botones_acciones')
	{{ Form::bsBtnCreate( 'doc_cruce_cxp/create'.$variables_url ) }}
	@if($doc_encabezado->estado != 'Anulado')
        <button class="btn-gmail" id="btn_anular" title="Anular"><i class="fa fa-close"></i></button>
    @endif
@endsection

@section('botones_imprimir_email')
	Formato: {{ Form::select('formato_impresion_id',['estandar'=>'Estándar','pos'=>'POS'],null, [ 'id' =>'formato_impresion_id' ]) }}
	{{ Form::bsBtnPrint( 'doc_cruce_cxp_imprimir/'.$id.$variables_url.'&formato_impresion_id=estandar' ) }}
@endsection

@section('botones_anterior_siguiente')
	{!! $botones_anterior_siguiente->dibujar( 'doc_cruce_cxp/', $variables_url ) !!}
@endsection


@section('filas_adicionales_encabezado')
    <tr>        
        <td colspan="2" style="border: solid 1px #ddd;">
            <b>Cliente:</b> {{ $doc_encabezado->tercero->descripcion }} 
            &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
            <b>{{ config("configuracion.tipo_identificador") }}: &nbsp;&nbsp;</b>
			@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif
            &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
            <b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado->tercero->direccion1 }}, {{ $doc_encabezado->tercero->ciudad->descripcion }} - {{ $doc_encabezado->tercero->ciudad->departamento->descripcion }}
            &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
            <b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado->telefono1 }}
            <br/>
            <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
        </td>
    </tr>
@endsection

@section('div_advertencia_anulacion')
    
    <div class="alert alert-warning" style="display: none;">
        <a href="#" id="close" class="close">&times;</a>
        <strong>Advertencia!</strong>
        <br>
        Al anular el documento se eliminan los registros de pagos a las Cuentas por Pagar y el movimiento contable relacionado.
        <br>
        Si realmente quiere anular el documento, haga click en el siguiente enlace: <small> <a href="{{ url('doc_cruce_cxp_anular/'.$id.$variables_url ) }}"> Anular </a> </small>
    </div>

@endsection

@section('documento_vista')

@endsection

@section('registros_otros_documentos')

@endsection