<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminTestCase;
use luya\admin\ngrest\plugins\Injector;

class InjectorTest extends AdminTestCase
{
    public function testInjectorDirectiveForCreate()
    {
        $plugin = new Injector([
            'alias' => 'injector',
            'name' => 'injector',
            'attribute' => 'type'
        ]);

        $directive = $plugin->renderCreate(1, "data.create.value");

        $this->assertSame($directive, '<zaa-injector dir="data.create.type" fieldid="1" model="data.create.value" label="injector" fieldname="injector" i18n=""></zaa-injector>');
    }

    public function testInjectorDirectiveForUpdate()
    {
        $plugin = new Injector([
            'alias' => 'injector',
            'name' => 'injector',
            'attribute' => 'type'
        ]);

        $directive = $plugin->renderUpdate(1, "data.update.value");

        $this->assertSame($directive, '<zaa-injector dir="data.update.type" fieldid="1" model="data.update.value" label="injector" fieldname="injector" i18n=""></zaa-injector>');
    }
}
