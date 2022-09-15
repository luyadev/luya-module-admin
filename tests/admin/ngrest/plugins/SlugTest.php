<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminTestCase;
use luya\admin\ngrest\plugins\Slug;

class SlugTest extends AdminTestCase
{
    public function testI18nInputNgRestModel()
    {
        $plugin = new Slug([
            'alias' => 'slug',
            'name' => 'slug',
            'i18n' => true,
            'listener' => 'title'
        ]);

        $f = $plugin->renderCreate(1, "data.create.price['en']");

        $this->assertSame($f, '<zaa-slug listener="data.create.title[&#039;en&#039;]" fieldid="1" model="data.create.price[&#039;en&#039;]" label="slug" fieldname="slug" i18n="1"></zaa-slug>');
    }

    public function testNotI18nInputNgRestModelListener()
    {
        $plugin = new Slug([
            'alias' => 'slug',
            'name' => 'slug',
            'i18n' => true,
            'listener' => 'title'
        ]);

        $f = $plugin->renderCreate(1, "data.create.price");

        $this->assertSame($f, '<zaa-slug listener="data.create.title" fieldid="1" model="data.create.price" label="slug" fieldname="slug" i18n="1"></zaa-slug>');
    }
}
