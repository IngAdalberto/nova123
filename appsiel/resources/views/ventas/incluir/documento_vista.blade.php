<div class="table-responsive">
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Cód.','Producto','U.M.','Cantidad','Precio','Total bruto','Sub-total <br> (Sin IVA)','% Dcto.','Total Dcto.','IVA','Total IVA','Total','Acción']) }}
        <tbody>
            <?php 
            
            $total_cantidad = 0;
            $total_bruto = 0;
            $subtotal = 0; // Sin impuestos
            $total_impuestos = 0;
            $total_factura = 0;
            $total_descuentos = 0;
            $cantidad_items = 0;
            ?>
            @foreach($doc_registros as $linea )

                <?php 

                    $unidad_medida = $linea->unidad_medida1;
                    if( $linea->producto->unidad_medida2 != '' )
                    {
                        $unidad_medida = $linea->producto->unidad_medida1 . ' - Talla: ' . $linea->producto->unidad_medida2;
                    }

                ?>
                <tr>
                    <td> {{ $linea->producto_id }} </td>
                    <td> {{ $linea->producto_descripcion }} </td>
                    <td> {{ $unidad_medida }} </td>
                    <td style="text-align: right;"> {{ number_format( $linea->cantidad, 2, ',', '.') }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea->precio_unitario, 0, ',', '.') }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea->cantidad * $linea->precio_unitario, 0, ',', '.') }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea->cantidad * ($linea->precio_unitario - $linea->valor_impuesto), 0, ',', '.') }} </td>
                    <td style="text-align: right;"> {{ number_format( $linea->tasa_descuento, 2, ',', '.') }}% </td>
                    <td style="text-align: right;"> ${{ number_format( $linea->valor_total_descuento, 0, ',', '.') }} </td>
                    <td style="text-align: right;"> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea->cantidad * $linea->valor_impuesto, 0, ',', '.') }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea->precio_total, 0, ',', '.') }} </td>
                    <td>
                        @if($doc_encabezado->estado != 'Anulado' && !$docs_relacionados[1]  && Input::get('id_transaccion') == 23 )
                            <button class="btn btn-warning btn-xs btn-detail btn_editar_registro" type="button" title="Modificar" data-linea_registro_id="{{$linea->id}}"><i class="fa fa-btn fa-edit"></i>&nbsp; </button>

                            @include('components.design.ventana_modal',['titulo'=>'Editar registro','texto_mensaje'=>''])
                        @endif
                    </td>
                </tr>
                <?php
                    $total_cantidad += $linea->cantidad;
                    $total_bruto += (float)$linea->precio_unitario * (float)$linea->cantidad;
                    $subtotal += (float)($linea->precio_unitario - $linea->valor_impuesto) * (float)$linea->cantidad;
                    $total_impuestos += (float)$linea->valor_impuesto * (float)$linea->cantidad;
                    $total_factura += $linea->precio_total;
                    $total_descuentos += $linea->valor_total_descuento;
                    $cantidad_items++;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold;">
                <td colspan="3"> Cantidad de items: {{ $cantidad_items }} </td>
                <td style="text-align: right;"> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
                <td >&nbsp;</td>
                <td style="text-align: right;"> {{ number_format($total_bruto, 0, ',', '.') }} </td>
                <td style="text-align: right;"> {{ number_format($subtotal, 0, ',', '.') }} </td>
                <td>&nbsp;</td>
                <td style="text-align: right;"> ${{ number_format($total_descuentos, 0, ',', '.') }} </td>
                <td>&nbsp;</td>
                <td style="text-align: right;"> ${{ number_format($total_impuestos, 0, ',', '.') }} </td>
                <td style="text-align: right;"> ${{ number_format($total_factura, 0, ',', '.') }} </td>
                <td>&nbsp;</td>
            </tr>
        </tfoot>
    </table>

</div>

@include('ventas.incluir.factura_firma_totales')