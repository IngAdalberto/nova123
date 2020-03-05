<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\web\Article;
use App\web\Articlesetup;
use Illuminate\Support\Facades\Input;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $as = new Articlesetup($request->all());
        $result = $as->save();
        $variables_url = $request->variables_url;
        if ($result) {
            $message = 'La configuración de la sección fue almacenada correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La configuración no pudo ser almacenada, intente mas tarde.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $a = Article::find($id);
        return view('web.components.viewfinder.viewfinder')
            ->with('a', $a);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $as = Articlesetup::find($id);
        $as->fill($request->all());
        $result = $as->save();
        $variables_url = $request->variables_url;
        if ($result) {
            $message = 'La configuración de la sección fue modificada correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La configuración no pudo ser modificada, intente mas tarde.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $article = Article::find($id);
        if($article){
            if($article->imagen)
             unlink($article->imagen);

            $flag =  $article->delete();

            if ($flag) {
                return response()->json([
                    'status' => 'ok',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error inesperado, por favor intentelo más tarde.'
                ]);
            }

        }else {
            return response()->json([
                'status' => 'error',
                'message' => 'Error inesperado, por favor intentelo más tarde.'
            ]);
        }


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function articlestore(Request $request)
    {
        $a = new Article($request->all());
        $result = $a->save();


        if ($request->hasFile('imagen'))
        {

            $file = $request->file('imagen');

            $name = str_slug( $file->getClientOriginalName() ) . '-' .time() . '.' . $file->clientExtension();

            $filename = "img/articles/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $a->fill(['imagen' => $filename])->save();
            }
        }

        $variables_url = $request->variables_url;
        if ($result) {
            $message = 'El artículo fue almacenado correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'El artículo no pudo ser almacenado, intente mas tarde.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function articleupdate(Request $request)
    {
        $a = Article::find($request->article_id);

        $old_image = $a->imagen;

        $a->titulo = $request->titulo;
        $a->estado = $request->estado;
        $a->contenido = $request->contenido;
        $a->descripcion = $request->descripcion;
        $result = $a->save();


        if($request->hasFile('imagen'))
        {

            $file = $request->file('imagen');
            $name = str_slug( $file->getClientOriginalName() ) . '-' .time() . '.' . $file->clientExtension();

            $filename = "img/articles/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);

            if($flag !== false)
            {
                if( $old_image != '' )
                {
                    unlink( $old_image );
                }

                $a->fill(['imagen' =>$filename])->save();
            }
        }


        $variables_url = $request->variables_url;
        if ($result) {
            $message = 'El artículo fue modificado correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'El artículo no pudo ser modificado, intente mas tarde.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }
}
