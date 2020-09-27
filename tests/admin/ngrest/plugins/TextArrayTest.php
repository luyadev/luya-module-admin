<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminModelTestCase;
use luya\admin\models\SearchData;
use yii\base\ModelEvent;
use luya\admin\ngrest\plugins\TextArray;
use luya\base\DynamicModel;
use luya\testsuite\fixtures\NgRestModelFixture;

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
