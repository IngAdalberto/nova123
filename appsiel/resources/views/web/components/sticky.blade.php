@extends('web.templates.main')

@section('style')
<style>
    .card-body {
        padding: 0 !important;
        overflow: hidden;
        width: 100%;
        height: 100%;
    }

    #wrapper {
        overflow-y: scroll;
        overflow-x: hidden;
        width: 30%;
        height: 100vh;
        margin-right: 0;
    }

    .list-group-item {
        background-color: transparent;
        font-size: 16px;
        list-style: none;
    }

    .list-group-item:hover {
        background-color: #3d6983;
        color: white;
        cursor: pointer;
    }

    .widgets {
        overflow-y: scroll;
        width: 70%;
        height: 100vh;
    }

    .widgets img {
        width: 100%;
        object-fit: cover;
        height: 72.5vh;
        max-width: 100%;
    }

    .widgets .card-body {
        position: relative;
    }

    .activo {}

    .contenido {
        display: flex;
        justify-content: space-between;
        padding: 5px;
        margin-top: 10px;
        border: 1px solid #3d6983;
        border-radius: 5px;
    }

    .contenido img {
        width: 80px;
        height: 80px;
        object-fit: cover;
    }

    .descripcion {
        padding: 5px;
    }

    .descripcion h5 {
        color: black;
        font-size: 16px;
    }

    .add {
        margin-top: 20px;
    }

    .add a {
        color: #1c85c4;
    }
</style>

@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12" style="text-align: center; font-weight: bold; padding: 15px;">
            <h4>.:: En ésta Sección: Sticky ::.</h4>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body d-flex justify-content-between flex-wrap">
        <div id="wrapper">
            <h4 class="column-title" style="padding: 10px;">Menú Sticky</h4>
            @if($sticky != null)
            <div class="descripcion" style="text-align: center; margin-top: 20px;">
                <h5 class="titulo">Sticky Nro. {{$sticky->id}}</h5>
                <p>Ancho de botón (px): {{$sticky->ancho_boton}}</p>
                <p>Posición del Sticky: {{$sticky->posicion}}</p>
            </div>
            <div class="col-md-12 add d-flex">
                <div class="col-md-4">
                    <a href="{{url('sticky/destroy').'/'.$sticky->id.$variables_url}}"
                        class="btn btn-primary waves-effect btn-block btn-sm" style="color: white; font-weight: bold;">
                        Eliminar</a>
                </div>
                <div class="col-md-4 justify-content-end">
                    <a data-toggle="modal" data-target="#Modaledit"
                        class="btn btn-primary waves-effect btn-block btn-sm"
                        style="color: white; font-weight: bold; cursor: pointer;"> Editar</a>
                </div>
                <div class="col-md-4 justify-content-end">
                    <a data-toggle="modal" data-target="#Modalboton"
                        class="btn btn-primary waves-effect btn-block btn-sm"
                        style="color: white; font-weight: bold; cursor: pointer;"><i class='fa fa-plus'></i> Botón</a>
                </div>
            </div>
            <div class="col-md-12" style="margin-top: 20px;">
                <h4>Listado de botones en el sticky</h4>
                @if(count($sticky->stickybotons)>0)
                @foreach($sticky->stickybotons as $b)
                <p>
                    Color: <i style="background-color: {{$b->color}}; color: {{$b->color}}">Muestra</i><br>
                    Ícono: @if($b->icono!=null) <i class="fa fa-{{$b->icono}}"></i> @else --- @endif<br>
                    Texto: {{$b->texto}}<br>
                    Enlace: {{$b->enlace}}<br>
                </p>
                <a class="btn btn-danger waves-effect btn-sm"
                    href="{{url('sticky/destroy').'/'.$b->id.'/boton'.$variables_url}}">Eliminar Botón</a>
                <hr>
                @endforeach
                @else
                <h5 style="color: red;">Sticky vacío!</h5>
                @endif
            </div>
            @else
            <div class="add d-flex justify-content-end col-md-12">
                <a data-toggle="modal" data-target="#modalcrear" class="btn btn-primary waves-effect btn-block btn-sm"
                    style="color: white; font-weight: bold; cursor: pointer;"> Agregar Sección </a>
            </div>
            @endif
        </div>
        <div class="widgets" id="widgets">
            <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
            @if($sticky != null)
            {!! Form::sticky($sticky)!!}
            @endif
        </div>
    </div>
</div>
@endsection

