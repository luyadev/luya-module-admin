<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminModelTestCase;
use luya\admin\base\TypesInterface;
use luya\admin\models\SearchData;
use luya\admin\ngrest\plugins\MultipleInputs;
use luya\testsuite\fixtures\NgRestModelFixture;
use yii\base\ModelEvent;

class MultipleInputsTest extends AdminModelTestCase
{
    public function testImplodeListView()
    {
        $fixture = new NgRestModelFixture(['modelClass' => SearchData::class]);

        $model = new SearchData();

        $event = new ModelEvent(['sender' => $model]);

        $plugin = new MultipleInputs([
            'alias' => 'query',
            'name' => 'query',
            'i18n' => false,
            'types' => [
                [
                    'type' => TypesInterface::TYPE_TEXT,
                    'var' => 'title',
                    'label' => 'Title',
                ],
            ]
        ]);

        $model->query = '[{"title":"title"}]';
        $plugin->onListFind($event);
        $this->assertSame('[{"title":"title"}]', $model->query);

        $model->query = '[{"title":"title"}]';
        $plugin->onBeforeFind($event);
        $this->assertSame('[{"title":"title"}]', $model->query);
    }
}
