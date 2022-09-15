<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminTestCase;
use luya\admin\ngrest\plugins\SelectAsyncApi;

class SelectAsyncApiTest extends AdminTestCase
{
    public function testGenericMethods()
    {
        $plugin = new SelectAsyncApi([
            'name' => 'selectasyncapi',
            'alias' => 'selectasyncapi',
            'i18n' => false,
            'api' => 'api/{otherfield}&foo={{bar}}',
        ]);

        $this->assertSame('<async-value api="api/{{otherfield}}&amp;foo={{{bar}}}" model="ngmodel" fields="[&quot;title&quot;]"></async-value>', $plugin->renderList('id', 'ngmodel'));
        $this->assertSame('<zaa-async-api-select api="api/{{otherfield}}&amp;foo={{{bar}}}" optionslabel="title" optionsvalue="id" fieldid="id" model="ngmodel" label="selectasyncapi" fieldname="selectasyncapi" i18n=""></zaa-async-api-select>', $plugin->renderCreate('id', 'ngmodel'));
        $this->assertSame('<zaa-async-api-select api="api/{{otherfield}}&amp;foo={{{bar}}}" optionslabel="title" optionsvalue="id" fieldid="id" model="ngmodel" label="selectasyncapi" fieldname="selectasyncapi" i18n=""></zaa-async-api-select>', $plugin->renderUpdate('id', 'ngmodel'));
    }

    public function testListApiAndDisableVariablize()
    {
        $plugin = new SelectAsyncApi([
            'name' => 'selectasyncapi',
            'alias' => 'selectasyncapi',
            'i18n' => false,
            'api' => 'api/{otherfield}',
            'listApi' => 'api/list',
            'variablizeApi' => false,
            'variablizeListApi' => false,
        ]);

        $this->assertSame('<async-value api="api/list" model="ngmodel" fields="[&quot;title&quot;]"></async-value>', $plugin->renderList('id', 'ngmodel'));
        $this->assertSame('<zaa-async-api-select api="api/{otherfield}" optionslabel="title" optionsvalue="id" fieldid="id" model="ngmodel" label="selectasyncapi" fieldname="selectasyncapi" i18n=""></zaa-async-api-select>', $plugin->renderCreate('id', 'ngmodel'));
        $this->assertSame('<zaa-async-api-select api="api/{otherfield}" optionslabel="title" optionsvalue="id" fieldid="id" model="ngmodel" label="selectasyncapi" fieldname="selectasyncapi" i18n=""></zaa-async-api-select>', $plugin->renderUpdate('id', 'ngmodel'));
    }
}
