<?php

return [
    'driver' => 'mustache',
    'file_sufix' => '.mustache',
    'auto_escape' => false,

    'paths' => [
        'cache' => __DIR__.'/../../var',
        'compiled' => __DIR__.'/../../var',
        'templates' => __DIR__.'/templates',
    ],
];
