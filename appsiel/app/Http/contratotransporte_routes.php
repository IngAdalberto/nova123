<?php 

//      CONTRATO DE TRANSPORTE (FORMATO UNICO DE EXTRACTO DE CONTRATO DE TRANSPORTE)

Route::resource('cte_contratos', 'ContratoTransporte\ContratoTransporteController');

Route::resource('cte_plantillas', 'ContratoTransporte\ContratoTransporteController');

Route::resource('contratos_transporte', 'ContratoTransporte\ContratoTransporteController');