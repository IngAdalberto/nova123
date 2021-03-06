@extends('web.templates.main')

@section('style')
<style>
    .card-body {
        padding: 0 !important;
        overflow-y: hidden;
    }

    #wrapper {
        overflow-y: scroll;
        height: 558px;
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
        position: relative;
    }

    .widgets img {
        width: 100%;
        object-fit: cover;
        height: 72.5vh;
        max-width: 100%;
    }


    .contenido {
        display: flex;
        padding: 5px;
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
<div class="card">
    <div class="card-body d-flex justify-content-between flex-wrap">
        <div id="wrapper">
            {!! Form::open(['route' => 'slider.store','method'=>'POST','files'=>'true','style' => 'margin:10px;']) !!}
            <input type="hidden" name="variables_url" value="{{$variables_url}}">
            <input type="hidden" name="widget_id" value="{{$widget}}">
            <div class="form-group">
                <label for="">Titulo</label>
                <span data-toggle="tooltip" title="Establece el titulo o encabezado del item."> <i class="fa fa-question-circle"></i></span>
                <input type="text" class="form-control" placeholder="" name="titulo">
            </div>
            <div class="form-group">
                <label>Color del Título</label>
                <span data-toggle="tooltip" title="Establece el color del titulo o encabezado del item."> <i class="fa fa-question-circle"></i></span>
                <input type="color" class="form-control" name="colorTitle" required>
            </div>
            <div class="form-group">
                <label for="">Descripción</label>
                <span data-toggle="tooltip" title="Establece la descripción del item."> <i class="fa fa-question-circle"></i></span>
                <textarea name="descripcion" id="" cols="30" rows="10" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label>Color de la Descripción</label>
                <span data-toggle="tooltip" title="Establece el color de la descripción del item."> <i class="fa fa-question-circle"></i></span>
                <input type="color" class="form-control" name="colorText" required>
            </div>
            <div class="form-group">
                <label for="">Imagen (1600x550 px)</label>
                <span data-toggle="tooltip" title="Establece la imagen del item."> <i class="fa fa-question-circle"></i></span>
                <input type="file" class="form-control" name="imagen" required>
            </div>
            <div class="form-group">
                <label>Disposición</label>
                <span data-toggle="tooltip" title="Establece el tipo de slider de la sección. SLIDER POR DEFECTO: Mantiene el alto de los items del slider y estos se desplazan a los lados para mostrar el contenido completo <img src='{{asset('assets/img/slider-pordefecto.png')}}' /> o SLIDER BOOTSTRAP: Mantiene el alto del slider y su contenido. <img src='{{asset('assets/img/slider-bootstrap.png')}}'/> o SLIDER APPSIEL: mantiene el alto de los items del slider y estos se desplazan a los lados para mostrar el contenido completo. <img src='{{asset('assets/img/slider-appsiel.png')}}' />"> <i class="fa fa-question-circle"></i></span>
                <select class="form-control" name="disposicion" required>
                    <option value="DEFAULT">SLIDER POR DEFECTO</option>
                    <option value="PREMIUM">SLIDER PREMIUM</option>
                    <option value="BOOTSTRAP">SLIDER BOOTSTRAP</option>
                    <option value="APPSIEL">SLIDER APPSIEL</option>
                </select>
            </div>
            <div class="form-group">
                <label for="">Fuente Para el Componente</label>
                <span data-toggle="tooltip" title="Establece el tipo de fuente de la sección."> <i class="fa fa-question-circle"></i></span>
                @if($fonts!=null)
                {!! Form::select('configuracionfuente_id',$fonts,null,['class'=>'form-control select2','placeholder'=>'-- Seleccione una opción --','required','style'=>'width: 100%;']) !!}
                @endif
            </div>

            <div class="col-md-12">
                <h5>Enlazar a</h5>
                <input type="hidden" id="tipo_enlace" name="tipo_enlace" value="pagina">
                <div class="form-group">
                    <label for="">Titulo del Enlace</label>
                    <span data-toggle="tooltip" title="Establece el titulo del elemento del slider."> <i class="fa fa-question-circle"></i></span>
                    <input type="text" class="form-control" name="button">
                </div>
                <nav>
                    <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true" onclick="select('pagina')">Página<span data-toggle="tooltip" title="Establece un enlace del elemento del slider a una pagina."> <i class="fa fa-question-circle"></i></span>
                        </a>
                        <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false" onclick="select('url')">URL del sitio web <span data-toggle="tooltip" title="Establece un enlace del elemento del slider a una pagina web externa."> <i class="fa fa-question-circle"></i></span>
                        </a>
                    </div>
                </nav>
                <div class="tab-content py-3 px-3 px-sm-0" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                        <div class="form-group" style="display: inline-block; width: 40%;">
                            <label for="">Página</label>
                            <select class="form-control" id="paginas" onchange="buscarSecciones(event)" name="pagina">
                                @foreach($paginas as $pagina)
                                <option value="{{$pagina->id}}">{{$pagina->titulo}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="display: inline-block;width: 58%;">
                            <label for="">Sección en una página</label>
                            <span data-toggle="tooltip" title="Establece la pagina a la cual quieres enlazar el elemento del slider."> <i class="fa fa-question-circle"></i></span>
                            <select class="form-control" id="secciones" name="seccion">
                                <option value="principio">Principio de la Página</option>
                            </select>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                        <div class="form-group">
                            <label for="formGroupExampleInput">URL de sitio web (se abre en una pestaña
                                nueva)</label>
                            <input type="text" class="form-control" placeholder="https://" name="url">
                        </div>
                    </div>
                </div>
                <div class="buttons d-flex justify-content-end">
                    <button type="submit" class="btn btn-info mx-1">Guardar</button>
                    <a href="{{url('seccion/'.$widget).$variables_url}}" class="btn btn-danger mx-1">Cancelar</a>
                    <button type="reset" class="btn btn-warning mx-1">limpiar</button>
                </div>
            </div>

            {!! Form::close() !!}
        </div>
        <div class="widgets" id="widgets">
            <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>

            @if($slider != null)
            <?php switch ($slider->disposicion) {
                case 'DEFAULT': ?>
                    {!! Form::slider($slider) !!}
                <?php break;
                case 'PREMIUM': ?>
                    {!! Form::sliderpremiun($slider) !!}
                <?php break;
                case 'BOOTSTRAP': ?>
                    {!! Form::sliderbootstrap($slider) !!}
            <?php break;
                default:
                    break;
            } ?>
            @endif
        </div>
    </div>
</div>
@endsection

@section('script')

<script src="{{asset('assets/js/axios.min.js')}}"></script>
<script>
    $(function() {
        const select = document.getElementById('paginas');
        rellenarSelect(select);
    });

    function buscarSecciones(event) {
        let select = event.target;
        rellenarSelect(select);
    }

    function rellenarSelect(select) {

        select = select.options[select.selectedIndex].value;
        const url = "{{url('')}}/" + "pagina/secciones/" + select;

        axios.get(url)
            .then(function(response) {
                const data = response.data;
                let tbody = document.getElementById('secciones');
                let secciones = data.secciones;
                $html = `<option value="principio">Principio de la página</option>`;
                secciones.forEach(function(item) {
                    $html += `<option value="${item.widget_id}">${item.seccion}</option>`;
                });
                tbody.innerHTML = $html;
            });
    }

    function select(opcion) {
        let tipo = document.getElementById('tipo_enlace');
        tipo.value = opcion;
    }
    
    $('[data-toggle="tooltip"]').tooltip({
        animated: 'fade',
        placement: 'right',
        html: true
    });

</script>
@endsection