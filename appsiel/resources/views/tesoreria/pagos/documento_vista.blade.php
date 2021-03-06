<div class="table-responsive">
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Motivo','Tercero','Detalle','Valor']) }}
        <tbody>
            <?php 
            
            $total_abono = 0;

            ?>
            @foreach($doc_registros as $linea )

                <tr>
                    <td> {{ $linea->motivo }} </td>
                    <td> {{ $linea->tercero }} </td>
                    <td> {{ $linea->detalle_operacion }} </td>
                    <td class="text-right"> ${{ number_format( $linea->valor, 0, ',', '.') }} </td>
                </tr>
                <?php 
                    $total_abono += $linea->valor;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">&nbsp;</td>
                <td class="text-right"> ${{ number_format($total_abono, 0, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>
</div>