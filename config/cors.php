<?php

return [

    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    // 'allowed_origins' => ['http://127.0.0.1:8080/', 'http://localhost:8080/'], <-- doesn't work, still gets CORS error
    'allowed_origins' => ['*'],  // <-- it works but it should not be like that
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['content-type', 'accept', 'x-custom-header', 'Access-Control-Allow-Origin'],
    // 'allowed_headers' => ['*'],
    'exposed_headers' => ['x-custom-response-header'],
    'max_age' => 0,
    'supports_credentials' => false,
];
