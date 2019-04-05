<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Utils\JwtAUTH();
        $checkToken = $jwtAuth->checkToken($token);

        if($checkToken){
            return $next($request);
        }
        else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Usuario no identificado'
            );
            return response()->json($data, $data['code']);
        }
        return $next($request);
    }
}
