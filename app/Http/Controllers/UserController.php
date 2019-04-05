<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\User;

class UserController extends Controller {

    //
    public function test(Request $request) {
        return "Método de prueba de UserController";
    }

    public function register(Request $request) {

        //recoger datos del usuario por post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        //validar datos
        if (!empty($params_array)) {
            $params_array = array_map('trim', $params_array);

            //comprobar que el usuario no exista
            $validate = \Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users',
                        'password' => 'required'
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'No se ha podido registrar el usuario',
                    'error' => $validate->errors()
                );
            } else {
                //cifrar contraseña
                $pwd = hash('sha256', $params->password);


                //crear usuario
                $user = new User();

                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';

                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Usuario registrado con éxito',
                    'user' => $user
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Los datos enviados no son correctos'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function login(Request $request) {

        $jwtAuth = new \App\Utils\JwtAUTH();

        //Recibir datos
        $json = $request->input('json', null);

        $params = json_decode($json);
        $params_array = json_decode($json, true);

        $validate = \Validator::make($params_array, [
                    'email' => 'required|email',
                    'password' => 'required'
        ]);

        if ($validate->fails()) {
            $signup = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No se ha podido Identificar al usuario',
                'error' => $validate->errors()
            );
        } else {

            //cifrar contraseña
            $pwd = hash('sha256', $params->password);

            //devolver token o datos

            $signup = $jwtAuth->signup($params->email, $pwd);

            if (!empty($params->getToken)) {
                $signup = $jwtAuth->signup($params->email, $pwd, true);
            }
        }

        return response()->json($signup, 200);
    }

    public function update(Request $request) {
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Utils\JwtAUTH();
        $checkToken = $jwtAuth->checkToken($token);


        //Recoger datos por post

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        $params = json_decode($json);


        if ($checkToken && !empty($params_array)) {
      
            //Sacar datos del usuario identificado
            $user = $jwtAuth->checkToken($token, true);

            //Validar datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users,' . $user->sub
            ]);

            //Quitar campos que no se actualizarán
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            //Actualizar el usuario
            $user_update = User::where('id', $user->sub)->update($params_array);

            //Devolver array
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user,
                'changes' => $params_array
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Usuario no identificado'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request) {

        //Recoger datos de la petición
        $image = $request->file('file0');

        //validar imagen
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //Guardar imagen
        if(!$image || $validate->fails()){
            //devolver el resultado
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen',
                'image' => $image
            );

        }else{
        
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));   
            
            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );

        }      

        return response()->json($data, $data['code']);
    }

    public function getImage($filename){
        $isset = \Storage::disk('users')->exists($filename);

        if($isset){
            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
        }else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'La imagen no existe'
            );
            return response()->json($data, $data['code']);
        }
        
    } 

    public function detail($id){
        $user = User::find($id);

        if(is_object($user)){
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user
            );
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El usuario no existe'
            );
        }
        return response()->json($data, $data['code']);
    }
}