<div class="modal" id="modalcrear" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Sección</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    {!!
                    Form::open(['route'=>'sticky.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <div class="form-group">
                        <label>Ancho del Botón en Pixeles</label>
                        <span data-toggle="tooltip" title="Establece el ancho de los botones Sticky en pixeles."> <i class="fa fa-question-circle"></i></span>
                        <input name="ancho_boton" type="number" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Posición en la Pantalla</label>
                        <span data-toggle="tooltip" title="Establece la ubicación de los botones Sticky."> <i class="fa fa-question-circle"></i></span>
                        <select class="form-control" name="posicion" required>
                            <option value="DERECHA">DERECHA</option>
                            <option value="IZQUIERDA">IZQUIERDA</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">Fuente Para el Componente</label>
                        <span data-toggle="tooltip" title="Establece la fuente de los botones Sticky."> <i class="fa fa-question-circle"></i></span>
                        @if($fonts!=null)
                        {!! Form::select('configuracionfuente_id',$fonts,null,['class'=>'form-control
                        select2','placeholder'=>'-- Seleccione una opción --','required','style'=>'width: 100%;']) !!}
                        @endif
                    </div>                    
                    <div class="form-group">
                        <br /><br /><a class="btn btn-danger" id="modalcrear" style="color: white"
                            onclick="cerrar(this.id)">Cancelar</a>
                        <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                        {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="Modaledit" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Sección</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    @if($sticky != null)
                    {!!
                    Form::model($sticky,['route'=>['sticky.updated',$sticky],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <input type="hidden" name="sticky" value="{{$sticky->id}}">
                    <div class="form-group">
                        <label>Ancho del Botón en Pixeles</label>
                        <span data-toggle="tooltip" title="Establece el ancho de los botones Sticky en pixeles."> <i class="fa fa-question-circle"></i></span>
                        <input name="ancho_boton" type="number" value="{{$sticky->ancho_boton}}" required
                            class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Posición en la Pantalla</label>
                        <span data-toggle="tooltip" title="Establece la ubicación de los botones Sticky."> <i class="fa fa-question-circle"></i></span>
                        <select class="form-control" name="posicion" required>
                            @if($sticky->posicion=='DERECHA')
                            <option selected value="DERECHA">DERECHA</option>
                            @else
                            <option value="DERECHA">DERECHA</option>
                            @endif
                            @if($sticky->posicion=='IZQUIERDA')
                            <option selected value="IZQUIERDA">IZQUIERDA</option>
                            @else
                            <option value="IZQUIERDA">IZQUIERDA</option>
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">Fuente Para el Componente</label>
                        <span data-toggle="tooltip" title="Establece la fuente de los botones Sticky."> <i class="fa fa-question-circle"></i></span>
                        @if($fonts!=null)
                        {!!
                        Form::select('configuracionfuente_id',$fonts,$sticky->configuracionfuente_id,['class'=>'form-control
                        select2','placeholder'=>'-- Seleccione una opción --','required','style'=>'width: 100%;']) !!}
                        @endif
                    </div>
                    <div class="form-group">
                        <br /><br /><a class="btn btn-danger" id="Modaledit" style="color: white"
                            onclick="cerrar(this.id)">Cancelar</a>
                        <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                        {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                    </div>
                    {!! Form::close() !!}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="Modalboton" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Botón</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    @if($sticky != null)
                    <p style="text-align: justify;"><b>Nota: </b>Puede colocar, una sola imágen para el botón, solo
                        ícono, solo texto, si prefiere dos o tres de las opciones también es posible. De la estética del
                        componente usted será responsable si decide usar las opciones combinadas (texto, ícono e imágen)
                    </p>
                    {!!
                    Form::open(['route'=>'sticky.storeboton','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <input type="hidden" name="sticky_id" value="{{$sticky->id}}">
                    <div class="form-group">
                        <label>Color</label>
                        <span data-toggle="tooltip" title="Establece el color del boton Sticky."> <i class="fa fa-question-circle"></i></span>
                        <input name="color" type="color" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Ícono (Opcional)</label>
                        <span data-toggle="tooltip" title="Establece el ícono del boton Sticky."> <i class="fa fa-question-circle"></i></span>
                        <input name="icono" type="text" class="form-control" data-toggle="modal"
                            data-target="#exampleModal" id="iconotxt">
                    </div>
                    <div class="form-group">
                        <label>Enlace (Opcional)</label>
                        <span data-toggle="tooltip" title="Establece un enlace al boton Sticky."> <i class="fa fa-question-circle"></i></span>
                        <input name="enlace" type="text" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Texto (Opcional)</label>
                        <span data-toggle="tooltip" title="Establece el texto a mostrar del boton Sticky."> <i class="fa fa-question-circle"></i></span>
                        <input name="texto" type="text" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Imágen (Opcional)</label>
                        <span data-toggle="tooltip" title="Establece una imagen para el boton Sticky."> <i class="fa fa-question-circle"></i></span>
                        <input name="imagen" type="file" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="alpha">Transparencia</label>
                        <span data-toggle="tooltip" title="Establece la transparencia del boton Sticky."> <i class="fa fa-question-circle"></i></span>
                        <input class="form-control" type="range" id="alpha" name="alpha" min="0" max="10">
                    </div>
                    <div class="form-group">
                        <br /><br /><a class="btn btn-danger" id="Modalboton" style="color: white"
                            onclick="cerrar(this.id)">Cancelar</a>
                        <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                        {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                    </div>
                    {!! Form::close() !!}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="exampleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Icono</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    {!! Form::iconos($iconos) !!}
                </div>
            </div>
        </div>
    </div>
</div>


@section('script')
<script type="text/javascript">
    $(function() {
        $('#iconos').load('web/icons/view.blade.php');
    })

    function cerrar(id) {
        $("#" + id).modal('hide');
        $("#" + id).removeClass('modal-open');
        $('.' + id).remove();
    }
    $('[data-toggle="tooltip"]').tooltip({
        animated: 'fade',
        placement: 'right',
        html: true
    });
</script>
@endsection