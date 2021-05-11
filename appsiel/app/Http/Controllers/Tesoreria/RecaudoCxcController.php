<?php

namespace App\Http\Controllers\Tesoreria;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Auth;
use DB;
use View;
use Lava;
use Input;
use NumerosEnLetras;
use Form;
use Schema;


use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;

use App\Http\Controllers\Contabilidad\ContabilidadController;

// Modelos
use App\Sistema\TipoTransaccion;
use App\Sistema\Modelo;

use App\Core\Tercero;
use App\Core\EncabezadoDocumentoTransaccion;

use App\Core\Empresa;

use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoDocEncabezado;
use App\Tesoreria\TesoDocRegistro;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\TesoRecaudosLibreta;
use App\Tesoreria\TesoPlanPagosEstudiante;
use App\Tesoreria\ControlCheque;
use App\Tesoreria\TesoEntidadFinanciera;

use App\Matriculas\FacturaAuxEstudiante;

use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\Retencion;
use App\Contabilidad\RegistroRetencion;

use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;

use App\CxP\CxpMovimiento;

use App\Ventas\VtasDocEncabezado;

class RecaudoCxcController extends Controller
{
    protected $datos = [];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $id_transaccion = 32;// 32 = Recaudos de CxC

        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        $lista_campos = ModeloController::get_campos_modelo($modelo,'','create');
        $cantidad_campos = count($lista_campos);

