<?php

namespace admintests\admin\ngrest\base;

use admintests\AdminModelTestCase;
use admintests\AdminTestCase;
use luya\admin\models\User;
use luya\admin\models\UserAuthNotification;
use luya\admin\ngrest\base\Api;
use luya\testsuite\fixtures\NgRestModelFixture;

class ApiTest extends AdminModelTestCase
{
    public function testWithRelations()
    {
        $rel = new TestApi('test-api', $this->app);
        $this->app->request->setQueryParams(['expand' => 'bar,foo,news']);
        $f = $rel->getWithRelation('index');

        $this->assertSame(['bar', 'foo', 'news.image'], $f);
    }

    public function testWithRelationsActions()
    {
        $rel = new TestActionApi('test-api', $this->app);
        $this->app->request->setQueryParams(['expand' => 'image,news']);
        $f = $rel->getWithRelation('index');

        $this->assertSame(['images', 'news.image'], $f);
    }

    public function testFilterAction()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => User::class,
        ]);
        new NgRestModelFixture([
            'modelClass' => UserAuthNotification::class,
        ]);
        $rel = new TestActionApi('test-api', $this->app);
        $rel->modelClass = User::class;
        $response = $rel->actionFilter('Removed');

        $this->assertInstanceOf('yii\data\ActiveDataProvider', $response);

        $this->expectException("yii\base\InvalidCallException");
        $rel->actionFilter('Unknown');
    }
}

class TestApi extends Api
{
    public function withRelations()
    {
        return ['bar', 'foo', 'news.image'];
    }

    public $modelClass = 'null';
}

class TestActionApi extends TestApi
{
    public function withRelations()
    {
        return [
             'index' => ['images', 'news.image', 'not'],
             'list' => ['user'],
        ];
    }
}
