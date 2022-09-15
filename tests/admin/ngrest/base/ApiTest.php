<?php

namespace admintests\admin\ngrest\base;

use admintests\AdminModelTestCase;
use luya\admin\models\User;
use luya\admin\models\UserAuthNotification;
use luya\admin\ngrest\base\Api;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\AdminDatabaseTableTrait;
use yii\web\NotFoundHttpException;

class ApiTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testWithRelations()
    {
        $this->createAdminLangFixture();
        $rel = new TestApi('test-api', $this->app);
        $this->app->request->setQueryParams(['expand' => 'bar,foo,news']);
        $f = $rel->getWithRelation('index');

        $this->assertSame(['bar', 'foo', 'news.image'], $f);
    }

    public function testWithRelationsActions()
    {
        $this->createAdminLangFixture();
        $rel = new TestActionApi('test-api', $this->app);
        $this->app->request->setQueryParams(['expand' => 'image,news']);
        $f = $rel->getWithRelation('index');

        $this->assertSame(['images', 'news.image'], $f);
    }

    public function testFilterAction()
    {
        $this->createAdminLangFixture();
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

    public function testActiveButtons()
    {
        $this->createAdminLangFixture();
        $fixture = new NgRestModelFixture([
            'modelClass' => User::class,
            'fixtureData' => [
                1  => [
                    'id' => 1,
                    'is_deleted' => 0,
                    'is_api_user' => 0,
                    'email' => 'foobar@barfoo.com',
                ]
            ]
        ]);

        new NgRestModelFixture([
            'modelClass' => UserAuthNotification::class,
        ]);
        $rel = new TestActionApi('test-api', $this->app);
        $rel->modelClass = User::class;
        $response = $rel->actionActiveButton('hash', 1);
        $this->assertFalse($response);
    }

    public function testActionActiveSelection()
    {
        $this->createAdminLangFixture();
        $fixture = new NgRestModelFixture([
            'modelClass' => User::class,
            'fixtureData' => [
                1  => [
                    'id' => 1,
                    'is_deleted' => 0,
                    'is_api_user' => 0,
                    'email' => 'foobar@barfoo.com',
                ]
            ]
        ]);

        new NgRestModelFixture([
            'modelClass' => UserAuthNotification::class,
        ]);
        $rel = new TestActionApi('test-api', $this->app);
        $rel->modelClass = User::class;

        $this->expectException(NotFoundHttpException::class);
        $rel->actionActiveSelection(0);
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
