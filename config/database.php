<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        # Default DB marketAdvertisment
        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'read' => [
                'host' => [
                    env('DB_HOST', '127.0.0.1'),
                ],
            ],
            'write' => [
                'host' => [
                    env('DB_HOST', '127.0.0.1'),
                ],
            ],
            'host' => env('DB_HOST', 'forge'),
            'port' => env('DB_PORT', 'forge'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
            'options'   => [
                PDO::ATTR_PERSISTENT => true,
            ],
        ],
        
        # For master
        'pgsql_masters' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'read' => [
                'host' => [
                    env('DB_MASTER_HOST', '127.0.0.1'),
                ],
            ],
            'write' => [
                'host' => [
                    env('DB_MASTER_HOST', '127.0.0.1'),
                ],
            ],
            'host' => env('DB_MASTER_HOST', 'forge'),
            'port' => env('DB_MASTER_PORT', 'forge'),
            'database' => env('DB_MASTER_DATABASE', 'forge'),
            'username' => env('DB_MASTER_USERNAME', 'forge'),
            'password' => env('DB_MASTER_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
            'options'   => [
                PDO::ATTR_PERSISTENT => true,
            ],
        ],

        # For Property
        'pgsql_property' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_PROP_HOST', 'forge'),
            'port' => env('DB_PROP_PORT', 'forge'),
            'database' => env('DB_PROP_DATABASE', 'forge'),
            'username' => env('DB_PROP_USERNAME', 'forge'),
            'password' => env('DB_PROP_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
            'options'   => [
                PDO::ATTR_PERSISTENT => true,
            ],
        ],

         #_For Trade Service
         'pgsql_trade' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_TRADE_HOST', '127.0.0.1'),
            'read' => [
                'host' => [
                    env('DB_TRADE_READ_HOST', env('DB_TRADE_HOST', '127.0.0.1')),
                ],
                'port' => env('DB_TRADE_READ_PORT', env('DB_TRADE_PORT', '5432')),
                'database' => env('DB_TRADE_READ_DATABASE', env('DB_TRADE_DATABASE', 'juidco_trade')),
                'username' => env('DB_TRADE_READ_USERNAME', env('DB_TRADE_USERNAME', 'postgres')),
                "password" => env('DB_TRADE_READ_PASSWORD', env('DB_TRADE_PASSWORD', 'root')),
            ],
            'write' => [
                'host' => env('DB_TRADE_HOST', '127.0.0.1'),
            ],
            'port' => env('DB_TRADE_PORT', '5432'),
            'database' => env('DB_TRADE_DATABASE', 'forge'),
            'username' => env('DB_TRADE_USERNAME', 'forge'),
            'password' => env('DB_TRADE_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
            'options'   => [
                PDO::ATTR_PERSISTENT => true,
            ],
        ],

        #_For Notice Service
        'pgsql_notice' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_NOTICE_HOST', '127.0.0.1'),
            'read' => [
                'host' => [
                    env('DB_NOTICE_READ_HOST', env('DB_NOTICE_HOST', '127.0.0.1')),
                ],
                'port' => env('DB_NOTICE_READ_PORT', env('DB_NOTICE_PORT', '5432')),
                'database' => env('DB_NOTICE_READ_DATABASE', env('DB_NOTICE_DATABASE', 'juidco_notice')),
                'username' => env('DB_NOTICE_READ_USERNAME', env('DB_NOTICE_USERNAME', 'postgres')),
                "password" => env('DB_NOTICE_READ_PASSWORD', env('DB_NOTICE_PASSWORD', 'root')),
            ],
            'write' => [
                'host' => env('DB_TRADE_HOST', '127.0.0.1'),
            ],
            'port' => env('DB_NOTICE_PORT', '5432'),
            'database' => env('DB_NOTICE_DATABASE', 'juidco_notice'),
            'username' => env('DB_NOTICE_USERNAME', 'postgres'),
            'password' => env('DB_NOTICE_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
            'options'   => [
                PDO::ATTR_PERSISTENT => true,
            ],
        ],



        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'predis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
