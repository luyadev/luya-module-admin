<?php

namespace luya\admin\tests\admin\openapi;

use admintests\AdminModelTestCase;
use cebe\openapi\spec\Operation;
use luya\admin\apis\UserController;
use luya\admin\models\Config;
use luya\admin\models\Group;
use luya\admin\models\Logger;
use luya\admin\models\NgrestLog;
use luya\admin\models\Property;
use luya\admin\models\ProxyBuild;
use luya\admin\models\ProxyMachine;
use luya\admin\models\QueueLog;
use luya\admin\models\QueueLogError;
use luya\admin\models\StorageEffect;
use luya\admin\models\StorageFile;
use luya\admin\models\StorageFilter;
use luya\admin\models\StorageImage;
use luya\admin\models\User;
use luya\admin\openapi\events\PathParametersEvent;
use luya\admin\openapi\Generator;
use luya\admin\openapi\specs\ActiveRecordToSchema;
use luya\admin\openapi\specs\ControllerSpecs;
use luya\admin\openapi\UrlRuleRouteParser;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\DatabaseTableTrait;
use luya\web\UrlManager;
use yii\base\Event;
use yii\web\UrlRule;

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



    public function testSecuritySchema()
    {
        $this->createAdminLangFixture();
        $this->createAdminUserFixture();
        $this->createAdminGroupFixture(1);

        $this->app->controllerMap = ['user' => UserController::class];
        $routerParser = new UrlRuleRouteParser('user', 'user/index', [new UrlRule(['pattern' => 'user', 'route' => 'user/index', 'verb' => 'GET'])], 'user');

        $operations = $routerParser->getOperations();
        $this->assertSame('/user', $routerParser->getPath());
        $this->assertArrayHasKey('GET', $operations);

        /** @var Operation $operation */
        $operation = $operations['GET'];

        $this->assertNull($operation->security);
        // since security is assigned in a global scope
        /*
        $this->assertSame([
            'BasicAuth'
        ], (array) $operation->security[0]->getSerializableData());
        */
    }

    public function testGetPaths()
    {
        $this->createAdminLangFixture();
        new NgRestModelFixture(['modelClass' => Property::class]);
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
        new NgRestModelFixture(['modelClass' => NgrestLog::class]);
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

    public function testAssignUrlRules()
    {
        $this->createAdminLangFixture();
        $urlManager = new UrlManager([
            'rules' =>
                [
                    'this/is/my/pattern' => 'admin/account/dashboard'
                ]

        ]);

        $generator = new Generator($urlManager, []);
        $generator->assignUrlRule('admin/account/dashboard', 'POST', 'endpointname');

        $this->assertSame(['/this/is/my/pattern'], array_keys($generator->getPaths()));
    }

    public function testModelResponse()
    {
        $this->createAdminLangFixture();
        $this->createAdminUserFixture();
        $this->createAdminGroupFixture(1);
        $spec = new ControllerSpecs(new UserController('user', $this->app));

        $model = new User();

        $ars = new ActiveRecordToSchema($spec, $model);

        $props = $ars->getProperties();
        $this->assertSame([
            'id',
            'title',
            'firstname',
            'lastname',
            'email',
            'is_deleted',
            'is_api_user',
            'api_last_activity',
            'auth_token',
            'is_request_logger_enabled',
            'email_verification_token_timestamp',
            'login_attempt_lock_expiration',
            'login_attempt',
            'email_verification_token',
            'api_allowed_ips',
            'api_rate_limit',
            'cookie_token',
            'settings',
            'force_reload',
            'secure_token_timestamp',
            'secure_token',
            'password',
            'password_salt',
            'login_2fa_enabled',
            'login_2fa_secret',
            'login_2fa_backup_key',
            'password_verification_token',
            'password_verification_token_timestamp',
            'setting',
            'groups',
        ], array_keys($props));

        $groupModelKeys = array_keys($ars->createSchema('groups')->items->properties);

        $this->assertSame([
            'id', 'name', 'text', 'is_deleted', 'users',
        ], $groupModelKeys);
    }

    public function testParamsEvents()
    {
        $this->createAdminLangFixture();
        $this->createAdminUserFixture();
        $this->createAdminGroupFixture(1);

        Event::on(Generator::class, Generator::EVENT_PATH_PARAMETERS, function (PathParametersEvent $e) {
            unset($e->params['_lang']);

            $e->params['foo'] = 'bar';
        });

        $spec = new ControllerSpecs(new UserController('user', $this->app));
        $params = $spec->getParameters();

        $this->assertSame([
            'foo' => 'bar',
        ], $params);
    }

    public function testFilterParams()
    {
        $this->createAdminLangFixture();
        $this->createAdminUserFixture();
        $this->createAdminGroupFixture(1);

        Event::off(Generator::class, Generator::EVENT_PATH_PARAMETERS);

        $spec = new ControllerSpecs(new UserController('user', $this->app, ['filterSearchModelClass' => Group::class]));
        $params = $spec->getParameters();

        $this->assertSame(5, count($params['filter']->schema->properties)); // the user has 5 keys which are required.
    }

    public function testUniqOperationIdGeneraator()
    {
        $this->assertSame('test', Generator::generateUniqueOperationId('test'));
        $this->assertSame('test1', Generator::generateUniqueOperationId('test'));
        $this->assertSame('test2', Generator::generateUniqueOperationId('test'));
    }
}
