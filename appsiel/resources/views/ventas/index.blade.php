<?php

	use App\Http\Controllers\Ventas\ReportesController;

	$fecha_hoy = date('Y-m-d');
	$tabla = ReportesController::grafica_ventas_diarias(date("Y-m-d", strtotime($fecha_hoy . "- 30 days")), $fecha_hoy);
	$vencidas = ReportesController::pedidos_vencidos();
	$futuras = ReportesController::pedidos_futuros();
	$semana = ReportesController::pedidos_semana();

	$vendedor = App\Ventas\Vendedor::where( 'user_id', Auth::user()->id )->get()->first();

	$vendedor_id = 0;
	if ( !is_null( $vendedor ) )
	{
		$vendedor_id = $vendedor->id;
	}
?>


@extends('layouts.principal')

@section('estilos_2')
	<style>

		div.boton {
		  border: 1px solid #ddd;
		  border-radius: 10px;
		  background: linear-gradient(90deg, rgba(110,41,183,1) 0%, rgba(79,138,232,1) 44%, rgba(13,214,159,1) 100%);
		  text-align: center;
		  margin: 20px 20px;
		}

		a:hover {
		  color: red;
		}

		a:link {
		  color: white;
		}
	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	@can('vtas_bloquear_vista_index')
		
	@else
		{!! $select_crear !!}
	@endcan

	<hr>

	@include('layouts.mensajes')

	@can('vtas_bloquear_vista_index')
		<div class="container-fluid">
			<div class="marco_formulario">
				<div class="row">
					<div class="col-sm-4">
						<div class="boton">
							<a href="{{url( 'vtas_pedidos/create?id=13&id_modelo=175&id_transaccion=42' )}}">
								<h1> <i class="fa fa-file"> </i> </h1>
								Crear pedido
							</a>							
						</div>
					</div>
					<div class="col-sm-4">
						<div class="boton">
							<a href="{{url( 'web/create?id=13&id_modelo=216' )}}">
								<h1> <i class="fa fa-smile-o"> </i> </h1>
								Crear cliente
							</a>							
						</div>
					</div>
					<div class="col-sm-4">
						<div class="boton">
							<a href="{{ url( 'web?id=13&id_modelo=175&id_transaccion=42&vendedor_id='. $vendedor_id )}}">
								<h1> <i class="fa fa-file"> </i> </h1>
								Facturar pedidos
							</a>							
						</div>
					</div>
				</div>
			</div>
		</div>
	@else
		<div class="container-fluid">
			<div class="marco_formulario">
				<h3> Pedidos de ventas (pedidos pendientes, actuales, futuros y vencidos)</h3>
				<hr>
				<div class="row">
					
					<div class="col-md-12">
						<h4 style="text-align: center; width: 100%; background-color: #faf4d4; color: #636363;">Pedidos ésta semana</h4>
						<div class="table-responsive">
							@if($semana!=null)
							<table class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>LUNES</th>
										<th>MARTES</th>
										<th>MIERCOLES</th>
										<th>JUEVES</th>
										<th>VIERNES</th>
										<th>SABADO</th>
										<th>DOMINGO</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										@foreach($semana as $s)
										<td>
											<!-- <div class="alert alert-success" style="padding: 5px" role="alert">
												{ {$s['fecha']}}
											</div> -->
											<h5 style="text-align: center; width: 100%; background-color: #ddd; color: #636363;">{{$s['fecha']}}</h5>
											@if($s['data']!=null)
											<ol>
												@foreach($s['data'] as $d)
												<li>
													<a style="color: #0b97c4;" target="_blank" href="{{url('vtas_pedidos/'.$d['id'].'?id=13&id_modelo=175&id_transaccion=42')}}">{{$d['documento']}}</a>
													- {{$d['cliente']." - ".$d['fecha']}}
												</li>
												@endforeach
											</ol>
											@else
											<p>---</p>
											@endif
										</td>
										@endforeach
									</tr>
								</tbody>
							</table>
							@else
							<p>No hay pedidos pendientes esta semana</p>
							@endif
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						<h4 style="text-align: center; width: 100%; background-color: #FFD3D3; color: #636363;">Pedidos vencidos</h4>
						@if($vencidas!=null)
						<table class="table table-striped table-bordered">
							<thead>
								<tr>
									<th>Pedido</th>
									<th>Cliente</th>
									<th>Venció</th>
								</tr>
							</thead>
							<tbody>
								@foreach($vencidas as $v)
								<tr>
									<td><a target="_blank" href="{{url('vtas_pedidos/'.$v['id'].'?id=13&id_modelo=175&id_transaccion=42')}}">{{$v['documento']}}</a></td>
									<td>{{$v['cliente']}}</td>
									<td>{{$v['fecha']}}</td>
								</tr>
								@endforeach
							</tbody>
						</table>
						@else
						<p>No hay pedidos pendientes vencidos</p>
						@endif
					</div>
					<div class="col-md-6">
						<h4 style="text-align: center; width: 100%; background-color: #d3eac9; color: #636363;">Pedidos futuros</h4>

						@if($futuras!=null)
						<table class="table table-striped table-bordered">
							<thead>
								<tr>
									<th>Pedido</th>
									<th>Cliente</th>
									<th>Vence</th>
								</tr>
							</thead>
							<tbody>
								@foreach($futuras as $v)
								<tr>
									<td><a target="_blank" href="{{url('vtas_pedidos/'.$v['id'].'?id=13&id_modelo=175&id_transaccion=42')}}">{{$v['documento']}}</a></td>
									<td>{{$v['cliente']}}</td>
									<td>{{$v['fecha']}}</td>
								</tr>
								@endforeach
							</tbody>
						</table>
						@else
						<p>No hay pedidos pendientes futuros</p>
						@endif
					</div>
				</div>

			</div>
		</div>

		<div class="container-fluid">
			<div class="marco_formulario">

				<?php
				echo Lava::render('BarChart', 'ventas_diarias', 'grafica1');
				$cant = count($tabla);
				$totales = 0;
				?>
				<h3> Ventas de los últimos 30 días <small> <a href="#" data-toggle="tooltip" data-placement="right" title="IVA no incluido!"> <i class="fa fa-info-circle"></i> </a> </small> </h3>
				<hr>
				<div id="grafica1"></div>

				<br><br>
				<div class="row">
					<div class="col-md-4 col-md-offset-4">
						<table class="table table-striped table-bordered">
							<thead>
								<tr>
									<th>Fecha</th>
									<th>Total</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								@for($i=0; $i < $cant; $i++) <tr>
									<td> {{ $tabla[$i]['fecha'] }} </td>
									<td style="text-align: right;"> ${{ number_format($tabla[$i]['valor'], 2, ',', '.') }} </td>
									<td> </td>
									</tr>
									@php
									$totales += $tabla[$i]['valor'];
									@endphp
									@endfor
							</tbody>
							<tfoot>
								<tr>
									<td> </td>
									<td style="text-align: right;"> <b> ${{ number_format($totales, 2, ',', '.') }} </b> </td>
									<td> </td>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>
		</div>

		<br />
	@endcan	
@endsection