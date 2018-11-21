<?php

namespace admintests\admin\ngrest\base;

use admintests\AdminTestCase;
use luya\admin\ngrest\base\Api;


class ApiTest extends AdminTestCase
{
    public function testWithRelations()
    {
        $rel = new TestApi('test-api', $this->app);
        $this->app->request->setQueryParams(['expand' => 'bar,foo,news']);
        $f = $rel->getWithRelation('index');

        $this->assertSame(['bar', 'foo', 'news.image'], $f);
    }

    public function testWithRelatiosnAction()
    {
        $rel = new TestActionApi('test-api', $this->app);
        $this->app->request->setQueryParams(['expand' => 'image,news']);
        $f = $rel->getWithRelation('index');

        $this->assertSame(['images', 'news.image'], $f);
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