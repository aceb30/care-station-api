<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Tener en cuenta los origenes que se usarán. Hasta ahora solo hay
        // localhosts, hay que agregar IPs
        $allowedOrigins = ['http://localhost:8082', 'http://localhost:8001']; 
        $origin = $request->header('Origin');
        $headers = [
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Auth-Token, Origin, Authorization',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Origin'  => $request->header('Origin') ? $origin : '*', 
        ];

        // Manejo de solicitudes pre-vuelo (OPTIONS)
        if ($request->isMethod('OPTIONS')) {
            return response()->json('OK', 200)->withHeaders($headers);
        }

        // Obtener la respuesta y agregar las cabeceras
        $response = $next($request);
        
        // Agregar las cabeceras a la respuesta
        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}