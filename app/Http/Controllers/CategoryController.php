<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller
{
    public function __construct(){
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }
    //
    public function test(Request $request) {
        return 'Método de prueba para CategoryController';
    }


    public function index(){
        $categories = Category::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories
        ]);
    }

    public function show($id){
        $category = Category::find($id);
        
        if(is_object($category)){
            $data = array(
                'code' => 200,
                'status' => 'success',
                'category' => $category
            );
        }
        else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No existe esa categoria'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request){
        //recoger los datos por post
        $json = $request->input('json', null);
        $params_array = \json_decode($json, true);

 
        
        if(!empty($params_array)){

            //validar los datos
            $validate = \Validator::make($params_array, [
                'name' => 'required'
            ]);

            //guardar la categoria  
            if($validate->fails()){
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se pudo guardar la categoria'
                );
            }else{
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'category' => $category 
                );
            }
        }else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'No se ha enviado ninguna categoria' 
            ); 
        }


        //devolver resultado
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request){

        //recoger datos
        $json = $request->input('json', null);
        $params_array = \json_decode($json, true);

        if(!empty($params_array)){
            //validar
            $validate = \Validator::make($params_array, [
                'name' => 'required'
            ]);

            //quitar lo que no se va a actualizar
            unset($params_array['id']);
            unset($params_array['created_at']);

            //actualizar
            $category = Category::where('id', $id)->update($params_array);

            $data = array(
                'code' => 200,
                'status' => 'success',
                'category' => $params_array
            );
        }
        else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'No se ha enviado ninguna categoria' 
            );             
        }
        //devolver  respuesta   
        return response()->json($data, $data['code']);
    }
}
