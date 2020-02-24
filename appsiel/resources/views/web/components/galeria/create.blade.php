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
    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                    {!! Form::open(['route'=>'galeria.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <div class="form-group">
                        <label>Titulo</label>
                        <input name="titulo" type="text" placeholder="Titulo del Álbum" required="required" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Descripción del Álbum</label>
                        {!! Form::textarea('descripcion',null,['class'=>'form-control col-md-12 col-xs-12','required']) !!}
                    </div>
                    <div class="form-group">
                        <label>Añadir Imagenes (Las imagenes deben ser de 600px de alto por 400px de ancho)</label>
                        <input name="imagen[]" multiple type="file" placeholder="Agregar una imagen" required="required"
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <br/><br/><a href="{{url('seccion/'.$widget).$variables_url}}"
                                     class="btn btn-danger">Cancelar</a>
                        <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                        {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                    </div>
                    {!! Form::close() !!}
            </div>
            <div class="widgets" id="widgets">
                @if($galeria != null)
                    {!! Form::galeria($galeria)!!}
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')

@endsection
