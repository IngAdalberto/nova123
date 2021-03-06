<?php  
    $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion;

    $medio_recaudo = $encabezado_documento->medio_recaudo;

    switch ( $medio_recaudo->comportamiento )
    {
        case 'Efectivo':
            $caja = $encabezado_documento->caja;
            $cuenta_bancaria = null;
            break;

        case 'Tarjeta bancaria':
            $cuenta_bancaria = $encabezado_documento->cuenta_bancaria;
            $caja = null;
            break;
        
        default:
            $caja = null;
            $cuenta_bancaria = null;
            break;
    }
?>

@extends('transaccion.show')

@section('botones_acciones')
	{{ Form::bsBtnCreate( 'web/create'.$variables_url ) }}
	@if($doc_encabezado->estado != 'Anulado')

        {{ Form::bsBtnEdit2( 'web/'.$id.'/edit'.$variables_url,'Editar') }}

        <button class="btn btn-danger btn-xs" id="btn_anular"><i class="fa fa-close"></i> Anular </button>
    @endif
@endsection

@section('botones_imprimir_email')
	Formato: {{ Form::select('formato_impresion_id',['estandar'=>'Estándar','pos'=>'POS'],null, [ 'id' =>'formato_impresion_id' ]) }}
	{{ Form::bsBtnPrint( 'teso_recibo_caja_imprimir/'.$id.$variables_url.'&formato_impresion_id=estandar' ) }}
@endsection

@section('botones_anterior_siguiente')
    {!! $botones_anterior_siguiente->dibujar( 'teso_recibo_caja_show/', $variables_url ) !!}
@endsection


@section('documento_vista')
    {!! $documento_vista2 !!}
@endsection

@section('datos_adicionales_encabezado')
    <br>
    <b>Valor: &nbsp;&nbsp;</b> ${{ number_format( $encabezado_documento->valor_total,0,',','.') }}
@endsection



@section('filas_adicionales_encabezado')
    
@endsection

@section('div_advertencia_anulacion')
	<div class="alert alert-warning" style="display: none;">
		<a href="#" id="close" class="close">&times;</a>
		<strong>¡ADVERTENCIA!</strong>
		Al anular el documento se eliminan los registros del movimiento de tesorería. La anulación no puede revertirse. Si quieres confirmar, hacer click en: <a class="btn btn-danger btn-sm" href="{{ url('teso_recibo_caja_anular/'.$id.$variables_url ) }}"><i class="fa fa-arrow-right" aria-hidden="true"></i> Anular </a>
	</div>
@endsection