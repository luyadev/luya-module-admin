<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminTestCase;
use luya\admin\ngrest\plugins\Color;

class ColorTest extends AdminTestCase
{
    public function testRenderes()
    {
        $color = new Color(['name' => 'badge', 'alias' => 'raw']);

        $this->assertSame([
            0 => '<span style="background-color: {{model }}; width:12px; height:12px; border-radius:50%; display:inline-block" ng-if="model"></span>',
            1 => '<span ng-bind="model"></span>',
        ], $color->renderList('id', 'model'));

        $color = new Color(['name' => 'badge', 'alias' => 'raw', 'valueInList' => false]);

        $this->assertSame([
            0 => '<span style="background-color: {{model }}; width:12px; height:12px; border-radius:50%; display:inline-block" ng-if="model"></span>',
        ], $color->renderList('id', 'model'));

        $this->assertSame('<zaa-color fieldid="id" model="model" label="raw" fieldname="badge" i18n=""></zaa-color>', $color->renderUpdate('id', 'model'));
        $this->assertSame('<zaa-color fieldid="id" model="model" label="raw" fieldname="badge" i18n=""></zaa-color>', $color->renderCreate('id', 'model'));
    }
}
