<?php

namespace admintests;

use luya\base\Boot;
use luya\testsuite\cases\BaseTestSuite;

require 'vendor/autoload.php';
require 'data/env.php';

class AdminConsoleSqLiteTestCase extends BaseTestSuite
{
    public function getConfigArray()
    {
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
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
                'storage' => [
                    'class' => 'luya\admin\filesystem\DummyFileSystem'
                ]
            ],
        ];
    }

    public function bootApplication(Boot $boot)
    {
        $boot->applicationConsole();
    }

    protected function removeNewline($text)
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));

        return str_replace(['> ', ' <'], ['>', '<'], $text);
    }
}
