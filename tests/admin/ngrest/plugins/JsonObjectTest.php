<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminModelTestCase;
use luya\admin\models\SearchData;
use luya\admin\ngrest\plugins\JsonObject;
use yii\base\ModelEvent;
use luya\testsuite\fixtures\NgRestModelFixture;

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
        
        $model->query = '{"foo":"bar","baz":"foo"}';
        $plugin->onListFind($event);
        $this->assertSame('{"foo":"bar","baz":"foo"}', $model->query);

        $model->query = '{"foo":"bar","baz":"foo"}';
        $plugin->onAfterFind($event);
        $this->assertSame(['foo' => 'bar', 'baz' => 'foo'], $model->query);
    }
}
