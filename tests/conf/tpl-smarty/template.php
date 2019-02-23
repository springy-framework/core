<?php

return [
    'driver' => 'smarty',
    'file_sufix' => '.tpl',
    'auto_escape' => true,
    'debug' => false,
    'force_compile' => false,
    'strict' => false,
    'use_sub_dirs' => false,

    'paths' => [
        'cache' => __DIR__.'/../../var',
        'compiled' => __DIR__.'/../../var',
        'templates' => __DIR__.'/templates',
    ],
];
