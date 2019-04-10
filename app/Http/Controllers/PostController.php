<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Utils\JwtAUTH;

class PostController extends Controller
{

    public function __construct() {
        $methods = [
            'index', 
            'show', 
            'getImage', 
            'getPostByCategory', 
            'getPostByUser'
        ];
        return $this->middleware('api.auth', ['except' => $methods]);
    }


    public function index() {
        $posts = Post::all()->load('category')->load('user');

        $data = array(
            'code' => 200,
            'status' => 'success',
            'posts' => $posts
        );
        return response()->json($data, $data['code']);
    }

    public function show($id) {
        $post = Post::find($id)->load('category')->load('user');

        if(is_object($post)){
            $data = array(
                'code' => 200,
                'status' => 'success',
                'post' => $post
            );
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La entrada no existe'
            ); 
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request){
        //recoger datos por  Post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        //var_dump($params_array);die();
        if(!empty($params_array)){
            //conseguir usuario identificado
            $user = $this->getIdentity($request);

            //Validar datos
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required'
            ]);

            if($validate->fails()){
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Faltan datos del post'
                );
            }else{
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;
                $post->save();

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post
                );
            }

            //guardar la publicación
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Datos incorrectos'
            );
        }


        //devolver respuesta
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request){
        //recoger datos
        $json = $request->input('json', null);
        $params_array = \json_decode($json, true);

        //conseguir usuario
        $user = $this->getIdentity($request);

        if(!empty($params_array)){
            //validar
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'
            ]);

            if($validate->fails()){
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Faltan datos'
                );
            }else{
                //quitar campos que no van para el update
                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['created_at']);
                unset($params_array['user']);

                
                $postUpdate = Post::where('id', $id)->where('user_id', $user->sub)->first();
                //var_dump($postUpdate); die();
                if(!empty($postUpdate) && is_object($post)){
                    //update
                    $post = $postUpdate->updateOrCreate(['id' => $id, 'user_id' => $user->sub], $params_array);
                    //devolver algo
                    $data = array(
                        'code' => 200,
                        'status' => 'success',
                        'post' => $post,
                        'changes' => $params_array                    
                    );
                }else{
                    //devolver algo
                    $data = array(
                        'code' => 404,
                        'status' => 'error',
                        'message' => 'No se encontró post'                    
                    );
                }


            }
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Datos incorrectos'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request) {

        //conseguir usuario identificado
        $user = $this->getIdentity($request);

        //conseguir registro
        $post = Post::where('id', $id)
                    ->where('user_id', $user->sub)
                    ->first();

        //var_dump($post); die();
        if(!empty($post)){
            //borrar
            $post->delete();

            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'Se ha borrado el post'
            );
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No existe el post'
            );
        }

        //devolver
        return response()->json($data, $data['code']);
    }

    private function getIdentity(Request $request){
         //conseguir usuario identificado
         $jwtAuth = new JwtAUTH();
         $token = $request->header('Authorization', null);
         $user = $jwtAuth->checkToken($token, true);
         
         return $user;
    }

    
    public function upload(Request $request){
        //recoger imagen
        $image = $request->file('file0');

        //validar imagen
        $validate = \Validator::make($request->all(), [
            'file0' => 'image|mimes:jpg,png,gif,jpeg'
        ]);

        //guardar imagen
        if(!$image || $validate->fails()){
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'No se ha podido subir imagen' 
            );   
        }else{
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('images')->put($image_name, \File::get($image));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );   
        }

        //devolver datos
        return response()->json($data, $data['code']);
    }

    public function getImage($filename){
        //comprobar si la imagen existe
        $isset = \Storage::disk('images')->exists($filename);
        
        if($isset){
            //conseguir a imagen
            $file = \Storage::disk('images')->get($filename);
            
            //devolver la imagen
            return new Response($file, 200);
        }else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'La imagen no existe' 
            );
        }

        return response()->json($data, $data['code']);
    } 

    public function getPostByCategory($id){
        $posts = Post::where('category_id', $id)->get()->load('user');

        $data = array(
            'code' => 200,
            'status' => 'success',
            'posts' => $posts
        );   

        return response()->json($data, $data['code']);
    }

    public function getPostByUser($id){
        $posts = Post::where('user_id', $id)->get()->load('category');

        $data = array(
            'code' => 200,
            'status' => 'success',
            'posts' => $posts
        );   

        return response()->json($data, $data['code']);
    }
}
