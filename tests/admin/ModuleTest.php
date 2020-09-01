<?php

namespace admintests\admin;

use admintests\AdminModelTestCase;
use luya\admin\Module;

class ModuleTest extends AdminModelTestCase
{
    public function testGetJsTranslations()
    {
        $module = new Module('admin');

        $this->assertArrayHasKey(64, $module->getJsTranslationMessages());

        $module->setJsTranslations([]); // call for 100% coverage reason
    }
}