<?php

return [
    'driver' => 'twig',
    'file_sufix' => '.html',
    'auto_escape' => false,
    'debug' => false,
    'force_compile' => false,
    'strict' => false,
    'optimizations' => 0,

    'paths' => [
        'cache' => __DIR__.'/../../var',
        'compiled' => __DIR__.'/../../var',
        'templates' => __DIR__.'/templates',
    ],
];
