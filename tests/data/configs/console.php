<?php

return [
    'id' => 'testenv',
    'siteTitle' => 'Luya Tests',
    'remoteToken' => 'testtoken',
    'basePath' => dirname(__DIR__),
    'defaultRoute' => 'admin',
    'language' => 'en',
    'aliases' => [
        '@runtime' => dirname(__DIR__) . '/runtime',
        '@data' => dirname(__DIR__),
    ],
    'modules' => [
        'admin' => [
            'class' => 'luya\admin\Module',
            'queueMutexClass' => 'yii\mutex\FileMutex',
        ],
        'crudmodulefolderadmin' => [
            'class' => 'admintests\data\modules\crudmodulefolder\admin\Module',
        ]
    ],
    'components' => [
        'session' => ['class' => 'yii\web\CacheSession'],
        'cache' => ['class' => 'yii\caching\DummyCache'],
        'request' => [
            'isConsoleRequest' => true
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => DB_DSN,
            'username' => DB_USER,
            'password' => DB_PASS,
            'charset' => 'utf8',
        ],
        'sqlite' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite::memory:',
        ],
        'storage' => [
            'class' => 'luya\admin\filesystem\DummyFileSystem'
        ]
    ],
];