        $tipo_transaccion = TipoTransaccion::find($id_transaccion);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$tipo_transaccion,$lista_campos,$cantidad_campos,'create');

        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => $lista_campos
                    ];

        $motivos = [''];//RecaudoCxcController::get_motivos($id_transaccion);
        $medios_recaudo = TesoMedioRecaudo::opciones_campo_select();
        $cajas = TesoCaja::opciones_campo_select();
        $cuentas_bancarias = TesoCuentaBancaria::opciones_campo_select();
        $retenciones = Retencion::opciones_campo_select();

        $terceros = [''];

        $entidades_financieras = TesoEntidadFinanciera::opciones_campo_select();

        $miga_pan = [
                ['url'=>'tesoreria?id='.Input::get('id'),'etiqueta'=>'Tesorería'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => $modelo->descripcion ],
                ['url'=>'NO','etiqueta' => 'Crear nuevo' ]
            ];

        return view('tesoreria.recaudos_cxc.create', compact( 'form_create','id_transaccion','motivos','miga_pan','medios_recaudo','cajas','cuentas_bancarias', 'terceros', 'entidades_financieras', 'retenciones' ) );
    }

    /**
     * Este método almacena el Encabezado documento de Pago creado. Este tipo de documentos no maneja líneas de registros.
     * En lugar de líneas de registros, se llena la tabla cxc_documentos_abonos donde se realaciona cada documento de pago
     * con el (los) documento(s) de cxc  
     * // Este método es llamado desde ModeloController@store
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request['creado_por'] = Auth::user()->email;
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $request->url_id_modelo );
        $doc_encabezado = $encabezado_documento->crear_nuevo( $request->all() );

        $total_abonos_cxc = $this->almacenar_registros_cxc( $request, $doc_encabezado );
        
        $this->almacenar_retenciones( $request, $doc_encabezado, $total_abonos_cxc );

        $this->almacenar_registros_efectivo( $request->lineas_registros_efectivo, $doc_encabezado );

        $this->almacenar_registros_transferencia_consignacion( $request->lineas_registros_transferencia_consignacion, $doc_encabezado );

        $this->almacenar_registros_tarjeta_debito( $request->lineas_registros_tarjeta_debito, $doc_encabezado );

        $this->almacenar_registros_tarjeta_credito( $request->lineas_registros_tarjeta_credito, $doc_encabezado );

        $this->almacenar_registros_cheques( $request->lineas_registros_cheques, $doc_encabezado );

        // se llama la vista de RecaudoCxcController@show
        return redirect( 'tesoreria/recaudos_cxc/'.$doc_encabezado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion );
    }

    /*
        Se almacenas lineas de registro por cada tabla de medios de pago enviada
    */
    public function almacenar_registros_cxc( Request $request, $doc_encabezado )
    {
        $lineas_registros = json_decode($request->lineas_registros);

        array_pop($lineas_registros);

        $total_abonos_cxc = 0;
        
        $cantidad = count($lineas_registros);
        for ($i=0; $i < $cantidad; $i++) 
        {
            $abono = (float)$lineas_registros[$i]->abono;
            $registro_documento_pendiente = CxcMovimiento::find( (int)$lineas_registros[$i]->id_doc );
            
            // Almacenar registro de abono
            $datos = ['core_tipo_transaccion_id' => $doc_encabezado->core_tipo_transaccion_id]+
                        ['core_tipo_doc_app_id' => $doc_encabezado->core_tipo_doc_app_id]+
                        ['consecutivo' => $doc_encabezado->consecutivo]+
                        ['core_empresa_id' => $doc_encabezado->core_empresa_id]+
                        ['core_tercero_id' => $doc_encabezado->core_tercero_id]+
                        ['modelo_referencia_tercero_index' => $registro_documento_pendiente->modelo_referencia_tercero_index]+
                        ['referencia_tercero_id' => $registro_documento_pendiente->referencia_tercero_id]+
                        ['fecha' => $doc_encabezado->fecha]+
                        ['doc_cxc_transacc_id' => $registro_documento_pendiente->core_tipo_transaccion_id]+
                        ['doc_cxc_tipo_doc_id' => $registro_documento_pendiente->core_tipo_doc_app_id]+
                        ['doc_cxc_consecutivo' => $registro_documento_pendiente->consecutivo]+
                        ['abono' => $abono]+
                        ['creado_por' => $doc_encabezado->creado_por];

            CxcAbono::create( $datos );

            // CONTABILIZAR
            $detalle_operacion = 'Abono factura de cliente';

            // 1.2. Para cada registro del documento, también se va actualizando el movimiento de contabilidad

            // MOVIMIENTO CREDITO: Cartera Cuenta por pagar. Cada Documento pagado puede tener cuenta por pagar distinta.
            // Del movimiento contable, Se llama al ID de la cuenta (moviento DB) afectada por el documento cxc
            $cta_x_cobrar_id = ContabMovimiento::where('core_tipo_transaccion_id',$registro_documento_pendiente->core_tipo_transaccion_id)
                                                ->where('core_tipo_doc_app_id',$registro_documento_pendiente->core_tipo_doc_app_id)
                                                ->where('consecutivo',$registro_documento_pendiente->consecutivo)
                                                ->where('core_tercero_id',$registro_documento_pendiente->core_tercero_id)
                                                ->where('valor_credito',0)
                                                ->value('contab_cuenta_id');

            if( is_null( $cta_x_cobrar_id ) )
            {
                $cta_x_cobrar_id = config('configuracion.cta_cartera_default');
            }
            
            ContabilidadController::contabilizar_registro2( array_merge( $request->all(), [ 'consecutivo' => $doc_encabezado->consecutivo ] ), $cta_x_cobrar_id, $detalle_operacion, 0, $abono);

            // Se diminuye el saldo_pendiente en el documento pendiente, si saldo_pendiente == 0 se marca como pagado
            $registro_documento_pendiente->actualizar_saldos($abono);

            $total_abonos_cxc += $abono;
        }

        return $total_abonos_cxc;
    }

    /*
        La Retencion solo se aplica sobre los Documentos de CxC abonado
    */
    public function almacenar_retenciones( $request, $doc_encabezado, $valor_base_retencion )
    {        
        $lineas_registros_retenciones = json_decode($request->lineas_registros_retenciones);

        if( is_null($lineas_registros_retenciones) )
        {
            return false;
        }

        array_pop($lineas_registros_retenciones); // Elimina ultimo elemento del array
        
        $cantidad = count($lineas_registros_retenciones);
        for ($i=0; $i < $cantidad; $i++) 
        {
            $tasa_retencion = round( (float)$lineas_registros_retenciones[$i]->valor_retencion * 100 / $valor_base_retencion, 2);
            $datos = [
                        'tipo' => 'sufrida',
                        'numero_certificado' => $lineas_registros_retenciones[$i]->numero_certificado,
                        'fecha_certificado' => $lineas_registros_retenciones[$i]->fecha_certificado,
                        'fecha_recepcion_certificado' => $lineas_registros_retenciones[$i]->fecha_recepcion_certificado,
                        'numero_doc_identidad_agente_retencion' => $lineas_registros_retenciones[$i]->numero_doc_identidad_agente_retencion,
                        'contab_retencion_id' => (int)$lineas_registros_retenciones[$i]->contab_retencion_id,
                        'valor_base_retencion' => $valor_base_retencion,
                        'tasa_retencion' => $tasa_retencion,
                        'valor' => (float)$lineas_registros_retenciones[$i]->valor_retencion,
                        'detalle' => 'Recaudo de CxC'
                    ] + $doc_encabezado->toArray();
            
            RegistroRetencion::create( $datos );

            // Contabilizar Retención
            $retencion = Retencion::find( (int)$lineas_registros_retenciones[$i]->contab_retencion_id );
            $movimiento_contable = new ContabMovimiento();
            $movimiento_contable->contabilizar_linea_registro( $datos, $retencion->cta_ventas_id, 'Recaudo de CxC', (float)$lineas_registros_retenciones[$i]->valor_retencion, 0 );
        }
    }

    public function almacenar_registros_efectivo( $json_lineas_registros, $doc_encabezado )
    {
        $teso_medio_recaudo_id = 1; // Efectivo
        $lineas_registros = json_decode( $json_lineas_registros );

        if( is_null($lineas_registros) )
        {
            return false;
        }

        array_pop($lineas_registros); // Elimina ultimo elemento del array
        
        $cantidad = count($lineas_registros);
        for ($i=0; $i < $cantidad; $i++) 
        {
            $valor_linea = (float)$lineas_registros[$i]->valor_efectivo;
            $tipo_operacion = $lineas_registros[$i]->tipo_operacion_id_efectivo;

            $datos = [
                        'teso_encabezado_id' => $doc_encabezado->id,
                        'teso_motivo_id' => (int)$lineas_registros[$i]->teso_motivo_id_efectivo,
                        'teso_medio_recaudo_id' => $teso_medio_recaudo_id,
                        'teso_caja_id' => (int)$lineas_registros[$i]->caja_id_efectivo,
                        'teso_cuenta_bancaria_id' => 0,
                        'detalle_operacion' => $tipo_operacion,
                        'valor' => $valor_linea
                    ] + $doc_encabezado->toArray();
            
            TesoDocRegistro::create( $datos );

            $datos['valor_movimiento'] = $valor_linea;
            $datos['descripcion'] = $tipo_operacion;
            TesoMovimiento::create( $datos );

            // Contabilizar DB
            $caja = TesoCaja::find( (int)$lineas_registros[$i]->caja_id_efectivo );
            $movimiento_contable = new ContabMovimiento();
            $movimiento_contable->contabilizar_linea_registro( $datos, $caja->contab_cuenta_id, $tipo_operacion, $valor_linea, 0 );

            // Contabilizar CR
            // La contabilizacion CR para Recaudo Cartera se hace en el metodo almacenar_registros_cxc()
            if ( $tipo_operacion != 'Recaudo cartera' )
            { 
                $motivo = TesoMotivo::find( (int)$lineas_registros[$i]->teso_motivo_id_efectivo );
                $movimiento_contable = new ContabMovimiento();
                $movimiento_contable->contabilizar_linea_registro( $datos, $motivo->contab_cuenta_id, $tipo_operacion, 0, $valor_linea );
            }

            $this->transacciones_adicionales( $datos, $tipo_operacion, $valor_linea );
        }
    }

    public function almacenar_registros_transferencia_consignacion( $json_lineas_registros, $doc_encabezado )
    {
        $teso_medio_recaudo_id = 4; // Banco (Consignación)
        $lineas_registros = json_decode( $json_lineas_registros );

        if( is_null($lineas_registros) )
        {
            return false;
        }

        array_pop($lineas_registros); // Elimina ultimo elemento del array
        
        $cantidad = count($lineas_registros);
        for ($i=0; $i < $cantidad; $i++) 
        {
            $valor_linea = (float)$lineas_registros[$i]->valor_transferencia_consignacion;
            $tipo_operacion = $lineas_registros[$i]->tipo_operacion_id_transferencia_consignacion;

            $datos = [
                        'teso_encabezado_id' => $doc_encabezado->id,
                        'teso_motivo_id' => (int)$lineas_registros[$i]->teso_motivo_id_transferencia_consignacion,
                        'teso_medio_recaudo_id' => $teso_medio_recaudo_id,
                        'teso_caja_id' => 0,
                        'teso_cuenta_bancaria_id' => (int)$lineas_registros[$i]->banco_id_transferencia_consignacion,
                        'detalle_operacion' => $tipo_operacion . ' Comprobante numero ' . $lineas_registros[$i]->numero_comprobante_transferencia_consignacion,
                        'valor' => $valor_linea
                    ] + $doc_encabezado->toArray();
            
            TesoDocRegistro::create( $datos );

            $datos['valor_movimiento'] = $valor_linea;
            $datos['descripcion'] = $tipo_operacion;
            $datos['documento_soporte'] = 'Comprobante numero ' . $lineas_registros[$i]->numero_comprobante_transferencia_consignacion;
            TesoMovimiento::create( $datos );

            // Contabilizar DB
            $cuenta_bancaria = TesoCuentaBancaria::find( (int)$lineas_registros[$i]->banco_id_transferencia_consignacion );
            $movimiento_contable = new ContabMovimiento();
            $movimiento_contable->contabilizar_linea_registro( $datos, $cuenta_bancaria->contab_cuenta_id, $tipo_operacion, $valor_linea, 0 );

            // Contabilizar CR
            // La contabilizacion CR para Recaudo Cartera se hace en el metodo almacenar_registros_cxc()
            if ( $tipo_operacion != 'Recaudo cartera' )
            { 
                $motivo = TesoMotivo::find( (int)$lineas_registros[$i]->teso_motivo_id_transferencia_consignacion );
                $movimiento_contable = new ContabMovimiento();
                $movimiento_contable->contabilizar_linea_registro( $datos, $motivo->contab_cuenta_id, $tipo_operacion, 0, $valor_linea );
            }

            $this->transacciones_adicionales( $datos, $tipo_operacion, $valor_linea );
        }
    }

    public function almacenar_registros_tarjeta_debito( $json_lineas_registros, $doc_encabezado )
    {
        $teso_medio_recaudo_id = 2; // Tarjeta débito
        $lineas_registros = json_decode( $json_lineas_registros );

        if( is_null($lineas_registros) )
        {
            return false;
        }

        array_pop($lineas_registros); // Elimina ultimo elemento del array
        
        $cantidad = count($lineas_registros);
        for ($i=0; $i < $cantidad; $i++) 
        {
            $valor_linea = (float)$lineas_registros[$i]->valor_tarjeta_debito;
            $tipo_operacion = $lineas_registros[$i]->tipo_operacion_id_tarjeta_debito;

            $datos = [
                        'teso_encabezado_id' => $doc_encabezado->id,
                        'teso_motivo_id' => (int)$lineas_registros[$i]->teso_motivo_id_tarjeta_debito,
                        'teso_medio_recaudo_id' => $teso_medio_recaudo_id,
                        'teso_caja_id' => 0,
                        'teso_cuenta_bancaria_id' => (int)$lineas_registros[$i]->banco_id_tarjeta_debito,
                        'detalle_operacion' => $tipo_operacion . ' Comprobante numero ' . $lineas_registros[$i]->numero_comprobante_tarjeta_debito,
                        'valor' => $valor_linea
                    ] + $doc_encabezado->toArray();
            
            TesoDocRegistro::create( $datos );

            $datos['valor_movimiento'] = $valor_linea;
            $datos['descripcion'] = $tipo_operacion;
            $datos['documento_soporte'] = 'Comprobante numero ' . $lineas_registros[$i]->numero_comprobante_tarjeta_debito;
            TesoMovimiento::create( $datos );

            // Contabilizar DB
            $cuenta_bancaria = TesoCuentaBancaria::find( (int)$lineas_registros[$i]->banco_id_tarjeta_debito );
            $movimiento_contable = new ContabMovimiento();
            $movimiento_contable->contabilizar_linea_registro( $datos, $cuenta_bancaria->contab_cuenta_id, $tipo_operacion, $valor_linea, 0 );

            // Contabilizar CR
            // La contabilizacion CR para Recaudo Cartera se hace en el metodo almacenar_registros_cxc()
            if ( $tipo_operacion != 'Recaudo cartera' )
            { 
                $motivo = TesoMotivo::find( (int)$lineas_registros[$i]->teso_motivo_id_tarjeta_debito );
                $movimiento_contable = new ContabMovimiento();
                $movimiento_contable->contabilizar_linea_registro( $datos, $motivo->contab_cuenta_id, $tipo_operacion, 0, $valor_linea );
            }

            $this->transacciones_adicionales( $datos, $tipo_operacion, $valor_linea );
        }
    }

    public function almacenar_registros_tarjeta_credito( $json_lineas_registros, $doc_encabezado )
    {
        $teso_medio_recaudo_id = 3; // Tarjeta crédito
        $lineas_registros = json_decode( $json_lineas_registros );

        if( is_null($lineas_registros) )
        {
            return false;
        }

        array_pop($lineas_registros); // Elimina ultimo elemento del array
        
        $cantidad = count($lineas_registros);
        for ($i=0; $i < $cantidad; $i++) 
        {
            $valor_linea = (float)$lineas_registros[$i]->valor_tarjeta_credito;
            $tipo_operacion = $lineas_registros[$i]->tipo_operacion_id_tarjeta_credito;

            $datos = [
                        'teso_encabezado_id' => $doc_encabezado->id,
                        'teso_motivo_id' => (int)$lineas_registros[$i]->teso_motivo_id_tarjeta_credito,
                        'teso_medio_recaudo_id' => $teso_medio_recaudo_id,
                        'teso_caja_id' => 0,
                        'teso_cuenta_bancaria_id' => (int)$lineas_registros[$i]->banco_id_tarjeta_credito,
                        'detalle_operacion' => $tipo_operacion . ' Comprobante numero ' . $lineas_registros[$i]->numero_comprobante_tarjeta_credito,
                        'valor' => $valor_linea
                    ] + $doc_encabezado->toArray();
            
            TesoDocRegistro::create( $datos );

            $datos['valor_movimiento'] = $valor_linea;
            $datos['descripcion'] = $tipo_operacion;
            $datos['documento_soporte'] = 'Comprobante numero ' . $lineas_registros[$i]->numero_comprobante_tarjeta_credito;
            TesoMovimiento::create( $datos );

            // Contabilizar DB
            $cuenta_bancaria = TesoCuentaBancaria::find( (int)$lineas_registros[$i]->banco_id_tarjeta_credito );
            $movimiento_contable = new ContabMovimiento();
            $movimiento_contable->contabilizar_linea_registro( $datos, $cuenta_bancaria->contab_cuenta_id, $tipo_operacion, $valor_linea, 0 );

            // Contabilizar CR
            // La contabilizacion CR para Recaudo Cartera se hace en el metodo almacenar_registros_cxc()
            if ( $tipo_operacion != 'Recaudo cartera' )
            { 
                $motivo = TesoMotivo::find( (int)$lineas_registros[$i]->teso_motivo_id_tarjeta_credito );
                $movimiento_contable = new ContabMovimiento();
                $movimiento_contable->contabilizar_linea_registro( $datos, $motivo->contab_cuenta_id, $tipo_operacion, 0, $valor_linea );
            }

            $this->transacciones_adicionales( $datos, $tipo_operacion, $valor_linea );
        }
    }

    public function almacenar_registros_cheques( $json_lineas_registros, $doc_encabezado )
    {
        $teso_medio_recaudo_id = 7; // Cheque de tercero
        $lineas_registros = json_decode( $json_lineas_registros );

        if( is_null($lineas_registros) )
        {
            return false;
        }

        array_pop($lineas_registros); // Elimina ultimo elemento del array
        
        $cantidad = count($lineas_registros);
        for ($i=0; $i < $cantidad; $i++) 
        {
            $valor_linea = (float)$lineas_registros[$i]->valor_cheque;
            $tipo_operacion = $lineas_registros[$i]->tipo_operacion_id_cheque;

            $datos = [
                        'fuente' => 'de_tercero',
                        'tercero_id' => $doc_encabezado->core_tercero_id,
                        'fecha_emision' => $lineas_registros[$i]->fecha_emision,
                        'fecha_cobro' => $lineas_registros[$i]->fecha_cobro,
                        'numero_cheque' => $lineas_registros[$i]->numero_cheque,
                        'referencia_cheque' => $lineas_registros[$i]->referencia_cheque,
                        'entidad_financiera_id' => $lineas_registros[$i]->entidad_financiera_id,
                        'valor' => $valor_linea,
                        'core_tipo_transaccion_id_origen' => $doc_encabezado->core_tipo_transaccion_id,
                        'core_tipo_doc_app_id_origen' => $doc_encabezado->core_tipo_doc_app_id,
                        'teso_caja_id' => 1,//$lineas_registros[$i]->caja_id_cheque,
                        'estado' => 'Recibido'
                    ] + $doc_encabezado->toArray();

            ControlCheque::create( $datos );

            $datos['valor_movimiento'] = $valor_linea;
            $datos['descripcion'] = $tipo_operacion;
            $datos['teso_motivo_id'] = (int)$lineas_registros[$i]->teso_motivo_id_cheque;
            $datos['documento_soporte'] = 'Cheque número ' . $lineas_registros[$i]->numero_cheque;
            TesoMovimiento::create( $datos );

            // Contabilizar DB
            $caja = TesoCaja::find( 1 );//(int)$lineas_registros[$i]->caja_id_cheque );
            $movimiento_contable = new ContabMovimiento();
            $movimiento_contable->contabilizar_linea_registro( $datos, $caja->contab_cuenta_id, $tipo_operacion, $valor_linea, 0 );

            // Contabilizar CR
            // La contabilizacion CR para Recaudo Cartera se hace en el metodo almacenar_registros_cxc()
            if ( $tipo_operacion != 'Recaudo cartera' )
            { 
                $motivo = TesoMotivo::find( (int)$lineas_registros[$i]->teso_motivo_id_cheque );
                $movimiento_contable = new ContabMovimiento();
                $movimiento_contable->contabilizar_linea_registro( $datos, $motivo->contab_cuenta_id, $tipo_operacion, 0, $valor_linea );
            }

            $this->transacciones_adicionales( $datos, $tipo_operacion, $valor_linea );
        }
    }

    public function transacciones_adicionales( $datos, $tipo_operacion, $valor )
    {

        // Solo los anticipos de clientes se guardan en el movimiento de cartera (CxC)
        if ( $tipo_operacion == 'Anticipo' )
        {
            $datos['valor_documento'] = $valor * -1;
            $datos['valor_pagado'] = 0;
            $datos['saldo_pendiente'] = $valor * -1;
            $datos['fecha_vencimiento'] = $datos['fecha'];
            $datos['estado'] = 'Pendiente';
            CxcMovimiento::create( $datos );
        }
 
        // Generar CxP porque se utilizó dinero de un agente externo (banco, coopertaiva, tarjeta de crédito).
        if ( $tipo_operacion == 'Prestamo financiero' )
        {
            $datos['valor_documento'] = $valor;
            $datos['valor_pagado'] = 0;
            $datos['saldo_pendiente'] = $valor;
            $datos['fecha_vencimiento'] = $datos['fecha'];
            $datos['estado'] = 'Pendiente';
            CxpMovimiento::create( $datos );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $modelo = Modelo::find(Input::get('id_modelo'));

        $reg_anterior = TesoDocEncabezado::where('id', '<', $id)->where('core_empresa_id', Auth::user()->empresa_id)->where('core_tipo_transaccion_id', 8)->max('id');
        $reg_siguiente = TesoDocEncabezado::where('id', '>', $id)->where('core_empresa_id', Auth::user()->empresa_id)->where('core_tipo_transaccion_id', 8)->min('id');

        $doc_encabezado = TesoDocEncabezado::get_registro_impresion( $id );

        // Documentos pagados
        $doc_pagados = CxcAbono::get_documentos_abonados( $doc_encabezado );

        $empresa = Empresa::find( $doc_encabezado->core_empresa_id );

        $registros_contabilidad = TransaccionController::get_registros_contabilidad( $doc_encabezado );

        $documento_vista = View::make( 'tesoreria.recaudos_cxc.documento_vista', compact('doc_encabezado', 'doc_pagados', 'empresa', 'registros_contabilidad' ) )->render();
        $id_transaccion = $doc_encabezado->core_tipo_transaccion_id;

        $miga_pan = [
                        ['url'=>'tesoreria?id='.Input::get('id'),'etiqueta'=>'Tesorería'],
                        ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=> $modelo->descripcion ],
                        ['url'=>'NO','etiqueta' => $doc_encabezado->documento_transaccion_prefijo_consecutivo]
                    ];
        
        return view( 'tesoreria.recaudos_cxc.show', compact( 'id', 'reg_anterior', 'reg_siguiente', 'documento_vista', 'id_transaccion', 'miga_pan','doc_encabezado') );
    }


    public function imprimir($id)
    {
        $doc_encabezado = TesoDocEncabezado::get_registro_impresion( $id );

        // Documentos pagados
        $doc_pagados = CxcAbono::get_documentos_abonados( $doc_encabezado );

        $empresa = Empresa::find( $doc_encabezado->core_empresa_id );

        $registros_contabilidad = [];//TransaccionController::get_registros_contabilidad( $doc_encabezado );

        $elaboro = $doc_encabezado->creado_por;
       
        if( Input::get('formato_impresion_id') == 'estandar'){
            $documento_vista = View::make( 'tesoreria.recaudos_cxc.documento_imprimir', compact('doc_encabezado', 'doc_pagados', 'empresa', 'registros_contabilidad', 'elaboro' ) )->render();
        }
        if( Input::get('formato_impresion_id') == 'estandar2'){
            $documento_vista = View::make( 'tesoreria.recaudos_cxc.documento_imprimir2', compact('doc_encabezado', 'doc_pagados', 'empresa', 'registros_contabilidad', 'elaboro' ) )->render();
        }
        
        
        // Se prepara el PDF
        $orientacion='portrait';
        $tam_hoja = 'Letter';//array(0,0,50,800);//'A4';

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML( $documento_vista );//->setPaper( $tam_hoja, $orientacion );

        return $pdf->stream( $doc_encabezado->documento_transaccion_descripcion.' - '.$doc_encabezado->documento_transaccion_prefijo_consecutivo.'.pdf');
    }


    public function get_documentos_pendientes_cxc()
    {                
        $operador = '=';
        $cadena = Input::get('core_tercero_id');    

        $movimiento = CxcMovimiento::get_documentos_referencia_tercero( $operador, $cadena );

        $vista = View::make( 'cxc.incluir.documentos_pendientes', compact('movimiento') )->render();
   
        return $vista;
    }


    public function ajax_get_terceros($tercero_id)
    {
        $registros = Tercero::where('estado','Activo')
                            ->get();
            $opciones='<option value=""></option>';                
        foreach ($registros as $campo) {
            if ( $campo->id == $tercero_id ) {
                $selected = ' selected="selected"';
            }else{
                $selected = '';
            }
            $opciones.= '<option value="'.$campo->id.'"'.$selected.'>'.$campo->descripcion.'</option>';
        }
        return $opciones;
    }

    /*
        Proceso de eliminar RECAUDO DE CXC
        Se eliminan los registros de:
            - cxc_abonos y su movimiento en contab_movimientos
            - teso_movimientos y su contabilidad. Además se actualiza el estado a Anulado en vtas_doc_registros y vtas_doc_encabezados
    */
    public static function anular_recaudo_cxc($id)
    {        
        $recaudo = TesoDocEncabezado::find( $id );

        $array_wheres = ['core_empresa_id'=>$recaudo->core_empresa_id, 
            'core_tipo_transaccion_id' => $recaudo->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $recaudo->core_tipo_doc_app_id,
            'consecutivo' => $recaudo->consecutivo];

        // >>> Validaciones inciales

        // Está en un documento cruce de cartera?
        $cantidad = CxcAbono::where($array_wheres)
                            ->where('doc_cruce_transacc_id','<>',0)
                            ->count();

        if($cantidad != 0)
        {
            return redirect( 'tesoreria/recaudos_cxc/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('mensaje_error','Recaudo NO puede ser anulado. Está en documento cruce de cartera.');
        }

        // Se reversan los abonos hecho por este documento de recaudo: aumenta el saldo_pendiente en el documento pendiente
        $documentos_abonados = CxcAbono::get_documentos_abonados( $recaudo );

        foreach ( $documentos_abonados as $linea )
        {
            $documento_cxc_pendiente = CxcMovimiento::where('core_tipo_transaccion_id',$linea->doc_cxc_transacc_id)
                                                        ->where('core_tipo_doc_app_id',$linea->doc_cxc_tipo_doc_id)
                                                        ->where('consecutivo',$linea->doc_cxc_consecutivo)
                                                        ->get()
                                                        ->first();
            
            if ( $documento_cxc_pendiente->estado == 'Pagado' )
            {
                // Se halla el total de todos los abonos que halla tenido el documento de cxc abonado (incluido el abono realizado por este recaudo)
                $valor_abonos_aplicados = CxcAbono::where('doc_cxc_transacc_id',$linea->doc_cxc_transacc_id)
                                                ->where('doc_cxc_tipo_doc_id',$linea->doc_cxc_tipo_doc_id)
                                                ->where('doc_cxc_consecutivo',$linea->doc_cxc_consecutivo)
                                                ->where('referencia_tercero_id',$linea->referencia_tercero_id)
                                                ->sum('abono');


                $nuevo_saldo_pendiente = $documento_cxc_pendiente->valor_documento - $valor_abonos_aplicados + $linea->abono;
                
                $nuevo_valor_pagado = $valor_abonos_aplicados - $linea->abono; // el valor_abonos_aplicados es como mínimo el valor de $linea->abono

            }else{

                $nuevo_saldo_pendiente = $documento_cxc_pendiente->saldo_pendiente + $linea->abono;

                $nuevo_valor_pagado = $documento_cxc_pendiente->valor_pagado - $linea->abono;
            }

            // Actualizar registro del documento pendiente
            $documento_cxc_pendiente->valor_pagado = $nuevo_valor_pagado;
            $documento_cxc_pendiente->saldo_pendiente = $nuevo_saldo_pendiente;
            $documento_cxc_pendiente->save();

            // Se elimina el abono
            $linea->delete();
        }

        // Borrar movimiento de tesorería del recaudo y su contabilidad. Además actualizar estado del encabezado del documento de recaudo.
        TesoMovimiento::where('core_tipo_transaccion_id',$recaudo->core_tipo_transaccion_id)->where('core_tipo_doc_app_id',$recaudo->core_tipo_doc_app_id)->where('consecutivo',$recaudo->consecutivo)->delete();

        // Borrar movimiento contable generado por el documento de pago ( DB: Caja/Banco, CR: CxC )
        ContabMovimiento::where('core_tipo_transaccion_id',$recaudo->core_tipo_transaccion_id)->where('core_tipo_doc_app_id',$recaudo->core_tipo_doc_app_id)->where('consecutivo',$recaudo->consecutivo)->delete();

        // Si es el Recaudo de una o varias facturas asociadas a un registro de Plan de Pagos de una libreta de pagos
        $recaudos_libreta = TesoRecaudosLibreta::where([
                                                    ['core_tipo_transaccion_id','=',$recaudo->core_tipo_transaccion_id],
                                                    ['core_tipo_doc_app_id','=',$recaudo->core_tipo_doc_app_id],
                                                    ['consecutivo','=',$recaudo->consecutivo]
                                                ])->get();
        foreach( $recaudos_libreta AS $recaudo_libreta )
        {
            $recaudo_libreta->anular();
        }

        // Marcar como anulado el encabezado
        $recaudo->update(['estado'=>'Anulado']);

        $this->restablecer_cheque( $recaudo );

        return redirect( 'tesoreria/recaudos_cxc/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('flash_message','Recaudo de CxC ANULADO correctamente.');
        
    }



    public function restablecer_cheque( $recaudo )
    {
        $cheque_recibido = ControlCheque::where([
                                                    'core_tipo_transaccion_id' => $recaudo->core_tipo_transaccion_id,
                                                    'core_tipo_doc_app_id' => $recaudo->core_tipo_doc_app_id,
                                                    'consecutivo' => $recaudo->consecutivo
                                                ])
                                        ->get()
                                        ->first();

        if ( !is_null($cheque_recibido) )
        {
            $cheque_recibido->estado = 'Anulado';
            $cheque_recibido->save();
        }
    }


    // ESTE METODO SE LLAMA DESDE LIBRETAPAGOCONTROLLER
    public function almacenar( Request $request )
    {
        // Crear Documento de tesorería (RECAUDO)
        $doc_encabezado = RecaudoCxcController::crear_encabezado_documento($request, $request->url_id_modelo);

        // NOTA: No se crean líneas de registros (teso_doc_registros) para este tipo de documentos

        $lineas_registros = json_decode($request->lineas_registros);

        array_pop($lineas_registros); 

        $valor_total = 0;
        
        $cantidad = count($lineas_registros);
        for ($i=0; $i < $cantidad; $i++) 
        {
            $abono = (float)$lineas_registros[$i]->abono;
            $registro_documento_pendiente = CxcMovimiento::find( $lineas_registros[$i]->id_doc );
            
            // Almacenar registro de abono
            $datos = ['core_tipo_transaccion_id' => $doc_encabezado->core_tipo_transaccion_id]+
                        ['core_tipo_doc_app_id' => $doc_encabezado->core_tipo_doc_app_id]+
                        ['consecutivo' => $doc_encabezado->consecutivo]+
                        ['core_empresa_id' => $doc_encabezado->core_empresa_id]+
                        ['core_tercero_id' => $doc_encabezado->core_tercero_id]+
                        ['modelo_referencia_tercero_index' => $registro_documento_pendiente->modelo_referencia_tercero_index]+
                        ['referencia_tercero_id' => $registro_documento_pendiente->referencia_tercero_id]+
                        ['fecha' => $doc_encabezado->fecha]+
                        ['doc_cxc_transacc_id' => $registro_documento_pendiente->core_tipo_transaccion_id]+
                        ['doc_cxc_tipo_doc_id' => $registro_documento_pendiente->core_tipo_doc_app_id]+
                        ['doc_cxc_consecutivo' => $registro_documento_pendiente->consecutivo]+
                        ['abono' => $abono]+
                        ['creado_por' => $doc_encabezado->creado_por];

            CxcAbono::create( $datos );

            // CONTABILIZAR
            $detalle_operacion = 'Abono factura de cliente';

            // 1.2. Para cada registro del documento, también se va actualizando el movimiento de contabilidad
            
            // Para el movimiento contable se guarda en detalle_operacion el detalle del encabezado del documento

            if ( $detalle_operacion == '') {
              $detalle_operacion = $request->descripcion;
            }

            // MOVIMIENTO CREDITO: Cartera Cuenta por pagar. Cada Documento pagado puede tener cuenta por pagar distinta.
            // Del movimiento contable, Se llama al ID de la cuenta (moviento DB) afectada por el documento cxc
            $cta_x_cobrar_id = ContabMovimiento::where('core_tipo_transaccion_id',$registro_documento_pendiente->core_tipo_transaccion_id)
                                                ->where('core_tipo_doc_app_id',$registro_documento_pendiente->core_tipo_doc_app_id)
                                                ->where('consecutivo',$registro_documento_pendiente->consecutivo)
                                                ->where('core_tercero_id',$registro_documento_pendiente->core_tercero_id)
                                                ->where('valor_credito',0)
                                                ->value('contab_cuenta_id');

            if( is_null( $cta_x_cobrar_id ) )
            {
                $cta_x_cobrar_id = config('configuracion.cta_cartera_default');
            }
            
            ContabilidadController::contabilizar_registro2( array_merge( $request->all(), [ 'consecutivo' => $doc_encabezado->consecutivo ] ), $cta_x_cobrar_id, $detalle_operacion, 0, $abono);


            // Se diminuye el saldo_pendiente en el documento pendiente, si saldo_pendiente == 0 se marca como pagado
            CxcMovimiento::actualizar_valores_doc_cxc( $registro_documento_pendiente, $abono);

            $valor_total += $abono;

            // Cuando NO se esta haciendo un Recaudo desde Libreta de Pagos
            if ( Schema::hasTable( 'sga_facturas_estudiantes' ) && !isset( $request->vtas_doc_encabezado_id ) )
            {
                $this->registrar_recaudo_cartera_estudiante( $doc_encabezado, $registro_documento_pendiente, $abono );
            }

        } // FIN FOR CADA LINEA DEL PAGO

        // Actualizar total del documento en el encabezado
        $doc_encabezado->valor_total = $valor_total;
        $doc_encabezado->save();

        // UN SOLO MOVIMIENTO DE TESORERIA y un solo movimiento contable de (DB) CAJA O BANCO
        $datos = array_merge( $request->all(), [ 'consecutivo' => $doc_encabezado->consecutivo ] );

        // Datos la caja o el la cuenta bancaria
        // Tambien se asigna el ID de la cuenta contable para el movimiento DEBITO
        $vec_3 = explode("-", $request->teso_medio_recaudo_id);
        $teso_medio_recaudo_id = $vec_3[0];
        if ( $vec_3[1] == 'Tarjeta bancaria' ) {
            $banco = TesoCuentaBancaria::find($request->teso_cuenta_bancaria_id);
            $contab_cuenta_id = $banco->contab_cuenta_id;
            $teso_caja_id = 0;
            $datos['teso_caja_id'] = 0;
            $teso_cuenta_bancaria_id = $banco->id;
        }else{
            $caja = TesoCaja::find($request->teso_caja_id);
            $contab_cuenta_id = $caja->contab_cuenta_id;
            $teso_caja_id = $caja->id;
            $teso_cuenta_bancaria_id = 0;
            $datos['teso_cuenta_bancaria_id'] = 0;
        }

        // Movimiento de entrada
        $valor_movimiento = $valor_total;

        $teso_motivo_id = TesoMotivo::where('movimiento','entrada')->get()->first()->id;
        
        TesoMovimiento::create( $datos + 
                                    [ 'teso_motivo_id' => $teso_motivo_id] + 
                                    [ 'teso_caja_id' => $teso_caja_id] + 
                                    [ 'teso_cuenta_bancaria_id' => $teso_cuenta_bancaria_id] + 
                                    [ 'valor_movimiento' => $valor_movimiento] +
                                    [ 'estado' => 'Activo' ]
                                );

        // MOVIMIENTO CREDITO (CAJA/BANCO)
        ContabilidadController::contabilizar_registro2( $datos, $contab_cuenta_id, $detalle_operacion, $valor_total, 0);

        return $doc_encabezado;
    }


    /*
        Crea el encabezado de un documento
        Devuelve LA INSTANCIA del documento creado
    */
    public static function crear_encabezado_documento(Request $request, $modelo_id)
    {
        $request['creado_por'] = Auth::user()->email;

        $encabezado_documento = new EncabezadoDocumentoTransaccion( $modelo_id );
        return $encabezado_documento->crear_nuevo( $request->all() );
    }

    // Por cada linea del Recaudo de CxC
    public function registrar_recaudo_cartera_estudiante( $doc_encabezado_recaudo, $registro_cxc_pendiente, $abono  )
    {
        $factura = VtasDocEncabezado::where([
                                                [ 'core_tipo_transaccion_id','=', $registro_cxc_pendiente->core_tipo_transaccion_id ],
                                                [ 'core_tipo_doc_app_id','=', $registro_cxc_pendiente->core_tipo_doc_app_id ],
                                                [ 'consecutivo','=', $registro_cxc_pendiente->consecutivo ]
                                            ])->get()->first();

        if ( is_null($factura) )
        {
            return false;
        }

        $aux_factura = FacturaAuxEstudiante::where('vtas_doc_encabezado_id', $factura->id )->get()->first();

        if ( is_null($aux_factura) )
        {
            return false;
        }

        $recaudo = TesoRecaudosLibreta::create( [
                                    'core_tipo_transaccion_id' => (int)$doc_encabezado_recaudo->core_tipo_transaccion_id,
                                    'core_tipo_doc_app_id' => (int)$doc_encabezado_recaudo->core_tipo_doc_app_id,
                                    'consecutivo' => $doc_encabezado_recaudo->consecutivo,
                                    'id_libreta' => $aux_factura->cartera_estudiante->id_libreta,
                                    'id_cartera' => $aux_factura->cartera_estudiante_id,
                                    'concepto' => $aux_factura->cartera_estudiante->inv_producto_id,
                                    'fecha_recaudo' => $doc_encabezado_recaudo->fecha,
                                    'teso_medio_recaudo_id' => $doc_encabezado_recaudo->teso_medio_recaudo_id,
                                    'cantidad_cuotas' => 1,
                                    'valor_recaudo' => $abono,
                                    'creado_por' => $doc_encabezado_recaudo->creado_por
                                ] );

        $recaudo->registro_cartera_estudiante->sumar_abono_registro_cartera_estudiante( $abono );
        $recaudo->libreta->actualizar_estado();
    }
}