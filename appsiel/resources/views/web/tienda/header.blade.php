<?php 
    $empresa = App\Core\Empresa::find(1);
    $configuracion = App\web\Configuraciones::all()->first();
?>

<header>
    <div class="top-link" style="background: {{ $configuracion->color_primario }};">
        <div class="container" style="padding: 0 ">
            <div class="top-link-inner">
                <div class="row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <div class="toplink-static">
                            <span style="line-height: 40px; color: white;">
                                Linea Directa : <a href="https://api.whatsapp.com/send?phone=+57{{ $empresa->telefono1 }}" target="_blank"><i style="font-size: 16px; color: green;" class="fa fa-whatsapp" aria-hidden="true"></i> {{ $empresa->telefono1 }}</a>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-9 col-sm-9 col-xs-12 toplink-static">
                        <p class="welcome-msg">Bienvenido a {{ $empresa->descripcion }} </p>

                        <ul class="links">
                            <li class="first"><a
                                        href="{{route('tienda.micuenta')}}"
                                        title="Mi Cuenta">Mi Cuenta</a></li>
                            <li><a href="{{route("tienda.comprar")}}"
                                   title="My Cart" class="top-link-cart">Mi Carrito</a></li>
                            <li><a href="#"
                                   title="Checkout" class="top-link-checkout">Revisa</a></li>
                            @if(Auth::guest())
                                     <li class=" last"><a
                                        href="{{route('tienda.login')}}"
                                        title="Iniciar sesión">Iniciar Sesión</a>
                                     </li>
                                <li class=" last"><a
                                            href="{{route('tienda.nuevacuenta')}}"
                                            title="Registrarse"
                                            onclick="registrarse( event )">Registrarse</a>
                                </li>
                            <!--
                                <li class=" last">
                                    <button onclick="document.getElementById('id01').style.display='block'" title="Registrarse" class="_no_abrir_modal" data-elemento_id="218" style="background: transparent; border: 0px;">Registrarse 2</button>

                                </li>-->
                            @else


                                <li class=" last"><a
                                            href="{{url('/logout')}}"
                                            title="Cerra sesión">Cerrar Sesión</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<div id="id01" class="modal">
  <span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span>
  <form class="modal-content" action="/action_page.php">
    <div class="container">
      <h1>Sign Up</h1>
      <p>Please fill in this form to create an account.</p>
      <hr>
      <label for="email"><b>Email</b></label>
      <input type="text" placeholder="Enter Email" name="email" required>

      <label for="psw"><b>Password</b></label>
      <input type="password" placeholder="Enter Password" name="psw" required>

      <label for="psw-repeat"><b>Repeat Password</b></label>
      <input type="password" placeholder="Repeat Password" name="psw-repeat" required>
      
      <label>
        <input type="checkbox" checked="checked" name="remember" style="margin-bottom:15px"> Remember me
      </label>

      <p>By creating an account you agree to our <a href="#" style="color:dodgerblue">Terms & Privacy</a>.</p>

      <div class="clearfix">
        <button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Cancel</button>
        <button type="submit" class="signupbtn">Sign Up</button>
      </div>
    </div>
  </form>
</div>

<script>
// Get the modal
var modal = document.getElementById('id01');

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>
