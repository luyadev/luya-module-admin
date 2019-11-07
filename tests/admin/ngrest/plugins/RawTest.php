<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminTestCase;
use luya\admin\ngrest\plugins\Raw;

class RawTest extends AdminTestCase
{
    public function testHtmlNewLine()
    {
        $raw = new Raw(['name' => 'raw', 'alias' => 'raw', 'i18n' => false]);
        $this->assertSame('<div ng-bind-html="model | trustAsUnsafe"></div>', $raw->renderList('id', 'model'));
        $this->assertSame('<zaa-textarea fieldid="id" model="model" label="raw" fieldname="raw" i18n=""></zaa-textarea>', $raw->renderCreate('id', 'model'));
        $this->assertSame('<zaa-textarea fieldid="id" model="model" label="raw" fieldname="raw" i18n=""></zaa-textarea>', $raw->renderUpdate('id', 'model'));
    }
}
