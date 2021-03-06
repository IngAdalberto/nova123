
<style type="text/css">
    p { margin: -2px; }
</style>

<h3 style="width: 100%; text-align: center;"> Lista de etiquetas de códigos de barra de items </h3>
<hr>
<div class="container-fluid">
    
    <?php
        $i=$numero_columnas;
        $minimo_comun_multiplo_columnas = 12;
    ?>

    <table class="table" style="width: 100%; font-size: 12px;">
        <thead>
            <tr>
                @for($f=0;$f<$minimo_comun_multiplo_columnas;$f++)
                    <th>&nbsp;</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            
        
            @foreach($items as $fila)
              
                @if($i % $numero_columnas == 0)
                    <tr>
                @endif

                <td colspan="{{ $minimo_comun_multiplo_columnas / $numero_columnas }}">
                    <div style="border: solid 1px #ddd; border-radius: 4px; padding: 5px; text-align: center;">
                        @if($etiqueta != '')
                            <p>
                                <b>{{ $etiqueta }}</b>
                            </p>
                        @endif

                        @if($mostrar_descripcion)
                            <p>
                                <b>{{ $fila->descripcion }}</b>
                            </p>
                        @endif

                        
                        <!-- 
                            DNS1D::getBarcodePNG( texto_codigo, tipo_codigo, ancho, alto) 

                            tipo_codigo: { C128B, C39 }

                        -->
                        <p>
                            <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG( (int)$fila->codigo_barras, "EAN13", 2, 100) }}" alt="barcode" />
                        </p>
                        @if( $fila->codigo_barras == '' )
                            {{ $fila->id }}
                        @else
                            {{ $fila->codigo_barras }}
                        @endif
                    </div>
                        
                </td>

                <?php
                    $i++;
                ?>

                @if($i % $numero_columnas == 0)
                    </tr>
                @endif

            @endforeach
        </tbody>
    </table>
</div>