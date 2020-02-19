<div>
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Cód.','Producto','Precio','IVA','Cantidad','Total','Acción']) }}
        </thead>
        <tbody>
            <?php 
            
            $total_cantidad = 0;
            $subtotal = 0;
            $total_impuestos = 0;
            $total_factura = 0;
            ?>
            @foreach($doc_registros as $linea )
                <tr>
                    <td> {{ $linea->producto_id }} </td>
                    <td> {{ $linea->producto_descripcion }} </td>
                    <td> {{ '$ '.number_format( $linea->precio_unitario, 2, ',', '.') }} </td>
                    <td> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
                    <td> {{ number_format( $linea->cantidad, 2, ',', '.') }} {{ $linea->unidad_medida1 }} </td>
                    <td> {{ '$ '.number_format( $linea->precio_total, 2, ',', '.') }} </td>
                    <td>
                        @if($doc_encabezado->estado != 'Anulado' && !$docs_relacionados[1] )
                            <button class="btn btn-warning btn-xs btn-detail btn_editar_registro" type="button" title="Modificar" data-linea_registro_id="{{$linea->id}}"><i class="fa fa-btn fa-edit"></i>&nbsp; </button>

                            @include('components.design.ventana_modal',['titulo'=>'Editar registro','texto_mensaje'=>''])
                        @endif
                    </td>
                </tr>
                <?php 
                    $total_cantidad += $linea->cantidad;
                    $subtotal += (float)$linea->base_impuesto;
                    $total_impuestos += (float)$linea->valor_impuesto;
                    $total_factura += $linea->precio_total;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">&nbsp;</td>
                <td> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        </tfoot>
    </table>

    <table class="table table-bordered">
        <tr>
            <td> <span style="text-align: right; font-weight: bold;"> Subtotal: </span> $ {{ number_format($subtotal, 0, ',', '.') }}</td>
            <td> <span style="text-align: right; font-weight: bold;"> Impuestos: </span> $ {{ number_format($total_impuestos, 0, ',', '.') }}</td>
            <td> <span style="text-align: right; font-weight: bold;"> Total factura: </span> $ {{ number_format($total_factura, 0, ',', '.') }}</td>
        </tr>
    </table>
</div>