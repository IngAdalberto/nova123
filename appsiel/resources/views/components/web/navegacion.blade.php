<style>

      li.active a{
           color: black !important;
           background-color: transparent !important;
      }

      li a:hover {
          color: black !important;
          background-color:transparent !important;
      }

    .icono img{
        max-height: 100px !important;
        /*border-radius: 4px;
        height:35px !important;
        object-fit: cover;*/
    }

    @media (max-width: 468px){

    }

</style>

<?php  
    if($nav->fixed)
    {
        $clase_header = 'fixed-top';
        //$estilo = 'clear: both;';
    }else{
        $clase_header = 'no-fixed';
    }
?>

<header class="{{ $clase_header }}" style="background-color: {{$nav->background}};">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light"><!-- mu-navbar  d-flex -->
            
            <!-- Text based logo -->
            @if( $nav->logo != '' )
                <a class="navbar-brand p-0 icono" href="{{url('/')}}">
                    <img src="{{asset($nav->logo)}}">
                </a>
            @endif

            <button class="navbar-toggler" type="button" data-toggle="collapse"
                    data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="fa fa-bars" style="color: {{$nav->color}}"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto mu-navbar-nav">
                    @foreach($nav->menus as $item)
                        @if($item->parent_id == 0)
                            @if($item->subMenus()->count()>0)
                                <li class="nav-item dropdown {{request()->url() == $item->enlace ? 'active':''}}">
                                    <a class="dropdown-toggle" style="color: {{$nav->color}}"
                                       href="{{$item->enlace}}" role="button" id="navbarDropdown"
                                       data-toggle="dropdown" aria-haspopup="true"
                                       aria-expanded="false"><i class="fa fa-{{$item->icono}}" style="font-size: 20px;"></i>{{' '.$item->titulo}}</a>
                                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                        @foreach($item->subMenus() as $subItems)
                                            <a style="color: {{$nav->color}}" class="dropdown-item"
                                               href="{{$subItems->enlace}}"><i class="fa fa-{{$subItems->icono}}" style="font-size: 20px;"></i>{{' '.$subItems->titulo}}</a>
                                        @endforeach
                                    </div>
                                </li>
                            @else
                                <li class="nav-item {{request()->url() == $item->enlace ? 'active':''}}"><a href="{{$item->enlace}}" style="color: {{$nav->color}}"><i class="fa fa-{{$item->icono}}" style="font-size: 20px;"></i>{{' '.$item->titulo}}</a></li>
                            @endif
                        @endif
                    @endforeach
                </ul>
            </div>
        </nav>
    </div>
</header>