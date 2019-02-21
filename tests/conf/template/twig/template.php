<?php

return [
    'driver' => 'twig',
    'file_sufix' => '.tpl',
    'auto_escape' => true,
    'debug' => true,
    'force_compile' => false,
    'strict' => true,
    'optimizations' => -1,

    'paths' => [
        'templates' => __DIR__.'/templates',
    ]
];
