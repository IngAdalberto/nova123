@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('estilos_1')
	<style>
		#suggestions {
		    position: absolute;
		    z-index: 9999;
		}
		#clientes_suggestions {
		    position: absolute;
		    z-index: 9999;
		}

		#existencia_actual, #tasa_impuesto{
			width: 35px;
		}

		#popup_alerta{
			display: none;/**/
			color: #FFFFFF;
			background: red;
			border-radius: 5px;
			position: fixed; /*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			right:10px; /*A la izquierda deje un espacio de 0px*/
			bottom:10px; /*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div */
			width: 20%;
			z-index:999999;
			float: right;
    		text-align: center;
    		padding: 5px;
    		opacity: 0.7;
		}
	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">		    

			<h4>Nuevo registro</h4>
			<hr>
			{{ Form::open([ 'url' => $form_create['url'], 'id'=>'form_create']) }}
				<?php
				  if (count($form_create['campos'])>0) {
				  	$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
				  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm2($url).'</div>';
				  }else{
				  	echo "<p>El modelo no tiene campos asociados.</p>";
				  }
				?>

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				{{ Form::hidden('url_id',Input::get('id')) }}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo')) }}

				<input type="hidden" name="url_id_transaccion" id="url_id_transaccion" value="{{Input::get('id_transaccion')}}" required="required">

				{{ Form::hidden('inv_bodega_id_aux',null,['id'=>'inv_bodega_id_aux']) }}

				<input type="hidden" name="cliente_id" id="cliente_id" value="" required="required">
				<input type="hidden" name="zona_id" id="zona_id" value="" required="required">
				<input type="hidden" name="clase_cliente_id" id="clase_cliente_id" value="" required="required">
				<input type="hidden" name="equipo_ventas_id" id="equipo_ventas_id" value="" required="required">


				<input type="hidden" name="core_tercero_id" id="core_tercero_id" value="" required="required">
				<input type="hidden" name="lista_precios_id" id="lista_precios_id" value="" required="required">
				<input type="hidden" name="lista_descuentos_id" id="lista_descuentos_id" value="" required="required">
				<input type="hidden" name="liquida_impuestos" id="liquida_impuestos" value="" required="required">
				<input type="hidden" name="lineas_registros" id="lineas_registros" value="0">

				<input type="hidden" name="tipo_transaccion"  id="tipo_transaccion" value="factura_directa">

				<input type="hidden" name="rm_tipo_transaccion_id"  id="rm_tipo_transaccion_id" value="{{config('ventas')['rm_tipo_transaccion_id']}}">
				<input type="hidden" name="dvc_tipo_transaccion_id"  id="dvc_tipo_transaccion_id" value="{{config('ventas')['dvc_tipo_transaccion_id']}}">

				<input type="hidden" name="saldo_original" id="saldo_original" value="0">

				<div id="popup_alerta"> </div>
				
			{{ Form::close() }}

			<br/>

			@include('ventas.incluir.elementos_remisiones_pendientes')

			<br/>



			<!--
			<div class="well">
				<h3>Llamar datos de la bascula</h3>
				<label class="radio-inline"><input type="radio" name="bascula_id" value="3">Bascula 1</label>
				<label class="radio-inline"><input type="radio" name="bascula_id" value="34">Bascula 2</label>
				<label class="radio-inline"> <button class="btn btn-default btn-xs" id="cargar_datos_producto"> Cargar productos </button> </label>
			</div>
		-->

			{!! $tabla->dibujar() !!}


			Productos ingresados: <span id="numero_lineas"> 0 </span>
			
			<div style="text-align: right;">
				<div id="total_cantidad" style="display: none;"> 0 </div>
            	<table style="display: inline;">
            		<tr>
            			<td style="text-align: right; font-weight: bold;"> Subtotal: &nbsp; </td> <td> <div id="subtotal"> $ 0 </div> </td>
            		</tr>
					<tr>
            			<td style="text-align: right; font-weight: bold;"> Impuestos: &nbsp; </td> <td> <div id="total_impuestos"> $ 0 </div> </td>
            		</tr>
            		<tr>
            			<td style="text-align: right; font-weight: bold;"> Total factura: &nbsp; </td> <td> <div id="total_factura"> $ 0 </div> </td>
            		</tr>
            	</table>
			</div>
			
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			checkCookie();

			$('#fecha').val( get_fecha_hoy() );

			$('#cliente_input').focus( );


			/* INVENTARIOS*/
			var respuesta;
			var hay_productos = 0;

		    // Al cambiar la fecha
		    $('#fecha').on('change',function(){

		    	// Reset línea de registro de productos
		    	$('#linea_ingreso_default input[type="text"]').val('');
				$('#linea_ingreso_default input[type="text"]').attr('style','background-color:#ECECE5;');
				$('#linea_ingreso_default input[type="text"]').attr('disabled','disabled');


				$('#inv_motivo_id').attr('style','background-color:#ECECE5;');
				$('#inv_motivo_id').attr('disabled','disabled');

				// Se habilitan los campos necesarios
				$('#precio_unitario').removeAttr('style');
				$('#precio_unitario').removeAttr('disabled');

				$('#inv_producto_id').removeAttr('style');
				$('#inv_producto_id').removeAttr('disabled');
		    });


		    $('#cliente_input').on('focus',function(){
		    	$(this).select();
		    });

			$("#cliente_input").after('<div id="clientes_suggestions"> </div>');

			// Al ingresar código, descripción o código de barras del producto
		    $('#cliente_input').on('keyup',function(){

		    	reset_campos_formulario();

		    	var x = event.which || event.keyCode; // Capturar la tecla presionada

		    	switch( x )
		    	{
		    		case 27:// 27 = ESC
						$('#clientes_suggestions').html('');
	                	$('#clientes_suggestions').hide();
		    			break;

		    		case 40:// Flecha hacia abajo
						var item_activo = $("a.list-group-item.active");					
						item_activo.next().attr('class','list-group-item list-group-item-cliente active');
						item_activo.attr('class','list-group-item list-group-item-cliente');
						$('#cliente_input').val( item_activo.next().html() );
		    			break;

		    		case 38:// Flecha hacia arriba
						$(".flecha_mover:focus").prev().focus();
						var item_activo = $("a.list-group-item.active");					
						item_activo.prev().attr('class','list-group-item list-group-item-cliente active');
						item_activo.attr('class','list-group-item list-group-item-cliente');
						$('#cliente_input').val( item_activo.prev().html() );
		    			break;

		    		case 13:// Al presionar Enter
						var item = $('a.list-group-item.active');
						
						if( item.attr('data-cliente_id') === undefined )
						{
							alert('El cliente ingresado no existe.');
							reset_campos_formulario();
						}else{
							seleccionar_cliente( item );
						}
		    			break;

		    		default :
			    		// Manejo código de producto o nombre
			    		var campo_busqueda = 'descripcion';
			    		if( $.isNumeric( $(this).val() ) ){
				    		var campo_busqueda = 'numero_identificacion';
				    	}

				    	// Si la longitud es menor a tres, todavía no busca
					    if ( $(this).val().length < 2 ) { return false; }

				    	var url = '../vtas_consultar_clientes';

						$.get( url, { texto_busqueda: $(this).val(), campo_busqueda: campo_busqueda } )
							.done(function( data ) {
								// Se llena el DIV con las sugerencias que arooja la consulta
				                $('#clientes_suggestions').show().html(data);
				                $('a.list-group-item.active').focus();
							});
		    			break;
		    	}
		    });


		    //Al hacer click en alguna de las sugerencias (escoger un producto)
            $(document).on('click','.list-group-item-cliente', function(){
            	seleccionar_cliente( $(this) );
            	return false;
            });


			// Al seleccionar una bodega, se ubica en el siguiente elemento
			$('#inv_bodega_id').change(function(){

				reset_linea_ingreso_default();

				$('#inv_producto_id').focus();

				if( $('#url_id_transaccion').val()==2 ) 
				{ // Si es una transferencia
					$('#bodega_destino_id').focus();
				}

			});


		    // Al Activar/Inactivar modo de ingreso
		    $('#modo_ingreso').on('click',function(){

		    	if( $(this).val() == "true" ){
	        		$(this).val( "false" );
	        		setCookie("modo_ingreso_codigo_de_barra", "false", 365);
	        	}else{
	        		$(this).val( "true" );
	        		setCookie("modo_ingreso_codigo_de_barra", "true", 365);
	        	}
		    	
		    	reset_linea_ingreso_default();
		    });


		    $('[data-toggle="tooltip"]').tooltip();
		    var terminar = 0; // Al presionar ESC dos veces, se posiciona en el botón guardar
		    // Al ingresar código, descripción o código de barras del producto
		    $('#inv_producto_id').on('keyup',function(event){

		    	$("[data-toggle='tooltip']").tooltip('hide');

		    	if ( validar_requeridos() == false )
				{
					return false;
				}

				var codigo_tecla_presionada = event.which || event.keyCode; // Capturar la tecla presionada

				switch( codigo_tecla_presionada )
		    	{
		    		case 27: // 27 = ESC
						terminar++;
						$('#suggestions').html('');
	                	$('#suggestions').hide();

	                	if ( terminar == 2 )
	                	{ 
	                		terminar = 0;
	                		$('#btn_guardar').focus(); 
	                	}
		    			break;

		    		case 40: // Flecha hacia abajo
						var item_activo = $("a.list-group-item.active");					
						item_activo.next().attr('class','list-group-item list-group-item-productos active');
						item_activo.attr('class','list-group-item list-group-item-productos');
						$('#inv_producto_id').val( item_activo.html() );
		    			break;

		    		case 38: // Flecha hacia arriba
						$(".flecha_mover:focus").prev().focus();
						var item_activo = $("a.list-group-item.active");					
						item_activo.prev().attr('class','list-group-item list-group-item-productos active');
						item_activo.attr('class','list-group-item list-group-item-productos');
						$('#inv_producto_id').val( item_activo.html() );
		    			break;

		    		case 13: // Al presionar Enter

		    			// Si el campo_busqueda es ID y el texto_busqueda coincide con el ID exacto del producto, en el listado de sugerencias ya viene marcado como Active el producto de la lista 
		    		
		    			// Cuando se ingresa el ID, se selecciona el item activo cuando se presiona Enter 

						var item = $('a.list-group-item.active');
						
						if( item.attr('data-producto_id') === undefined )
						{
							alert('El producto ingresado no existe.');
							reset_linea_ingreso_default();
						}else{
							seleccionar_producto( item );
		                	consultar_existencia( $('#inv_bodega_id').val(), item.attr('data-producto_id') );
		                	return false;
						}
		    			break;

		    		default :
			    		// Se determina el campo de busqueda
			    		if( $.isNumeric( $(this).val() ) )
			    		{
			    			if( $('#modo_ingreso').is(':checked') )
					    	{
					    		// Manejo códigos de barra
					    		var campo_busqueda = 'codigo_barras'; // Busqueda por CÓDIGO DE BARRA
					    	}else{
					    		var campo_busqueda = 'id'; // Busqueda por CODIGO (ID en base de datos)
					    	}
				    	}else{
				    		var campo_busqueda = 'descripcion'; // Busqueda por NOMBRE

				    		// Si la longitud es menor a tres, todavía no busca
				    		if ( $(this).val().length < 2 ) { return false; }
				    	}

				    	terminar = 0;

				    	// Realizar consulta y mostar sugerencias
				    	var url = '../inv_consultar_productos';

						$.get( url, { texto_busqueda: $(this).val(), campo_busqueda: campo_busqueda } )
							.done(function( data ) {
								//Escribimos las sugerencias que nos manda la consulta
				                $('#suggestions').show().html(data);
				                $('.list-group-item-productos:first').focus();
							});
		    			break;
		    	}

		    });

				    

            //Al hacer click en alguna de las sugerencias (escoger un producto)
            $(document).on('click','.list-group-item-productos', function(){
                
            	seleccionar_producto( $(this) );

                // Consultar datos de existencia y costo y asignarlos a los inputs
                consultar_existencia( $('#inv_bodega_id').val(), $(this).attr('data-producto_id') );
            });


			/*
			** Al digitar la cantidad, se valida la existencia actual y se calcula el precio total
			*/
			$('#cantidad').keyup(function(event){

				if( validar_input_numerico( $(this) ) && $(this).val() > 0 )
				{
					if( $('#url_id_transaccion').val() == 23 ) 
					{
						// Si es una factura de ventas
						calcula_nuevo_saldo_a_la_fecha(); // NO VALIDA AÚN
					}

					// El registro se agrega al presionas Enter, si pasa las validaciones
					var x = event.which || event.keyCode;
					if( x==13)
					{
						if ( !validar_existencia_actual() )
						{
							return false;
						}

						$('#precio_unitario').select();

						return true;					
					}

					// Si el costo_unitario del producto es cero (por algún motivo de la APP Inventarios, ejemplo al hacer ENSAMBLES) 
					var costo_unitario = $('#linea_ingreso_default').find('.costo_unitario').html();
					if ( costo_unitario == 0 || costo_unitario == "" ) 
					{
						$('#linea_ingreso_default').find('.costo_unitario').html( 0.0000001 );
					}

					if ( $(this).val() != '' && validar_input_numerico( $(this) ) )
					{
						calcula_precio_total();
					}
				}else{
					return false;
				}
			});

			function validar_venta_menor_costo()
			{
				var ok = true;
				var costo_unitario = parseFloat ( $('#linea_ingreso_default').find('.costo_unitario').html() );
				var base_impuesto = parseFloat ( $('#linea_ingreso_default').find('.base_impuesto').html() );

				if ( base_impuesto < costo_unitario)
				{
					$('#popup_alerta').show();
					$('#popup_alerta').css('background-color','red');
					$('#popup_alerta').text( 'El precio está por debajo del costo de venta del producto.' + ' $'+ new Intl.NumberFormat("de-DE").format( costo_unitario.toFixed(2) ) + ' + IVA' );
					ok = false;
				}else{
					$('#popup_alerta').hide();
					ok = true;
				}

				return ok;
			}

            // Al modificar el precio de venta
            $('#precio_unitario').keyup(function(event){
				
				// IVA INCLUIDO

				if( validar_input_numerico( $(this) ) )
				{	

					var x = event.which || event.keyCode;
					if( x == 13 )
					{
						if ( !validar_venta_menor_costo() )
						{ 
							return false;
						}

						if( $('#url_id_transaccion').val() == 23 ) 
						{ 
							// Si es una factura de ventas (salida de invetario)
							validacion_saldo_movimientos_posteriores();
						}else{
							agregar_nueva_linea();
						}			
					}

					var tasa_impuesto = $('#linea_ingreso_default').find('.tasa_impuesto').html();

	            	// Los impuestos se obtienen del precio ingresado
					var precio_unitario = parseFloat( $(this).val() );
		            
		            var base_impuesto = ( precio_unitario ) / ( 1 + parseFloat(tasa_impuesto) / 100 );


		            var valor_impuesto =  precio_unitario - base_impuesto;

					//$('#linea_ingreso_default').find('.costo_unitario').html( precio_unitario );
					$('#linea_ingreso_default').find('.base_impuesto').html( base_impuesto );
					
					$('#linea_ingreso_default').find('.valor_impuesto').html( valor_impuesto );
					$('#linea_ingreso_default').find('.precio_unitario').html( precio_unitario );

					// Asignar datos a los controles (formateados visualmente para el usuario)
					$('#tasa_impuesto').val( tasa_impuesto + '%' );

					calcula_precio_total();
				}else{

					$(this).focus();
					return false;
				}

			});

            // Al modificar el precio total
            $('#precio_total').keyup(function(event){
				
				if( validar_input_numerico( $(this) ) )
				{	
					var x = event.which || event.keyCode;
					if( x==13 )
					{
						if ( !validar_venta_menor_costo() ) { return false; }

						agregar_nueva_linea();
						return true;
					}

					var cantidad = parseFloat( $('#cantidad').val() );

					var tasa_impuesto = $('#linea_ingreso_default').find('.tasa_impuesto').html();

	            	// Los impuestos se obtienen del precio ingresado
					var precio_total = parseFloat( $(this).val() );
		            
		            var total_base_impuesto = ( precio_total ) / ( 1 + parseFloat(tasa_impuesto) / 100 );
		            var total_valor_impuesto =  precio_total - total_base_impuesto;

					$('#linea_ingreso_default').find('.base_impuesto').html( total_base_impuesto / cantidad );
					
					$('#linea_ingreso_default').find('.valor_impuesto').html( total_valor_impuesto / cantidad );
					$('#linea_ingreso_default').find('.precio_unitario').html( precio_total / cantidad );

					$('#precio_unitario').val( precio_total / cantidad );

					//calcula_precio_total();
				}else{

					$(this).focus();
					return false;
				}

			});
			

		    function seleccionar_cliente(item_sugerencia)
            {
            	
				// Asignar descripción al TextInput
                $('#cliente_input').val( item_sugerencia.html() );
                $('#cliente_input').css( 'background-color','white ' );

                // Asignar Campos ocultos
                $('#cliente_id').val( item_sugerencia.attr('data-cliente_id') );
                $('#zona_id').val( item_sugerencia.attr('data-zona_id') );
                $('#clase_cliente_id').val( item_sugerencia.attr('data-clase_cliente_id') );
                $('#equipo_ventas_id').val( item_sugerencia.attr('data-equipo_ventas_id') );
                $('#liquida_impuestos').val( item_sugerencia.attr('data-liquida_impuestos') );
                $('#core_tercero_id').val( item_sugerencia.attr('data-core_tercero_id') );
                $('#lista_precios_id').val( item_sugerencia.attr('data-lista_precios_id') );
                $('#lista_descuentos_id').val( item_sugerencia.attr('data-lista_descuentos_id') );

                // Asignar resto de campos
                $('#vendedor_id').val( item_sugerencia.attr('data-vendedor_id') );
                $('#inv_bodega_id').val( item_sugerencia.attr('data-inv_bodega_id') );


                var forma_pago = 'contado';
                var dias_plazo = parseInt( item_sugerencia.attr('data-dias_plazo') );
                if ( dias_plazo > 0 ) { forma_pago = 'credito'; }
                $('#forma_pago').val( forma_pago );

                // Para llenar la fecha de vencimiento
                var fecha = new Date( $('#fecha').val() );
				fecha.setDate( fecha.getDate() + (dias_plazo + 1) );
				
				var mes = fecha.getMonth() + 1; // Se le suma 1, Los meses van de 0 a 11
				var dia = fecha.getDate();// + 1; // Se le suma 1,

                if( mes < 10 )
                {
                	mes = '0' + mes;
                }

                if( dia < 10 )
                {
                	dia = '0' + dia;
                }
                $('#fecha_vencimiento').val( fecha.getFullYear() + '-' +  mes + '-' + dia );


                //Hacemos desaparecer el resto de sugerencias
                $('#clientes_suggestions').html('');
                $('#clientes_suggestions').hide();

                reset_tabla_ingreso();

                consultar_remisiones_pendientes();
                
				// Bajar el Scroll hasta el final de la página
				$("html, body").animate( { scrollTop: $(document).height()+"px"} );
            }

            function seleccionar_producto(item_sugerencia)
            {
            	reset_linea_ingreso_default();
            	var fila = $('#linea_ingreso_default');

            	// Asignar ID del producto al campo oculto
                fila.find('.inv_producto_id').html( item_sugerencia.attr('data-producto_id') );

                // Asignar ID del motivo al campo oculto
                var mov = $('#inv_motivo_id').val().split('-');
				fila.find('.inv_motivo_id').html( mov[0] );

				// Asignar descripción del producto al TextInput
                $('#inv_producto_id').val( item_sugerencia.html() );
                //Hacemos desaparecer el resto de sugerencias
                $('#suggestions').html('');
                $('#suggestions').hide();
            }

            // Asignar valores de existecia_actual y costo_unitario
            function consultar_existencia(bodega_id, producto_id)
            {
            	$('#div_cargando').show();
            	var url = '../vtas_consultar_existencia_producto';

				$.get( url, { transaccion_id: $('#core_tipo_transaccion_id').val(), bodega_id: bodega_id, producto_id: producto_id, fecha: $('#fecha').val(), lista_precios_id: $('#lista_precios_id').val(), cliente_id: $('#cliente_id').val() } )
					.done(function( respuesta ) {

						$('#div_cargando').hide();
					
						// Se valida la existencia actual
						$('#existencia_actual').val(respuesta.existencia_actual);
						$('#saldo_original').val(respuesta.existencia_actual);
						$('#tipo_producto').val(respuesta.tipo);

						$('#existencia_actual').attr('style','background-color:#97D897;'); // color verde

						if (respuesta.existencia_actual<=0)
						{
							$('#existencia_actual').attr('style','background-color:#FF8C8C;'); // color rojo
							
							// Si no es un motivo de entrada, no se permite seguir con existencia 0
							
							var mov = $('#inv_motivo_id').val().split('-');
							
							if ( mov[1] != 'entrada' && respuesta.tipo != 'servicio' ) 
							{	
								$('#inv_producto_id').select();
								return false;
							}
							/**/
						}

						// Asignar datos a columnas invisibles (cantidades sin formatear)
						$('#linea_ingreso_default').find('.inv_bodega_id').html( $('#inv_bodega_id').val() );
						$('#linea_ingreso_default').find('.costo_unitario').html( respuesta.costo_promedio );
						$('#linea_ingreso_default').find('.base_impuesto').html( respuesta.base_impuesto );
						$('#linea_ingreso_default').find('.tasa_impuesto').html( respuesta.tasa_impuesto );
						$('#linea_ingreso_default').find('.valor_impuesto').html( respuesta.valor_impuesto );
						$('#linea_ingreso_default').find('.precio_unitario').html( respuesta.precio_venta );

						// Asignar datos a los controles (formateados visualmente para el usuario)
						//var precio_compra = 
						$('#precio_unitario').val( '$' + new Intl.NumberFormat("de-DE").format( respuesta.precio_venta )  );
						$('#tasa_impuesto').val( respuesta.tasa_impuesto + '%' );

						// Se pasa a ingresar las cantidades
						$('#cantidad').removeAttr('disabled');
						$('#cantidad').attr('style','background-color:white;');
						$('#cantidad').focus();

						if ( (respuesta.tipo != 'servicio') && (respuesta.costo_promedio == 0) )
						{
							alert('Advertencia! El producto no tiene costo en inventarios, esto puede afectar la contabilidad. Por favor comuníquese con el área de contabilidad.');
						}
							

						return true;
					});
            }

			/*
				validar_existencia_actual
			*/
			function validar_existencia_actual()
			{
				if ( $('#tipo_producto').val() == 'servicio') { return true; }

				if ( parseFloat( $('#existencia_actual').val() ) < 0 ) 
				{
					alert('Saldo negativo a la fecha.');
					$('#cantidad').val('');
					$('#cantidad').focus();
					return false;
				}/**/
				return true;
			}

			/*
			** Al presionar enter, luego de ingresar la cantidad y si se pasan la validaciones
			*/
			var numero_linea = 1;
			function agregar_nueva_linea()
			{
				if ( !calcula_precio_total() )
				{
					return false;
				}

				if ( !validar_existencia_actual() )
				{
					return false;
				}

				// Se escogen los campos de la fila ingresada
				var fila = $('#linea_ingreso_default');

				$('#ingreso_registros').find('tbody:last').append('<tr class="linea_registro" data-numero_linea="'+numero_linea+'">' + generar_string_celdas( fila ) + '</tr>');
				
				// Se calculan los totales
				calcular_totales();

				hay_productos++;
				$('#numero_lineas').text(hay_productos);
				deshabilitar_campos_encabezado();

				// Bajar el Scroll hasta el final de la página
				$("html, body").animate( { scrollTop: $(document).height()+"px"} );

				reset_linea_ingreso_default();

				// !!    WARNING   !!!!!!!!!!!!!!!! Se DEBE INACTIVAR la bodega para que ya no se pueda cambiar, pues el motiviemto está amarrado a la bodega

				numero_linea++;
			}

			// Crea la cadena de la celdas que se agregarán a la línea de ingreso de productos
			// Debe ser complatible con las columnas de la tabla de ingreso de registros
			function generar_string_celdas( fila )
			{
				var celdas = [];
				var num_celda = 0;
				
				celdas[ num_celda ] = '<td style="display: none;"><div class="inv_motivo_id">'+ fila.find('.inv_motivo_id').html() +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="inv_bodega_id">'+ fila.find('.inv_bodega_id').html() +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="inv_producto_id">'+ fila.find('.inv_producto_id').html() +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="costo_unitario">'+ fila.find('.costo_unitario').html() +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="precio_unitario">'+ fila.find('.precio_unitario').html() +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="base_impuesto">'+ fila.find('.base_impuesto').html() +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="tasa_impuesto">'+ fila.find('.tasa_impuesto').html() +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="valor_impuesto">'+ fila.find('.valor_impuesto').html() +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="base_impuesto_total">'+ fila.find('.base_impuesto_total').html() +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="cantidad">'+ $('#cantidad').val() +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="costo_total">'+ fila.find('.costo_total').html() +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="precio_total">'+ fila.find('.precio_total').html() +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td> &nbsp; </td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td> <span style="background-color:#F7B2A3;">'+ fila.find('.inv_producto_id').html() + "</span> " + $('#inv_producto_id').val() + '</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td>'+ $('#inv_motivo_id option:selected').text() + '</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td> '+ $('#existencia_actual').val() + '</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td>'+ $('#cantidad').val() + ' </td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td> '+ $('#precio_unitario').val() + '</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td>'+ $('#tasa_descuento').val() + ' </td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td> '+ $('#valor_total_descuento').val() + '</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td>'+ $('#tasa_impuesto').val() + '</td>';
				
				num_celda++;

				var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";
				celdas[ num_celda ] = '<td> '+ $('#precio_total').val() + ' </td><td>' + btn_borrar + '</td>';

				var cantidad_celdas = celdas.length;
				var string_celdas = '';
				for (var i = 0; i < cantidad_celdas; i++)
				{
					string_celdas = string_celdas + celdas[i];
				}

				return string_celdas;
			}

			function deshabilitar_campos_encabezado()
			{
				$('#cliente_input').attr('disabled','disabled');
				$('#fecha').attr('disabled','disabled');
				$('#inv_bodega_id').attr('disabled','disabled');				
			}

			function habilitar_campos_encabezado()
			{
				$('#cliente_input').removeAttr('disabled');
				$('#fecha').removeAttr('disabled');
				$('#inv_bodega_id').removeAttr('disabled');
			}

			/*
			** Al eliminar una fila
			*/
			$(document).on('click', '.btn_eliminar', function(event) {
				event.preventDefault();
				var fila = $(this).closest("tr");

				fila.remove();

				calcular_totales();

				hay_productos--;
				numero_linea--;
				$('#numero_lineas').text(hay_productos);

				if ( hay_productos == 0)
				{
					habilitar_campos_encabezado();
				}

				reset_linea_ingreso_default();

			});

			// GUARDAR EL FORMULARIO
			$('#btn_guardar').click(function(event){
				event.preventDefault();

				var object = $('#ingreso_registros').val();
				
				if( typeof object == typeof undefined){
					// Si no existe la tabla de ingreso_registros, se envía el formulario
					// Esto es para los otros modelos que usan el ModeloController y que no
					// son una transacción

					// Desactivar el click del botón
					$( this ).off( event );

					$('#form_create').submit();
				}

				if ( !validar_requeridos() )
				{
					return false;	
				}


				if( hay_productos == 0) 
				{
					alert('No ha ingresado productos.');
					reset_linea_ingreso_default();
					return false;		  			
				}				

				// Desactivar el click del botón
				$( this ).off( event );

				$('#linea_ingreso_default').remove();

				if ( $('#tipo_transaccion').val() == 'factura_directa' ) // Cuando no hay remisiones o devoluciones pendientes
				{
					// Se transfoma la tabla a formato JSON a través de un plugin JQuery
					var table = $('#ingreso_registros').tableToJSON();
				}else{

					var table = $('#tabla_registros_documento').tableToJSON();
				}

				// Se asigna el objeto JSON a un campo oculto del formulario
		 		$('#lineas_registros').val(JSON.stringify(table));
				
			 	// No se puede enviar controles disabled
				habilitar_campos_encabezado();

		 		// Enviar formulario
				$('#form_create').submit();					
			});

			function reset_campos_formulario()
			{
				$('#cliente_id').val( '' );
				$('#cliente_input').css( 'background-color','#FF8C8C' );
                $('#vendedor_id').val( '' );
                $('#inv_bodega_id').val( '' );
                $('#forma_pago').val( 'contado' );
				$('#fecha_vencimiento').val( '' );
                $('#lista_precios_id').val( '' );
                $('#lista_descuentos_id').val( '' );
                $('#liquida_impuestos').val( '' );
			}

			function reset_tabla_ingreso()
			{
				$('.linea_registro').each(function( ){
					$(this).remove();
				});

				// reset totales
				$('#total_cantidad').text( '0' );

				// Subtotal (Sumatoria de base_impuestos por cantidad)
				$('#subtotal').text( '$ 0' );

				// Total impuestos (Sumatoria de valor_impuesto por cantidad)
				$('#total_impuestos').text( '$ 0' );

				// Total factura  (Sumatoria de precio_total)
				$('#total_factura').text( '$ 0' );


				reset_linea_ingreso_default()
			}


			function reset_linea_ingreso_default()
			{
				$('#linea_ingreso_default input[type="text"]').val('');
				$('#linea_ingreso_default input[type="text"]').attr('style','background-color:#ECECE5;');
				$('#linea_ingreso_default input[type="text"]').attr('disabled','disabled');


				$('#inv_motivo_id').attr('style','background-color:#ECECE5;');
				$('#inv_motivo_id').attr('disabled','disabled');

				$('#precio_unitario').removeAttr('style');
				$('#precio_unitario').removeAttr('disabled');

				$('#precio_total').removeAttr('style');
				$('#precio_total').removeAttr('disabled');

				$('#inv_producto_id').removeAttr('style');
				$('#inv_producto_id').removeAttr('disabled');
				$('#inv_producto_id').focus();
				$("[data-toggle='tooltip']").tooltip('show');
			}

			function calcula_precio_total()
			{
				var fila = $('#linea_ingreso_default');
				
				var cantidad = $('#cantidad').val();
				
				var base_impuesto = parseFloat( fila.find('.base_impuesto').html() );
				var base_impuesto_total = base_impuesto * cantidad;

				var precio_unitario = parseFloat( fila.find('.precio_unitario').html() );
				var precio_total = precio_unitario * cantidad;

				var costo_unitario = parseFloat( fila.find('.costo_unitario').html() );
				var costo_total = costo_unitario * cantidad;
				
				$('#precio_total').val('');
				fila.find('.precio_total').html('');
				
				$('#costo_total').val('');
				fila.find('.costo_total').html('');
				
				$('#base_impuesto_total').val('');
				fila.find('.base_impuesto_total').html('');

				if( $.isNumeric(precio_total) && precio_total > 0 )
				{
					$('#precio_total').val( '$ ' + new Intl.NumberFormat("de-DE").format( precio_total )  );
					fila.find('.precio_total').html( precio_total );

					fila.find('.base_impuesto_total').html( base_impuesto_total );
					
					fila.find('.costo_total').html( costo_total );
					return true;
				}else{
					//alert('Error en precio, por favor verifique.');
					//$('#cantidad').select();
					return false;
				}
			}

			function calcular_totales()
			{	
				var cantidad = 0.0;
				var subtotal = 0.0;
				var total_impuestos = 0.0;
				var total_factura = 0.0;
				$('.linea_registro').each(function()
				{
				    cantidad += parseFloat( $(this).find('.cantidad').text() );
				    subtotal += parseFloat( $(this).find('.base_impuesto').text() ) * parseFloat( $(this).find('.cantidad').text() );
				    total_impuestos += parseFloat( $(this).find('.valor_impuesto').text() ) * parseFloat( $(this).find('.cantidad').text() );
				    total_factura += parseFloat( $(this).find('.precio_total').text() );

				});
				$('#total_cantidad').text( new Intl.NumberFormat("de-DE").format( cantidad ) );

				// Subtotal (Sumatoria de base_impuestos por cantidad)
				//var valor = ;
				$('#subtotal').text( '$ ' + new Intl.NumberFormat("de-DE").format( subtotal.toFixed(2) )  );

				// Total impuestos (Sumatoria de valor_impuesto por cantidad)
				$('#total_impuestos').text( '$ ' + new Intl.NumberFormat("de-DE").format( total_impuestos.toFixed(2) ) );

				// Total factura  (Sumatoria de precio_total)
				$('#total_factura').text( '$ ' + new Intl.NumberFormat("de-DE").format( total_factura.toFixed(2) ) );
				
			}


			function setCookie(cname, cvalue, exdays) {
			  var d = new Date();
			  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
			  var expires = "expires="+d.toUTCString();
			  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
			}

			function getCookie(cname) {
			  var name = cname + "=";
			  var ca = document.cookie.split(';');
			  for(var i = 0; i < ca.length; i++) {
			    var c = ca[i];
			    while (c.charAt(0) == ' ') {
			      c = c.substring(1);
			    }
			    if (c.indexOf(name) == 0) {
			      return c.substring(name.length, c.length);
			    }
			  }
			  return "";
			}

			function checkCookie() {
			  var modo_ingreso_codigo_de_barra = getCookie("modo_ingreso_codigo_de_barra");

			  if (modo_ingreso_codigo_de_barra == "true" || modo_ingreso_codigo_de_barra == "")
			  {
		        $('#modo_ingreso').attr('checked','checked');
		        $('#modo_ingreso').val( "true" );
			  }else{
			  	$('#modo_ingreso').removeAttr('checked');
		        $('#modo_ingreso').val( "false" );
			  }
			}

			function consultar_remisiones_pendientes()
			{
				$('#div_remisiones_pendientes').hide();
				$('#listado_remisiones_pendientes').html('');
				$('#alert_listado_remisiones_seleccionadas').hide();
				$('#tabla_registros_documento').find('tbody').html( '' );

				url = '../vtas_consultar_remisiones_pendientes';

				// Si se está elaborando una factura de ventas
				if ( $('#core_tipo_transaccion_id').val() == 23 )
				{
					var inv_transaccion_id = $('#rm_tipo_transaccion_id').val();
				}

				// Si se está elaborando una Nota crédito directa (de ventas)
				if ( $('#core_tipo_transaccion_id').val() == 41 )
				{
					var inv_transaccion_id = $('#dvc_tipo_transaccion_id').val();
				}

				$.get( url, { core_tercero_id: $('#core_tercero_id').val(), inv_transaccion_id: inv_transaccion_id, lista_precios_id: $('#lista_precios_id').val(), fecha: $('#fecha').val() } )
					.done(function( data ) {
						if ( data != 'sin_registros')
						{
							$('#div_remisiones_pendientes').show( 500 );
							$('#listado_remisiones_pendientes').html( data );
							$('.td_boton').show();
		                	$('.btn_agregar_documento').show();
		                	$('#div_ingreso_registros').hide();
						}else{
							$('#div_ingreso_registros').show( 500 );
						}
						return false;
					});/**/
			}



			$("#btn_cerrar_alert").on('click', function(){
				$('#div_remisiones_pendientes').hide();

				$('#alert_listado_remisiones_seleccionadas').hide();
				$('#tabla_registros_documento').find('tbody').html( '' );
				hay_productos = 0;

				// Dependiendo del tipo de transacción ( Factura o Nota crédito directa)
				if ( $('#core_tipo_transaccion_id').val() == 23 ) // Factura
				{
					$('#tipo_transaccion').val( 'factura_directa' );
					cambiar_action_form( 'ventas' );
				}

				if ( $('#core_tipo_transaccion_id').val() == 41 ) // Nota Crédito
				{
					$('#tipo_transaccion').val( 'factura_directa' );
					cambiar_action_form( 'ventas_notas_credito_directa' );
				}	
				
				$('#div_ingreso_registros').show( 500 );

				$('#inv_producto_id').focus();
			});


			$(document).on('click', '.btn_agregar_documento', function(event) 
			{
				event.preventDefault();

				if ( $('#core_tipo_transaccion_id').val() == 23 )
				{
					$('#tipo_transaccion').val( 'factura_remision_pendiente' );

					cambiar_action_form( 'factura_remision_pendiente' );
				}

				if ( $('#core_tipo_transaccion_id').val() == 41 )
				{
					$('#tipo_transaccion').val( 'nota_devolucion_pendiente' );

					cambiar_action_form_dev( 'nota_devolucion_pendiente' );
				}

				$('#alert_listado_remisiones_seleccionadas').show();
				$(this).hide();
				$('#tabla_registros_documento').find('tbody:last').append( $(this).closest("tr") );
				hay_productos = 1;
			});
			
			function cambiar_action_form( nueva_accion )
			{
				var accion = $('#form_create').attr('action');
				var n = accion.search('ventas');

				if( n === -1 )
				{
					// No está la palabra ventas
					n = accion.search('factura_remision_pendiente');
					$('#form_create').attr('action', accion.substr(0,n) + nueva_accion );
				}else{
					$('#form_create').attr('action', accion.substr(0,n) + nueva_accion );
				}
			}
			
			function cambiar_action_form_dev( nueva_accion )
			{
				var accion = $('#form_create').attr('action');
				var n = accion.search('ventas_notas_credito_directa');

				if( n === -1 )
				{
					// No está la palabra ventas_notas_credito_directa
					n = accion.search('nota_devolucion_pendiente');
					$('#form_create').attr('action', accion.substr(0,n) + nueva_accion );
				}else{
					$('#form_create').attr('action', accion.substr(0,n) + nueva_accion );
				}
			}

			// Para las notas crédito directas (salida de inventario)
			function calcula_nuevo_saldo_a_la_fecha()
			{
				// saldo_original es la existencia_actual al consultar las existencias luego de seleccionar_producto()
				// 0 es la cantidad_original
				var nuevo_saldo = parseFloat( $('#saldo_original').val() ) + 0 - parseFloat( $('#cantidad').val() );

				$('#existencia_actual').val( nuevo_saldo );
			}
            
            function validacion_saldo_movimientos_posteriores( )
            {
            	// Se escogen los campos de la fila ingresada
				var fila = $('#linea_ingreso_default');

                var url = '../inv_validacion_saldo_movimientos_posteriores/' + $('#inv_bodega_id').val() + '/' + fila.find('.inv_producto_id').html() + '/' + $('#fecha').val() + '/' + $('#cantidad').val() + '/' + $('#existencia_actual').val() + '/salida';

                $.get( url )
                    .done( function( data ) {
                        if ( data != 0 )
                        {
                            $('#popup_alerta_danger').show();
                            $('#popup_alerta_danger').text( data );
                        }else{
							agregar_nueva_linea();
                            $('#popup_alerta_danger').hide();
                        }
                    });
            }
			


            // PARA BASCULA
			$('#cargar_datos_producto').on('click',function(event){
				event.preventDefault();

				if ( validar_requeridos() == false )
				{
					return false;
				}

				$('#div_cargando').show();
				var bascula_id = $("input[name='bascula_id']:checked").val();

				var mov = $('#inv_motivo_id').val().split('-');
				var bodega_id = $('#inv_bodega_id').val();
				var cliente_id = $('#cliente_id').val();


				var url = '../vtas_get_productos_por_facturar';

				$.get( url, { bascula_id: bascula_id, numero_linea: numero_linea, hay_productos:hay_productos, inv_motivo_id: mov[0], motivo_descripcion: $('#inv_motivo_id option:selected').text(), bodega_id:bodega_id, cliente_id: cliente_id } )
					.done(function( data ) {
						$('#ingreso_registros').find('tbody:last').append( data[0] );

						// Se calculan los totales
						calcular_totales();

						numero_linea = data[1];
						hay_productos = data[2];

						reset_linea_ingreso_default();
						$('#div_cargando').hide();
					});
				
			});
		});
	</script>
@endsection