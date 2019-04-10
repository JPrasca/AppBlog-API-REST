<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Utils;

/**
 * Description of JwtAUTH
 *
 * @author jdpra
 */
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAUTH {

    public $key;

    public function __construct() {
        $this->key = 'Esto_es_una_key_0123456789';
    }

    //
    public function signup($email, $password, $getToken = null) {

        //Buscar si existe el usuario por las credenciales
        $user = User::where([
                    'email' => $email,
                    'password' => $password
                ])->first();

        //Comprobar si son correctas     
        $signup = false;

        if (is_object($user)) {
            $signup = true;
        }


        //generar el token del usuario identificado    
        if ($signup) {
            $token = array(
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            );

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);

            //devolver los datos decodificados o el token en función de un parámetro
            if (is_null($getToken)) {
                $data = $jwt;
            } else {
                $data = $decoded;
            }
        } else {
            $data = array(
                'status' => 'error',
                'message' => 'Login incorrecto'
            );
        }
        return $data;
    }

    //
    public function checkToken($jwt, $getIdentity = false) {
        $auth = false;

        try {
            $jwt = str_replace('"', '', $jwt);
            $decode = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }

        if (!empty($decode) && is_object($decode) && isset($decode->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }
        
        if($getIdentity) {return $decode;}

        return $auth;
    }

}
