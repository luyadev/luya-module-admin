<?php

namespace admintests;

use luya\testsuite\cases\WebApplicationTestCase;

class AdminModelTestCase extends WebApplicationTestCase
{
    public function getConfigArray()
    {
        return [
            'id' => 'adminmodeltest',
            'basePath' => dirname(__DIR__),
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
                ],
            ],
        ];
    }
}
