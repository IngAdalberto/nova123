<tr style="font-size: {{$tam_letra}}mm;">
	<td colspan="{{$cant_columnas}}">
		<b> Observaciones: </b>
		<br/>&nbsp;&nbsp;
		@if( !is_null( $registro->observacion ) )
			{{ $registro->observacion->observacion }}
		@endif
	</td>
</tr>