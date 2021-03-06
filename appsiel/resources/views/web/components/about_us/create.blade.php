@extends('web.templates.main')

@section('style')
<style>
    .card-body {
        padding: 0 !important;
        overflow-y: hidden;
    }

    #wrapper {
        overflow-y: scroll;
        width: 30%;
        margin-right: 0;
    }

    .list-group-item {
        background-color: transparent;
        font-size: 16px;
    }

    .list-group-item:hover {
        background-color: #3d6983;
        color: white;
        cursor: pointer;
    }

    .widgets {
        width: 70%;
    }
</style>

@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12" style="text-align: center; font-weight: bold; padding: 15px;">
            <h4>.:: En ésta Sección: Quiénes Somos ::.</h4>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body d-flex justify-content-between flex-wrap">
        <div id="wrapper">
            @if($aboutus != null)
            <h4 class="column-title" style="padding: 10px;">Editar Quiénes Somos</h4>
            <div class="col-md-12">
                {!! Form::model($aboutus,['route'=>['aboutus.updated',$aboutus],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                <input type="hidden" name="widget_id" value="{{$widget}}">
                <input type="hidden" name="variables_url" value="{{$variables_url}}">
                <div class="form-group">
                    <label>Titulo</label>
                    <span data-toggle="tooltip" title="Establece el titulo o encabezado de la sección."> <i class="fa fa-question-circle"></i></span>
                    <input name="titulo" type="text" placeholder="Titulo" value="{{$aboutus->titulo}}" class="form-control">
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <span data-toggle="tooltip" title="Establece la descripción de la sección."> <i class="fa fa-question-circle"></i></span>
                    <input name="descripcion" type="text" placeholder="Descripción" value="{{$aboutus->descripcion}}" class="form-control">
                </div>
                <div class="form-group">
                    <label for="">Fuente Para el Componente</label>
                    <span data-toggle="tooltip" title="Establece el tipo de fuente de la sección."> <i class="fa fa-question-circle"></i></span>
                    @if($fonts!=null)
                    {!! Form::select('configuracionfuente_id',$fonts,$aboutus->configuracionfuente_id,['class'=>'form-control select2','placeholder'=>'-- Seleccione una opción --','required','style'=>'width: 100%;']) !!}
                    @endif
                </div>
                <div class="form-group">
                    <label>¿Mostrar Botón Leer Más?</label>
                    <span data-toggle="tooltip" title="Elija 'SI', si desea ver un resumen y ver el contenido completo en una nueva pagina."> <i class="fa fa-question-circle"></i></span>
                    <select class="form-control" name="mostrar_leermas">
                        <option value="SI">-- Seleccione una opción --</option>
                        @if($aboutus->mostrar_leermas=='SI')
                        <option selected value="SI">SI</option>
                        <option value="NO">NO</option>
                        @else
                        <option value="SI">SI</option>
                        <option selected value="NO">NO</option>
                        @endif
                    </select>
                </div>
                <div class="form-group">
                    <label>Misión</label>
                    <span data-toggle="tooltip" title="Establece el contenido de la Misión."> <i class="fa fa-question-circle"></i></span>
                    <textarea name="mision" class="form-control contenido" rows="5">{{$aboutus->mision}}</textarea>
                </div>
                <div class="form-group">
                    <label>Icono Misión</label>    
                    <span data-toggle="tooltip" title="Establece el icono de la Misión."> <i class="fa fa-question-circle"></i></span>                
                    <input data-toggle="modal" data-target="#exampleModal" name="mision_icono" onclick="cambiaricono(this.id)" value="{{$aboutus->mision_icono}}" type="text" id="icono_mision" placeholder="Nombre del icono" class="form-control">
                </div>
                <div class="form-group">
                    <label>Visión</label>
                    <span data-toggle="tooltip" title="Establece el contenido de la Visión."> <i class="fa fa-question-circle"></i></span>
                    <textarea name="vision" class="form-control contenido" rows="5">{{$aboutus->vision}}</textarea>
                </div>
                <div class="form-group">
                    <label>Icono Visión</label>
                    <span data-toggle="tooltip" title="Establece el icono de la Visión."> <i class="fa fa-question-circle"></i></span>
                    <input data-toggle="modal" data-target="#exampleModal" name="vision_icono" id="icono_vision" onclick="cambiaricono(this.id)" value="{{$aboutus->vision_icono}}" type="text" placeholder="Nombre del icono" class="form-control">
                </div>
                <div class="form-group">
                    <label>Valores</label>
                    <span data-toggle="tooltip" title="Establece el contenido de los valores."> <i class="fa fa-question-circle"></i></span>
                    <textarea name="valores" class="form-control contenido" rows="5">{{$aboutus->valores}}</textarea>
                </div>
                <div class="form-group">
                    <label>Icono Valores</label>
                    <span data-toggle="tooltip" title="Establece el icono de los Valores."> <i class="fa fa-question-circle"></i></span>
                    <input data-toggle="modal" data-target="#exampleModal" id="icono_valor" name="valor_icono" onclick="cambiaricono(this.id)" value="{{$aboutus->valor_icono}}" type="text" placeholder="Nombre del icono" class="form-control">
                </div>
                <div class="form-group">
                    <label>Reseña Historica</label>
                    <span data-toggle="tooltip" title="Establece el contenido de la Reseña Historica."> <i class="fa fa-question-circle"></i></span>
                    <textarea name="resenia" class="form-control contenido" rows="5">{{$aboutus->resenia}}</textarea>
                </div>
                <div class="form-group">
                    <label>Icono Reseña</label>
                    <span data-toggle="tooltip" title="Establece el icono de la Reseña Historica."> <i class="fa fa-question-circle"></i></span>
                    <input data-toggle="modal" data-target="#exampleModal" id="icono_resenia" onclick="cambiaricono(this.id)" name="resenia_icono" value="{{$aboutus->resenia_icono}}" type="text" placeholder="Nombre del icono" class="form-control">
                </div>
                <div class="form-group">
                    <label>Imagen Para Acompañar</label>
                    <span data-toggle="tooltip" title="Establece una imagen que acompaña el contenido de la seccion."> <i class="fa fa-question-circle"></i></span>
                    <a target="_blank" href="{{asset($aboutus->imagen)}}">Ver Actual</a><br>
                    <input name="imagen" type="file" placeholder="Agregar una imagen" class="form-control">
                </div>
                <div class="form-group">
                    <label>¿El fondo es Imagen o Color?</label>
                    <span data-toggle="tooltip" title="Establece el tipo de fondo de la sección. De tipo Imagen: <img src='{{asset('assets/img/fondo-imagen.png')}}' /> o Color: <img src='{{asset('assets/img/fondo-color.png')}}' />"> <i class="fa fa-question-circle"></i></span>
                    <select type="select" class="form-control" id="tipo_fondo2" name="tipo_fondo" onchange="cambiar2()">
                        @if($aboutus->tipo_fondo=='IMAGEN')
                        <option value="">-- Seleccione una opción --</option>
                        <option selected value="IMAGEN">IMAGEN</option>
                        <option value="COLOR">COLOR</option>
                        @else
                        <option value="">-- Seleccione una opción --</option>
                        <option value="IMAGEN">IMAGEN</option>
                        <option selected value="COLOR">COLOR</option>
                        @endif
                    </select>
                </div>
                <div class="form-group" id="fondo_container2">
                    @if($aboutus->tipo_fondo=='IMAGEN')
                    <label>Imagen de Fondo</label>
                    <a target="_blank" href="{{asset($aboutus->fondo)}}">Ver Actual</a><br>
                    <b>Repetir: {{$aboutus->repetir}}</b><br>
                    <b>Orientación Imagen: {{$aboutus->direccion}}</b>
                    @else
                    <label>Color de Fondo</label>
                    <div class="col-md-12" style="background-color: {{$aboutus->fondo}}; width: 100%; height: 20px;"></div>
                    @endif
                </div>
                <div class="form-group">
                    <br /><br /><a href="{{url('seccion/'.$widget).$variables_url}}" class="btn btn-danger">Cancelar</a>
                    <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                    {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                </div>
                {!! Form::close() !!}
            </div>
            @else
            <h4 class="column-title" style="padding: 10px;">Crear Quiénes Somos</h4>
            <div class="col-md-12">
                {!! Form::open(['route'=>'aboutus.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                <input type="hidden" name="widget_id" value="{{$widget}}">
                <input type="hidden" name="variables_url" value="{{$variables_url}}">
                <div class="form-group">
                    <label>Titulo</label>
                    <span data-toggle="tooltip" title="Establece el titulo o encabezado de la sección."> <i class="fa fa-question-circle"></i></span>
                    <input name="titulo" type="text" placeholder="Titulo" class="form-control">
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <span data-toggle="tooltip" title="Establece la descripción de la sección."> <i class="fa fa-question-circle"></i></span>
                    <input name="descripcion" type="text" placeholder="Descripción" class="form-control">
                </div>
                <div class="form-group">
                    <label for="">Fuente Para el Componente</label>
                    <span data-toggle="tooltip" title="Establece el tipo de fuente de la sección."> <i class="fa fa-question-circle"></i></span>
                    @if($fonts!=null)
                    {!! Form::select('configuracionfuente_id',$fonts,null,['class'=>'form-control select2','placeholder'=>'-- Seleccione una opción --','required','style'=>'width: 100%;']) !!}
                    @endif
                </div>
                <div class="form-group">
                    <label>¿Mostrar Botón Leer Más?</label>
                    <span data-toggle="tooltip" title="Elija 'SI', si desea ver un resumen y ver el contenido completo en una nueva pagina."> <i class="fa fa-question-circle"></i></span>
                    <select class="form-control" name="mostrar_leermas" required>
                        <option value="SI">-- Seleccione una opción --</option>
                        <option value="SI">SI</option>
                        <option value="NO">NO</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Misión</label>
                    <span data-toggle="tooltip" title="Establece el contenido de la Misión."> <i class="fa fa-question-circle"></i></span>
                    <textarea name="mision" class="form-control contenido" rows="5"></textarea>
                </div>
                <div class="form-group">
                    <label>Icono Misión</label>
                    <span data-toggle="tooltip" title="Establece el icono de la Misión."> <i class="fa fa-question-circle"></i></span>
                    <input data-toggle="modal" data-target="#exampleModal" name="mision_icono" onclick="cambiaricono(this.id)" type="text" id="icono_mision" placeholder="Nombre del icono" class="form-control">
                </div>
                <div class="form-group">
                    <label>Visión</label>
                    <span data-toggle="tooltip" title="Establece el contenido de la Visión."> <i class="fa fa-question-circle"></i></span>
                    <textarea name="vision" class="form-control contenido" rows="5"></textarea>
                </div>
                <div class="form-group">
                    <label>Icono Visión</label>
                    <span data-toggle="tooltip" title="Establece el icono de la Visión."> <i class="fa fa-question-circle"></i></span>
                    <input data-toggle="modal" data-target="#exampleModal" name="vision_icono" id="icono_vision" onclick="cambiaricono(this.id)" type="text" placeholder="Nombre del icono" class="form-control">
                </div>
                <div class="form-group">
                    <label>Valores</label>
                    <span data-toggle="tooltip" title="Establece el contenido de los valores."> <i class="fa fa-question-circle"></i></span>
                    <textarea name="valores" class="form-control contenido" rows="5"></textarea>
                </div>
                <div class="form-group">
                    <label>Icono Valores</label>
                    <span data-toggle="tooltip" title="Establece el icono de los Valores."> <i class="fa fa-question-circle"></i></span>
                    <input data-toggle="modal" data-target="#exampleModal" id="icono_valor" name="valor_icono" onclick="cambiaricono(this.id)" type="text" placeholder="Nombre del icono" class="form-control">
                </div>
                <div class="form-group">
                    <label>Reseña</label>
                    <span data-toggle="tooltip" title="Establece el contenido de la Reseña Historica."> <i class="fa fa-question-circle"></i></span>
                    <textarea name="resenia" class="form-control contenido" rows="5"></textarea>
                </div>
                <div class="form-group">
                    <label>Icono Reseña</label>
                    <span data-toggle="tooltip" title="Establece el icono de la Reseña Historica."> <i class="fa fa-question-circle"></i></span>
                    <input data-toggle="modal" data-target="#exampleModal" id="icono_resenia" onclick="cambiaricono(this.id)" name="resenia_icono" type="text" placeholder="Nombre del icono" class="form-control">
                </div>
                <div class="form-group">
                    <label>Imagen Para Acompañar</label>
                    <span data-toggle="tooltip" title="Establece una imagen que acompaña el contenido de la seccion."> <i class="fa fa-question-circle"></i></span>
                    <input name="imagen" type="file" placeholder="Agregar una imagen" required="required" class="form-control">
                </div>
                <div class="form-group">
                    <label>¿El fondo es Imagen o Color?</label>
                    <span data-toggle="tooltip" title="Establece el tipo de fondo de la sección. De tipo Imagen: <img src='{{asset('assets/img/fondo-imagen.png')}}' /> o Color: <img src='{{asset('assets/img/fondo-color.png')}}' />"> <i class="fa fa-question-circle"></i></span>
                    <select type="select" class="form-control" id="tipo_fondo" required name="tipo_fondo" onchange="cambiar()">
                        <option value="">-- Seleccione una opción --</option>
                        <option value="IMAGEN">IMAGEN</option>
                        <option value="COLOR">COLOR</option>
                    </select>
                </div>
                <div class="form-group" id="fondo_container">
                </div>
                <div class="form-group">
                    <br /><br /><a href="{{url('seccion/'.$widget).$variables_url}}" class="btn btn-danger">Cancelar</a>
                    <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                    {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                </div>
                {!! Form::close() !!}
            </div>
            @endif
        </div>
        <div class="widgets" id="widgets">
            <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
            @if($aboutus != null)
            @if($aboutus->disposicion == 'DEFAULT')
            {!! Form::aboutus($aboutus)!!}
            @else
            {!! Form::aboutuspremiun($aboutus) !!}
            @endif
            @endif
        </div>
    </div>
</div>
@endsection
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
    var original_name;

    function cambiaricono(id) {
        $("#nombre").attr('value', id);
    }

    $('.contenido').on('focus', function() {

        original_name = $(this).attr('name');

        $(this).attr('name', 'contenido');

        CKEDITOR.replace('contenido', {
            height: 200,
            // By default, some basic text styles buttons are removed in the Standard preset.
            // The code below resets the default config.removeButtons setting.
            removeButtons: ''
        });

    });

    $('.contenido').on('blur', function() {

        $(this).attr('name', original_name);

    });

    function cambiar() {
        $("#fondo_container").html("");
        var f = $("#tipo_fondo").val();
        var html = "<label>";
        if (f == 'IMAGEN') {
            html = html + `Imagen de Fondo <span data-toggle="tooltip" title="Establece la imagen de fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>            
            <input type='file' class='form-control' name='fondo' required>` +
                `<label>Repetir <span data-toggle="tooltip" title="Establece si la imagen se repite en el fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>                
                <select class='form-control' name='repetir' required><option value='repeat'>SI</option><option value='no-repeat'>NO</option></select>`+
                `<label>Orientación Imagen <span data-toggle="tooltip" title="Establece la orientacion de la imagen de fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>
                <select class='form-control' name='direccion' required><option value='center'>COLOCAR EN EL CENTRO</option><option value='left'>IZQUIERDA</option><option value='right'>DERECHA</option><option value='top'>ARRIBA</option></select>`;
        } else if (f == 'COLOR') {
            html = html + `Color de Fondo <span data-toggle="tooltip" title="Establece el color de fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>            
            <input type='color' class='form-control' name='fondo' required>`;
        } else {
            html = "";
        }
        $("#fondo_container").html(html);
        
        $('[data-toggle="tooltip"]').tooltip({
            animated: 'fade',
            placement: 'right',
            html: true
        });
    }

    function cambiar2() {
        $("#fondo_container2").html("");
        var f = $("#tipo_fondo2").val();
        var html = "<label>";
        if (f == 'IMAGEN') {
            html = html + `Imagen de Fondo <span data-toggle="tooltip" title="Establece la imagen de fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>            
            <input type='file' class='form-control' name='fondo' required>` +
                `<label>Repetir <span data-toggle="tooltip" title="Establece si la imagen se repite en el fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>                
                <select class='form-control' name='repetir' required><option value='repeat'>SI</option><option value='no-repeat'>NO</option></select>`+
                `<label>Orientación Imagen <span data-toggle="tooltip" title="Establece la orientacion de la imagen de fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>
                <select class='form-control' name='direccion' required><option value='center'>COLOCAR EN EL CENTRO</option><option value='left'>IZQUIERDA</option><option value='right'>DERECHA</option><option value='top'>ARRIBA</option></select>`;
        } else if (f == 'COLOR') {
            html = html + `Color de Fondo <span data-toggle="tooltip" title="Establece el color de fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>            
            <input type='color' class='form-control' name='fondo' required>`;
        } else {
            html = "";
        }
        $("#fondo_container2").html(html);

        $('[data-toggle="tooltip"]').tooltip({
            animated: 'fade',
            placement: 'right',
            html: true
        });
    }

    $('[data-toggle="tooltip"]').tooltip({
        animated: 'fade',
        placement: 'auto',
        html: true
    });
</script>
@endsection