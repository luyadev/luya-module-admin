<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminModelTestCase;
use luya\admin\models\SearchData;
use luya\admin\ngrest\plugins\TextArray;
use luya\testsuite\fixtures\NgRestModelFixture;
use yii\base\ModelEvent;

class TextArrayTest extends AdminModelTestCase
{
    public function testImplodeListView()
    {
        $fixture = new NgRestModelFixture(['modelClass' => SearchData::class]);

        $model = new SearchData();

        $event = new ModelEvent(['sender' => $model]);

        $plugin = new TextArray([
            'alias' => 'query',
            'name' => 'query',
            'i18n' => false,
        ]);

        $model->query = '[{"value":"foo"}, {"value":"baz"}]';
        $plugin->onListFind($event);
        $this->assertSame('foo, baz', $model->query);

        $model->query = '[{"value":"foo"}, {"value":"baz"}]';
        $plugin->onBeforeFind($event);
        $this->assertSame(['foo', 'baz'], $model->query);
    }
}
