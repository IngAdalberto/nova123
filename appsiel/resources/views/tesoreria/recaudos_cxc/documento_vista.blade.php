<div>
    <table class="table table-bordered">
        <tr>
            <td width="50%" style="border: solid 1px #ddd; margin-top: -40px;">
                    @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'show' ] )
            </td>
            <td style="border: solid 1px #ddd; padding-top: -20px;">
                <div style="vertical-align: center;">
                    <b style="font-size: 1.6em; text-align: center; display: block;">{{ $doc_encabezado->documento_transaccion_descripcion }}</b>
                    <br/>
                    <b>Documento:</b> {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
                    <br/>
                    <b>Fecha:</b> {{ $doc_encabezado->fecha }}
                </div>
                @if($doc_encabezado->estado == 'Anulado')
                    <div class="alert alert-danger" class="center">
                        <strong>Documento Anulado</strong>
                    </div>
                @endif
            </td>
        </tr>
        <tr>
            <td style="border: solid 1px #ddd;">docimpri
                <b>Tercero:</b> {{ $doc_encabezado->tercero_nombre_completo }}
                <br/>
                <b>Documento ID: &nbsp;&nbsp;</b> {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}
                <br/>
                <b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado->direccion1 }}
                <br/>
                <b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado->telefono1 }}
            </td>
            <td style="border: solid 1px #ddd;">
            </td>
        </tr>
        <tr>        
            <td colspan="2" style="border: solid 1px #ddd;">
                <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
            </td>
        </tr>
    </table>

    <h4 style="text-align: center;"> Documentos abonados </h4>
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Tercero','Documento','Fecha','Detalle','Abono']) }}
        <tbody>
            <?php 
            
            $total_abono = 0;

            ?>
            @foreach($doc_pagados as $linea )

                <?php 
            
                    $el_documento = app( App\Sistema\TipoTransaccion::find( $linea->doc_cxc_transacc_id )->modelo_encabezados_documentos )->where('core_tipo_transaccion_id',$linea->doc_cxc_transacc_id)
                    ->where('core_tipo_doc_app_id',$linea->doc_cxc_tipo_doc_id)
                    ->where('consecutivo',$linea->doc_cxc_consecutivo)
                    ->get()->first();

                ?>

                <tr>
                    <td> {{ $linea->tercero_nombre_completo }} </td>
                    <td> 
                        <a href="{{ url('ventas/'.$el_documento->id.'?id=13&id_modelo=139&id_transaccion=23') }}" target="_blank"> {{ $linea->documento_prefijo_consecutivo }}</a>  
                    </td>
                    <td> {{ $el_documento->fecha }} </td>
                    <td> {{ $el_documento->descripcion }} </td>
                    <td> ${{ number_format( $linea->abono, 0, ',', '.') }} </td>
                </tr>
                <?php 
                    $total_abono += $linea->abono;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">&nbsp;</td>
                <td> ${{ number_format($total_abono, 0, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>

    <h4 style="text-align: center;">Registros contables</h4>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Código</th>
                <th>Cuenta</th>
                <th>Débito</th>
                <th>Crédito</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_valor_debito = 0;
                $total_valor_credito = 0;
            @endphp
            @foreach( $registros_contabilidad as $fila )
                <tr>
                    <td> {{ $fila['cuenta_codigo'] }}</td>
                    <td> {{ $fila['cuenta_descripcion'] }}</td>
                    <td> {{ number_format(  $fila['valor_debito'], 0, ',', '.') }}</td>
                    <td> {{ number_format(  $fila['valor_credito'] * -1, 0, ',', '.') }}</td>
                </tr>
                @php
                    $total_valor_debito += $fila['valor_debito'];
                    $total_valor_credito += $fila['valor_credito'] * -1;
                @endphp
            @endforeach
        </tbody>
        <tfoot>            
                <tr>
                    <td colspan="2"> &nbsp; </td>
                    <td> {{ number_format( $total_valor_debito, 0, ',', '.') }}</td>
                    <td> {{ number_format( $total_valor_credito, 0, ',', '.') }}</td>
                </tr>
        </tfoot>
    </table>
</div>