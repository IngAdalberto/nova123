<h3 style="width: 100%; text-align: center;">
    LISTADO DE ACUMULADOS
</h3>
@if( !is_null($agrupacion) )
	<h4 style="width: 100%; text-align: center;">
	    {{ $agrupacion->descripcion }}
	</h4>
@endif
<p style="width: 100%; text-align: center;">
    Desde: {{ $fecha_desde }} - Hasta: {{ $fecha_hasta }}
</p>

<hr>


<?php 
	$lbl_encabezado = '';
	if ( $detalla_empleados )
	{
		$lbl_encabezado = 'Empleado';
	}
?>

<div class="table-responsive">
    <table id="myTable" class="table table-striped">
        <thead>
            <tr>
                <th> {{ $lbl_encabezado }} </th>
                @foreach( $conceptos as $concepto )
                	<th> <i title="{{ $concepto->descripcion }}">{{$concepto->abreviatura}}</i> </th>
                @endforeach
                <th> Totales </th>
            </tr>
        </thead>
        <tbody>
            <?php
                $j = 1;
                $totales_devengos = array_fill(0, count( $conceptos->toArray() ), 0);
                $totales_deducciones = array_fill(0, count( $conceptos->toArray() ), 0);
                $totales_cantidad_horas = array_fill(0, count( $conceptos->toArray() ), 0);

                $fila = '';
            	foreach( $empleados as $empleado )
            	{
	                $fila .= '<tr class="fila-'.$j.'">';

			        $k = 0;

	                $fila .= '<td> ' . $empleado->tercero->numero_identificacion . ' - ' . $empleado->tercero->descripcion . '</td>';

	                $total_fila_devengos = 0;
	                $total_fila_deducciones = 0;
	                $total_fila_cantidad_horas = 0;

                    foreach( $conceptos as $concepto )
                    {
                    		$devengo = $movimientos->whereLoose( 'core_tercero_id', $empleado->core_tercero_id )->whereLoose( 'nom_concepto_id', $concepto->id )->sum('valor_devengo');
                    		$deduccion = $movimientos->whereLoose( 'core_tercero_id', $empleado->core_tercero_id )->whereLoose( 'nom_concepto_id', $concepto->id )->sum('valor_deduccion');
                    		$cantidad_horas = $movimientos->whereLoose( 'core_tercero_id', $empleado->core_tercero_id )->whereLoose( 'nom_concepto_id', $concepto->id )->sum('cantidad_horas');

	                	$fila .= '<td> ' . dibuja_contenido_celda( $devengo, $deduccion, $cantidad_horas ) . ' </td>';

			                $totales_devengos[$k] += $devengo;
			                $totales_deducciones[$k] += $deduccion;
			                $totales_cantidad_horas[$k] += $cantidad_horas;

			                $total_fila_devengos += $devengo;
			                $total_fila_deducciones += $deduccion;
			                $total_fila_cantidad_horas += $cantidad_horas;

			                $k++;

	                }

	                $fila .= '<td> ' . dibuja_contenido_celda( $total_fila_devengos, $total_fila_deducciones, $total_fila_cantidad_horas ) . '</td>';
	                
	                $fila .= '</tr>';

	                $j++;
	                if ($j==3) {
	                    $j=1;
	                }
	            }
	        ?>

            @if( $detalla_empleados )
            	{!! $fila !!}
	        @endif
            
        </tbody>
        <tfoot>
			<tr style="background: #4a4a4a; color: white;">
				<td>
					Total x concepto
				</td>
				<?php
					$total_devengo_concepto = 0;
					$total_deduccion_concepto = 0;
					$total_cantidad_horas_concepto = 0;
				?>
		        	@for ( $i=0; $i < count($totales_devengos) ; $i++)
		        		<td> 
		        			<?php echo dibuja_contenido_celda( $totales_devengos[$i], $totales_deducciones[$i], $totales_cantidad_horas[$i] ); ?>
		        		</td>

		        		<?php
		        			$total_devengo_concepto += $totales_devengos[$i];
							$total_deduccion_concepto += $totales_deducciones[$i];
							$total_cantidad_horas_concepto += $totales_cantidad_horas[$i];
		        		?>
		        	@endfor        	
        		<td>
        			<?php echo dibuja_contenido_celda( $total_devengo_concepto, $total_deduccion_concepto, $total_cantidad_horas_concepto ); ?>
        		</td>
        	</tr>        	
        </tfoot>
    </table>
</div>

<?php 
	function dibuja_contenido_celda( $devengo, $deduccion, $cantidad_horas )
	{		
		$contenido = '<table>
						<tr>
							<td><b>Dev./Ded.</b></td>
							<td><b>Cant. horas</b></td>
						</tr>';

		$contenido .= '<tr><td>' . number_format( $devengo - $deduccion, 0, ',', '.') . ' </td>';
		$contenido .= '<td>' . number_format( $cantidad_horas, 2, ',', '.') . ' </td></tr>';

		$contenido .= '</table>';

		return $contenido;
	}

?>