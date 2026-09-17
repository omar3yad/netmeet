<?php

return [
    'name' => env('APP_NAME'),
    'manifest' => [
        'name' => env('APP_NAME'),
        'short_name' => env('APP_NAME'),
        'start_url' => '/',
        'background_color' => '#ffffff',
        'theme_color' => '#000000',
        'display' => 'standalone',
        'orientation'=> 'any',
        'status_bar'=> 'black',
        'icons' => [
            '16x16' => [    
                'path' => '/storage/images/FAVICON.png',
                'purpose' => 'any'
            ],
            '32x32' => [    
                'path' => '/storage/images/FAVICON.png',
                'purpose' => 'any'
            ],
            '192x192' => [
                'path' => '/storage/images/FAVICON.png',
                'purpose' => 'any'
            ],
            '512x512' => [
                'path' => '/storage/images/FAVICON.png',
                'purpose' => 'any'
            ],
        ],
        'custom' => []
    ]
];
