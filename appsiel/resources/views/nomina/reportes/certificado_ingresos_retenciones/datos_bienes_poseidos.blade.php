<table class="table table-bordered table-striped">
	<thead>
		<tr>
			<th width="5%">
				Item
			</th>
			<th width="70%" colspan="2">
				<b>72. Identificación de los bienes poseídos </b>
			</th>
			<th>
				<b>73. Valor patrimonial</b>
			</th>
		</tr>
	</thead>
	<tbody>
		@for( $i=1; $i < 7; $i++ )
			<tr>
				<td class="celda_numero_indicador">
					<b>{{ $i }}</b>
				</td>
				<td colspan="2">
					&nbsp;
				</td>
				<td>
					&nbsp;
				</td>
			</tr>
		@endfor
		<tr>
			<td style="background-color: #396395; color: white;">
			&nbsp;
			</td>
			<td style="background-color: #396395; color: white;" width="80%">
				Deudas vigentes a 31 de Diciembre de 2020
			</td>
			<td class="celda_numero_indicador">
				74
			</td>
			<td>
				&nbsp;
			</td>
		</tr>
	</tbody>
</table>