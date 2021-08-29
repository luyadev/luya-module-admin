<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminTestCase;
use admintests\data\fixtures\UserFixture;
use luya\admin\models\User;
use luya\admin\ngrest\Config;
use luya\admin\ngrest\ConfigBuilder;
use luya\admin\ngrest\plugins\SelectArrayGently;
use luya\admin\ngrest\render\RenderCrud;
use luya\testsuite\fixtures\NgRestModelFixture;
use yii\base\Event;
use yii\base\ModelEvent;

class SelectArrayGentlyTest extends AdminTestCase
{
    public function testBasicMethods()
    {
        $plugin = new SelectArrayGently([
                                            'name'  => 'testName',
                                            'alias' => 'test',
                                            'i18n'  => false,
                                            'data'  => [
                                                1 => 'Mr.',
                                                2 => 'Mrs.',
                                                3 => 'Dr.'
                                            ],
                                        ]);

        $this->assertSame(
            '<select-array-gently model="someModel" options="service.testName.selectdata"></select-array-gently>',
            $plugin->renderList(1, 'someModel')
        );

        $this->assertSame([
                              0 => ['value' => 1, 'label' => 'Mr.'],
                              1 => ['value' => 2, 'label' => 'Mrs.'],
                              2 => ['value' => 3, 'label' => 'Dr.'],
                          ], $plugin->getData());

        unset($plugin);
    }
}