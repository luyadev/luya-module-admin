<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminTestCase;
use luya\admin\ngrest\plugins\Sortable;

class SortableTest extends AdminTestCase
{
    public function testSortableRenderList()
    {
        $renderList = new Sortable([
            'alias' => 'slug',
            'name' => 'slug',
            'i18n' => true,
        ]);

        $html = $renderList->renderList(1, 'foobar');
        $this->assertNotEmpty($html);
    }
}
