<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminTestCase;
use luya\admin\ngrest\plugins\SelectArrayGently;

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
