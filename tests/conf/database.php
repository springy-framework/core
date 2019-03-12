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
            'migration_dir' => __DIR__.'/../migration/mysql',
        ],
        'mysql_file' => [
            'driver' => 'mysql',
            'host' => ['localhost'],
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
            'host' => ['localhost'],
            'username' => 'travis',
            'password' => '',
            'database' => 'test',
            'round_robin' => [
                'driver' => 'memcached',
                'address' => 'localhost',
            ],
        ],
        'postgres' => [
            'driver' => 'pgsql',
            'host' => 'localhost',
            'username' => 'test',
            'password' => '123',
            'database' => 'test',
        ],
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'foreign_key_constraints' => '',
        ],
    ],
];
