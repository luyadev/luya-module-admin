<?php

namespace luya\admin\tests\data\modules;

use luya\admin\base\Module;

class ExtendPermissionModule extends Module
{
    public function extendPermissionApis()
    {
        return [
            ['api' => 'my-test-api', 'alias' => 'Foobar Alias'],
        ];
    }

    public function extendPermissionRoutes()
    {
        return [
            ['route' => 'foobar/route', 'alias' => 'Foobar Alias'],
        ];
    }
}
