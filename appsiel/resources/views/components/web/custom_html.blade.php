<style type="text/css">
	.custom_html{
          border-radius: 30% 70% 70% 30% / 55% 30% 70% 45%;
          background-image: linear-gradient(45deg, #3023AE 0%, #f09 100%);
        }	
</style>

<div class="custom_html">
	{!! str_replace( '_el_token_csrf_', csrf_field(), $registro->contenido ) !!}
</div>