<?php

namespace luya\admin\tests\admin\openapi;

use admintests\AdminModelTestCase;
use luya\admin\models\Config;
use luya\admin\models\Logger;
use luya\admin\models\ProxyBuild;
use luya\admin\models\ProxyMachine;
use luya\admin\models\QueueLog;
use luya\admin\models\QueueLogError;
use luya\admin\models\StorageEffect;
use luya\admin\models\StorageFile;
use luya\admin\models\StorageFilter;
use luya\admin\models\StorageImage;
use luya\admin\openapi\Generator;
use luya\admin\openapi\OpenApiGenerator;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\DatabaseTableTrait;
use luya\web\Bootstrap;
use luya\web\UrlManager;

class GeneratorTest extends AdminModelTestCase
{
    use DatabaseTableTrait;

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
                'request' => [
                    'isConsoleRequest' => false,
                    'forceWebRequest' => true,
                    'isAdmin' => true,
                ]
            ],
            'modules' => [
                'admin' => [
                    'class' => 'luya\admin\Module',
                ],
            ],
        ];
    }

    public function testGetPaths()
    {
        $this->createAdminLangFixture();
        new NgRestModelFixture(['modelClass' => Logger::class]);
        $this->createAdminUserFixture();
        $this->createAdminGroupFixture(1);
        new NgRestModelFixture(['modelClass' => StorageEffect::class]);
        new NgRestModelFixture(['modelClass' => StorageFile::class]);
        new NgRestModelFixture(['modelClass' => StorageFilter::class]);
        new NgRestModelFixture(['modelClass' => StorageImage::class]);
        $this->createAdminTagFixture();
        $this->createAdminTagRelationFixture();
        new NgRestModelFixture(['modelClass' => ProxyBuild::class]);
        new NgRestModelFixture(['modelClass' => ProxyMachine::class]);
        new NgRestModelFixture(['modelClass' => Config::class]);
        new NgRestModelFixture(['modelClass' => QueueLog::class]);
        new NgRestModelFixture(['modelClass' => QueueLogError::class]);
        $generator = new Generator($this->app->urlManager, $this->app->getModule('admin')->controllerMap);

        $paths = $generator->getPaths();

        $this->assertTrue(count($paths) > 0);
    }

    public function testOpenApiGenerator()
    {
        $this->createAdminLangFixture();
        $urlManager = new UrlManager([
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['v1/does-not-exsts', 'admin/api-admin-remote'],
                ]
            ]
        
        ]);

        $generator = new Generator($urlManager, []);

        $this->assertSame(['/admin/api-admin-remotes'], array_keys($generator->getPaths()));
    }
}