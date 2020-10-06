<?php

namespace luya\admin\tests;

use luya\testsuite\cases\NgRestTestCase as BaseNgRestTestCase;

class NgRestTestCase extends BaseNgRestTestCase
{
    public function getConfigArray()
    {
        return [
            'id' => 'adminmodeltest',
            'basePath' => dirname(__DIR__),
            'language' => 'en',
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
                'storage' => [
                    'class' => 'luya\admin\filesystem\DummyFileSystem'
                ]
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
