<?php

return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'username' => 'travis',
            'password' => '',
            'database' => 'test',
        ],
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'foreign_key_constraints' => '',
        ],
    ],
];
