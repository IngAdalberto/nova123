@extends('layouts.principal')
@section('content')

    {{ Form::bsMigaPan($miga_pan) }}

    <div class="row">
        <div class="col-md-4">
            <div class="btn-group">
                @if( isset($url_crear) )
                    @if($url_crear!='')
                        {{ Form::bsBtnCreate($url_crear) }}
                    @endif
                @endif

                @if( isset($url_edit) )
                    @if($url_edit!='')
                        {{ Form::bsBtnEdit2(str_replace('id_fila', $registro->id, $url_edit),'Editar') }}
                    @endif
                @endif
                @if(isset($botones))
                    @php
                        $i=0;
                    @endphp
                    @foreach($botones as $boton)
                        {!! str_replace( 'id_fila', $registro->id, $boton->dibujar() ) !!}
                        @php
                            $i++;
                        @endphp
                    @endforeach
                @endif
            </div>
        </div>
        <div class="col-md-4 text-center">
            <div class="btn-group">
                Imprimir
                {{ Form::bsBtnPrint( 'tesoreria/imprimir/'.$registro->id ) }}
                {{--                Imprimir <a class="btn btn-info btn-xs btn-detail" href="http://localhost/Appsiel/todo_appsiel/tesoreria_pagos_cxp_imprimir/1?id=3&amp;id_modelo=150&amp;id_transaccion=33&amp;formato_impresion_id=estandar" title="Imprimir" id="btn_print" target="_blank" style="transform: rotate(0deg); border-spacing: 0px;"><i class="fa fa-btn fa-print"></i>&nbsp;</a>--}}
            </div>
        </div>
        <div class="col-md-4">
            <div class="btn-group pull-right">
                @if($reg_anterior!='')
                    {{ Form::bsBtnPrev('tesoreria/arqueo_caja/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
                @endif

                @if($reg_siguiente!='')
                    {{ Form::bsBtnNext('tesoreria/arqueo_caja/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
                @endif
            </div>
        </div>
    </div>
    <hr>

    @include('layouts.mensajes')
    <div class="col-md-12">
        <div class="container-fluid">
            <div class="marco_formulario">
                <div class="container">
                    <div class="table-responsive">
                        <table class="table table-bordered" style="margin-top: 20px;">
                            <tr>
                                <td width="50%" style="border: solid 1px #ddd; margin-top: -40px;">
                                    @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'show' ] )
                                </td>
                                <td style="border: solid 1px #ddd; padding-top: -20px;">
                                    <div style="vertical-align: center;">
                                        <b style="font-size: 1.6em; text-align: center; display: block;">{{ $doc_encabezado['titulo'] }}</b>
                                        <br/>
                                        <b>Documento:</b> {{ $doc_encabezado['documento'] }}
                                        <br/>
                                        <b>Fecha:</b> {{ $doc_encabezado['fecha'] }}

                                        @yield('datos_adicionales_encabezado')

                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="border: solid 1px #ddd;">
                                    <b>Responsable:</b> {{$user->name}}
                                    <br/>
                                    <b>Fecha y Hora de Realizaci??n: &nbsp;&nbsp;</b> {{$registro->created_at}}
                                    <br/>
                                </td>
                                <td style="border: solid 1px #ddd;">
                                    <b>Observaciones:</b> {{$registro->observaciones}}
                                    <br/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="border: solid 1px #ddd;">
                                    <b>Base: &nbsp;&nbsp;</b> ${{number_format($registro->base,'0',',','.')}}
                                </td>
                            </tr>
                        </table>
                    </div>
                    <hr>
                    <h1 class="card-inside-title">Datos de la fecha {{$registro->fecha}}</h1>
                    <div class="row clearfix">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <tbody>
                                    <tr>
                                        <td colspan="3">
                                            <center><strong>ACTA DE ARQUEO DE CAJA</strong></center>
                                        </td>
                                    </tr>
                                    <tr class="read">
                                        <td class="contact"><b>EFECTIVO</b></td>
                                        <td class="subject"><b>UNIDADES</b></td>
                                        <td class="subject"><b>VALOR</b></td>
                                    </tr>
                                    @foreach($registro->billetes_contados as $key => $value)
                                        <tr class="read">
                                            <td class="contact"><b>Billetes de ${{number_format($key,'0',',','.')}}</b></td>
                                            <td class="subject">{{$value == ""?0:$value}}</td>
                                            <td class="subject">
                                                ${{number_format($value == ""?0:$key*$value,'0',',','.')}}</td>
                                        </tr>
                                    @endforeach
                                    @foreach($registro->monedas_contadas as $key => $value)
                                        <tr class="read">
                                            <td class="contact"><b>Monedas de ${{number_format($key,'0',',','.')}}</b></td>
                                            <td class="subject">{{$value == ""?0:$value}}</td>
                                            <td class="subject">
                                                ${{number_format($value == ""?0:$key*$value,'0',',','.')}}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="read">
                                        <td class="contact"><b>Otros Saldos (bonos,pagar??s,etc.)</b></td>
                                        <td class="subject">{{$registro->detalle_otros_saldos}}</td>
                                        <td class="subject">${{number_format($registro->otros_saldos,'0',',','.')}}</td>
                                    </tr>
                                    <tr class="read">
                                        <td class="contact"><b>Total Billetes</b></td>
                                        <td class="subject"></td>
                                        <td class="subject">${{number_format($registro->total_billetes,'0',',','.')}}</td>
                                    </tr>
                                    <tr class="read">
                                        <td class="contact"><b>Total Monedas</b></td>
                                        <td class="subject"></td>
                                        <td class="subject">${{number_format($registro->total_monedas,'0',',','.')}}</td>
                                    </tr>
                                    <tr class="read">
                                        <td class="contact"><b>Total Efectivo</b></td>
                                        <td class="subject"></td>
                                        <td class="subject">
                                            ${{number_format($registro->lbl_total_efectivo,'0',',','.')}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <center><strong>MOVIMIENTOS DEL SISTEMA</strong></center>
                                        </td>
                                    </tr>
                                    <tr class="read">
                                        <td class="contact"><b>MOTIVO</b></td>
                                        <td class="subject"><b>MOVIMIENTO</b></td>
                                        <td class="subject"><b>VALOR</b></td>
                                    </tr>
                                    @foreach($registro->detalles_mov_entradas as $item)
                                        <tr class="read">
                                        <td class="contact"><b>{{$item->motivo}}</b></td>
                                        <td class="subject">{{strtoupper($item->movimiento)}}</td>
                                        <td class="subject">${{number_format($item->valor_movimiento,'0',',','.')}}</td>
                                        </tr>
                                    @endforeach
                                    @foreach($registro->detalles_mov_salidas as $item)
                                        <tr class="read">
                                        <td class="contact"><b>{{$item->motivo}}</b></td>
                                        <td class="subject">{{strtoupper($item->movimiento)}}</td>
                                        <td class="subject">${{number_format($item->valor_movimiento,'0',',','.')}}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="read">
                                        <td class="contact"><b>Total Entrada de Caja</b></td>
                                        <td class="subject"></td>
                                        <td class="subject">
                                            ${{number_format($registro->total_mov_entradas,'0',',','.')}}</td>
                                    </tr>
                                    <tr class="read">
                                        <td class="contact"><b>Total Salida de Caja</b></td>
                                        <td class="subject"></td>
                                        <td class="subject">
                                            ${{number_format($registro->total_mov_salidas,'0',',','.')}}</td>
                                    </tr>
                                    <tr class="read">
                                        <td class="contact"><b>Total Saldo en el Sistema</b></td>
                                        <td class="subject"></td>
                                        <td class="subject">
                                            ${{number_format($registro->lbl_total_sistema,'0',',','.')}}</td>
                                    </tr>
                                    <tr class="read">
                                        <td class="contact"><b>Diferencia</b></td>
                                        <td class="subject"></td>
                                        <td class="subject">
                                            ${{number_format($registro->total_saldo,'0',',','.')}}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
@endsection

@section('scripts2')
    <script type="text/javascript">

        $(document).ready(function () {

            var app_id = getParameterByName( 'id' );

            if( app_id == 20 )
            {
                $('.breadcrumb > li').eq(1).find('a').attr('href','#');

                $('#btn_create_general').hide();
                $('.btn.btn-warning.btn-xs').hide();
                $('.btn-group.pull-right').hide();                
            }

        });

    </script>
@endsection