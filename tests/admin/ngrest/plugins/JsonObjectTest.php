<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminModelTestCase;
use luya\admin\models\SearchData;
use luya\admin\ngrest\plugins\JsonObject;
use luya\testsuite\fixtures\NgRestModelFixture;
use yii\base\ModelEvent;

class JsonObjectTest extends AdminModelTestCase
{
    public function testImplodeListView()
    {
        $fixture = new NgRestModelFixture(['modelClass' => SearchData::class]);

        $model = new SearchData();

        $event = new ModelEvent(['sender' => $model]);

        $plugin = new JsonObject([
            'alias' => 'query',
            'name' => 'query',
            'i18n' => false,
        ]);


        $this->assertNotEmpty($plugin->renderList('id', 'model'));
        $this->assertNotEmpty($plugin->renderCreate('id', 'model'));
        $this->assertNotEmpty($plugin->renderUpdate('id', 'model'));

        $model->query = '{"foo":"bar","baz":"foo"}';
        $plugin->onBeforeExpandFind($event);
        $this->assertSame(['foo' => 'bar', 'baz' => 'foo'], $model->query);

        $model->query = ['abc' => 'def'];
        $plugin->onBeforeSave($event);
        $this->assertSame('{"abc":"def"}', $model->query);

        $model->query = '{"foo":"bar","baz":"foo"}';
        $plugin->onAfterFind($event);
        $this->assertSame(['foo' => 'bar', 'baz' => 'foo'], $model->query);

        $model->query = ['abc' => 'def'];
        $plugin->i18n = true;
        $plugin->onBeforeSave($event);
        $this->assertSame(['abc' => 'def'], $model->query);

        $model->query = '{"foo":"bar","baz":"foo"}';
        $plugin->i18n = true;
        $plugin->onBeforeExpandFind($event);
        $this->assertSame('{"foo":"bar","baz":"foo"}', $model->query);
    }
}
