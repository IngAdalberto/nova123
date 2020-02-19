<?php

namespace App\Http\Controllers\Ventas;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;

use Auth;
use DB;
use Input;
use Storage;

use App\Sistema\Modelo;
use App\Sistema\Campo;
use App\Core\Tercero;

use App\Ventas\Cliente;
use App\Ventas\Vendedor;
use App\Ventas\VtasMovimiento;
use App\Ventas\VtasDocEncabezado;

class ClienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $general = new ModeloController();

        return $general->create();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Almacenar datos básicos (Tercero)
        $descripcion = $request->all()['apellido1']." ".$request->all()['apellido2']." ".$request->all()['nombre1']." ".$request->all()['otros_nombres'];

        if ( $request->all()['razon_social'] != '' )
        {
            $descripcion = $request->all()['razon_social'];
        }

        $tercero = new Tercero;
        $tercero->fill( array_merge( $request->all(), ['descripcion' => $descripcion] ) );
        $tercero->save();
        
        // Datos del Cliente
        $Cliente = new Cliente;
        $Cliente->fill( array_merge( $request->all(), ['core_tercero_id' => $tercero->id] ) );
        $Cliente->save();

        return redirect( 'vtas_clientes/'.$Cliente->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo )->with( 'flash_message','Registro CREADO correctamente.' );
    }




    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $modelo = Modelo::find( Input::get('id_modelo') );
        
        $registro = Cliente::find($id);
        $reg_anterior = app($modelo->name_space)->where('id', '<', $registro->id)->max('id');
        $reg_siguiente = app($modelo->name_space)->where('id', '>', $registro->id)->min('id');

        // Se obtienen los campos asociados a ese modelo
        $lista_campos = $modelo->campos()->orderBy('orden')->get()->toArray();

        // Formatear-asignar el valor correspondiente del registro del modelo
        // 1ro. Para los campos del modelo Cliente
        $lista_campos = Campo::asignar_valores_registro( $lista_campos, $registro );
        // 2do. Para los campos del modelo Tercero
        $tercero = Tercero::find($registro->core_tercero_id);
        $lista_campos = Campo::asignar_valores_registro( $lista_campos, $tercero );
        
        $lista_campos = $this->cambiar_opciones_campo_vendedor( $lista_campos );

        //dd( $lista_campos );

        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => $lista_campos
                    ];

        $descripcion = $tercero->apellido1." ".$tercero->apellido2." ".$tercero->nombre1." ".$tercero->otros_nombres;
        $miga_pan = [
                        ['url'=>'ventas?id='.Input::get('id'),'etiqueta'=>'Ventas'],
                        ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=> $modelo->descripcion],
                        ['url'=>'NO','etiqueta'=> $descripcion ]
                    ];

        $url_crear = '';
        $url_edit = '';

        // Se le asigna a cada variable url, su valor en el modelo correspondiente
        $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
        if ($modelo->url_crear!='') {
            $url_crear = $modelo->url_crear.$variables_url;    
        }
        if ($modelo->url_edit!='') {
            $url_edit = $modelo->url_edit.$variables_url;
        }

        $tabla = '';

        return view('ventas.clientes.show',compact('form_create','miga_pan','registro','url_crear','url_edit','reg_anterior','reg_siguiente','tabla') );
    }

    public function cambiar_opciones_campo_vendedor( $lista_campos )
    {
        $cantidad_campos = count($lista_campos);
        for ($i=0; $i < $cantidad_campos; $i++) 
        {
            if ( $lista_campos[$i]['name'] == 'vendedor_id' ) 
            {
                
                $raw = 'CONCAT(core_terceros.apellido1, " ",core_terceros.apellido2, " ",core_terceros.nombre1, " ",core_terceros.otros_nombres) AS descripcion';

                $opciones = Vendedor::leftJoin('core_terceros','core_terceros.id','=','vtas_vendedores.core_tercero_id')
                            ->select('vtas_vendedores.id',DB::raw($raw))
                            ->get();

                $vec = '{';
                $es_el_primero = true;
                foreach ($opciones as $opcion)
                {
                    if ( $es_el_primero ) 
                    {
                        $vec .= '"'.$opcion->id.'":"'.$opcion->descripcion.'"';
                        $es_el_primero = false;
                    }else{
                        $vec .= ',"'.$opcion->id.'":"'.$opcion->descripcion.'"';
                    }
                }

                $vec .= '}';

                $lista_campos[$i]['opciones'] = $vec;
            }
        }
        return $lista_campos;        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $general = new ModeloController();

        $modelo = Modelo::find(Input::get('id_modelo'));

        // Se obtiene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

        $lista_campos = $general->get_campos_modelo($modelo,$registro,'edit');

        $tercero = Tercero::find($registro->core_tercero_id);
        $registro->nombre1 = $tercero->nombre1;
        $registro->otros_nombres = $tercero->otros_nombres;
        $registro->apellido1 = $tercero->apellido1;
        $registro->apellido2 = $tercero->apellido2;
        $registro->id_tipo_documento_id = $tercero->id_tipo_documento_id;
        $registro->numero_identificacion = $tercero->numero_identificacion;
        $registro->direccion1 = $tercero->direccion1;
        $registro->telefono1 = $tercero->telefono1;
        $registro->codigo_ciudad = $tercero->codigo_ciudad;
        $registro->tipo = $tercero->tipo;
        $registro->razon_social = $tercero->razon_social;
        $registro->email = $tercero->email;
        
        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => $lista_campos
                    ];

        $url_action = 'web/'.$id;
        if ($modelo->url_form_create != '') {
            $url_action = $modelo->url_form_create.'/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
        }

        $descripcion = $tercero->apellido1." ".$tercero->apellido2." ".$tercero->nombre1." ".$tercero->otros_nombres;
        $miga_pan = [
                        ['url'=>'ventas?id='.Input::get('id'),'etiqueta'=>'Ventas'],
                        ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=> $modelo->descripcion],
                        ['url'=>'NO','etiqueta'=> $descripcion." > Modificar" ]
                    ];

        $archivo_js = app($modelo->name_space)->archivo_js;

        return view('layouts.edit',compact('form_create','miga_pan','registro','archivo_js','url_action')); 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $modelo = Modelo::find($request->url_id_modelo);

        // Se obtinene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);
        $registro->fill( $request->all() );
        $registro->save();

        // Actualizar datos del Tercero
        $descripcion = $request->all()['apellido1']." ".$request->all()['apellido2']." ".$request->all()['nombre1']." ".$request->all()['otros_nombres'];

        if ( $request->all()['razon_social'] != '' )
        {
            $descripcion = $request->all()['razon_social'];
        }
        
        $tercero = Tercero::find( $registro->core_tercero_id );
        $tercero->fill( array_merge( $request->all(), ['descripcion' => $descripcion] ) );
        $tercero->save();

        return redirect('vtas_clientes/'.$id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Registro MODIFICADO correctamente.');
    }


    
    public function eliminar_cliente($id)
    {
        // Verificar si cliente está en movimiento de ventas
        $movimiento = VtasMovimiento::where('cliente_id',$id)->get()->toArray();

        if( !empty($movimiento) )
        {
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Cliente no se puede eliminar tiene movimientos de ventas.');
        }

        // Verificar si cliente está en encabezados de ventas
        $docs = VtasDocEncabezado::where('cliente_id',$id)->get()->toArray();

        if( !empty($docs) )
        {
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Cliente no se puede eliminar tiene documentos de ventas.');
        }

        Cliente::find($id)->delete();

        return redirect('web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('flash_message','Cliente ELIMINADO correctamente.');
    }
}

// cardona cha