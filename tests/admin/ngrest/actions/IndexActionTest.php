<?php

namespace admintests\admin\ngrest\actions;

use Yii;
use admintests\AdminTestCase;
use luya\admin\ngrest\base\Api;
use luya\admin\ngrest\base\NgRestModel;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\admin\models\User;


class IndexActionTest extends AdminTestCase
{
    public function getConfigArray()
    {
        $array = parent::getConfigArray();
        $array['components']['db'] = [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite::memory:',
        ];
        $array['components']['session'] = ['class' => 'yii\web\CacheSession'];
        $array['components']['cache'] = ['class' => 'yii\caching\DummyCache'];
        $array['components']['adminuser'] = ['class' => 'luya\admin\components\AdminUser', 'enableSession' => false];

        return $array;
    }

    public function testCacheDepencie()
    {
        $model = new NgRestModelFixture([
            'modelClass' => TestModel::class,
            'fixtureData' => [
                'id1' => [
                    'name' => 'barfoo',
                ]
            ]
        ]);

        $ctrl = new TestApi('test-api', $this->app);
        Yii::$app->controller = $ctrl;
        $ctrl->detachBehavior('authenticator');
        $r = $ctrl->runAction('index');
        
        $this->assertSame([
            ['id' => null, 'name' => 'barfoo']
        ], $r);
    }
}

class TestApi extends Api
{
    public $modelClass = '\admintests\admin\ngrest\actions\TestModel';

    public $cacheDependency = [
        'class' => 'yii\caching\DbDependency',
        'sql' => 'SELECT MAX(id) FROM test_model',
    ];

    public function checkAccess($action, $model = null, $params = [])
    {
        return true;
    }
}

class TestModel extends NgRestModel
{
    public static function ngRestApiEndpoint()
    {
        return 'test-model-api';
    }

    public static function tableName()
    {
        return 'test_model';
    }

    public function rules()
    {
        return [
            [['name'], 'string'],
        ];
    }
}