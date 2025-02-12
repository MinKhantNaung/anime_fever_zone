<?php

return [
    'name' => 'LaravelPWA',
    'manifest' => [
        'name' => env('APP_NAME', 'Anime Fever Zone'),
        'short_name' => 'AFZ',
        'start_url' => '/',
        'background_color' => '#ffffff',
        'theme_color' => '#9926f0',
        'display' => 'standalone',
        'orientation'=> 'any',
        'status_bar'=> 'black',
        'icons' => [
            '16x16' => [
                'path' => '/images/icons/icon-16x16.png',
                'purpose' => 'any'
            ],
            '32x32' => [
                'path' => '/images/icons/icon-32x32.png',
                'purpose' => 'any'
            ],
            '192x192' => [
                'path' => '/images/icons/icon-192x192.png',
                'purpose' => 'any'
            ],
            '512x512' => [
                'path' => '/images/icons/icon-512x512.png',
                'purpose' => 'any'
            ],
        ],
        'splash' => [
            '640x1136' => '/images/icons/4__iPhone_SE__iPod_touch_5th_generation_and_later_portrait.png',
            '750x1334' => '/images/icons/iPhone_8__iPhone_7__iPhone_6s__iPhone_6__4.7__iPhone_SE_portrait.png',
            '828x1792' => '/images/icons/iPhone_13_mini__iPhone_12_mini__iPhone_11_Pro__iPhone_XS__iPhone_X_portrait.png',
            '1125x2436' => '/images/icons/iPhone_11__iPhone_XR_portrait.png',
            '1242x2208' => '/images/icons/iPhone_8_Plus__iPhone_7_Plus__iPhone_6s_Plus__iPhone_6_Plus_portrait.png',
            '1242x2688' => '/images/icons/iPhone_11_Pro_Max__iPhone_XS_Max_portrait.png',
            '1536x2048' => '/images/icons/8.3__iPad_Mini_portrait.png',
            '1668x2224' => '/images/icons/10.5__iPad_Air_portrait.png',
            '1668x2388' => '/images/icons/11__iPad_Pro_M4_portrait.png',
            '2048x2732' => '/images/icons/12.9__iPad_Pro_portrait.png',
        ],
        'shortcuts' => [],
        'custom' => []
    ]
];
