<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // Cambiar en producción!!!!! 
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*', 'Authorization'], // Asegúrate de que 'Authorization' esté aquí
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false, // Cambia a true si usas cookies/sesiones (con Sanctum tokens no es necesario)
];