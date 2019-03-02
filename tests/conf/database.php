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
        'mysql_file' => [
            'driver' => 'mysql',
            'host' => ['localhost', '127.0.0.1'],
            'username' => 'travis',
            'password' => '',
            'database' => 'test',
            'round_robin' => [
                'driver' => 'file',
                'file' => __DIR__.'/../var/roundrobin',
            ],
        ],
        'mysql_mc' => [
            'driver' => 'mysql',
            'host' => ['localhost', '127.0.0.1'],
            'username' => 'travis',
            'password' => '',
            'database' => 'test',
            'round_robin' => [
                'driver' => 'memcached',
                'address' => 'localhost',
            ],
        ],
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'foreign_key_constraints' => '',
        ],
    ],
];
