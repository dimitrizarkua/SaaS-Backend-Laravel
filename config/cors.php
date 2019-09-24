<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS
    |--------------------------------------------------------------------------
    |
    | allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
    | to accept any value.
    |
    */

    'supportsCredentials'    => true,
    'allowedOrigins'         => explode(' ', env('CORS_ORIGINS', 'http://localhost:3000')),
    'allowedOriginsPatterns' => [],
    'allowedHeaders'         => ['Origin', 'Content-Type', 'Authorization', 'Accept', 'X-Requested-with', 'Authorization'],
    'allowedMethods'         => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
    'exposedHeaders'         => [],
    'maxAge'                 => 604800,

];
