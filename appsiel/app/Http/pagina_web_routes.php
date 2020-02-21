<?php 


// Página Web - FRONT END
Route::resource('paginas','web\PaginaController');
Route::get('pagina/secciones/{id}','web\PaginaController@secciones');
Route::get('pagina/administrar','web\PaginaController@admin');
Route::get('pagina/addSeccion/{id}','web\PaginaController@addSeccion');
Route::post('pagina/nuevaSeccion','web\PaginaController@nuevaSeccion');

//navegacion
Route::resource('navegacion', 'web\NavegacionController');

Route::resource('menuItem','web\MenuNavegacionController');
Route::post('menuItem/update/{id}','web\MenuNavegacionController@update')->name('itemUpdate');
Route::get('item/delete/{id}','web\MenuNavegacionController@destroy');

Route::get('seccion/{widget}','web\SeccionController@orquestador');

Route::get('slider/create/{widget}','web\SliderController@create');
Route::resource('slide','web/SliderController');

Route::post('pagina_web/contactenos', 'PaginaWeb\FrontEndController@contactenos');

Route::get('categoria/{id?}', 'PaginaWeb\FrontEndController@show_categoria');
Route::get('blog/{articulo?}', 'PaginaWeb\FrontEndController@blog');
Route::get('ajax_galeria_imagenes/{carousel_id}', 'PaginaWeb\FrontEndController@ajax_galeria_imagenes');


//Route::get('/{url?}', 'PaginaWeb\FrontEndController@direccionar_url');


// Página Web - BACK END

Route::post('pagina_web/crear_nuevo_modulo', 'PaginaWeb\ModuloController@crear_nuevo');
Route::resource('pagina_web/modulos', 'PaginaWeb\ModuloController');

Route::resource('pagina_web/secciones', 'PaginaWeb\SeccionController');

Route::get('pagina_web/be/{modulo}/{accion}/{registro_id?}', 'PaginaWeb\BackEndController@gestionar_modulos');
Route::resource('pagina_web', 'PaginaWeb\BackEndController');


Route::get('pw_barra_navegacion', 'PaginaWeb\FrontEndController@micrositio');

Route::get('/mweb/{id}/microsites', 'PaginaWeb\FrontEndController@micrositio');
Route::get('generar_slug/{cadena}', 'PaginaWeb\SlugController@generar_slug');


// MÓDULOS
Route::resource('pagina_web/carousel', 'PaginaWeb\CarouselController');