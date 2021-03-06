<table class="table table-bordered table-striped">
	<thead>
		<tr>
			<th colspan="5" style="text-align: center;">
				Datos a cargo del trabajador o pensionado
			</th>
		</tr>
		<tr>
			<th width="50%">
				Concepto de otros ingresos
			</th>
			<th colspan="2">
				Valor recibido
			</th>
			<th colspan="2">
				Valor retenido
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				Arrendamientos
			</td>
			<td class="celda_numero_indicador">
				57
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
			<td class="celda_numero_indicador">
				64
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td>
				Honorarios, comisiones y servicios
			</td>
			<td class="celda_numero_indicador">
				58
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
			<td class="celda_numero_indicador">
				65
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td>
				Intereses y rendimientos financieros
			</td>
			<td class="celda_numero_indicador">
				59
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
			<td class="celda_numero_indicador">
				66
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td>
				Enajenación de activos fijos
			</td>
			<td class="celda_numero_indicador">
				60
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
			<td class="celda_numero_indicador">
				67
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td>
				Loterías, rifas, apuestas y similares
			</td>
			<td class="celda_numero_indicador">
				61
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
			<td class="celda_numero_indicador">
				57
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td>
				Otros
			</td>
			<td class="celda_numero_indicador">
				62
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
			<td class="celda_numero_indicador">
				69
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td>
				<b>Totales:</b> (<b>Valor recibido:</b> Sume 57 a 62), (<b>Valor retenido:</b> Sume 64 a 69)
			</td>
			<td class="celda_numero_indicador">
				63
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
			<td class="celda_numero_indicador">
				70
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<b>Total retenciones año gravable 2020</b> (Sume 55 + 56 + 70)
			</td>
			<td class="celda_numero_indicador">
				71
			</td>
			<td class="celda_valor">
				${{ number_format( $retefuente_descontada, 0, ',', '.' ) }}
			</td>
		</tr>
	</tbody>
</table>