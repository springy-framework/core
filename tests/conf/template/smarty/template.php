<?php

return [
    'driver' => 'smarty',
    'file_sufix' => '.html',
    'auto_escape' => true,
    'debug' => true,
    'force_compile' => false,
    'strict' => true,
    'use_sub_dirs' => true,

    'paths' => [
        'templates' => __DIR__.'/templates',
    ],
];
