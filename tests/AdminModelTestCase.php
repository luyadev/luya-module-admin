<?php

namespace admintests;

use luya\testsuite\cases\WebApplicationTestCase;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class AdminModelTestCase extends WebApplicationTestCase
{
    use AdminDatabaseTableTrait;

    public function getConfigArray()
    {
        return [
            'id' => 'adminmodeltest',
            'basePath' => dirname(__DIR__),
            'language' => 'en',
            'aliases' => [
                '@bower' => '@vendor/bower-asset',
                '@npm'   => '@vendor/npm-asset',
            ],
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
                'storage' => [
                    'class' => 'luya\admin\filesystem\DummyFileSystem'
                ],
                'session' => ['class' => 'luya\testsuite\components\DummySession'],
                'cache' => ['class' => 'yii\caching\DummyCache'],
                'assetManager' => [
                    'basePath' => dirname(__DIR__) . '/tests/assets',
                    'bundles' => [
                        'yii\web\JqueryAsset' => false,
                    ],
                ],
            ],
            'modules' => [
                'admin' => [
                    'class' => 'luya\admin\Module',
                    'queueMutexClass' => 'yii\mutex\FileMutex',
                ],
            ],
        ];
    }
}
